<?php
/**
 * Pay
 *
 * Class handling payment steps of the payment gateway. When a customer selects Lending Works as a payment method, he is
 * redirected to the order-pay page where a form and some Javascript code is printed on the page in order to display
 * the iFrame allowing the customer to get a loan quote with Lending Works.
 *
 * @package WordPress
 * @subpackage WooCommerce
 * @version 1.0.0
 * @author  Lending Works Ltd
 * @license https://www.gnu.org/licenses/gpl-2.0.txt GNU/GPLv2
 * @link https://www.lendingworks.co.uk/
 * @since  1.0.0
 */

namespace WC_Lending_Works\Lib\Pay;

use WC_Lending_Works\Lib\LW_Framework;
use WC_Lending_Works\Lib\Payment_Gateway;
use const WC_Lending_Works\PLUGIN_NAME;
use const WC_Lending_Works\PLUGIN_DIR;
use const WC_Lending_Works\PLUGIN_VERSION;

/**
 * Pay
 *
 * Class handling payment steps of the payment gateway. When a customer selects Lending Works as a payment method, he is
 * redirected to the order-pay page where a form and some Javascript code is printed on the page in order to display
 * the iFrame allowing the customer to get a loan quote with Lending Works.
 */
class Pay {
	const STATUS_ACCEPTED  = 'accepted';
	const STATUS_APPROVED  = 'approved';
	const STATUS_REFERRED  = 'referred';
	const STATUS_CANCELLED = 'cancelled';
	const STATUS_EXPIRED   = 'expired';
	const STATUS_DECLINED  = 'declined';

	/**
	 * The WooCommerce adapter providing access to framework static methods.
	 *
	 * @var LW_Framework
	 */
	private $woocommerce;

	/**
	 * The flag denoting whether the plugin test mode is activated or not.
	 *
	 * @var bool
	 */
	private $test_mode;

	/**
	 * Pay constructor.
	 *
	 * @param LW_Framework $woocommerce The woocommerce adapter.
	 * @param bool         $test_mode Plugin test mode flag.
	 */
	public function __construct( LW_Framework $woocommerce, $test_mode = false ) {
		$this->woocommerce = $woocommerce;
		$this->test_mode   = $test_mode;
	}

	/**
	 * Prints on the page the form to redirect back to thank you or checkout page.
	 *
	 * @param string|int $order_id The order ID for which to print a form.
	 */
	public function print_form( $order_id ) {
		$order       = $this->woocommerce->get_order( $order_id );
		$order_token = $this->woocommerce->get_order_meta( $order, Payment_Gateway::ORDER_TOKEN_METADATA_KEY );
		$nonce       = $this->woocommerce->encrypt( $order_token );

		// phpcs:disable WordPress.Security.EscapeOutput
		echo '<form id="order_review" method="POST" action="' . $this->woocommerce->webhook_url() . '?nonce=' . $nonce . '">
				 <input type="hidden" name="order_id" value="" />
				 <input type="hidden" name="reference" value="" />
				 <input type="hidden" name="status" value="" />
				 <input type="hidden" name="nonce" value="' . $nonce . '" />
				 <input type="submit" style="display: none;"/>
			 </form>';
		// phpcs:enable WordPress.Security.EscapeOutput
	}

	/**
	 * Prints the JS scripts on the order-pay page.
	 *
	 * @param string|int $order_id The order ID for which to load scripts.
	 */
	public function load_scripts( $order_id ) {
		$sandbox_checkout = isset( $_ENV['SANDBOX_CHECKOUT'] ) ? $_ENV['SANDBOX_CHECKOUT'] : Payment_Gateway::SANDBOX_CHECKOUT;

		$src = $this->test_mode
			? $sandbox_checkout
			: Payment_Gateway::PROD_CHECKOUT;

		$order = $this->woocommerce->get_order( $order_id );

		// Checks if the order has already been paid for.
		$status = $this->woocommerce->get_order_meta( $order, Payment_Gateway::ORDER_STATUS_METADATA_KEY );

		if ( ! empty( $status ) && ! in_array( $status, [ 'cancelled', 'expired' ], true ) ) {
			$this->woocommerce->notify( __( 'Your order has already been paid.' ), 'error' );
			$this->woocommerce->redirect( $this->woocommerce->checkout_url() );
			return;
		}

		$this->woocommerce->add_script( PLUGIN_NAME, $src, [ 'jquery' ], PLUGIN_VERSION );

		$template = $this->woocommerce->is_legacy()
			? 'checkoutHandler.legacy.js.php'
			: 'checkoutHandler.js.php';

		$this->woocommerce->add_inline_script(
			PLUGIN_NAME,
			$template,
			[
				'order'       => $order,
				'webhook_url' => $this->woocommerce->checkout_url(),
			],
			PLUGIN_DIR
		);
	}
}
