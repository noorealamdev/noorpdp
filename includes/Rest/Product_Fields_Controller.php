<?php
/**
 * Product custom-field list REST controller.
 *
 * @package NoorPDP
 */

namespace NoorPDP\Rest;

use NoorPDP\Traits\Singleton;
use NoorPDP\Blocks\Bindings;
use NoorPDP\Layouts\Preview;

defined( 'ABSPATH' ) || exit;

/**
 * Serves the field list the "NoorPDP: Product Field" block bindings
 * source shows in the editor's "Connect to custom field" picker and uses
 * to preview a bound value in the canvas — both editor-only concerns, so
 * this never needs to run on the front end.
 */
class Product_Fields_Controller {

	use Singleton;

	/**
	 * REST namespace.
	 */
	const REST_NAMESPACE = 'noorpdp/v1';

	/**
	 * Hooks route registration.
	 */
	protected function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers the product-fields route.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/product-fields',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_fields' ),
					'permission_callback' => array( $this, 'permission_check' ),
				),
			)
		);
	}

	/**
	 * Restricts the field list to users who can actually use it (building
	 * a Layout in the block editor).
	 *
	 * @return bool
	 */
	public function permission_check() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Returns the stand-in preview product's bindable fields.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_fields() {
		$product_id = Preview::instance()->get_preview_product_id();

		if ( ! $product_id ) {
			return rest_ensure_response( array() );
		}

		return rest_ensure_response( Bindings::instance()->get_fields( $product_id ) );
	}
}
