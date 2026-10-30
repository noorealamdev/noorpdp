<?php
/**
 * Product Gallery block server render.
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
?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped. ?>>
	<?php
	/*
	 * WooCommerce's own `single-product/product-image.php`, exactly as
	 * `content-single-product.php` would render it — not a NoorPDP
	 * reimplementation. This is what makes it "native": the theme's own
	 * gallery CSS applies unmodified, zoom/lightbox/flexslider come from
	 * WooCommerce's own scripts (enqueued independently of this block,
	 * gated on theme support for each), and — because this calls the same
	 * template WooCommerce's variation JS already knows how to find and
	 * swap (`wc_find_gallery_root()` looks for `.woocommerce-product-gallery`
	 * under the nearest `.product` ancestor, which is why the Layout
	 * wrapper in templates/single-product-layout.php carries that class) —
	 * a variation with its own gallery_image_ids swaps in its own full
	 * gallery on selection, the same as it would on a vanilla product page.
	 */
	woocommerce_show_product_images();
	?>
</div>
