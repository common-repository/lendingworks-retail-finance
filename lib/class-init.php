<?php
/**
 * Init
 *
 * Class responsible for starting the Lending Works payment gateway. It is hooking various event handling classes to
 * WooCommerce and WordPress actions and filters.
 *
 * @package WordPress
 * @subpackage WooCommerce
 * @version 1.0.0
 * @author  Lending Works Ltd
 * @license https://www.gnu.org/licenses/gpl-2.0.txt GNU/GPLv2
 * @link https://www.lendingworks.co.uk/
 * @since  1.0.0
 */

namespace WC_Lending_Works\Lib;

use WC_Lending_Works;
use WC_Lending_Works\Lib\Callback\Callback;
use WC_Lending_Works\Lib\Checkout\Checkout;
use WC_Lending_Works\Lib\Fulfill\Fulfill;
use WC_Lending_Works\Lib\Pay\Pay;
use WC_Lending_Works\Lib\Webhook\Webhook;
use const WC_Lending_Works\PLUGIN_NAME;
use const WC_Lending_Works\PLUGIN_VERSION;

/**
 * Init
 *
 * Class responsible for starting the Lending Works payment gateway. It is hooking various event handling classes to
 * WooCommerce and WordPress actions and filters.
 */
class Init {
	/**
	 * The WordPress Plugin API.
	 *
	 * @var WC_Lending_Works\Lib\Loader
	 */
	private $loader;

	/**
	 * The checkout instance for checkout steps logic.
	 *
	 * @var Checkout
	 */
	private $checkout;

	/**
	 * The Pay instance for pay steps logic.
	 *
	 * @var Pay
	 */
	private $pay;

	/**
	 * The Webhook instance for webhook steps logic.
	 *
	 * @var Webhook
	 */
	private $webhook;

	/**
	 * The Fulfill instance for fulfillment steps logic.
	 *
	 * @var Fulfill
	 */
	private $fulfill;

	/**
	 * The Callback instance for responding to Lending Works order status updates callbacks.
	 *
	 * @var Callback
	 */
	private $callback;

	/**
	 * The plugin id.
	 *
	 * @var string $id
	 */
	private $id;

	/**
	 * Init constructor.
	 *
	 * @param Loader   $loader The plugin loader instance.
	 * @param Checkout $checkout The checkout hooks instance.
	 * @param Pay      $pay The pay hooks instance.
	 * @param Webhook  $webhook The webhook hooks instance.
	 * @param Fulfill  $fulfill The fulfill hooks instance.
	 * @param Callback $callback The callback hooks instance.
	 */
	public function __construct( Loader $loader, Checkout $checkout, Pay $pay, Webhook $webhook, Fulfill $fulfill, Callback $callback ) {
		$this->loader   = $loader;
		$this->checkout = $checkout;
		$this->pay      = $pay;
		$this->webhook  = $webhook;
		$this->fulfill  = $fulfill;
		$this->callback = $callback;

		$this->declare_payment_gateway();
		$this->define_admin_hooks();
		$this->define_checkout_hooks();
	}

	/**
	 * Register this Payment Gateway to WooCommerce.
	 */
	private function declare_payment_gateway() {
		$this->loader->add_filter( 'woocommerce_payment_gateways', $this, 'add_gateway_classes' );
		$this->loader->add_filter( 'woocommerce_available_payment_gateways', $this, 'setup_gateway' );
	}

	/**
	 * Callback for payment gateway registration.
	 *
	 * @param array $methods The registered payment methods.
	 *
	 * @return array
	 */
	public function add_gateway_classes( $methods ) {
		// Not enabling the gateway if user session shows a previously failed payment.
		if ( ! is_admin() && true === WC()->session->get( PLUGIN_NAME . '_has_failed_payment' ) ) {
			return $methods;
		}

		$methods[] = Payment_Gateway::class;
		return $methods;
	}

	/**
	 * Sets the checkout instance on the gateway, so that the gateway can have its dependencies injectied via constructor.
	 *
	 * @param array $methods The available payment gateways.
	 *
	 * @return mixed
	 */
	public function setup_gateway( $methods ) {
		if ( isset( $methods[ PLUGIN_NAME ] ) ) {
			$gateway = $methods[ PLUGIN_NAME ];

			$gateway->set_checkout( $this->checkout );

			$methods[ PLUGIN_NAME ] = $gateway;
		}

		return $methods;
	}

	/**
	 * Register all the hooks related to the admin area.
	 */
	private function define_admin_hooks() {
		$admin_plugin = new Payment_Gateway();

		$tag = 'woocommerce_update_options_payment_gateways_' . $admin_plugin->id;
		$this->loader->add_action( $tag, $admin_plugin, 'process_admin_options' );
		$this->loader->add_filter( 'woocommerce_settings_api_sanitized_fields_wc-lendingworks', $admin_plugin, 'validate_admin_options' );

		$this->loader->add_action( 'woocommerce_admin_order_data_after_order_details', $this->fulfill, 'process_order_options', 10, 4 );
		$this->loader->add_action( 'admin_footer', $this->fulfill, 'load_script' );
		$this->loader->add_action( 'wp_ajax_fulfill-order', $this->fulfill, 'ajax_fulfill_order' );
		$this->loader->add_action( 'woocommerce_order_status_processing_to_completed', $this->fulfill, 'complete_order', 10, 2 );

		// Registering a route for callback url when loan request is updated on Lending Works side and that needs to be reflected on WooCommerce order.
		$this->loader->add_action( 'rest_api_init', $this->callback, 'regiter_callback_route' );
		$this->loader->add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', $this->callback, 'handle_custom_query', 10, 2 );
	}

	/**
	 * Register all the hooks related to the checkout page.
	 */
	private function define_checkout_hooks() {
		// Actions below are for printing out a form and relevant JS scripts on pay-order page to show the iFrame.
		$this->loader->add_action( 'woocommerce_receipt_wc-lending-works', $this->pay, 'print_form' );
		$this->loader->add_action( 'woocommerce_receipt_wc-lending-works', $this->pay, 'load_scripts' );

		// Actions below handle the response of LW RF module and reflect the status in WooCommerce order.
		$this->loader->add_action( 'woocommerce_api_wc-lending-works', $this->webhook, 'process' );

		// Action below is to disable this payment gateway for the user who just had a loan application declined.
		$this->loader->add_action( 'woocommerce_order_status_pending_to_failed', $this->checkout, 'disable_gateway', 10, 2 );
	}

	/**
	 * Run the loader and register all the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}
}
