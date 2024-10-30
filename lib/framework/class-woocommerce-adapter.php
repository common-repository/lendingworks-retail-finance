<?php
/**
 * Woocommerce_Adapter
 *
 * Implementation of LW_Framework for WooCommerce from version 3.0. Provide access to WooCommerce 3.0 features without coupling this plugin
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

use const WC_Lending_Works\PLUGIN_NAME;
use const WC_Lending_Works\PLUGIN_DIR;

/**
 * Woocommerce_Adapter
 *
 * Implementation of LW_Framework for WooCommerce from version 3.0. Provide access to WooCommerce 3.0 features without coupling this plugin
 * code to the overarching framework.
 */
class Woocommerce_Adapter extends Abstract_Woocommerce_Adapter {
	/**
	 * Gets additional information from the order.
	 *
	 * @param mixed  $order The order to get meta for.
	 * @param string $key The key of the meta to retrieve.
	 *
	 * @return mixed
	 */
	public function get_order_meta( $order, $key ) {
		return $order->get_meta( $key );
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
		return $order->update_meta_data( $key, $value );
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
		return wp_add_inline_script(
			$handle,
			wc_get_template_html( $template, $data, '', $dir . 'templates/' )
		);
	}

	/**
	 * Checks if the woocommerce version is below 3.0.
	 *
	 * @return bool
	 */
	public function is_legacy() {
		return false;
	}

	/**
	 * Gets the payment method used for an order.
	 *
	 * @param \WC_Order $order The order to get the payment method for.
	 *
	 * @return string
	 */
	public function get_payment_method( \WC_Order $order ) {
		return $order->get_payment_method();
	}

	/**
	 * Send a JSON response back to an Ajax request.
	 *
	 * @param mixed $data The data to include in response.
	 * @param int   $status_code The status code of the response.
	 *
	 * @return void
	 */
	public function response( $data, $status_code = 200 ) {
		wp_send_json( $data, $status_code );
	}

	/**
	 * Send a JSON response back to an Ajax request, indicating failure.
	 *
	 * @param mixed $data The data to include in the response.
	 * @param int   $status_code The status code of the response.
	 *
	 * @return mixed
	 */
	public function error( $data, $status_code ) {
		wp_send_json_error( $data, $status_code );
	}
}
