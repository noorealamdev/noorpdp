<?php
/**
 * Product Gallery Carousel block server render.
 *
 * Renders the current product's own images (featured + gallery) as a
 * self-contained carousel, instead of wrapping WooCommerce's own
 * flexslider-based gallery output.
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

$noorpdp_image_ids = array();
$noorpdp_featured  = $product->get_image_id();

if ( $noorpdp_featured ) {
	$noorpdp_image_ids[] = (int) $noorpdp_featured;
}

foreach ( $product->get_gallery_image_ids() as $noorpdp_gallery_id ) {
	$noorpdp_image_ids[] = (int) $noorpdp_gallery_id;
}

$noorpdp_image_ids = array_values( array_unique( array_filter( $noorpdp_image_ids ) ) );

$noorpdp_show_thumbs = ! isset( $attributes['showThumbnails'] ) || (bool) $attributes['showThumbnails'];
$noorpdp_multiple    = count( $noorpdp_image_ids ) > 1;
$noorpdp_thumb_size  = wc_get_image_size( 'gallery_thumbnail' );
$noorpdp_wrapper     = get_block_wrapper_attributes();
?>
<div <?php echo $noorpdp_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped. ?>>
	<div class="noorpdp-product-gallery-carousel">
		<div class="noorpdp-product-gallery-carousel__stage">
			<div class="noorpdp-product-gallery-carousel__viewport">
				<div class="noorpdp-product-gallery-carousel__track">
					<?php if ( empty( $noorpdp_image_ids ) ) : ?>
						<div class="noorpdp-product-gallery-carousel__slide">
							<div class="noorpdp-product-gallery-carousel__image-wrap">
								<?php echo wc_placeholder_img( 'woocommerce_single' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped. ?>
							</div>
						</div>
					<?php else : ?>
						<?php foreach ( $noorpdp_image_ids as $noorpdp_id ) : ?>
							<div class="noorpdp-product-gallery-carousel__slide" data-image-id="<?php echo esc_attr( $noorpdp_id ); ?>">
								<div class="noorpdp-product-gallery-carousel__image-wrap">
									<?php
									echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped.
										$noorpdp_id,
										'woocommerce_single',
										false,
										array(
											'class'     => 'noorpdp-product-gallery-carousel__image',
											// Otherwise the browser's native "drag this image out"
											// gesture competes with (and can override) our own
											// pointer-based carousel drag.
											'draggable' => 'false',
										)
									);
									?>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( $noorpdp_multiple ) : ?>
				<button type="button" class="noorpdp-product-gallery-carousel__arrow noorpdp-product-gallery-carousel__arrow--prev" aria-label="<?php echo esc_attr__( 'Previous image', 'noorpdp' ); ?>">
					<span aria-hidden="true">&#10094;</span>
				</button>
				<button type="button" class="noorpdp-product-gallery-carousel__arrow noorpdp-product-gallery-carousel__arrow--next" aria-label="<?php echo esc_attr__( 'Next image', 'noorpdp' ); ?>">
					<span aria-hidden="true">&#10095;</span>
				</button>
			<?php endif; ?>
		</div>

		<?php if ( $noorpdp_multiple && $noorpdp_show_thumbs ) : ?>
			<div class="noorpdp-product-gallery-carousel__thumbs">
				<?php foreach ( $noorpdp_image_ids as $noorpdp_index => $noorpdp_id ) : ?>
					<?php
					/* translators: %d: image number in the gallery. */
					$noorpdp_thumb_label = sprintf( __( 'Show image %d', 'noorpdp' ), $noorpdp_index + 1 );
					?>
					<button
						type="button"
						class="noorpdp-product-gallery-carousel__thumb<?php echo 0 === $noorpdp_index ? ' is-active' : ''; ?>"
						data-slide-index="<?php echo esc_attr( $noorpdp_index ); ?>"
						aria-label="<?php echo esc_attr( $noorpdp_thumb_label ); ?>"
					>
						<?php
						echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped.
							$noorpdp_id,
							array( $noorpdp_thumb_size['width'], $noorpdp_thumb_size['height'] ),
							false,
							array( 'class' => 'noorpdp-product-gallery-carousel__thumb-image' )
						);
						?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php elseif ( $noorpdp_multiple ) : ?>
			<div class="noorpdp-product-gallery-carousel__dots">
				<?php foreach ( $noorpdp_image_ids as $noorpdp_index => $noorpdp_id ) : ?>
					<?php
					/* translators: %d: image number in the gallery. */
					$noorpdp_dot_label = sprintf( __( 'Show image %d', 'noorpdp' ), $noorpdp_index + 1 );
					?>
					<button
						type="button"
						class="noorpdp-product-gallery-carousel__dot<?php echo 0 === $noorpdp_index ? ' is-active' : ''; ?>"
						data-slide-index="<?php echo esc_attr( $noorpdp_index ); ?>"
						aria-label="<?php echo esc_attr( $noorpdp_dot_label ); ?>"
					></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
