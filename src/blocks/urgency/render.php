<?php
/**
 * Urgency & Countdown block server render.
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

$noorpdp_type        = ! empty( $attributes['type'] ) ? $attributes['type'] : 'stock';
$noorpdp_boxed       = ! isset( $attributes['boxed'] ) || (bool) $attributes['boxed'];
$noorpdp_boxed_width = isset( $attributes['boxedWidth'] ) ? absint( $attributes['boxedWidth'] ) : 800;

/*
 * Background/text/border/padding all come from the block's own supports
 * (Color, Border, Dimensions panels) via get_block_wrapper_attributes() —
 * nothing here hardcodes a look. `is-boxed`/max-width is merged into that
 * same single wrapper (not a separately-styled inner div) so the merchant's
 * Inspector choices always land on the one visible surface.
 */
$noorpdp_wrapper = get_block_wrapper_attributes(
	array(
		'class' => $noorpdp_boxed ? 'is-boxed' : '',
		'style' => $noorpdp_boxed ? 'max-width:' . $noorpdp_boxed_width . 'px;' : '',
	)
);

if ( 'stock' === $noorpdp_type ) {
	// Honest scarcity only: relies entirely on the product's own real
	// stock-management data, never a fabricated/estimated number.
	if ( ! $product->managing_stock() || ! $product->is_in_stock() ) {
		return;
	}

	$noorpdp_stock     = $product->get_stock_quantity();
	$noorpdp_threshold = isset( $attributes['stockThreshold'] ) ? absint( $attributes['stockThreshold'] ) : 10;

	if ( null === $noorpdp_stock || $noorpdp_stock > $noorpdp_threshold ) {
		return;
	}

	$noorpdp_template  = ! empty( $attributes['stockMessage'] )
		? $attributes['stockMessage']
		: __( 'Only {stock} left in stock — order soon!', 'noorpdp' );
	$noorpdp_message   = str_replace( '{stock}', $noorpdp_stock, $noorpdp_template );
	$noorpdp_show_icon = ! isset( $attributes['showIcon'] ) || (bool) $attributes['showIcon'];
	?>
	<div <?php echo $noorpdp_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped. ?>>
		<?php if ( $noorpdp_show_icon ) : ?>
			<svg class="noorpdp-urgency__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2 1 21h22L12 2zm0 5.5 7.53 12.5H4.47L12 7.5zM11 10v5h2v-5h-2zm0 6.5v2h2v-2h-2z"/></svg>
		<?php endif; ?>
		<span class="noorpdp-urgency__message"><?php echo esc_html( $noorpdp_message ); ?></span>
	</div>
	<?php
	return;
}

// Countdown type: only ever counts down to a real, merchant-controlled
// deadline (the product's own scheduled sale end date, or a date the
// merchant explicitly sets) — never a fake/rolling "limited time" timer.
$noorpdp_source   = ! empty( $attributes['countdownSource'] ) ? $attributes['countdownSource'] : 'sale';
$noorpdp_deadline = null;

if ( 'sale' === $noorpdp_source ) {
	$noorpdp_sale_to = $product->get_date_on_sale_to();

	if ( $noorpdp_sale_to && $product->is_on_sale() ) {
		$noorpdp_deadline = $noorpdp_sale_to->getTimestamp();
	}
} elseif ( ! empty( $attributes['customDate'] ) ) {
	try {
		$noorpdp_custom   = new DateTime( $attributes['customDate'], wp_timezone() );
		$noorpdp_deadline = $noorpdp_custom->getTimestamp();
	} catch ( Exception $e ) {
		$noorpdp_deadline = null;
	}
}

if ( null === $noorpdp_deadline ) {
	return;
}

$noorpdp_hide_when_expired = ! isset( $attributes['hideWhenExpired'] ) || (bool) $attributes['hideWhenExpired'];
$noorpdp_is_expired        = $noorpdp_deadline <= time();

if ( $noorpdp_is_expired && $noorpdp_hide_when_expired ) {
	return;
}

$noorpdp_label           = ! empty( $attributes['countdownLabel'] ) ? $attributes['countdownLabel'] : __( 'Sale ends in:', 'noorpdp' );
$noorpdp_expired_message = ! empty( $attributes['expiredMessage'] ) ? $attributes['expiredMessage'] : __( 'This offer has ended.', 'noorpdp' );
?>
<div <?php echo $noorpdp_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped. ?>>
	<?php if ( $noorpdp_is_expired ) : ?>
		<span class="noorpdp-urgency__message"><?php echo esc_html( $noorpdp_expired_message ); ?></span>
	<?php else : ?>
		<span class="noorpdp-urgency__label"><?php echo esc_html( $noorpdp_label ); ?></span>
		<div
			class="noorpdp-urgency__timer"
			data-deadline="<?php echo esc_attr( $noorpdp_deadline * 1000 ); ?>"
			data-expired-message="<?php echo esc_attr( $noorpdp_expired_message ); ?>"
			data-hide-when-expired="<?php echo $noorpdp_hide_when_expired ? '1' : '0'; ?>"
		>
			<div class="noorpdp-urgency__unit">
				<span class="noorpdp-urgency__value" data-unit="days">00</span>
				<span class="noorpdp-urgency__unit-label"><?php esc_html_e( 'Days', 'noorpdp' ); ?></span>
			</div>
			<div class="noorpdp-urgency__unit">
				<span class="noorpdp-urgency__value" data-unit="hours">00</span>
				<span class="noorpdp-urgency__unit-label"><?php esc_html_e( 'Hrs', 'noorpdp' ); ?></span>
			</div>
			<div class="noorpdp-urgency__unit">
				<span class="noorpdp-urgency__value" data-unit="minutes">00</span>
				<span class="noorpdp-urgency__unit-label"><?php esc_html_e( 'Min', 'noorpdp' ); ?></span>
			</div>
			<div class="noorpdp-urgency__unit">
				<span class="noorpdp-urgency__value" data-unit="seconds">00</span>
				<span class="noorpdp-urgency__unit-label"><?php esc_html_e( 'Sec', 'noorpdp' ); ?></span>
			</div>
		</div>
	<?php endif; ?>
</div>
