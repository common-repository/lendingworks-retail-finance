<?php
/**
 * Checkout
 *
 * Class handling checkout steps of the payment gateway. When a customer adds products to the cart and proceed to the
 * checkout, when the checkout button is hit an order representing the current cart should be created on Lending Works
 * side and the WooCommerce order status should be set on 'on-hold' while the loan application is filled by the user.
 *
 * @package WordPress
 * @subpackage WooCommerce
 * @version 1.0.0
 * @author  Lending Works Ltd
 * @license https://www.gnu.org/licenses/gpl-2.0.txt GNU/GPLv2
 * @link https://www.lendingworks.co.uk/
 * @since  1.0.0
 */

namespace WC_Lending_Works\Lib\Checkout;

use UnexpectedValueException;
use WC_Lending_Works;
use WC_Lending_Works\Lib\LW_Framework;
use WC_Lending_Works\Lib\Order\Order_Repository;
use WC_Lending_Works\Lib\Payment_Gateway;

/**
 * Checkout
 *
 * Class handling checkout steps of the payment gateway. When a customer adds products to the cart and proceed to the
 * checkout, when the checkout button is hit an order representing the current cart should be created on Lending Works
 * side and the WooCommerce order status should be set on 'on-hold' while the loan application is filled by the user.
 */
class Checkout {
	/**
	 * The WooCommerce adapter providing access to framework static methods.
	 *
	 * @var LW_Framework
	 */
	private $woocommerce;

	/**
	 * The order repository.
	 *
	 * @var Order_Repository
	 */
	private $order_repository;

	/**
	 * Checkout constructor.
	 *
	 * @param LW_Framework     $woocommerce The woocommerce adapter.
	 * @param Order_Repository $order_repository The order repository.
	 */
	public function __construct( LW_Framework $woocommerce, Order_Repository $order_repository ) {
		$this->woocommerce      = $woocommerce;
		$this->order_repository = $order_repository;
	}

	/**
	 * Processes the order payment.
	 *
	 * @param int $order_id The order ID to purchase.
	 *
	 * @return array
	 */
	public function checkout( $order_id ) {
		$order = $this->woocommerce->get_order( $order_id );

		try {
			$response = $this->order_repository->create( $order );

			if ( $response->is_error() ) {
				return $this->redirect(
					'failure',
					$order->get_checkout_payment_url(),
					$response->get_error_message()
				);
			}

			$order_token = $response->get_order_token();
		} catch ( UnexpectedValueException $e ) {
			// Catching exception with Json request encoding or response decoding.
			return $this->redirect(
				'failure',
				$order->get_checkout_payment_url(),
				$e->getMessage()
			);
		}

		$this->woocommerce->update_order_meta( $order, Payment_Gateway::ORDER_TOKEN_METADATA_KEY, $order_token );

		// Mark as pending payment (we're awaiting the loan acceptation).
		$order->update_status( 'pending', __( 'Awaiting loan approval.', 'lendingworks' ) );

		return $this->redirect( 'success', $order->get_checkout_payment_url( true ) );
	}

	/**
	 * Disables the Lending Works payment gateway when a loan was declined.
	 *
	 * @param int|null $order_id The order id.
	 * @param int|null $order The order object.
	 *
	 * @return void
	 */
	public function disable_gateway( $order_id = null, $order = null ) {
		$this->woocommerce->flag_payment_gateway_disabled_for_user();
	}

	/**
	 * Handles the payment result.
	 *
	 * @param string      $result  The payment result. Can be 'success' or 'failure'.
	 * @param string      $url     The URL to redirect to.
	 * @param string|null $message The message to display when result is 'failure'.
	 *
	 * @return array
	 */
	private function redirect( $result, $url, $message = null ) {
		if ( 'success' !== $result ) {
			$this->woocommerce->notify( $message, 'error' );
		}

		return [
			'result'   => $result,
			'redirect' => $url,
		];
	}
}
