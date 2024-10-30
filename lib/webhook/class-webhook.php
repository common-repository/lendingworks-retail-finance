<?php
/**
 * Webhook
 *
 * Class handling webhooks concerns of the payment gateway. When a customer completes a Lending Works loan application,
 * the details and status of this applications are reflected on the WooCommerce order to inform the store owner of the
 * payment status.
 *
 * @package WordPress
 * @subpackage WooCommerce
 * @version 1.0.0
 * @author  Lending Works Ltd
 * @license https://www.gnu.org/licenses/gpl-2.0.txt GNU/GPLv2
 * @link https://www.lendingworks.co.uk/
 * @since  1.0.0
 */

namespace WC_Lending_Works\Lib\Webhook;

use WC_Lending_Works\Lib\LW_Framework;
use WC_Lending_Works\Lib\Order\Order_Status_Update;
use WC_Lending_Works\Lib\Payment_Gateway;

/**
 * Webhook
 *
 * Class handling webhooks concerns of the payment gateway. When a customer completes a Lending Works loan application,
 * the details and status of this applications are reflected on the WooCommerce order to inform the store owner of the
 * payment status.
 */
class Webhook {
	/**
	 * The WooCommerce adapter providing access to framework static methods.
	 *
	 * @var LW_Framework
	 */
	private $woocommerce;

	/**
	 * Webhook constructor.
	 *
	 * @param LW_Framework $woocommerce The woocommerce adapter.
	 */
	public function __construct( LW_Framework $woocommerce ) {
		$this->woocommerce = $woocommerce;
	}

	/**
	 * Processes the inbound Lending Works http request to update the payment and order when loan application is completed.
	 *
	 * @return array
	 */
	public function process() {
        // phpcs:disable WordPress.Security.NonceVerification
		if ( isset( $_POST['order_id'], $_POST['reference'], $_POST['status'], $_POST['nonce'] ) ) {
			$input = $this->woocommerce->unslash( $_POST );
            // phpcs:enable WordPress.Security.NonceVerification
			$order_id               = $this->woocommerce->sanitize( $input['order_id'] );
			$loan_request_reference = $this->woocommerce->sanitize( $input['reference'] );
			$status                 = $this->woocommerce->sanitize( $input['status'] );
			$nonce                  = $this->woocommerce->sanitize( $input['nonce'] );
		} else {
			$this->woocommerce->notify( 'There was a problem with your loan application. Please try again.', 'error' );
			$this->woocommerce->redirect( $this->woocommerce->checkout_url() );
			return [];
		}

		$order = $this->woocommerce->get_order( $order_id );

		$order_token = $this->woocommerce->get_order_meta( $order, Payment_Gateway::ORDER_TOKEN_METADATA_KEY );

		if ( ! $this->woocommerce->authenticate( $nonce, $order_token ) ) {
			return [
				'result'   => 'failure',
				'redirect' => $this->woocommerce->checkout_url(),
			];
		}

		// Update lendingworks_status of the order to what was reported by RF module.
		$status_update = new Order_Status_Update( $status );

		if ( $status_update->is_valid() ) {
			$this->woocommerce->update_order_meta( $order, Payment_Gateway::ORDER_STATUS_METADATA_KEY, $status );

			if ( ! empty( $loan_request_reference ) ) {
				$this->woocommerce->update_order_meta( $order, Payment_Gateway::ORDER_REFERENCE_METADATA_KEY, $loan_request_reference );
			}

			switch ( true ) {
				case $status_update->is_accepted():
					$order->payment_complete();
					return $this->woocommerce->redirect( $order->get_checkout_order_received_url() );
				case $status_update->is_cancelled():
					$order->update_status( 'pending', __( 'Loan cancelled or expired', 'lendingworks' ) );
					$this->woocommerce->notify( __( 'Your Loan quote was cancelled or expired.' ), 'error' );
					break;
				case $status_update->is_declined():
					$order->update_status( 'failed', __( 'Loan declined', 'lendingworks' ) );
					$this->woocommerce->notify( __( 'Please use an alternative payment method.' ), 'error' );
					break;
			}
		} else {
			$this->woocommerce->notify( __( 'Status invalid' ), 'error' );
		}

		$this->woocommerce->redirect( $this->woocommerce->checkout_url() );
	}
}
