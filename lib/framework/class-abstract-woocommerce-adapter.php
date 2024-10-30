<?php
/**
 * Abstract_Woocommerce_Adapter
 *
 * Implementation of LW_Framework for WooCommerce. Provide access to WooCommerce features without coupling this plugin
 * code to the overarching framework.
 *
 * @package WordPress
 * @subpackage WooCommerce
 * @version 1.0.0
 * @author  Lending Works Ltd
 * @license https://www.gnu.org/licenses/gpl-2.0.txt GNU/GPLv2
 * @link https://www.lendingworks.co.uk/
 * @since  1.0.0
 */

namespace WC_Lending_Works\Lib\Framework;

use WC_Lending_Works\Lib\LW_Framework;
use const WC_Lending_Works\PLUGIN_NAME;

/**
 * Abstract_Woocommerce_Adapter
 *
 * Implementation of LW_Framework for WooCommerce. Provide access to WooCommerce features without coupling this plugin
 * code to the overarching framework.
 */
abstract class Abstract_Woocommerce_Adapter implements LW_Framework {
	/**
	 * Main function for returning orders, uses the WC_Order_Factory class.
	 *
	 * @param int $id The order ID.
	 *
	 * @return bool|\WC_Order|\WC_Refund
	 */
	public function get_order( $id ) {
		return wc_get_order( $id );
	}

	/**
	 * Retrieve an order matching meta-data key with specific value.
	 *
	 * @param string $meta The meta key to filter.
	 * @param string $value The meta value to filter.
	 *
	 * @return array|\stdClass|\WC_Order[]
	 */
	public function get_order_by_meta( $meta, $value ) {
		return wc_get_orders( [ $meta => $value ] );
	}

	/**
	 * Performs a remote call to Lending Works api to create an order.
	 *
	 * @param string $url The Lending Works api url.
	 * @param array  $data An array of data to send as post payload.
	 *
	 * @return mixed
	 */
	public function post( $url, $data ) {
		return wp_remote_post( $url, $data );
	}

	/**
	 * Gets additional information from the order.
	 *
	 * @param mixed  $order The order to get meta for.
	 * @param string $key The key of the meta to retrieve.
	 *
	 * @return mixed
	 */
	abstract public function get_order_meta( $order, $key );

	/**
	 * Saves additional information on the order.
	 *
	 * @param string $order The order to update meta for.
	 * @param string $key The key of the meta to update.
	 * @param mixed  $value The value to update in order metas.
	 *
	 * @return mixed
	 */
	abstract public function update_order_meta( $order, $key, $value );

	/**
	 * Add and store a notice.
	 *
	 * @param string $message The message to display to the user.
	 * @param string $level The level of the notification.
	 *
	 * @return void
	 */
	public function notify( $message, $level ) {
		wc_add_notice( $message, $level );
	}

	/**
	 * Creates a cryptographic token tied to a specific action, user, user session, and window of time.
	 *
	 * @param string $value The value to use to generate a nonce.
	 *
	 * @return bool|string
	 */
	public function encrypt( $value ) {
		return wp_create_nonce( $value );
	}

	/**
	 * Generates a WC_Api url at which WooCommerce can receive a callback http request.
	 *
	 * @return string
	 */
	public function webhook_url() {
		return WC()->api_request_url( PLUGIN_NAME );
	}

	/**
	 * Registers the script if $src provided (does NOT overwrite), and enqueues it.
	 *
	 * @param string $handle The script name.
	 * @param string $src The script source location.
	 * @param array  $dependencies Array of dependencies.
	 * @param string $version Plugin version.
	 *
	 * @return void
	 */
	public function add_script( $handle, $src, $dependencies, $version ) {
		wp_enqueue_script(
			$handle,
			$src,
			$dependencies,
			$version,
			true
		);
	}

	/**
	 * Adds extra code to a registered script.
	 *
	 * @param string $handle The script name.
	 * @param string $template The template name storing the script content.
	 * @param array  $data Array of data to pass-in to the template.
	 * @param string $dir The directory where to find the template file.
	 *
	 * @return bool|string|null
	 */
	abstract public function add_inline_script( $handle, $template, $data, $dir );

	/**
	 * Verifies a value received from Lending Works remote service to authenticate an incoming http request.
	 *
	 * @param string $nonce The nonce received by remote service.
	 * @param string $value The value the nonce was generated from.
	 *
	 * @return bool|int
	 */
	public function authenticate( $nonce, $value ) {
		return wp_verify_nonce( $nonce, $value );
	}

	/**
	 * Gets the WooCommerce store url.
	 *
	 * @return string
	 */
	public function checkout_url() {
		return wc_get_checkout_url();
	}

	/**
	 * Redirects the user to the specified url.
	 *
	 * @param string $url The url to redirect user to.
	 *
	 * @return void
	 */
	public function redirect( $url ) {
		wp_safe_redirect( $url );

		if ( 'cli' !== PHP_SAPI ) {
			exit;
		}
	}

	/**
	 * Disables the Lending Works payment gateway for current user.
	 *
	 * @return void
	 */
	public function flag_payment_gateway_disabled_for_user() {
		WC()->session->set( PLUGIN_NAME . '_has_failed_payment', true );
	}

	/**
	 * Checks if the woocommerce version is below 3.0.
	 *
	 * @return bool
	 */
	abstract public function is_legacy();

	/**
	 * Gets the payment gateway being used to process the order payment.
	 *
	 * @return array
	 */
	public function get_payment_gateway() {
		return WC()->payment_gateways() ? WC()->payment_gateways->payment_gateways() : [];
	}

	/**
	 * Encode a variable into JSON, with some sanity checks.
	 *
	 * @param mixed $data Variable to encode as Json.
	 * @param int   $options Options to be passed to json_encode().
	 * @param int   $depth Maximum depth to walk through $data.
	 * @return false|string
	 */
	public function json_encode( $data, $options = 0, $depth = 512 ) {
		return wp_json_encode( $data, $options, $depth );
	}

	/**
	 * Gets the payment method used for an order.
	 *
	 * @param \WC_Order $order The order to get the payment method for.
	 *
	 * @return string
	 */
	abstract public function get_payment_method( \WC_Order $order );

	/**
	 * Send a JSON response back to an Ajax request.
	 *
	 * @param mixed $data The data to include in response.
	 * @param int   $status_code The status code of the response.
	 *
	 * @return void
	 */
	abstract public function response( $data, $status_code = 200 );

	/**
	 * Send a JSON response back to an Ajax request, indicating failure.
	 *
	 * @param mixed $data The data to include in the response.
	 * @param int   $status_code The status code of the response.
	 *
	 * @return mixed
	 */
	abstract public function error( $data, $status_code );

	/**
	 * Add a route to the WordPress site.
	 *
	 * @param string $namespace The namespace prefix for the route.
	 * @param string $route The route.
	 * @param array  $options Array of options for the route.
	 */
	public function add_route( $namespace, $route, $options ) {
		register_rest_route( $namespace, $route, $options );
	}

	/**
	 * Remove slashes from a string or array of strings.
	 *
	 * @param string|array $value The string value or array of string values to sanitize.
	 *
	 * @return string|array
	 */
	public function unslash( $value ) {
		return wp_unslash( $value );
	}

	/**
	 * Sanitizes a string.
	 *
	 * @param string $value The string value to sanitize.
	 *
	 * @return string
	 */
	public function sanitize( $value ) {
		return sanitize_text_field( $value );
	}
}
