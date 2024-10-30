<?php
/**
 * LW_Framework
 *
 * Interface specifying contract between this plugin code and the framework it executes within.
 * Its implementations allow the code of this plugin to communicate with the overarching framework without
 * tightly coupling it to any specific vendor or version.
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

/**
 * LW_Framework
 *
 * Interface specifying contract between this plugin code and the framework it executes within.
 * Its implementations allow the code of this plugin to communicate with the overarching framework without
 * tightly coupling it to any specific vendor or version.
 */
interface LW_Framework {
	/**
	 * Main function for returning orders, uses the WC_Order_Factory class.
	 *
	 * @param int $id The ID of order to retrieve from storage.
	 *
	 * @return bool|\WC_Order|\WC_Refund
	 */
	public function get_order( $id );

	/**
	 * Retrieve an order matching meta-data key with specific value.
	 *
	 * @param string $meta The meta key to filter.
	 * @param string $value The meta value to filter.
	 *
	 * @return array|\stdClass|\WC_Order[]
	 */
	public function get_order_by_meta( $meta, $value );

	/**
	 * Performs a remote call to Lending Works api to create an order.
	 *
	 * @param string $url The url of Lending Works remote servers to post data to.
	 * @param array  $data The request data.
	 *
	 * @return mixed
	 */
	public function post( $url, $data );

	/**
	 * Gets additional information from the order.
	 *
	 * @param mixed  $order The order for which to retrieve meta data.
	 * @param string $key The key of meta data to retrieve.
	 *
	 * @return mixed
	 */
	public function get_order_meta( $order, $key );

	/**
	 * Saves additional information on the order.
	 *
	 * @param mixed  $order The order for which to save a meta data.
	 * @param string $key The key of meta data to save.
	 * @param mixed  $value The value to save in order meta data.
	 *
	 * @return mixed
	 */
	public function update_order_meta( $order, $key, $value );

	/**
	 * Add and store a notice.
	 *
	 * @param string $message The notification message.
	 * @param string $level The notification level.
	 *
	 * @return void
	 */
	public function notify( $message, $level );

	/**
	 * Creates a cryptographic token tied to a specific action, user, user session, and window of time.
	 *
	 * @param mixed $value The value to encrypt.
	 *
	 * @return bool|string
	 */
	public function encrypt( $value );

	/**
	 * Generates a WC_Api url at which WooCommerce can receive a callback http request.
	 *
	 * @return string
	 */
	public function webhook_url();

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
	public function add_script( $handle, $src, $dependencies, $version );

	/**
	 * Adds extra code to a registered script.
	 *
	 * @param string $handle The script name.
	 * @param string $template The template name storing the script content.
	 * @param array  $data Array of data to pass-in to the template.
	 * @param string $dir The directory where to find the template file.
	 *
	 * @return void
	 */
	public function add_inline_script( $handle, $template, $data, $dir );

	/**
	 * Verifies a value received from Lending Works remote service to authenticate an incoming http request.
	 *
	 * @param string $nonce The nonce received by remote service.
	 * @param string $value The value the nonce was generated from.
	 *
	 * @return bool|int
	 */
	public function authenticate( $nonce, $value );

	/**
	 * Gets the WooCommerce store url.
	 *
	 * @return string
	 */
	public function checkout_url();

	/**
	 * Redirects the user to the specified url.
	 *
	 * @param string $url The url to redirect user to.
	 *
	 * @return void
	 */
	public function redirect( $url );

	/**
	 * Disables the Lending Works payment gateway for current user.
	 *
	 * @return void
	 */
	public function flag_payment_gateway_disabled_for_user();

	/**
	 * Gets the payment gateway being used to process the order payment.
	 *
	 * @return array
	 */
	public function get_payment_gateway();

	/**
	 * Send a JSON response back to an Ajax request.
	 *
	 * @param mixed $data The data to send back as json.
	 * @param int   $status_code The response status code.
	 *
	 * @return string
	 */
	public function response( $data, $status_code = 200 );

	/**
	 * Send a JSON response back to an Ajax request, indicating failure.
	 *
	 * @param mixed $data The data to send back as json.
	 * @param int   $status_code The response status code.
	 *
	 * @return mixed
	 */
	public function error( $data, $status_code );

	/**
	 * Add a route to the WordPress site.
	 *
	 * @param string $namespace The namespace prefix for the route.
	 * @param string $route The route.
	 * @param array  $options Array of options for the route.
	 */
	public function add_route( $namespace, $route, $options );

	/**
	 * Remove slashes from a string or array of strings.
	 *
	 * @param string|array $value The string value or array of string values to sanitize.
	 *
	 * @return string|array
	 */
	public function unslash( $value );

	/**
	 * Sanitizes a string.
	 *
	 * @param string $value The string value to sanitize.
	 *
	 * @return string
	 */
	public function sanitize( $value );

}
