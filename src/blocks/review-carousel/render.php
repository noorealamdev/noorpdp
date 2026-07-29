<?php
/**
 * Review Carousel block server render.
 *
 * @package NoorPDP
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$noorpdp_product_id = absint( $attributes['productId'] );
$noorpdp_selected   = array_map( 'absint', (array) $attributes['selectedReviews'] );

if ( ! $noorpdp_product_id || empty( $noorpdp_selected ) ) {
	return;
}

$noorpdp_product = wc_get_product( $noorpdp_product_id );

if ( ! $noorpdp_product ) {
	return;
}

$noorpdp_comments = get_comments(
	array(
		'post_id'     => $noorpdp_product_id,
		'status'      => 'approve',
		'comment__in' => $noorpdp_selected,
	)
);

// Reorders results to match the curated selection order (get_comments()
// doesn't preserve comment__in order), dropping any picked review that's
// since been unapproved or deleted.
$noorpdp_comments_by_id = array();

foreach ( $noorpdp_comments as $noorpdp_comment ) {
	$noorpdp_comments_by_id[ (int) $noorpdp_comment->comment_ID ] = $noorpdp_comment;
}

$noorpdp_ordered = array();

foreach ( $noorpdp_selected as $noorpdp_id ) {
	if ( isset( $noorpdp_comments_by_id[ $noorpdp_id ] ) ) {
		$noorpdp_ordered[] = $noorpdp_comments_by_id[ $noorpdp_id ];
	}
}

if ( empty( $noorpdp_ordered ) ) {
	return;
}

$noorpdp_ratings_enabled = wc_review_ratings_enabled();
$noorpdp_wrapper         = get_block_wrapper_attributes();
$noorpdp_multiple        = count( $noorpdp_ordered ) > 1;

// Self-contained max-width instead of relying on the theme/template to
// constrain block content — this block can land in templates that render
// blocks without any width-limiting ancestor.
$noorpdp_boxed       = ! isset( $attributes['boxed'] ) || (bool) $attributes['boxed'];
$noorpdp_boxed_width = isset( $attributes['boxedWidth'] ) ? absint( $attributes['boxedWidth'] ) : 1200;
?>
<div <?php echo $noorpdp_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped. ?>>
	<div
		class="noorpdp-review-carousel<?php echo $noorpdp_boxed ? ' is-boxed' : ''; ?>"
		<?php if ( $noorpdp_boxed ) : ?>
			style="max-width:<?php echo esc_attr( $noorpdp_boxed_width ); ?>px"
		<?php endif; ?>
	>
		<div class="noorpdp-review-carousel__stage">
			<div class="noorpdp-review-carousel__viewport">
				<div class="noorpdp-review-carousel__track">
					<?php foreach ( $noorpdp_ordered as $noorpdp_comment ) : ?>
						<?php
						$noorpdp_rating   = (float) get_comment_meta( $noorpdp_comment->comment_ID, 'rating', true );
						$noorpdp_verified = wc_review_is_from_verified_owner( $noorpdp_comment->comment_ID );
						?>
						<div class="noorpdp-review-carousel__slide">
							<div class="noorpdp-review-carousel__card">
								<?php if ( $noorpdp_ratings_enabled ) : ?>
									<?php echo wc_get_rating_html( $noorpdp_rating ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped. ?>
								<?php endif; ?>
								<p class="noorpdp-review-carousel__content">
									<?php echo wp_kses_post( get_comment_text( $noorpdp_comment ) ); ?>
								</p>
								<div class="noorpdp-review-carousel__footer">
									<?php if ( $noorpdp_verified ) : ?>
										<span class="noorpdp-review-carousel__verified">
											<?php esc_html_e( 'Verified Purchase', 'noorpdp' ); ?>
										</span>
									<?php endif; ?>
									<span class="noorpdp-review-carousel__name">
										&#8211;<?php echo esc_html( $noorpdp_comment->comment_author ); ?>
									</span>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( $noorpdp_multiple ) : ?>
				<button type="button" class="noorpdp-review-carousel__arrow noorpdp-review-carousel__arrow--prev" aria-label="<?php echo esc_attr__( 'Previous review', 'noorpdp' ); ?>">
					<span aria-hidden="true">&#10094;</span>
				</button>
				<button type="button" class="noorpdp-review-carousel__arrow noorpdp-review-carousel__arrow--next" aria-label="<?php echo esc_attr__( 'Next review', 'noorpdp' ); ?>">
					<span aria-hidden="true">&#10095;</span>
				</button>
			<?php endif; ?>
		</div>

		<?php if ( $noorpdp_multiple ) : ?>
			<div class="noorpdp-review-carousel__dots">
				<?php foreach ( $noorpdp_ordered as $noorpdp_index => $noorpdp_comment ) : ?>
					<?php
					/* translators: %d: review number in the carousel. */
					$noorpdp_dot_label = sprintf( __( 'Go to review %d', 'noorpdp' ), $noorpdp_index + 1 );
					?>
					<button
						type="button"
						class="noorpdp-review-carousel__dot<?php echo 0 === $noorpdp_index ? ' is-active' : ''; ?>"
						data-slide-index="<?php echo esc_attr( $noorpdp_index ); ?>"
						aria-label="<?php echo esc_attr( $noorpdp_dot_label ); ?>"
					></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
