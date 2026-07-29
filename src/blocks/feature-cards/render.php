<?php
/**
 * Feature Cards block server render.
 *
 * @package NoorPDP
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$noorpdp_items = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();

if ( empty( $noorpdp_items ) ) {
	return;
}

$noorpdp_columns = ! empty( $attributes['columns'] ) ? max( 1, min( 4, (int) $attributes['columns'] ) ) : 1;

/*
 * Dark-card reference defaults — same values as edit.js's DEFAULT_CARD_*
 * constants (PHP/JS can't share a module, so these are kept in sync by
 * hand). Applied as conditional inline styles per card, not a CSS class
 * default: once the merchant clears a value via the block's own "Card"
 * panel, there's no inline override left to beat a class rule with, so a
 * class-based default would keep showing regardless of intent (the exact
 * issue already found and fixed for Trust Badges this session).
 */
$noorpdp_card_background = ! empty( $attributes['cardBackground'] ) ? $attributes['cardBackground'] : '#1c1c1c';
$noorpdp_card_radius     = ! empty( $attributes['cardRadius'] ) ? (int) $attributes['cardRadius'] : 20;
$noorpdp_card_padding    = ! empty( $attributes['cardPadding'] ) ? (int) $attributes['cardPadding'] : 28;

$noorpdp_card_style = sprintf(
	'background-color:%s;border-radius:%dpx;padding:%dpx;',
	$noorpdp_card_background,
	$noorpdp_card_radius,
	$noorpdp_card_padding
);

/*
 * The dark card background needs a paired light text default — plain
 * `color: inherit` on the heading/text would otherwise pick up whatever
 * ambient color happens to be active on the page, which has no guaranteed
 * contrast against a dark card. Only applied when the merchant hasn't set
 * their own Text Color (their choice always wins once set, same
 * conditional-inline-default pattern as the card background above).
 */
$noorpdp_has_own_text_color = ! empty( $attributes['textColor'] ) || ! empty( $attributes['style']['color']['text'] );
$noorpdp_text_style         = $noorpdp_has_own_text_color ? '' : 'color:#fff;';

// Self-contained max-width instead of relying on the theme/template to
// constrain block content — applied to the inner `__list` grid, not this
// block's own wrapper, since the wrapper only carries the columns CSS
// variable and isn't itself the grid container.
$noorpdp_boxed       = ! isset( $attributes['boxed'] ) || (bool) $attributes['boxed'];
$noorpdp_boxed_width = isset( $attributes['boxedWidth'] ) ? absint( $attributes['boxedWidth'] ) : 1200;

$noorpdp_wrapper = get_block_wrapper_attributes(
	array( 'style' => '--noorpdp-feature-cards-columns:' . $noorpdp_columns . ';' )
);
?>
<div <?php echo $noorpdp_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped. ?>>
	<div
		class="noorpdp-feature-cards__list<?php echo $noorpdp_boxed ? ' is-boxed' : ''; ?>"
		<?php if ( $noorpdp_boxed ) : ?>
			style="max-width:<?php echo esc_attr( $noorpdp_boxed_width ); ?>px"
		<?php endif; ?>
	>
		<?php foreach ( $noorpdp_items as $noorpdp_item ) : ?>
			<div class="noorpdp-feature-cards__item" style="<?php echo esc_attr( $noorpdp_card_style ); ?>">
				<div class="noorpdp-feature-cards__heading" style="<?php echo esc_attr( $noorpdp_text_style ); ?>"><?php echo wp_kses_post( $noorpdp_item['heading'] ?? '' ); ?></div>
				<div class="noorpdp-feature-cards__text" style="<?php echo esc_attr( $noorpdp_text_style ); ?>"><?php echo wp_kses_post( $noorpdp_item['text'] ?? '' ); ?></div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
