<?php
/**
 * Plugin Name:       NoorPDP
 * Description:       Build WooCommerce single product pages from premade templates, using blocks that wrap WooCommerce's own rendering.
 * Version:           1.1.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * Author:            Noor E Alam
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       noorpdp
 *
 * @package NoorPDP
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'NOORPDP_VERSION', '1.1.0' );
define( 'NOORPDP_FILE', __FILE__ );
define( 'NOORPDP_DIR', plugin_dir_path( __FILE__ ) );
define( 'NOORPDP_URL', plugin_dir_url( __FILE__ ) );
define( 'NOORPDP_BASENAME', plugin_basename( __FILE__ ) );

// Register the PSR-4 autoloader.
require_once NOORPDP_DIR . 'includes/Autoloader.php';
\NoorPDP\Autoloader::register();

// Lifecycle hooks.
register_activation_hook( __FILE__, array( '\NoorPDP\Install', 'activate' ) );
register_deactivation_hook( __FILE__, array( '\NoorPDP\Install', 'deactivate' ) );

/**
 * Returns the main plugin instance.
 *
 * @return \NoorPDP\Plugin
 */
function noorpdp() {
	return \NoorPDP\Plugin::instance();
}

// Boot the plugin once all active plugins (including WooCommerce) have loaded.
add_action( 'plugins_loaded', 'noorpdp' );
