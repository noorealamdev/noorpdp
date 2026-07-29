import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import WooPlaceholder from '../../utils/woo-placeholder';

export default function Edit() {
	return (
		<div { ...useBlockProps() }>
			<WooPlaceholder
				icon="category"
				label={ __( 'Product Category', 'noorpdp' ) }
				instructions={ __(
					"Displays the current product's category as a small label. Preview appears on the product page.",
					'noorpdp'
				) }
			/>
		</div>
	);
}
