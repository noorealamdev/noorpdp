<?php
/**
 * PSR-4 autoloader for the NoorPDP namespace.
 *
 * @package NoorPDP
 */

namespace NoorPDP;

defined( 'ABSPATH' ) || exit;

/**
 * Maps NoorPDP\Sub\ClassName to includes/Sub/ClassName.php.
 */
class Autoloader {

	/**
	 * Namespace prefix handled by this autoloader.
	 *
	 * @var string
	 */
	const PREFIX = 'NoorPDP\\';

	/**
	 * Registers the autoloader with SPL.
	 *
	 * @return void
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Loads the file for a given fully qualified class name.
	 *
	 * @param string $class_name Fully qualified class name.
	 * @return void
	 */
	public static function autoload( $class_name ) {
		if ( 0 !== strpos( $class_name, self::PREFIX ) ) {
			return;
		}

		$relative = substr( $class_name, strlen( self::PREFIX ) );
		$path     = NOORPDP_DIR . 'includes/' . str_replace( '\\', '/', $relative ) . '.php';

		if ( is_readable( $path ) ) {
			require $path;
		}
	}
}
