<?php
/**
 * Product Layout custom post type.
 *
 * @package NoorPDP
 */

namespace NoorPDP\Layouts;

use NoorPDP\Traits\Singleton;
use NoorPDP\Admin\Dashboard;

defined( 'ABSPATH' ) || exit;

/**
 * Each Product Layout post holds the block markup for one alternate
 * single-product page arrangement. Layouts are edited in the standard
 * block editor (works on any theme) and applied to products via the
 * Resolver.
 */
class Post_Type {

	use Singleton;

	/**
	 * Post type slug.
	 */
	const SLUG = 'noorpdp_layout';

	/**
	 * Hooks post type registration.
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Registers the Product Layout post type.
	 *
	 * @return void
	 */
	public function register() {
		register_post_type(
			self::SLUG,
			array(
				'labels'          => array(
					'name'          => __( 'Product Layouts', 'noorpdp' ),
					'singular_name' => __( 'Product Layout', 'noorpdp' ),
					'add_new_item'  => __( 'Add New Product Layout', 'noorpdp' ),
					'edit_item'     => __( 'Edit Product Layout', 'noorpdp' ),
					'all_items'     => __( 'Product Layouts', 'noorpdp' ),
					'search_items'  => __( 'Search Product Layouts', 'noorpdp' ),
					'not_found'     => __( 'No product layouts found.', 'noorpdp' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => Dashboard::PAGE_SLUG,
				'show_in_rest'    => true,
				'supports'        => array( 'title', 'editor' ),
				'capability_type' => 'post',
				'map_meta_cap'    => true,
			)
		);
	}
}
