<?php
/**
 * Block Bindings source exposing product custom fields.
 *
 * @package NoorPDP
 */

namespace NoorPDP\Blocks;

use NoorPDP\Traits\Singleton;
use NoorPDP\Layouts\Preview;

defined( 'ABSPATH' ) || exit;

/**
 * Registers a WordPress Block Bindings source so core blocks (Paragraph,
 * Heading, Image, Button) can connect directly to a product's custom
 * fields — ACF fields included, since ACF stores its values as ordinary
 * post meta — without a NoorPDP-specific block or shortcode for every
 * possible field.
 *
 * On a real product page, `get_value_callback()` resolves against the
 * actual product being viewed (via the `postId` context `render_block()`
 * seeds from the global `$post`). While editing a Layout — which has no
 * product of its own — it falls back to the same stand-in product
 * `Layouts\Preview` uses for its "Preview" row action, so the field list
 * and example values shown while building a Layout are real data, not
 * placeholders.
 */
class Bindings {

	use Singleton;

	/**
	 * Block bindings source name.
	 */
	const SOURCE = 'noorpdp/product-field';

	/**
	 * Non-underscore-prefixed meta keys that are still WooCommerce/WordPress
	 * bookkeeping rather than merchant-authored content, so the usual
	 * "no leading underscore" rule doesn't catch them on its own.
	 *
	 * @var string[]
	 */
	const INTERNAL_KEYS = array( 'total_sales' );

	/**
	 * Hooks source registration.
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'register_source' ) );
	}

	/**
	 * Registers the binding source, if the running WordPress version
	 * supports the Block Bindings API (6.5+).
	 *
	 * @return void
	 */
	public function register_source() {
		if ( ! function_exists( 'register_block_bindings_source' ) ) {
			return;
		}

		register_block_bindings_source(
			self::SOURCE,
			array(
				'label'              => __( 'NoorPDP: Product Field', 'noorpdp' ),
				'get_value_callback' => array( $this, 'get_value' ),
				'uses_context'       => array( 'postId' ),
			)
		);
	}

	/**
	 * Resolves a bound attribute's value.
	 *
	 * @param array     $source_args    Binding args; `key` is the meta/ACF field name.
	 * @param \WP_Block $block_instance The block being rendered.
	 * @return string|null
	 */
	public function get_value( $source_args, $block_instance ) {
		if ( empty( $source_args['key'] ) ) {
			return null;
		}

		$product_id = $this->resolve_product_id( $block_instance );

		if ( ! $product_id ) {
			return null;
		}

		$fields = $this->get_fields( $product_id );
		$key    = $source_args['key'];

		return isset( $fields[ $key ] ) ? $fields[ $key ]['value'] : null;
	}

	/**
	 * Works out which product a binding should read from.
	 *
	 * @param \WP_Block $block_instance The block being rendered.
	 * @return int Product ID, or 0 if none could be resolved.
	 */
	private function resolve_product_id( $block_instance ) {
		$context_post_id = isset( $block_instance->context['postId'] )
			? (int) $block_instance->context['postId']
			: 0;

		if ( $context_post_id && 'product' === get_post_type( $context_post_id ) ) {
			return $context_post_id;
		}

		return Preview::instance()->get_preview_product_id();
	}

	/**
	 * Lists a product's bindable fields: public (non-underscore-prefixed)
	 * post meta, keyed by field name, each with a human label and its
	 * current scalar value. ACF fields are included automatically — ACF
	 * stores its values as post meta under the field's own name — and get
	 * ACF's own label when ACF is active.
	 *
	 * Array/object field values (ACF repeaters, image fields, etc.) are
	 * skipped: a Block Bindings value must be a single string, and there's
	 * no generically correct way to flatten an arbitrary structure into one.
	 *
	 * @param int $product_id Product (post) ID.
	 * @return array<string, array{label: string, value: string}>
	 */
	public function get_fields( $product_id ) {
		$fields = array();

		if ( function_exists( 'get_field_objects' ) ) {
			$acf_fields = get_field_objects( $product_id, array( 'load_value' => true ) );

			foreach ( (array) $acf_fields as $acf_field ) {
				if ( empty( $acf_field['name'] ) || ! is_scalar( $acf_field['value'] ) || '' === $acf_field['value'] ) {
					continue;
				}

				$fields[ $acf_field['name'] ] = array(
					'label' => $acf_field['label'],
					'value' => (string) $acf_field['value'],
				);
			}
		}

		foreach ( get_post_meta( $product_id ) as $meta_key => $values ) {
			if ( isset( $fields[ $meta_key ] ) || '' === $meta_key || '_' === $meta_key[0] || in_array( $meta_key, self::INTERNAL_KEYS, true ) ) {
				continue;
			}

			$value = isset( $values[0] ) ? maybe_unserialize( $values[0] ) : '';

			if ( ! is_scalar( $value ) || '' === $value ) {
				continue;
			}

			$fields[ $meta_key ] = array(
				'label' => ucwords( str_replace( array( '-', '_' ), ' ', $meta_key ) ),
				'value' => (string) $value,
			);
		}

		return $fields;
	}
}
