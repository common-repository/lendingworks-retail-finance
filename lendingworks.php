<?php
/**
 * Plugin Name: LendingWorks Retail Finance
 * Plugin URI: https://github.com/lendingworks/woocommerce-retail-finance
 * Description: Lending Works Retail Finance payment gateway
 * Version: 1.0.0
 * Author: Lending Works Ltd
 * Author URI: http://www.lendingworks.co.uk/
 * Developer: Lending Works technology team
 * Developer URI: http://www.lendingworks.co.uk/
 * Text Domain: lendingworks
 * Domain Path: /languages
 *
 * Woo:
 * WC requires at least: 2.6.0
 * WC tested up to: 3.6
 *
 * Copyright:
 * License: GPLv2 or later License
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 */

/**
 * Lending Works WooCommerce plugin main file.
 *
 * Main file registering the Lending Works plugin and define its actions and filters.
 *
 * @package WordPress
 * @subpackage WooCommerce
 * @version 1.0.0
 * @author  Lending Works Ltd
 * @license https://www.gnu.org/licenses/gpl-2.0.txt GNU/GPLv2
 * @link https://www.lendingworks.co.uk/
 * @since  1.0.0
 */

namespace WC_Lending_Works;

use WC_Lending_Works\Lib\Callback\Callback;
use WC_Lending_Works\Lib\Checkout\Checkout;
use WC_Lending_Works\Lib\Fulfill\Fulfill;
use WC_Lending_Works\Lib\Http;
use WC_Lending_Works\Lib\Order\Order_Repository;
use WC_Lending_Works\Lib\Pay\Pay;
use WC_Lending_Works\Lib\Payment_Gateway;
use WC_Lending_Works\Lib\Webhook\Webhook;
use WC_Lending_Works\Lib\Framework\Woocommerce_Adapter;
use WC_Lending_Works\Lib\Framework\Woocommerce_Adapter_Legacy;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! in_array(
	'woocommerce/woocommerce.php',
	apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
	true
)
) {
	return; // Check if WooCommerce is active.
}

define( __NAMESPACE__, __NAMESPACE__ . '\\' );
define( WC_Lending_Works . 'PLUGIN_NAME', 'wc-lending-works' );
define( WC_Lending_Works . 'PLUGIN_CLASS', 'WC_Lending_Works' );
define( WC_Lending_Works . 'PLUGIN_VERSION', '1.0.1' );
define( WC_Lending_Works . 'PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( WC_Lending_Works . 'PLUGIN_NAME_URL', plugin_dir_url( __FILE__ ) );
define( WC_Lending_Works . 'PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( file_exists( PLUGIN_DIR . 'lendingworks/vendor/autoload.php' ) ) {
	require_once PLUGIN_DIR . 'lendingworks/vendor/autoload.php';
} else {
	require_once PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * Register Activation and Deactivation Hooks
 */
register_activation_hook( __FILE__, [ WC_Lending_Works . 'Lib\Activator', 'activate' ] );

/**
 * Class WC_Gateway_Lendingworks
 *
 * Class responsible for putting together the plugin pieces and initiating it once the dependencies are ready.
 */
class WC_Gateway_Lendingworks {

	/**
	 * The instance of plugin Init.
	 *
	 * @var Lib\Init $instance
	 */
	private static $instance;

	/**
	 * Insanciate and initialise the plugin.
	 *
	 * @return Lib\Init
	 */
	public static function init() {
		if ( null === self::$instance ) {
			$loader  = new Lib\Loader();
			$gateway = new Payment_Gateway();

			$sandbox_url = isset( $_ENV['SANDBOX_URL'] ) ? $_ENV['SANDBOX_URL'] : Payment_Gateway::SANDBOX_URL;

			$base_url = $gateway->get_test_mode() ? $sandbox_url : Payment_Gateway::PROD_URL;

			global $woocommerce;
			if ( version_compare( $woocommerce->version, '3.0', '<' ) ) {
				$woocommerce_adapter = new Woocommerce_Adapter_Legacy();
				$create_request      = new Http\Create\Request_Legacy( $base_url, $gateway->get_api_key(), $woocommerce_adapter );
			} else {
				$woocommerce_adapter = new Woocommerce_Adapter();
				$create_request      = new Http\Create\Request( $base_url, $gateway->get_api_key(), $woocommerce_adapter );
			}

			$fulfill_request  = new Http\Fulfill\Request( $base_url, $gateway->get_api_key(), $woocommerce_adapter );
			$order_repository = new Order_Repository( $woocommerce_adapter, $create_request, $fulfill_request );

			$checkout = new Checkout( $woocommerce_adapter, $order_repository );
			$gateway->set_checkout( $checkout );

			$pay = new Pay( $woocommerce_adapter, $gateway->get_test_mode() );

			$webhook = new Webhook( $woocommerce_adapter );

			$fulfill = new Fulfill( $woocommerce_adapter, $order_repository, $gateway->is_fulfillment() );

			$callback = new Callback( $woocommerce_adapter, $gateway->get_api_key() );

			self::$instance = new Lib\Init( $loader, $checkout, $pay, $webhook, $fulfill, $callback );
			self::$instance->run();
		}

		return self::$instance;
	}
}

/**
 * Action to run upon 'plugins_loaded' WordPress action.
 *
 * @return Lib\Init
 */
function wc_lending_works_init() {
	return WC_Gateway_Lendingworks::init();
}

add_action( 'plugins_loaded', '\WC_Lending_Works\wc_lending_works_init', 10 );

