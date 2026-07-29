<?php
/**
 * Install, upgrade and deactivation routines.
 *
 * @package NoorPDP
 */

namespace NoorPDP;

use NoorPDP\Templates\Repository;

defined( 'ABSPATH' ) || exit;

/**
 * Tracks the installed version and runs migrations between releases.
 */
class Install {

	/**
	 * Option storing the version the database was last migrated to.
	 */
	const VERSION_OPTION = 'noorpdp_version';

	/**
	 * Hooks the upgrade check.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_upgrade' ), 5 );
	}

	/**
	 * Runs on plugin activation.
	 *
	 * @return void
	 */
	public static function activate() {
		self::maybe_upgrade();
	}

	/**
	 * Runs on plugin deactivation.
	 *
	 * @return void
	 */
	public static function deactivate() {
		Repository::instance()->flush_caches();
	}

	/**
	 * Applies pending migrations when the plugin version changed.
	 *
	 * @return void
	 */
	public static function maybe_upgrade() {
		$installed = (string) get_option( self::VERSION_OPTION, '' );

		if ( NOORPDP_VERSION === $installed ) {
			return;
		}

		foreach ( self::get_migrations() as $version => $callback ) {
			if ( '' !== $installed && version_compare( $installed, $version, '<' ) ) {
				call_user_func( $callback );
			}
		}

		update_option( self::VERSION_OPTION, NOORPDP_VERSION );

		/**
		 * Fires after the plugin migrated to a new version.
		 *
		 * @param string $installed Previous version, empty on first install.
		 */
		do_action( 'noorpdp/upgraded', $installed );
	}

	/**
	 * Returns the migration callbacks, keyed by the version introducing them.
	 *
	 * A migration runs once when a site upgrades from a version below its
	 * key. Example: '1.1.0' => array( __CLASS__, 'migrate_110' ).
	 *
	 * @return array<string, callable>
	 */
	private static function get_migrations() {
		return array();
	}
}
