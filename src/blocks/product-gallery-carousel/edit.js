import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import WooPlaceholder from '../../utils/woo-placeholder';

export default function Edit() {
	return (
		<div { ...useBlockProps() }>
			<WooPlaceholder
				icon="images-alt2"
				label={ __( 'Product Gallery', 'noorpdp' ) }
				instructions={ __(
					"The current product's own image gallery, rendered the same way WooCommerce's own single-product page would. Preview (including zoom/lightbox, if the theme supports them) appears on the product page.",
					'noorpdp'
				) }
			/>
		</div>
	);
}
