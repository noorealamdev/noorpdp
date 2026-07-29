<?php
/**
 * Trust Badges block server render.
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

/*
 * No forced default background/text-color/radius here — same approach as
 * Icon List. WordPress can't distinguish "cleared" from "never set" for a
 * color attribute, so any baked-in default can never be fully suppressed
 * by the user; plain color/border supports with nothing hardcoded avoids
 * the problem entirely. The soft rounded card look ships as starter
 * *content* instead (see the `items` default in block.json) — pick this
 * block from the inserter and its own default color/border/spacing
 * values (set once via Inspector, no code needed) give the same look.
 */
$noorpdp_wrapper = get_block_wrapper_attributes();

$noorpdp_boxed       = ! isset( $attributes['boxed'] ) || (bool) $attributes['boxed'];
$noorpdp_boxed_width = isset( $attributes['boxedWidth'] ) ? absint( $attributes['boxedWidth'] ) : 1200;
?>
<div <?php echo $noorpdp_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped. ?>>
	<ul
		class="noorpdp-trust-badges__list<?php echo $noorpdp_boxed ? ' is-boxed' : ''; ?>"
		<?php if ( $noorpdp_boxed ) : ?>
			style="max-width:<?php echo esc_attr( $noorpdp_boxed_width ); ?>px"
		<?php endif; ?>
	>
		<?php foreach ( $noorpdp_items as $noorpdp_item ) : ?>
			<li class="noorpdp-trust-badges__item">
				<?php if ( ! empty( $noorpdp_item['icon'] ) ) : ?>
					<span class="noorpdp-trust-badges__icon">
						<?php echo \NoorPDP\Blocks\WP_Icon_Registry::render( $noorpdp_item['icon'], 'nt-shieldCheck' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- self-escaping, fixed internal registry. ?>
					</span>
				<?php endif; ?>
				<span class="noorpdp-trust-badges__label"><?php echo wp_kses_post( $noorpdp_item['label'] ?? '' ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
