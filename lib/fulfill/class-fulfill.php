<?php
/**
 * Fulfill
 *
 * Class handling orders fulfillment concerns of the payment gateway. When a customer completes a Lending Works loan application,
 * and the store owner marks the order as completed and ready to ship, the order on Lending Works side should be updated
 * accordingly to inform Lending Works to proceed with paying-out to the store owner.
 *
 * @package WordPress
 * @subpackage WooCommerce
 * @version 1.0.0
 * @author  Lending Works Ltd
 * @license https://www.gnu.org/licenses/gpl-2.0.txt GNU/GPLv2
 * @link https://www.lendingworks.co.uk/
 * @since  1.0.0
 */

namespace WC_Lending_Works\Lib\Fulfill;

use UnexpectedValueException;
use WC_Lending_Works\Lib\Http\Fulfill\Response;
use WC_Lending_Works\Lib\LW_Framework;
use WC_Lending_Works\Lib\Order\Order_Repository;
use WC_Lending_Works\Lib\Payment_Gateway;
use const WC_Lending_Works\PLUGIN_NAME;
use const WC_Lending_Works\PLUGIN_DIR;
use const WC_Lending_Works\PLUGIN_VERSION;

/**
 * Fulfill
 *
 * Class handling orders fulfillment concerns of the payment gateway. When a customer completes a Lending Works loan application,
 * and the store owner marks the order as completed and ready to ship, the order on Lending Works side should be updated
 * accordingly to inform Lending Works to proceed with paying-out to the store owner.
 */
class Fulfill {
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
	 * The flag denoting manual fulfillment is allowed.
	 *
	 * @var bool
	 */
	private $fulfillment_allowed;

	/**
	 * Fulfill constructor.
	 *
	 * @param LW_Framework     $woocommerce The woocommerce adapter.
	 * @param Order_Repository $order_repository The order repository.
	 * @param bool             $fulfillment_allowed Whether manual fulfillment is allowed or not.
	 */
	public function __construct( LW_Framework $woocommerce, Order_Repository $order_repository, $fulfillment_allowed ) {
		$this->woocommerce         = $woocommerce;
		$this->order_repository    = $order_repository;
		$this->fulfillment_allowed = $fulfillment_allowed;
	}

	/**
	 * Adds a 'Fulfill order' in admin order details page.
	 *
	 * @param \WC_Order $order The order to fulfill.
	 */
	public function process_order_options( $order ) {
		$loan_request_reference = $this->woocommerce->get_order_meta( $order, Payment_Gateway::ORDER_REFERENCE_METADATA_KEY );
		$order_id               = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;

		$disabled = $this->is_disabled( $order ) ? 'disabled ' : '';

		if ( $this->fulfillment_allowed
			&& $this->is_lending_works_order( $order )
			&& empty( $order->get_total_refunded() )
		) {
            // phpcs:disable WordPress.Security.EscapeOutput
			echo "<p class='form-field form-field-wide lw-wc-order-fulfill'>
					  <label for='fulfill-item'>Lending Works:</label>
					  <input id='fulfill-item' type='submit' class='button fulfill-items' value='Fulfill order' 
						data-order-reference='$loan_request_reference'
						data-order-id='$order_id' $disabled/>
				  </p>";
            // phpcs:enable WordPress.Security.EscapeOutput
		}
	}

	/**
	 * Verifies that an order was paid using Lending Works.
	 *
	 * @param \WC_Order $order The order to fulfill.
	 *
	 * @return bool
	 */
	private function is_lending_works_order( $order ) {
		$payment_gateways = $this->woocommerce->get_payment_gateway();

		$payment_method = $this->woocommerce->get_payment_method( $order );

		return PLUGIN_NAME === $payment_gateways[ $payment_method ]->id;
	}

	/**
	 * Checks if an order fulfill button should be enabled.
	 *
	 * @param WC_Order $order The order to check.
	 *
	 * @return boolean
	 */
	private function is_disabled( $order ) {
		return $order->get_status() !== 'processing'
			|| 'fulfilled' === $this->woocommerce->get_order_meta(
				$order,
				Payment_Gateway::ORDER_FULFILLED_METADATA_KEY
			);
	}

	/**
	 * Load script to handle click event on Fulfill order admin button.
	 */
	public function load_script() {
		$this->woocommerce->add_script(
			PLUGIN_NAME,
			PLUGIN_DIR . 'templates/fulfillOrder.js',
			[],
			PLUGIN_VERSION
		);
	}

	/**
	 * Fulfill an order when the 'Fulfill order' button is hit.
	 */
	public function ajax_fulfill_order() {
        // phpcs:disable WordPress.Security.NonceVerification
		$input = $this->woocommerce->unslash( $_POST );

		$order_id = isset( $_POST['order_id'] ) ? $this->woocommerce->sanitize( $input['order_id'] ) : 0;
        // phpcs:enable WordPress.Security.NonceVerification
		$order = $this->woocommerce->get_order( $order_id );

		try {
			$response = $this->fulfill_order( $order );

			if ( $response->is_error() ) {
				$this->woocommerce->error( $response->get_error_message(), 500 );
			}

			$this->woocommerce->response( __( 'Order fulfilled' ) );
		} catch ( UnexpectedValueException $exception ) {
			$this->woocommerce->error( $exception->getMessage(), 500 );
		}
	}

	/**
	 * Fulfill an order when it is marked as completed.
	 *
	 * @param int       $order_id The order ID.
	 * @param \WC_Order $order The order to fulfill.
	 */
	public function complete_order( $order_id, \WC_Order $order ) {
		if ( ! $this->fulfillment_allowed
			&& 'fulfilled' !== $this->woocommerce->get_order_meta( $order, Payment_Gateway::ORDER_FULFILLED_METADATA_KEY )
		) {
			$this->fulfill_order( $order );
		}
	}

	/**
	 * Fulfills Lending Works loan request.
	 *
	 * @param \WC_Order $order The order to fulfill.
	 *
	 * @return Response
	 */
	private function fulfill_order( \WC_Order $order ) {
		$response = $this->order_repository->fulfill( $order );

		if ( ! $response->is_error() || __( 'Loan request is already fulfilled.' ) === $response->get_error_message() ) {
			$this->woocommerce->update_order_meta( $order, Payment_Gateway::ORDER_FULFILLED_METADATA_KEY, 'fulfilled' );
			if ( method_exists( $order, 'save' ) ) {
				$order->save();
			}
		}

		return $response;
	}
}
