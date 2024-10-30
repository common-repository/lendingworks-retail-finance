<?php
/**
 * Woocommerce_Adapter
 *
 * Implementation of LW_Framework for WooCommerce versions prior to 3.0. Provide access to WooCommerce versions 2.6.* and above features without coupling this plugin
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

/**
 * Woocommerce_Adapter
 *
 * Implementation of LW_Framework for WooCommerce versions prior to 3.0. Provide access to WooCommerce versions 2.6.* and above features without coupling this plugin
 * code to the overarching framework.
 */
class Woocommerce_Adapter_Legacy extends Abstract_Woocommerce_Adapter {
	/**
	 * Gets additional information from the order.
	 *
	 * @param mixed  $order The order to get meta for.
	 * @param string $key The key of the meta to retrieve.
	 *
	 * @return mixed
	 */
	public function get_order_meta( $order, $key ) {
		$meta = get_post_meta( $order->id, $key );
		return count( $meta ) > 0 ? $meta[0] : null;
	}

	/**
	 * Saves additional information on the order.
	 *
	 * @param string $order The order to update meta for.
	 * @param string $key The key of the meta to update.
	 * @param mixed  $value The value to update in order metas.
	 *
	 * @return mixed
	 */
	public function update_order_meta( $order, $key, $value ) {
		return update_post_meta( $order->id, $key, $value );
	}

	/**
	 * Adds extra code to a registered script.
	 *
	 * @param string $handle The script name.
	 * @param string $template The template name storing the script content.
	 * @param array  $data Array of data to pass-in to the template.
	 * @param string $dir The directory where to find the template file.
	 *
	 * @return bool
	 */
	public function add_inline_script( $handle, $template, $data, $dir ) {
		$script = new \WP_Scripts();
		$script->add( $handle, '' );
		$script->add_data( $handle, 'data', wc_get_template_html( $template, $data, '', $dir . 'templates/' ) );
		return $script->print_extra_script( $handle );
	}

	/**
	 * Checks if the woocommerce version is below 3.0.
	 *
	 * @return bool
	 */
	public function is_legacy() {
		return true;
	}

	/**
	 * Gets the payment method used for an order.
	 *
	 * @param \WC_Order $order The order to get the payment method for.
	 *
	 * @return string
	 */
	public function get_payment_method( \WC_Order $order ) {
		$payment_methods = get_post_meta( $order->id, '_payment_method' );

		if ( count( $payment_methods ) > 0 ) {
			return $payment_methods[0];
		}

		return '';
	}

	/**
	 * Send a JSON response back to an Ajax request.
	 *
	 * @param mixed $data The data to include in response.
	 * @param int   $status_code The status code of the response.
	 *
	 * @return void
	 */
	public function response( $data, $status_code = null ) {
		wp_send_json( $data );
	}

	/**
	 * Send a JSON response back to an Ajax request, indicating failure.
	 *
	 * @param mixed $data The data to include in the response.
	 * @param int   $status_code The status code of the response.
	 *
	 * @return mixed
	 */
	public function error( $data, $status_code = null ) {
		wp_send_json_error( $data );
	}
}
