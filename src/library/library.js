import { __ } from '@wordpress/i18n';
import { useState, useEffect, useRef, createPortal } from '@wordpress/element';
import {
	Button,
	Modal,
	TabPanel,
	Spinner,
	Notice,
	TextControl,
	SelectControl,
} from '@wordpress/components';
import { parse, cloneBlock } from '@wordpress/blocks';
import { useDispatch, useSelect, select } from '@wordpress/data';
import { layout, image as imagePlaceholder } from '@wordpress/icons';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

const LAYOUT_POST_TYPE = 'noorpdp_layout';

// Delay, in ms, before a search keystroke triggers a new request.
const SEARCH_DEBOUNCE = 300;

// Renders children into a container appended to the editor header toolbar.
function ToolbarPortal( { children } ) {
	const [ container, setContainer ] = useState( null );

	useEffect( () => {
		const toolbar =
			document.querySelector( '.editor-header__toolbar' ) ||
			document.querySelector( '.edit-post-header__toolbar' );

		if ( ! toolbar ) {
			return;
		}

		const wrap = document.createElement( 'div' );
		wrap.className = 'noorpdp-library__toolbar-wrap';
		toolbar.appendChild( wrap );
		setContainer( wrap );

		return () => {
			toolbar.removeChild( wrap );
		};
	}, [] );

	return container ? createPortal( children, container ) : null;
}

// A single template card. Shows a static thumbnail (or a placeholder) so
// the grid stays cheap to render even with hundreds of templates; the full
// block content is only fetched when the card is applied.
function TemplateCard( { template, isBusy, onSelect } ) {
	return (
		<div className="noorpdp-library__card">
			<button
				type="button"
				className="noorpdp-library__card-preview"
				onClick={ () => onSelect( template ) }
				aria-label={ template.title }
				disabled={ isBusy }
			>
				{ template.thumbnail ? (
					<img
						src={ template.thumbnail }
						alt=""
						loading="lazy"
					/>
				) : (
					<span className="noorpdp-library__card-placeholder">
						{ imagePlaceholder }
					</span>
				) }
				{ isBusy && (
					<span className="noorpdp-library__card-spinner">
						<Spinner />
					</span>
				) }
				<span className="noorpdp-library__card-action">
					{ template.type === 'layout'
						? __( 'Use Layout', 'noorpdp' )
						: __( 'Insert Section', 'noorpdp' ) }
				</span>
			</button>
			<div className="noorpdp-library__card-title">
				{ template.title }
			</div>
			{ template.description && (
				<div className="noorpdp-library__card-description">
					{ template.description }
				</div>
			) }
		</div>
	);
}

// A small modal for exporting the Layout currently open in the editor as a
// downloadable .json template file — entirely client-side, no server round
// trip, since the file is meant to be manually dropped into the plugin's
// templates/ folder to ship as a bundled premade template. Reads the live
// editor content (including unsaved edits) at export time.
function ExportTemplateModal( { onClose } ) {
	const [ title, setTitle ] = useState( '' );
	const [ type, setType ] = useState( 'layout' );
	const [ category, setCategory ] = useState( '' );
	const [ error, setError ] = useState( '' );

	const handleExport = () => {
		if ( ! title.trim() ) {
			setError( __( 'Please enter a title.', 'noorpdp' ) );
			return;
		}

		const template = {
			title,
			description: '',
			type,
			category: category || type,
			content: select( 'core/editor' ).getEditedPostContent(),
		};

		const slug =
			title
				.toLowerCase()
				.trim()
				.replace( /[^a-z0-9]+/g, '-' )
				.replace( /^-+|-+$/g, '' ) || 'template';

		const blob = new Blob( [ JSON.stringify( template, null, 2 ) ], {
			type: 'application/json',
		} );
		const url = URL.createObjectURL( blob );
		const link = document.createElement( 'a' );

		link.href = url;
		link.download = `${ slug }.json`;
		document.body.appendChild( link );
		link.click();
		document.body.removeChild( link );
		URL.revokeObjectURL( url );

		onClose();
	};

	return (
		<Modal
			title={ __( 'Export as JSON', 'noorpdp' ) }
			onRequestClose={ onClose }
			className="noorpdp-library__export-modal"
		>
			{ error && (
				<Notice status="error" isDismissible={ false }>
					{ error }
				</Notice>
			) }
			<TextControl
				label={ __( 'Title', 'noorpdp' ) }
				value={ title }
				onChange={ setTitle }
			/>
			<SelectControl
				label={ __( 'Type', 'noorpdp' ) }
				value={ type }
				onChange={ setType }
				options={ [
					{ value: 'layout', label: __( 'Full Layout', 'noorpdp' ) },
					{ value: 'section', label: __( 'Section', 'noorpdp' ) },
				] }
			/>
			<TextControl
				label={ __( 'Category (optional)', 'noorpdp' ) }
				value={ category }
				onChange={ setCategory }
				placeholder={ __( 'e.g. jewelry', 'noorpdp' ) }
			/>
			<Button variant="primary" onClick={ handleExport }>
				{ __( 'Download JSON', 'noorpdp' ) }
			</Button>
		</Modal>
	);
}

/**
 * Returns true when the post has no meaningful content yet.
 */
function isPostEmpty() {
	const blocks = select( 'core/block-editor' ).getBlocks();

	return blocks.every(
		( block ) =>
			block.name === 'core/paragraph' &&
			( ! block.attributes.content ||
				block.attributes.content.length === 0 )
	);
}

/**
 * The Templates button in the editor top bar plus the library modal.
 *
 * Only rendered while editing a Product Layout, since that is the only
 * post type NoorPDP's premade layouts and sections apply to.
 */
export default function Library() {
	const [ isOpen, setOpen ] = useState( false );
	const [ isExportOpen, setExportOpen ] = useState( false );
	const [ type, setType ] = useState( 'layout' );
	const [ category, setCategory ] = useState( '' );
	const [ search, setSearch ] = useState( '' );
	const [ debouncedSearch, setDebouncedSearch ] = useState( '' );
	const [ templates, setTemplates ] = useState( null );
	const [ categoryOptions, setCategoryOptions ] = useState( [] );
	const [ hasError, setHasError ] = useState( false );
	const [ applyingName, setApplyingName ] = useState( null );
	const { resetBlocks, insertBlocks } = useDispatch( 'core/block-editor' );
	const importInputRef = useRef( null );

	const isLayoutEditor = useSelect(
		( selectStore ) =>
			selectStore( 'core/editor' )?.getCurrentPostType() ===
			LAYOUT_POST_TYPE,
		[]
	);

	// Debounce the search input before it drives a request.
	useEffect( () => {
		const timeout = setTimeout( () => {
			setDebouncedSearch( search );
		}, SEARCH_DEBOUNCE );

		return () => clearTimeout( timeout );
	}, [ search ] );

	// Fetch the filtered template list whenever the modal is open and the
	// active filters change. Payloads are metadata-only (no block content),
	// so this stays cheap even with hundreds of templates.
	useEffect( () => {
		if ( ! isOpen ) {
			return;
		}

		setHasError( false );

		const args = { type };

		if ( category ) {
			args.category = category;
		}

		if ( debouncedSearch ) {
			args.search = debouncedSearch;
		}

		apiFetch( { path: addQueryArgs( '/noorpdp/v1/templates', args ) } )
			.then( ( result ) =>
				setTemplates( Array.isArray( result ) ? result : [] )
			)
			.catch( () => setHasError( true ) );
	}, [ isOpen, type, category, debouncedSearch ] );

	// Separately fetch the unfiltered templates for the active type, purely
	// to populate the category dropdown's options.
	useEffect( () => {
		if ( ! isOpen ) {
			return;
		}

		apiFetch( { path: addQueryArgs( '/noorpdp/v1/templates', { type } ) } )
			.then( ( result ) => {
				const found = new Set();

				( Array.isArray( result ) ? result : [] ).forEach( ( template ) => {
					if ( template.category ) {
						found.add( template.category );
					}
				} );

				setCategoryOptions( Array.from( found ).sort() );
			} )
			.catch( () => {} );
	}, [ isOpen, type ] );

	if ( ! isLayoutEditor ) {
		return null;
	}

	const applyTemplate = ( template, blocks ) => {
		if (
			template.type === 'layout' &&
			! isPostEmpty() &&
			// eslint-disable-next-line no-alert
			! window.confirm(
				__(
					'This will replace the current layout content. Continue?',
					'noorpdp'
				)
			)
		) {
			return;
		}

		// Clone so inserting the same template twice never reuses client ids.
		const toInsert = blocks.map( ( block ) => cloneBlock( block ) );

		if ( template.type === 'layout' ) {
			resetBlocks( toInsert );
		} else {
			insertBlocks( toInsert );
		}

		setOpen( false );
	};

	// Fetches a template's full content on demand, since the list payload
	// intentionally omits it to stay lightweight at scale.
	const fetchFullTemplate = ( name ) =>
		apiFetch( { path: `/noorpdp/v1/templates/${ name }` } );

	const handleSelect = ( template ) => {
		setApplyingName( template.name );

		fetchFullTemplate( template.name )
			.then( ( full ) => {
				applyTemplate( full, parse( full.content ) );
			} )
			.catch( () => setHasError( true ) )
			.finally( () => setApplyingName( null ) );
	};

	// Reads a locally-picked .json template file (e.g. one previously
	// downloaded via "Export as JSON") and applies it the same way a
	// library template is applied — entirely client-side, no server
	// round trip.
	const handleImportFile = ( event ) => {
		const file = event.target.files && event.target.files[ 0 ];

		// Reset so picking the same file again still fires a change event.
		event.target.value = '';

		if ( ! file ) {
			return;
		}

		file.text()
			.then( ( text ) => {
				const data = JSON.parse( text );

				if (
					! data ||
					typeof data.content !== 'string' ||
					! [ 'layout', 'section' ].includes( data.type )
				) {
					throw new Error( 'Invalid template file' );
				}

				applyTemplate( data, parse( data.content ) );
			} )
			.catch( () => {
				// eslint-disable-next-line no-alert
				window.alert(
					__(
						"That file doesn't look like a valid NoorPDP JSON template.",
						'noorpdp'
					)
				);
			} );
	};

	const tabs = [
		{ name: 'layout', title: __( 'Full Layouts', 'noorpdp' ) },
		{ name: 'section', title: __( 'Sections', 'noorpdp' ) },
	];

	const renderContent = () => {
		if ( hasError ) {
			return (
				<Notice status="error" isDismissible={ false }>
					{ __(
						'The template library could not be loaded. Please try again.',
						'noorpdp'
					) }
				</Notice>
			);
		}

		return (
			<>
				<div className="noorpdp-library__toolbar">
					<TextControl
						__nextHasNoMarginBottom
						className="noorpdp-library__search"
						value={ search }
						onChange={ setSearch }
						placeholder={ __( 'Search templates…', 'noorpdp' ) }
					/>
					<SelectControl
						__nextHasNoMarginBottom
						className="noorpdp-library__category"
						value={ category }
						onChange={ setCategory }
						options={ [
							{ value: '', label: __( 'All categories', 'noorpdp' ) },
							...categoryOptions.map( ( slug ) => ( {
								value: slug,
								label: slug,
							} ) ),
						] }
					/>
				</div>

				{ ! templates ? (
					<div className="noorpdp-library__loading">
						<Spinner />
					</div>
				) : (
					<div className="noorpdp-library__grid">
						{ templates.map( ( template ) => (
							<TemplateCard
								key={ template.name }
								template={ template }
								isBusy={ applyingName === template.name }
								onSelect={ handleSelect }
							/>
						) ) }
					</div>
				) }
			</>
		);
	};

	return (
		<>
			<ToolbarPortal>
				<Button
					variant="primary"
					size="compact"
					icon={ layout }
					onClick={ () => setOpen( true ) }
				>
					{ __( 'NoorPDP', 'noorpdp' ) }
				</Button>
				<Button
					variant="secondary"
					size="compact"
					onClick={ () => setExportOpen( true ) }
				>
					{ __( 'Export as JSON', 'noorpdp' ) }
				</Button>
				<Button
					variant="secondary"
					size="compact"
					onClick={ () => importInputRef.current?.click() }
				>
					{ __( 'Import JSON', 'noorpdp' ) }
				</Button>
				<input
					ref={ importInputRef }
					type="file"
					accept="application/json,.json"
					className="noorpdp-library__import-input"
					onChange={ handleImportFile }
				/>
			</ToolbarPortal>

			{ isExportOpen && (
				<ExportTemplateModal onClose={ () => setExportOpen( false ) } />
			) }

			{ isOpen && (
				<Modal
					title={ __(
						'NoorPDP Layout Library',
						'noorpdp'
					) }
					onRequestClose={ () => setOpen( false ) }
					className="noorpdp-library__modal"
					isFullScreen
				>
					<TabPanel
						className="noorpdp-library__tabs"
						tabs={ tabs }
						onSelect={ ( tabName ) => {
							setType( tabName );
							setCategory( '' );
						} }
					>
						{ () => renderContent() }
					</TabPanel>
				</Modal>
			) }
		</>
	);
}
