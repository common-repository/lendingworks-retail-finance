<?php
/**
 * Request_Legacy
 *
 * Class extending Abstract_Create_Request and modelling an HTTP request compatible with WooCommerce version prior to 3.0
 * sent to Lending Works in order to create a matching order.
 *
 * @package WordPress
 * @subpackage WooCommerce
 * @version 1.0.0
 * @author  Lending Works Ltd
 * @license https://www.gnu.org/licenses/gpl-2.0.txt GNU/GPLv2
 * @link https://www.lendingworks.co.uk/
 * @since  1.0.0
 */

namespace WC_Lending_Works\Lib\Http\Create;

use UnexpectedValueException;

/**
 * Request_Legacy
 *
 * Class extending Abstract_Create_Request and modelling an HTTP request compatible with WooCommerce version prior to 3.0
 * sent to Lending Works in order to create a matching order.
 */
class Request_Legacy extends Abstract_Create_Request {

	/**
	 * Returns the url of Lendinworks api order creation endpoint.
	 *
	 * @return string
	 */
	public function get_url() {
		return $this->base_url . 'orders';
	}

	/**
	 * Returns the request body containing the order details.
	 *
	 * @param \WC_Order $order The ordder to send a request for.
	 *
	 * @return false|string
	 */
	public function get_body( \WC_Order $order ) {
		$items = $order->get_items();

		$products = [];

		foreach ( $items as $item ) {
			$products[] = [
				'cost'        => (float) $order->get_item_total( $item, true ),
				'quantity'    => isset( $item['qty'] ) ? $item['qty'] : 0,
				'description' => isset( $item['name'] ) ? $item['name'] : '',
			];
		}

		$products[] = [
			'cost'        => (float) $order->calculate_shipping(),
			'quantity'    => 1,
			'description' => 'Shipping: ' . $order->get_shipping_method(),
		];

		$discount = $order->get_total_discount();
		if ( $discount > 0 ) {
			$products[] = [
				'cost'        => 0.0 - $discount,
				'quantity'    => 1,
				'description' => 'Discount',
			];
		}

		$json = $this->woocommerce->json_encode(
			[
				'amount'   => $order->get_total(),
				'products' => $products,
			]
		);

		return $json;
	}
}
