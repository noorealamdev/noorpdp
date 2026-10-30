import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import WooPlaceholder from '../../utils/woo-placeholder';

export default function Edit() {
	return (
		<div { ...useBlockProps() }>
			<WooPlaceholder
				icon="star-filled"
				label={ __( 'Product Rating', 'noorpdp' ) }
				instructions={ __(
					"Displays the current product's star rating. Preview appears on the product page.",
					'noorpdp'
				) }
			/>
		</div>
	);
}
