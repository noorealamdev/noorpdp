<?php
/**
 * Block registration manager.
 *
 * @package NoorPDP
 */

namespace NoorPDP\Blocks;

use NoorPDP\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * Discovers and registers every block in the build directory.
 */
class Manager {

	use Singleton;

	/**
	 * Option name storing the list of disabled block names.
	 */
	const DISABLED_OPTION = 'noorpdp_disabled_blocks';

	/**
	 * Hooks block registration.
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_filter( 'block_categories_all', array( $this, 'register_category' ), 10, 1 );
	}

	/**
	 * Registers every block found in build/blocks, skipping disabled ones.
	 *
	 * @return void
	 */
	public function register_blocks() {
		/**
		 * Filters the disabled block names.
		 *
		 * @param string[] $disabled Block names that will not be registered.
		 */
		$disabled = (array) apply_filters(
			'noorpdp/disabled_blocks',
			(array) get_option( self::DISABLED_OPTION, array() )
		);

		foreach ( $this->get_block_dirs() as $dir ) {
			$metadata = $this->get_block_metadata( $dir );

			if ( empty( $metadata['name'] ) || in_array( $metadata['name'], $disabled, true ) ) {
				continue;
			}

			register_block_type( $dir );
		}
	}

	/**
	 * Adds the NoorPDP category to the top of the block inserter.
	 *
	 * @param array $categories Existing block categories.
	 * @return array
	 */
	public function register_category( $categories ) {
		return array_merge(
			array(
				array(
					'slug'  => 'noorpdp',
					'title' => __( 'NoorPDP', 'noorpdp' ),
					'icon'  => null,
				),
			),
			$categories
		);
	}

	/**
	 * Returns the absolute paths of every built block directory.
	 *
	 * @return string[]
	 */
	public function get_block_dirs() {
		$dirs = glob( NOORPDP_DIR . 'build/blocks/*', GLOB_ONLYDIR );

		if ( ! is_array( $dirs ) ) {
			$dirs = array();
		}

		/**
		 * Filters the block directories to register.
		 *
		 * Add-ons can append their own build directories; every directory
		 * must contain a block.json file.
		 *
		 * @param string[] $dirs Absolute block directory paths.
		 */
		$dirs = (array) apply_filters( 'noorpdp/block_dirs', $dirs );

		return array_filter(
			$dirs,
			static function ( $dir ) {
				return file_exists( $dir . '/block.json' );
			}
		);
	}

	/**
	 * Reads and decodes a block.json file.
	 *
	 * @param string $dir Block directory.
	 * @return array
	 */
	public function get_block_metadata( $dir ) {
		$file = trailingslashit( $dir ) . 'block.json';

		if ( ! is_readable( $file ) ) {
			return array();
		}

		$metadata = wp_json_file_decode( $file, array( 'associative' => true ) );

		return is_array( $metadata ) ? $metadata : array();
	}

	/**
	 * Returns metadata for every discovered block, keyed by block name.
	 *
	 * @return array[]
	 */
	public function get_all_blocks() {
		$blocks = array();

		foreach ( $this->get_block_dirs() as $dir ) {
			$metadata = $this->get_block_metadata( $dir );

			if ( ! empty( $metadata['name'] ) ) {
				$blocks[ $metadata['name'] ] = $metadata;
			}
		}

		return $blocks;
	}
}
