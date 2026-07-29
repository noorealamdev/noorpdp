<?php
/**
 * Related Products block server render.
 *
 * @package NoorPDP
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

global $product;
$product = wc_get_product( get_the_ID() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- $product is WooCommerce's own global.

if ( ! $product ) {
	return;
}

// Self-contained max-width instead of relying on the theme/template to
// constrain block content — this block can land in templates that render
// blocks without any width-limiting ancestor.
$noorpdp_boxed       = ! isset( $attributes['boxed'] ) || (bool) $attributes['boxed'];
$noorpdp_boxed_width = isset( $attributes['boxedWidth'] ) ? absint( $attributes['boxedWidth'] ) : 1200;
?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped. ?>>
	<div
		class="noorpdp-related-products__inner<?php echo $noorpdp_boxed ? ' is-boxed' : ''; ?>"
		<?php if ( $noorpdp_boxed ) : ?>
			style="max-width:<?php echo esc_attr( $noorpdp_boxed_width ); ?>px"
		<?php endif; ?>
	>
		<?php
		woocommerce_related_products(
			array(
				'posts_per_page' => absint( $attributes['productsToShow'] ),
				'columns'        => absint( $attributes['columns'] ),
			)
		);
		?>
	</div>
</div>
