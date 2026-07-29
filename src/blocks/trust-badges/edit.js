import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	ToggleControl,
	RangeControl,
} from '@wordpress/components';
import { closeSmall } from '@wordpress/icons';
import IconPicker from '../../utils/icon-picker';

export default function Edit( { attributes, setAttributes } ) {
	const { items, boxed, boxedWidth } = attributes;
	const blockProps = useBlockProps();

	const updateItem = ( index, field, value ) => {
		const next = items.slice();
		next[ index ] = { ...next[ index ], [ field ]: value };
		setAttributes( { items: next } );
	};

	const addItem = () =>
		setAttributes( {
			items: [
				...items,
				{ icon: 'nt-shieldCheck', label: __( 'New badge', 'noorpdp' ) },
			],
		} );

	const removeItem = ( index ) =>
		setAttributes( { items: items.filter( ( _item, i ) => i !== index ) } );

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'noorpdp' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Boxed width', 'noorpdp' ) }
						checked={ boxed }
						onChange={ ( value ) => setAttributes( { boxed: value } ) }
						help={
							boxed
								? __(
										'Constrained to a max width and centered.',
										'noorpdp'
								  )
								: __(
										'Stretches the full width of its container.',
										'noorpdp'
								  )
						}
					/>
					{ boxed && (
						<RangeControl
							label={ __( 'Max width (px)', 'noorpdp' ) }
							value={ boxedWidth }
							onChange={ ( value ) =>
								setAttributes( { boxedWidth: value } )
							}
							min={ 480 }
							max={ 1800 }
							step={ 10 }
						/>
					) }
				</PanelBody>
			</InspectorControls>
			<ul
				className={
					'noorpdp-trust-badges__list' +
					( boxed ? ' is-boxed' : '' )
				}
				style={ boxed ? { maxWidth: boxedWidth } : undefined }
			>
				{ items.map( ( item, index ) => (
					<li className="noorpdp-trust-badges__item" key={ index }>
						<Button
							className="noorpdp-trust-badges__remove"
							icon={ closeSmall }
							label={ __( 'Remove badge', 'noorpdp' ) }
							onClick={ () => removeItem( index ) }
						/>
						<IconPicker
							value={ item.icon }
							allowNone
							onChange={ ( value ) =>
								updateItem( index, 'icon', value )
							}
						/>
						<RichText
							tagName="span"
							className="noorpdp-trust-badges__label"
							value={ item.label }
							onChange={ ( value ) =>
								updateItem( index, 'label', value )
							}
							placeholder={ __( 'Label', 'noorpdp' ) }
						/>
					</li>
				) ) }
			</ul>
			<Button variant="secondary" onClick={ addItem }>
				{ __( 'Add badge', 'noorpdp' ) }
			</Button>
		</div>
	);
}
