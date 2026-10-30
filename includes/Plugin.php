<?php
/**
 * Main plugin class.
 *
 * @package NoorPDP
 */

namespace NoorPDP;

use NoorPDP\Traits\Singleton;
use NoorPDP\Blocks\Manager as Blocks_Manager;
use NoorPDP\Blocks\Interactivity;
use NoorPDP\Blocks\Extensions as Blocks_Extensions;
use NoorPDP\Blocks\Buy_Now;
use NoorPDP\Blocks\Bindings as Blocks_Bindings;
use NoorPDP\Patterns\Manager as Patterns_Manager;
use NoorPDP\Assets\Manager as Assets_Manager;
use NoorPDP\Rest\Templates_Controller;
use NoorPDP\Rest\Product_Reviews_Controller;
use NoorPDP\Rest\Product_Fields_Controller;
use NoorPDP\Admin\Dashboard;
use NoorPDP\Layouts\Post_Type as Layouts_Post_Type;
use NoorPDP\Layouts\Meta_Box as Layouts_Meta_Box;
use NoorPDP\Layouts\Template_Override as Layouts_Template_Override;
use NoorPDP\Layouts\Duplicate as Layouts_Duplicate;
use NoorPDP\Layouts\Preview as Layouts_Preview;

defined( 'ABSPATH' ) || exit;

/**
 * Boots every plugin service.
 */
class Plugin {

	use Singleton;

	/**
	 * Wires up all services.
	 */
	protected function __construct() {
		if ( ! $this->has_woocommerce() ) {
			add_action( 'admin_notices', array( $this, 'render_missing_woocommerce_notice' ) );
			return;
		}

		$this->init_services();
	}

	/**
	 * Whether WooCommerce is active.
	 *
	 * @return bool
	 */
	private function has_woocommerce() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Warns the site admin that WooCommerce is required.
	 *
	 * @return void
	 */
	public function render_missing_woocommerce_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		?>
		<div class="notice notice-error">
			<p>
				<?php
				esc_html_e(
					'NoorPDP requires WooCommerce to be installed and active.',
					'noorpdp'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Instantiates the service managers.
	 *
	 * @return void
	 */
	private function init_services() {
		Install::init();

		Blocks_Manager::instance();
		Interactivity::instance();
		Blocks_Extensions::instance();
		Buy_Now::instance();
		Blocks_Bindings::instance();
		Patterns_Manager::instance();
		Assets_Manager::instance();
		Templates_Controller::instance();
		Product_Reviews_Controller::instance();
		Product_Fields_Controller::instance();

		Layouts_Post_Type::instance();
		Layouts_Meta_Box::instance();
		Layouts_Template_Override::instance();
		Layouts_Duplicate::instance();
		Layouts_Preview::instance();

		if ( is_admin() ) {
			Dashboard::instance();
		}

		/**
		 * Fires after every core service booted.
		 *
		 * Add-ons can hook here to register their own services.
		 *
		 * @param Plugin $plugin The plugin instance.
		 */
		do_action( 'noorpdp/loaded', $this );
	}
}
