import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl } from '@wordpress/components';
import WooPlaceholder from '../../utils/woo-placeholder';

export default function Edit( { attributes, setAttributes } ) {
	const { boxed, boxedWidth } = attributes;

	return (
		<div { ...useBlockProps() }>
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
			<WooPlaceholder
				icon="star-filled"
				label={ __( 'Product Reviews', 'noorpdp' ) }
				instructions={ __(
					"A rating summary and review grid for the current product. Preview appears on the product page.",
					'noorpdp'
				) }
			/>
		</div>
	);
}
