<?php
/**
 * Request
 *
 * Class extending Abstract_Fulfill_Request and modelling an HTTP request sent to Lending Works in order to fulfill a loan request.
 *
 * @package WordPress
 * @subpackage WooCommerce
 * @version 1.0.0
 * @author  Lending Works Ltd
 * @license https://www.gnu.org/licenses/gpl-2.0.txt GNU/GPLv2
 * @link https://www.lendingworks.co.uk/
 * @since  1.0.0
 */

namespace WC_Lending_Works\Lib\Http\Fulfill;

use WC_Lending_Works\Lib\Payment_Gateway;

/**
 * Request
 *
 * Class extending Abstract_Fulfill_Request and modelling an HTTP request sent to Lending Works in order to fulfill a loan request.
 */
class Request extends Abstract_Fulfill_Request {

	/**
	 * Returns the url of Lendinworks api order creation endpoint.
	 *
	 * @return string
	 */
	public function get_url() {
		return $this->base_url . 'loan-requests/fulfill';
	}

	/**
	 * Returns the request body containing the order details.
	 *
	 * @param \WC_Order $order The ordder to send a request for.
	 *
	 * @return false|string
	 */
	public function get_body( \WC_Order $order ) {
		$reference = $this->woocommerce->get_order_meta( $order, Payment_Gateway::ORDER_REFERENCE_METADATA_KEY );

		$json = $this->woocommerce->json_encode(
			[
				'reference' => $reference,
			]
		);

		return $json;
	}
}
