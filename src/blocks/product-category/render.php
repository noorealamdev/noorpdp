<?php
/**
 * Product Category block server render.
 *
 * @package NoorPDP
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$noorpdp_product_id = get_the_ID();
$noorpdp_terms      = get_the_terms( $noorpdp_product_id, 'product_cat' );

if ( empty( $noorpdp_terms ) || is_wp_error( $noorpdp_terms ) ) {
	return;
}

$noorpdp_term = $noorpdp_terms[0];

/*
 * This block ships with an opinionated "kicker label" default look (small,
 * uppercase, letter-spaced, muted) — applied as a conditional inline style
 * rather than a plain CSS class, since a class-based default can never be
 * fully "cleared": once the user changes a value via the Typography/Color
 * panel, that panel's own inline style naturally wins over ours; skipping
 * each default the moment the user has set their own keeps every one of
 * these controls genuinely editable instead of silently dead.
 */
$noorpdp_has_own_font_size      = ! empty( $attributes['fontSize'] ) || ! empty( $attributes['style']['typography']['fontSize'] );
$noorpdp_has_own_letter_spacing = ! empty( $attributes['style']['typography']['letterSpacing'] );
$noorpdp_has_own_text_transform = ! empty( $attributes['style']['typography']['textTransform'] );
$noorpdp_has_own_font_weight    = ! empty( $attributes['style']['typography']['fontWeight'] );
$noorpdp_has_own_text_color     = ! empty( $attributes['textColor'] ) || ! empty( $attributes['style']['color']['text'] );

$noorpdp_default_style = '';

if ( ! $noorpdp_has_own_font_size ) {
	$noorpdp_default_style .= 'font-size:0.8rem;';
}

if ( ! $noorpdp_has_own_letter_spacing ) {
	$noorpdp_default_style .= 'letter-spacing:0.06em;';
}

if ( ! $noorpdp_has_own_text_transform ) {
	$noorpdp_default_style .= 'text-transform:uppercase;';
}

if ( ! $noorpdp_has_own_font_weight ) {
	$noorpdp_default_style .= 'font-weight:600;';
}

if ( ! $noorpdp_has_own_text_color ) {
	$noorpdp_default_style .= 'color:#6b7280;';
}

$noorpdp_wrapper = get_block_wrapper_attributes(
	$noorpdp_default_style ? array( 'style' => $noorpdp_default_style ) : array()
);
?>
<div <?php echo $noorpdp_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped. ?>>
	<a href="<?php echo esc_url( get_term_link( $noorpdp_term ) ); ?>">
		<?php echo esc_html( $noorpdp_term->name ); ?>
	</a>
</div>
