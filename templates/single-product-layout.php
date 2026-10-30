<?php
/**
 * Fallback single-product template that renders a NoorPDP Product
 * Layout in place of the theme's default, mirroring the structure of
 * WooCommerce's own templates/single-product.php so theme header, footer
 * and sidebar wrappers stay intact.
 *
 * @package NoorPDP
 */

use NoorPDP\Layouts\Resolver;

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

do_action( 'woocommerce_before_main_content' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce's own hook, kept so the theme's structural CSS still applies.

while ( have_posts() ) :
	the_post();

	global $product;
	$product = wc_get_product( get_the_ID() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- $product is WooCommerce's own global.

	// phpcs:ignore Squiz.PHP.CommentedOutCode.Found -- explanatory prose, not commented-out code; it just mentions a filename and hook names.
	// Mirrors WooCommerce's own wrapping hooks around content-single-product
	// (empty by default) so plugins that hook onto them — structured data,
	// "recently viewed" trackers, page-builder compatibility layers — still
	// fire around a NoorPDP layout exactly as they would around the
	// theme's default template.
	do_action( 'woocommerce_before_single_product' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce's own hook.

	$noorpdp_layout = Resolver::instance()->get_layout( get_the_ID() );

	if ( $noorpdp_layout ) {
		// The same `id="product-N" class="product type-product ..."` wrapper
		// WooCommerce's own content-single-product.php uses — the Product
		// Gallery block's variation-image swap relies on WooCommerce's own
		// `wc_find_gallery_root()`, which looks for `.woocommerce-product-gallery`
		// under the nearest `.product` ancestor; without this class here,
		// that lookup (and any theme CSS keyed off `.product`) would see
		// nothing to match, breaking what's otherwise native WooCommerce
		// behavior.
		echo '<div id="product-' . esc_attr( get_the_ID() ) . '" ';
		wc_product_class( 'noorpdp-layout', $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wc_product_class() escapes its own output.
		echo '>';
		// Mirrors core's own `the_content` filter order (do_blocks at
		// priority 9, do_shortcode at 11): shortcodes typed into a block's
		// text content — or left over from a migrated classic layout —
		// render instead of showing up as literal `[shortcode]` text.
		echo do_shortcode( do_blocks( $noorpdp_layout->post_content ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block content is sanitized like any other post content.
		echo '</div>';
	}

	do_action( 'woocommerce_after_single_product' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce's own hook.

endwhile;

do_action( 'woocommerce_after_main_content' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce's own hook.

do_action( 'woocommerce_sidebar' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce's own hook.

get_footer( 'shop' );
