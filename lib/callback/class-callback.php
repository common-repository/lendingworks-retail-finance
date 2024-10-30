<?php
/**
 * Callback
 *
 * Class handling callback requests sent from Lending Works when a loan application is being processed further and its
 * status needs to be reflected on the order in WooCommerce.
 *
 * @package WordPress
 * @subpackage WooCommerce
 * @version 1.0.0
 * @author  Lending Works Ltd
 * @license https://www.gnu.org/licenses/gpl-2.0.txt GNU/GPLv2
 * @link https://www.lendingworks.co.uk/
 * @since  1.0.0
 */

namespace WC_Lending_Works\Lib\Callback;

use WC_Lending_Works\Lib\LW_Framework;
use WC_Lending_Works\Lib\Order\Order_Status_Update;
use WC_Lending_Works\Lib\Payment_Gateway;
use WP_REST_Server;

/**
 * Callback
 *
 * Class handling callback requests sent from Lending Works when a loan application is being processed further and its
 * status needs to be reflected on the order in WooCommerce.
 */
class Callback {

	/**
	 * The WooCommerce adapter providing access to framework static methods.
	 *
	 * @var LW_Framework
	 */
	private $woocommerce;

	/**
	 * The retailer api key.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Callback constructor.
	 *
	 * @param LW_Framework $woocommerce The woocommerce adapter.
	 * @param string       $api_key The api key.
	 */
	public function __construct( LW_Framework $woocommerce, $api_key ) {

		$this->woocommerce = $woocommerce;
		$this->api_key     = $api_key;
	}

	/**
	 * Processes an incoming callback request from Lending Works containing a status change for a Loan request reference.
	 */
	public function process() {
        // phpcs:disable WordPress.Security.NonceVerification
		if ( ! isset( $_SERVER['HTTP_X_HOOK_SIGNATURE'], $_POST['json'] ) ) {
			return $this->woocommerce->error( [ 'message' => __( 'Missing authentication or payload' ) ], 400 );
		}

        // phpcs:disable WordPress.Security.ValidatedSanitizedInput
		$signature_header = $this->woocommerce->unslash( $_SERVER['HTTP_X_HOOK_SIGNATURE'] );
		$signature_header = $this->woocommerce->sanitize( $signature_header );

		$input = $this->woocommerce->unslash( $_POST['json'] );
        // phpcs:enable WordPress.Security.ValidatedSanitizedInput
		$input = $this->woocommerce->sanitize( $input );
        // phpcs:enable WordPress.Security.NonceVerification

        // phpcs:disable WordPress.PHP.DiscouragedPHPFunctions
		$hash = base64_encode( hash( 'sha512', $input . $this->api_key, true ) );
        // phpcs:enable WordPress.PHP.DiscouragedPHPFunctions

		if ( $signature_header !== $hash ) {
			return $this->woocommerce->error( [ 'message' => __( 'Invalid credentials.' ) ], 403 );
		}

		$payload = json_decode( stripslashes( $input ), true );

		$orders = $this->woocommerce->get_order_by_meta( Payment_Gateway::ORDER_REFERENCE_METADATA_KEY, $payload['reference'] );

		if ( 0 === count( $orders ) ) {
			return $this->woocommerce->response( [ 'message' => __( 'No order found.' ) ] );
		}

		$this->woocommerce->update_order_meta( $orders[0], Payment_Gateway::ORDER_STATUS_METADATA_KEY, $payload['status'] );

		$status_update = new Order_Status_Update( $payload['status'] );

		switch ( true ) {
			case $status_update->is_accepted():
				$orders[0]->update_status( 'processing', __( 'Loan accepted', 'lendingworks' ) );
				break;
			case $status_update->is_cancelled():
				$orders[0]->update_status( 'pending', __( 'Loan cancelled or expired', 'lendingworks' ) );
				break;
			case $status_update->is_declined():
				$orders[0]->update_status( 'failed', __( 'Loan declined', 'lendingworks' ) );
		}

		if ( method_exists( $orders[0], 'save' ) ) {
			$orders[0]->save();
		}

		$this->woocommerce->response( [ 'message' => 'Order status for loan request reference ' . $payload['reference'] . ' updated' ] );
	}

	/**
	 * Register a new route this callback will be responding on.
	 */
	public function regiter_callback_route() {
		$this->woocommerce->add_route(
			'lendingworks',
			'/orders/update-status',
			[
				'methods'  => WP_REST_Server::EDITABLE,
				'callback' => [ $this, 'process' ],
			]
		);
	}

	/**
	 * Handle a custom query var to get orders with the 'lendingworks_order_loan_request_reference' meta.
	 *
	 * @param array $query Arguments for WP_Query.
	 * @param array $query_vars Query vars from WC_Order_Query.
	 *
	 * @return array modified $query
	 */
	public function handle_custom_query( $query, $query_vars ) {

		if ( ! empty( $query_vars[ Payment_Gateway::ORDER_REFERENCE_METADATA_KEY ] ) ) {
			$query['meta_query'][] = array(
				'key'   => Payment_Gateway::ORDER_REFERENCE_METADATA_KEY,
				'value' => $query_vars[ Payment_Gateway::ORDER_REFERENCE_METADATA_KEY ],
			);
		}

		return $query;
	}

}
