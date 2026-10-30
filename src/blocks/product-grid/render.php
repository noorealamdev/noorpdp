<?php
/**
 * Product Grid block server render.
 *
 * @package NoorPDP
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content (unused, dynamic block).
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$noorpdp_relation = in_array( $attributes['relation'], array( 'latest', 'related', 'upsells', 'cross-sells' ), true )
	? $attributes['relation']
	: 'latest';
$noorpdp_current  = wc_get_product( get_the_ID() );
$noorpdp_to_show  = absint( $attributes['productsToShow'] );

if ( 'related' === $noorpdp_relation && $noorpdp_current ) {
	$noorpdp_ids = wc_get_related_products( $noorpdp_current->get_id(), $noorpdp_to_show );
} elseif ( 'upsells' === $noorpdp_relation && $noorpdp_current ) {
	$noorpdp_ids = array_slice( $noorpdp_current->get_upsell_ids(), 0, $noorpdp_to_show );
} elseif ( 'cross-sells' === $noorpdp_relation && $noorpdp_current ) {
	$noorpdp_ids = array_slice( $noorpdp_current->get_cross_sell_ids(), 0, $noorpdp_to_show );
} else {
	$noorpdp_ids = array();
}

if ( 'latest' !== $noorpdp_relation && empty( $noorpdp_ids ) ) {
	// Related/upsell products requested but none exist for the current product.
	$noorpdp_query = new WP_Query();
} elseif ( 'latest' !== $noorpdp_relation ) {
	$noorpdp_query = new WP_Query(
		array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'post__in'            => $noorpdp_ids,
			'orderby'             => 'post__in',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
} else {
	$noorpdp_query = new WP_Query(
		array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => $noorpdp_to_show,
			'order'               => 'asc' === strtolower( $attributes['order'] ) ? 'ASC' : 'DESC',
			'orderby'             => in_array( $attributes['orderBy'], array( 'date', 'title', 'rand' ), true ) ? $attributes['orderBy'] : 'date',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
}

// Self-contained max-width instead of relying on the theme/template to
// constrain block content — applied to this block's own wrapper (rather
// than an inner div, like other blocks in this plugin) because that
// wrapper is also the CSS grid container itself; an extra nested div would
// separate `display: grid` from the item children it needs to apply to.
$noorpdp_boxed       = ! isset( $attributes['boxed'] ) || (bool) $attributes['boxed'];
$noorpdp_boxed_width = isset( $attributes['boxedWidth'] ) ? absint( $attributes['boxedWidth'] ) : 1200;

$noorpdp_wrapper_args = array( 'class' => 'columns-' . absint( $attributes['columns'] ) );

if ( $noorpdp_boxed ) {
	$noorpdp_wrapper_args['class'] .= ' is-boxed';
	$noorpdp_wrapper_args['style']  = 'max-width:' . $noorpdp_boxed_width . 'px';
}

$noorpdp_wrapper = get_block_wrapper_attributes( $noorpdp_wrapper_args );
?>
<div <?php echo $noorpdp_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped. ?>>
	<?php if ( ! $noorpdp_query->have_posts() ) : ?>
		<p class="noorpdp-product-grid__empty"><?php esc_html_e( 'No products found.', 'noorpdp' ); ?></p>
	<?php endif; ?>

	<?php
	while ( $noorpdp_query->have_posts() ) :
		$noorpdp_query->the_post();

		$noorpdp_product = wc_get_product( get_the_ID() );

		if ( ! $noorpdp_product ) {
			continue;
		}
		?>
		<div class="noorpdp-product-grid__item">
			<?php if ( $attributes['showImage'] ) : ?>
				<a class="noorpdp-product-grid__image" href="<?php the_permalink(); ?>">
					<?php echo wp_kses_post( $noorpdp_product->get_image( 'medium_large' ) ); ?>
				</a>
			<?php endif; ?>

			<h3 class="noorpdp-product-grid__title">
				<a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a>
			</h3>

			<?php if ( $attributes['showPrice'] ) : ?>
				<div class="noorpdp-product-grid__price">
					<?php echo wp_kses_post( $noorpdp_product->get_price_html() ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $attributes['showAddToCart'] ) : ?>
				<div class="noorpdp-product-grid__add-to-cart">
					<?php
					echo wp_kses_post(
						apply_filters(
							'woocommerce_loop_add_to_cart_link', // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce's own filter.
							sprintf(
								'<a href="%s" data-quantity="1" class="button product_type_%s add_to_cart_button ajax_add_to_cart" data-product_id="%s" aria-label="%s" rel="nofollow">%s</a>',
								esc_url( $noorpdp_product->add_to_cart_url() ),
								esc_attr( $noorpdp_product->get_type() ),
								esc_attr( $noorpdp_product->get_id() ),
								esc_attr( $noorpdp_product->add_to_cart_description() ),
								esc_html( $noorpdp_product->add_to_cart_text() )
							),
							$noorpdp_product,
							array()
						)
					);
					?>
				</div>
			<?php endif; ?>
		</div>
	<?php endwhile; ?>
</div>
<?php
wp_reset_postdata();
