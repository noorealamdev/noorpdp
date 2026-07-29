<?php
/**
 * Product Reviews block server render.
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

$noorpdp_ratings_enabled = wc_review_ratings_enabled();
$noorpdp_average         = (float) $product->get_average_rating();
$noorpdp_rating_counts   = $product->get_rating_counts();

/*
 * get_review_count() counts every approved comment on the product, which
 * can be higher than the number that actually carry a rating (e.g. legacy
 * comments imported without one) — using it as the percentage denominator
 * would make the breakdown bars never add up to 100%. The sum of
 * get_rating_counts() is the real total of *rated* reviews, so every
 * number shown (count, bars, percentages) stays internally consistent.
 */
$noorpdp_review_count = array_sum( $noorpdp_rating_counts );

$noorpdp_breakdown = array();

for ( $noorpdp_star = 5; $noorpdp_star >= 1; $noorpdp_star-- ) {
	$noorpdp_star_count                       = isset( $noorpdp_rating_counts[ $noorpdp_star ] ) ? (int) $noorpdp_rating_counts[ $noorpdp_star ] : 0;
	$noorpdp_breakdown[ $noorpdp_star ] = array(
		'count'      => $noorpdp_star_count,
		'percentage' => $noorpdp_review_count ? round( ( $noorpdp_star_count / $noorpdp_review_count ) * 100 ) : 0,
	);
}

$noorpdp_per_page = max( 1, absint( $attributes['reviewsToShow'] ) );

// Fetches one extra review to detect whether a "Load more" button is
// needed, without a second query.
$noorpdp_reviews  = \NoorPDP\Blocks\Product_Reviews_Renderer::get_reviews( $product->get_id(), 0, $noorpdp_per_page + 1 );
$noorpdp_has_more = count( $noorpdp_reviews ) > $noorpdp_per_page;
$noorpdp_reviews  = array_slice( $noorpdp_reviews, 0, $noorpdp_per_page );

$noorpdp_wrapper = get_block_wrapper_attributes();

// Self-contained max-width instead of relying on the theme/template to
// constrain block content — this block can land in templates that render
// blocks without any width-limiting ancestor. The modal further below sits
// outside this wrapper since it's position:fixed and already ignores
// ancestor width.
$noorpdp_boxed       = ! isset( $attributes['boxed'] ) || (bool) $attributes['boxed'];
$noorpdp_boxed_width = isset( $attributes['boxedWidth'] ) ? absint( $attributes['boxedWidth'] ) : 1200;
?>
<div <?php echo $noorpdp_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped. ?>>

	<div
		class="noorpdp-product-reviews__inner<?php echo $noorpdp_boxed ? ' is-boxed' : ''; ?>"
		<?php if ( $noorpdp_boxed ) : ?>
			style="max-width:<?php echo esc_attr( $noorpdp_boxed_width ); ?>px"
		<?php endif; ?>
	>
		<div class="noorpdp-product-reviews__summary">
			<div class="noorpdp-product-reviews__summary-main">
				<?php if ( $noorpdp_ratings_enabled ) : ?>
					<div class="noorpdp-product-reviews__average"><?php echo esc_html( number_format_i18n( $noorpdp_average, 1 ) ); ?></div>
					<?php echo wc_get_rating_html( $noorpdp_average, $noorpdp_review_count ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped. ?>
				<?php endif; ?>
				<p class="noorpdp-product-reviews__count">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: number of reviews. */
							_n( 'Based on %s review', 'Based on %s reviews', $noorpdp_review_count, 'noorpdp' ),
							number_format_i18n( $noorpdp_review_count )
						)
					);
					?>
				</p>
				<?php if ( comments_open() ) : ?>
					<button
						type="button"
						class="noorpdp-product-reviews__write-link"
						data-review-modal-target="noorpdp-review-modal-<?php echo esc_attr( $product->get_id() ); ?>"
					>
						<?php esc_html_e( 'Write a Review', 'noorpdp' ); ?>
					</button>
				<?php endif; ?>
			</div>

			<?php if ( $noorpdp_ratings_enabled && $noorpdp_review_count ) : ?>
				<div class="noorpdp-product-reviews__breakdown">
					<?php foreach ( $noorpdp_breakdown as $noorpdp_star => $noorpdp_row ) : ?>
						<div class="noorpdp-product-reviews__breakdown-row">
							<span class="noorpdp-product-reviews__breakdown-label">
								<?php
								echo esc_html(
									sprintf(
										/* translators: %d: star rating, 1-5. */
										_n( '%d star', '%d stars', $noorpdp_star, 'noorpdp' ),
										$noorpdp_star
									)
								);
								?>
							</span>
							<span class="noorpdp-product-reviews__breakdown-track">
								<span class="noorpdp-product-reviews__breakdown-fill" style="width:<?php echo esc_attr( $noorpdp_row['percentage'] ); ?>%"></span>
							</span>
							<span class="noorpdp-product-reviews__breakdown-percentage"><?php echo esc_html( $noorpdp_row['percentage'] ); ?>%</span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $noorpdp_reviews ) ) : ?>
			<div class="noorpdp-product-reviews__grid columns-<?php echo esc_attr( absint( $attributes['columns'] ) ); ?>">
				<?php foreach ( $noorpdp_reviews as $noorpdp_review ) : ?>
					<?php
					// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- render_card() escapes its own output internally.
					echo \NoorPDP\Blocks\Product_Reviews_Renderer::render_card(
						$noorpdp_review['comment'],
						$noorpdp_review['rating'],
						$noorpdp_ratings_enabled
					);
					// phpcs:enable
					?>
				<?php endforeach; ?>
			</div>
			<?php if ( $noorpdp_has_more ) : ?>
				<div class="noorpdp-product-reviews__load-more-wrap">
					<button
						type="button"
						class="noorpdp-product-reviews__load-more"
						data-rest-url="<?php echo esc_url( rest_url( 'noorpdp/v1/product-reviews/' . $product->get_id() ) ); ?>"
						data-offset="<?php echo esc_attr( $noorpdp_per_page ); ?>"
						data-per-page="<?php echo esc_attr( $noorpdp_per_page ); ?>"
						data-loading-text="<?php echo esc_attr__( 'Loading…', 'noorpdp' ); ?>"
					>
						<?php esc_html_e( 'Load more reviews', 'noorpdp' ); ?>
					</button>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<p class="noorpdp-product-reviews__empty"><?php esc_html_e( 'There are no reviews yet.', 'noorpdp' ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( comments_open() ) : ?>
		<div
			id="noorpdp-review-modal-<?php echo esc_attr( $product->get_id() ); ?>"
			class="noorpdp-product-reviews__modal"
			hidden
			aria-hidden="true"
		>
			<div class="noorpdp-product-reviews__modal-backdrop" data-review-modal-close></div>
			<div class="noorpdp-product-reviews__modal-panel" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__( 'Write a review', 'noorpdp' ); ?>">
				<button type="button" class="noorpdp-product-reviews__modal-close" data-review-modal-close aria-label="<?php echo esc_attr__( 'Close', 'noorpdp' ); ?>">
					&times;
				</button>
			<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
				<div id="review_form_wrapper">
					<div id="review_form">
						<?php
						$noorpdp_commenter    = wp_get_current_commenter();
						$noorpdp_comment_form = array(
							/* translators: %s is product title */
							'title_reply'         => $noorpdp_review_count ? esc_html__( 'Add a review', 'noorpdp' ) : sprintf( esc_html__( 'Be the first to review "%s"', 'noorpdp' ), esc_html( get_the_title() ) ),
							/* translators: %s is product title */
							'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'noorpdp' ),
							'title_reply_before'  => '<span id="reply-title" class="comment-reply-title" role="heading" aria-level="3">',
							'title_reply_after'   => '</span>',
							'comment_notes_after' => '',
							'label_submit'        => esc_html__( 'Submit', 'noorpdp' ),
							'logged_in_as'        => '',
							'comment_field'       => '',
						);

						$noorpdp_name_email_required = (bool) get_option( 'require_name_email', 1 );
						$noorpdp_fields              = array(
							'author' => array(
								'label'        => __( 'Name', 'noorpdp' ),
								'type'         => 'text',
								'value'        => $noorpdp_commenter['comment_author'],
								'required'     => $noorpdp_name_email_required,
								'autocomplete' => 'name',
							),
							'email'  => array(
								'label'        => __( 'Email', 'noorpdp' ),
								'type'         => 'email',
								'value'        => $noorpdp_commenter['comment_author_email'],
								'required'     => $noorpdp_name_email_required,
								'autocomplete' => 'email',
							),
						);

						$noorpdp_comment_form['fields'] = array();

						foreach ( $noorpdp_fields as $noorpdp_key => $noorpdp_field ) {
							$noorpdp_field_html  = '<p class="comment-form-' . esc_attr( $noorpdp_key ) . '">';
							$noorpdp_field_html .= '<label for="' . esc_attr( $noorpdp_key ) . '">' . esc_html( $noorpdp_field['label'] );

							if ( $noorpdp_field['required'] ) {
								$noorpdp_field_html .= '&nbsp;<span class="required">*</span>';
							}

							$noorpdp_field_html .= '</label><input id="' . esc_attr( $noorpdp_key ) . '" name="' . esc_attr( $noorpdp_key ) . '" type="' . esc_attr( $noorpdp_field['type'] ) . '" autocomplete="' . esc_attr( $noorpdp_field['autocomplete'] ) . '" value="' . esc_attr( $noorpdp_field['value'] ) . '" size="30" ' . ( $noorpdp_field['required'] ? 'required' : '' ) . ' /></p>';

							$noorpdp_comment_form['fields'][ $noorpdp_key ] = $noorpdp_field_html;
						}

						$noorpdp_account_page_url = wc_get_page_permalink( 'myaccount' );
						if ( $noorpdp_account_page_url ) {
							/* translators: %1$s/%2$s: opening/closing link tags */
							$noorpdp_comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'noorpdp' ), '<a href="' . esc_url( $noorpdp_account_page_url ) . '">', '</a>' ) . '</p>';
						}

						if ( $noorpdp_ratings_enabled ) {
							$noorpdp_comment_form['comment_field'] = '<div class="comment-form-rating"><label for="rating" id="comment-form-rating-label">' . esc_html__( 'Your rating', 'noorpdp' ) . ( wc_review_ratings_required() ? '&nbsp;<span class="required">*</span>' : '' ) . '</label><select name="rating" id="rating" ' . ( wc_review_ratings_required() ? 'required' : '' ) . '>
								<option value="">' . esc_html__( 'Rate…', 'noorpdp' ) . '</option>
								<option value="5">' . esc_html__( 'Perfect', 'noorpdp' ) . '</option>
								<option value="4">' . esc_html__( 'Good', 'noorpdp' ) . '</option>
								<option value="3">' . esc_html__( 'Average', 'noorpdp' ) . '</option>
								<option value="2">' . esc_html__( 'Not that bad', 'noorpdp' ) . '</option>
								<option value="1">' . esc_html__( 'Very poor', 'noorpdp' ) . '</option>
							</select></div>';
						}

						$noorpdp_comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Your review', 'noorpdp' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>';

						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- intentionally reusing WooCommerce's own review-form filter name (not a new hook) so plugins/themes that already customize the native review form via this filter keep working here too.
						comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $noorpdp_comment_form ) );
						?>
					</div>
				</div>
			<?php else : ?>
				<p class="noorpdp-product-reviews__verification-required">
					<?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'noorpdp' ); ?>
				</p>
			<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
