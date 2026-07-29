import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	ToggleControl,
	SelectControl,
	Disabled,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

const RELATION_OPTIONS = [
	{ label: __( 'Latest products', 'noorpdp' ), value: 'latest' },
	{
		label: __( 'Related to current product', 'noorpdp' ),
		value: 'related',
	},
	{
		label: __( 'Upsells for current product', 'noorpdp' ),
		value: 'upsells',
	},
	{
		label: __( 'Cross-sells for current product', 'noorpdp' ),
		value: 'cross-sells',
	},
];

export default function Edit( { attributes, setAttributes } ) {
	const {
		relation,
		productsToShow,
		columns,
		order,
		orderBy,
		showImage,
		showPrice,
		showAddToCart,
		boxed,
		boxedWidth,
	} = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Query', 'noorpdp' ) }>
					<SelectControl
						label={ __( 'Products', 'noorpdp' ) }
						value={ relation }
						options={ RELATION_OPTIONS }
						onChange={ ( value ) =>
							setAttributes( { relation: value } )
						}
					/>
					<RangeControl
						label={ __( 'Number of products', 'noorpdp' ) }
						value={ productsToShow }
						onChange={ ( value ) =>
							setAttributes( { productsToShow: value } )
						}
						min={ 1 }
						max={ 12 }
					/>
					{ 'latest' === relation && (
						<>
							<SelectControl
								label={ __( 'Order by', 'noorpdp' ) }
								value={ orderBy }
								options={ [
									{
										label: __( 'Date', 'noorpdp' ),
										value: 'date',
									},
									{
										label: __( 'Title', 'noorpdp' ),
										value: 'title',
									},
									{
										label: __( 'Random', 'noorpdp' ),
										value: 'rand',
									},
								] }
								onChange={ ( value ) =>
									setAttributes( { orderBy: value } )
								}
							/>
							<SelectControl
								label={ __( 'Order', 'noorpdp' ) }
								value={ order }
								options={ [
									{
										label: __(
											'Descending',
											'noorpdp'
										),
										value: 'desc',
									},
									{
										label: __(
											'Ascending',
											'noorpdp'
										),
										value: 'asc',
									},
								] }
								onChange={ ( value ) =>
									setAttributes( { order: value } )
								}
							/>
						</>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Layout', 'noorpdp' ) }>
					<RangeControl
						label={ __( 'Columns', 'noorpdp' ) }
						value={ columns }
						onChange={ ( value ) =>
							setAttributes( { columns: value } )
						}
						min={ 1 }
						max={ 4 }
					/>
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

				<PanelBody title={ __( 'Content', 'noorpdp' ) }>
					<ToggleControl
						label={ __( 'Product image', 'noorpdp' ) }
						checked={ showImage }
						onChange={ ( value ) =>
							setAttributes( { showImage: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Price', 'noorpdp' ) }
						checked={ showPrice }
						onChange={ ( value ) =>
							setAttributes( { showPrice: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Add to cart button', 'noorpdp' ) }
						checked={ showAddToCart }
						onChange={ ( value ) =>
							setAttributes( { showAddToCart: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...useBlockProps() }>
				<Disabled>
					<ServerSideRender
						block="noorpdp/product-grid"
						attributes={ attributes }
					/>
				</Disabled>
			</div>
		</>
	);
}
