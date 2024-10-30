<?php
/**
 * Order_Repository
 *
 * Class handling remote orders responsibilities. When an order is checked-out using Lending Works payment gateway,
 * the order is created remotely on Lending Works server. Upon completion of the order, an order fulfillment request
 * is sent to Lending Works to inform them the order has been completed and shipped, and the payment can be made by Lending
 * Works on behalf of the customer.
 *
 * @package WordPress
 * @subpackage WooCommerce
 * @version 1.0.0
 * @author  Lending Works Ltd
 * @license https://www.gnu.org/licenses/gpl-2.0.txt GNU/GPLv2
 * @link https://www.lendingworks.co.uk/
 * @since  1.0.0
 */

namespace WC_Lending_Works\Lib\Order;

use WC_Lending_Works\Lib\Http;
use WC_Lending_Works\Lib\LW_Framework;
use WC_Order;

/**
 * Order_Repository
 *
 * Class handling remote orders responsibilities. When an order is checked-out using Lending Works payment gateway,
 * the order is created remotely on Lending Works server. Upon completion of the order, an order fulfillment request
 * is sent to Lending Works to inform them the order has been completed and shipped, and the payment can be made by Lending
 * Works on behalf of the customer.
 *
 * @package WordPress
 * @subpackage WooCommerce
 * @version 1.0.0
 * @author  Lending Works Ltd
 * @license https://www.gnu.org/licenses/gpl-2.0.txt GNU/GPLv2
 * @link https://www.lendingworks.co.uk/
 * @since  1.0.0
 */
class Order_Repository {

	/**
	 * The WooCommerce adapter providing access to framework static methods.
	 *
	 * @var LW_Framework
	 */
	private $woocommerce;

	/**
	 * The request object modelling data transferred for order creation.
	 *
	 * @var Http\Create\Request
	 */
	private $create_request;

	/**
	 * The request object modelling data transferred for order fulfillment.
	 *
	 * @var Http\Fulfill\Request
	 */
	private $fulfill_request;

	/**
	 * Order_Repository constructor.
	 *
	 * @param LW_Framework                          $woocommerce The woocommerce adapter.
	 * @param Http\Create\Abstract_Create_Request   $create_request The request to create an order on Lending Works remote servers.
	 * @param Http\Fulfill\Abstract_Fulfill_Request $fulfill_request The request to fulfill an order on Lending Works remote servers.
	 */
	public function __construct(
		LW_Framework $woocommerce,
		Http\Create\Abstract_Create_Request $create_request,
		Http\Fulfill\Abstract_Fulfill_Request $fulfill_request
	) {
		$this->woocommerce     = $woocommerce;
		$this->create_request  = $create_request;
		$this->fulfill_request = $fulfill_request;
	}

	/**
	 * Performs a remote call to Lending Works api on the /orders create endpoint.
	 *
	 * @param WC_Order $order The woocommerce order.
	 *
	 * @return Http\Create\Response
	 */
	public function create( WC_Order $order ) {
		return new Http\Create\Response(
			$this->woocommerce->post(
				$this->create_request->get_url(),
				[
					'headers' => $this->create_request->get_headers(),
					'body'    => $this->create_request->get_body( $order ),
				]
			)
		);
	}

	/**
	 * Performs a remote call to Lending Works api on the /loan-requests/fulfill endpoint.
	 *
	 * @param WC_Order $order The order for which to fulfill a loan request.
	 *
	 * @return Http\Fulfill\Response
	 */
	public function fulfill( WC_Order $order ) {
		return new Http\Fulfill\Response(
			$this->woocommerce->post(
				$this->fulfill_request->get_url(),
				[
					'headers' => $this->fulfill_request->get_headers(),
					'body'    => $this->fulfill_request->get_body( $order ),
				]
			)
		);
	}
}
