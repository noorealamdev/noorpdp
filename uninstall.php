<?php
/**
 * Removes all plugin data on uninstall.
 *
 * @package NoorPDP
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'noorpdp_disabled_blocks' );
delete_option( 'noorpdp_version' );
delete_option( 'noorpdp_default_layout_id' );
delete_transient( 'noorpdp_cloud_templates' );

// Remove every Product Layout post and its per-product/per-category assignments.
$noorpdp_layouts = get_posts(
	array(
		'post_type'     => 'noorpdp_layout',
		'post_status'   => 'any',
		'numberposts'   => -1,
		'fields'        => 'ids',
		'no_found_rows' => true,
	)
);

foreach ( $noorpdp_layouts as $noorpdp_layout_id ) {
	wp_delete_post( $noorpdp_layout_id, true );
}

delete_post_meta_by_key( '_noorpdp_layout_id' );

$noorpdp_terms = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'fields'     => 'ids',
	)
);

if ( is_array( $noorpdp_terms ) ) {
	foreach ( $noorpdp_terms as $noorpdp_term_id ) {
		delete_term_meta( $noorpdp_term_id, 'noorpdp_layout_id' );
	}
}
