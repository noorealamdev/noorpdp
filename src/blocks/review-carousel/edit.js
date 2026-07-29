import { __, sprintf } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	ComboboxControl,
	CheckboxControl,
	Spinner,
	Notice,
	ToggleControl,
	RangeControl,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { decodeEntities } from '@wordpress/html-entities';
import WooPlaceholder from '../../utils/woo-placeholder';

export default function Edit( { attributes, setAttributes } ) {
	const { productId, selectedReviews, boxed, boxedWidth } = attributes;
	const blockProps = useBlockProps();

	const [ productSearch, setProductSearch ] = useState( '' );
	const [ productOptions, setProductOptions ] = useState( [] );

	const [ reviews, setReviews ] = useState( [] );
	const [ reviewsLoading, setReviewsLoading ] = useState( false );

	useEffect( () => {
		let cancelled = false;

		apiFetch( {
			path: addQueryArgs( '/wp/v2/product', {
				search: productSearch,
				per_page: 20,
				_fields: 'id,title',
			} ),
		} )
			.then( ( results ) => {
				if ( cancelled ) {
					return;
				}

				setProductOptions(
					( Array.isArray( results ) ? results : [] ).map(
						( product ) => ( {
							value: String( product.id ),
							label: decodeEntities(
								product.title?.rendered || `#${ product.id }`
							),
						} )
					)
				);
			} )
			.catch( () => {
				if ( ! cancelled ) {
					setProductOptions( [] );
				}
			} );

		return () => {
			cancelled = true;
		};
	}, [ productSearch ] );

	useEffect( () => {
		if ( ! productId ) {
			setReviews( [] );
			return;
		}

		let cancelled = false;
		setReviewsLoading( true );

		apiFetch( { path: `/noorpdp/v1/product-reviews/${ productId }/list` } )
			.then( ( results ) => {
				if ( ! cancelled ) {
					setReviews( Array.isArray( results ) ? results : [] );
				}
			} )
			.catch( () => {
				if ( ! cancelled ) {
					setReviews( [] );
				}
			} )
			.finally( () => {
				if ( ! cancelled ) {
					setReviewsLoading( false );
				}
			} );

		return () => {
			cancelled = true;
		};
	}, [ productId ] );

	const toggleReview = ( id ) => {
		const next = selectedReviews.includes( id )
			? selectedReviews.filter( ( existing ) => existing !== id )
			: [ ...selectedReviews, id ];

		setAttributes( { selectedReviews: next } );
	};

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Reviews', 'noorpdp' ) } initialOpen>
					<ComboboxControl
						label={ __( 'Product', 'noorpdp' ) }
						value={ productId ? String( productId ) : null }
						options={ productOptions }
						onFilterValueChange={ setProductSearch }
						onChange={ ( value ) =>
							setAttributes( {
								productId: value ? Number( value ) : 0,
								selectedReviews: [],
							} )
						}
						help={ __(
							'Search by product name, then pick reviews below.',
							'noorpdp'
						) }
					/>

					{ !! productId && reviewsLoading && <Spinner /> }

					{ !! productId && ! reviewsLoading && ! reviews.length && (
						<Notice status="warning" isDismissible={ false }>
							{ __(
								'This product has no reviews yet.',
								'noorpdp'
							) }
						</Notice>
					) }

					{ !! productId &&
						! reviewsLoading &&
						reviews.map( ( review ) => (
							<CheckboxControl
								key={ review.id }
								label={ `${ '★'.repeat(
									Math.round( review.rating )
								) } ${ review.author } — ${ review.excerpt }` }
								checked={ selectedReviews.includes(
									review.id
								) }
								onChange={ () => toggleReview( review.id ) }
							/>
						) ) }
				</PanelBody>
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
			<WooPlaceholder
				icon="slides"
				label={ __( 'Review Carousel', 'noorpdp' ) }
				instructions={
					selectedReviews.length
						? sprintf(
								/* translators: %d: number of selected reviews. */
								__(
									'%d review(s) selected. Preview appears on the front end.',
									'noorpdp'
								),
								selectedReviews.length
						  )
						: __(
								'Pick a product and select reviews in the block settings panel.',
								'noorpdp'
						  )
				}
			/>
		</div>
	);
}
