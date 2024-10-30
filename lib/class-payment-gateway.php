<?php
/**
 * Payment_Gateway
 *
 * Class implementing WooCommerce WC_Payment_Gateway. It is responsible for defining the available settings for the
 * payment gateway, as well as specifying its behaviour such as in which circumstances this payment gateway should be
 * available to checkout an order, whether it can be used to refund orders and how to process the payment.
 *
 * @package WordPress
 * @subpackage WooCommerce
 * @version 1.0.0
 * @author  Lending Works Ltd
 * @license https://www.gnu.org/licenses/gpl-2.0.txt GNU/GPLv2
 * @link https://www.lendingworks.co.uk/
 * @since  1.0.0
 */

namespace WC_Lending_Works\Lib;

use WC_Admin_Settings;
use WC_Lending_Works\Lib\Checkout\Checkout;
use WC_Payment_Gateway;
use WC_Lending_Works;
use const WC_Lending_Works\PLUGIN_NAME;

/**
 * Payment_Gateway
 *
 * Class implementing WooCommerce WC_Payment_Gateway. It is responsible for defining the available settings for the
 * payment gateway, as well as specifying its behaviour such as in which circumstances this payment gateway should be
 * available to checkout an order, whether it can be used to refund orders and how to process the payment.
 */
class Payment_Gateway extends WC_Payment_Gateway {
	const SANDBOX_URL      = 'https://retail-sandbox.lendingworks.co.uk/api/v2/';
	const PROD_URL         = 'https://www.lendingworks.co.uk/api/v2/';
	const SANDBOX_CHECKOUT = 'https://retail-sandbox.secure.lendingworks.co.uk/checkout.js';
	const PROD_CHECKOUT    = 'https://secure.lendingworks.co.uk/checkout.js';

	const ORDER_TOKEN_METADATA_KEY     = 'lendingworks_order_token';
	const ORDER_STATUS_METADATA_KEY    = 'lendingworks_order_status';
	const ORDER_REFERENCE_METADATA_KEY = 'lendingworks_order_loan_request_reference';
	const ORDER_FULFILLED_METADATA_KEY = 'lendingworks_order_fulfilled';

	/**
	 * The checkout actions and filters handler.
	 *
	 * @var Checkout $checkout
	 */
	private $checkout;

	/**
	 * The LendingWorks API token.
	 *
	 * @var string $api_token
	 */
	protected $api_key;

	/**
	 * The environment to use the API key in.
	 *
	 * @var string $environment
	 */
	protected $test_mode;

	/**
	 * Allow or disallow manual fulfillment.
	 *
	 * @var bool $fulfillment
	 */
	protected $fulfillment;

	/**
	 * The minimum order total amount this payment method can be used for.
	 *
	 * @var float $min_total
	 */
	protected $min_total;

	/**
	 * The maximum order total amount this payment method can be used for.
	 *
	 * @var float $max_total
	 */
	protected $max_total;

	/**
	 * Payment_Gateway constructor.
	 */
	public function __construct() {
		$this->id                 = PLUGIN_NAME;
		$this->icon               = 'https://d1a5pf57s4y6jz.cloudfront.net/cdn/farfuture/VXGbcJ6o6VeRYziv3fsd3tgYeezo_RrNwINvtAU68Mg/mtime:1557990353/sites/all/themes/lwt/images/lender/Logo-Shield.svg';
		$this->has_fields         = false;
		$this->method_title       = 'Lending Works Retail Finance';
		$this->method_description = 'Lending Works peer to peer lending';

		$this->init_form_fields();
		$this->init_settings();

		$this->enabled     = $this->get_option( 'enabled' );
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->api_key     = $this->get_option( 'api_key' );
		$this->test_mode   = 'yes' === $this->get_option( 'test_mode' );
		$this->fulfillment = 'yes' === $this->get_option( 'fulfillment' );
		$this->min_total   = $this->get_option( 'min_total' );
		$this->max_amount  = $this->get_option( 'max_total' );
		$this->max_total   = $this->max_amount;
	}

	/**
	 * Specify form fields to display in Admin settings for Lending Works payment method.
	 *
	 * @inheritdoc
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = [
			'enabled'     => [
				'title'   => __( 'Enable/Disable', 'lendingworks' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Lending Works Retail finance', 'lendingworks' ),
				'default' => 'no',
			],
			'title'       => [
				'title'       => __( 'Title', 'lendingworks' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the customer sees during checkout.<br/>We recommend you use the default title.', 'lendingworks' ),
				'default'     => __( 'Lending Works finance', 'lendingworks' ),
			],
			'description' => [
				'title'       => __( 'Description', 'lendingworks' ),
				'type'        => 'text',
				'description' => __( 'This controls the description which the customer sees during checkout.<br/>We recommend you use the default description.', 'lendingworks' ),
				'default'     => __( 'Apply directly to Lending Works for finance when making a purchase, and if approved, proceed with the order in minutes.', 'lendingworks' ),
			],
			'api_key'     => [
				'title'       => __( 'Your Lending Works API key' ),
				'type'        => 'password',
				'description' => __( 'Enter your Lending Works API key', 'lendingworks' ),
			],
			'test_mode'   => [
				'title'   => __( 'Test mode', 'lendingworks' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Lending Works integration/test mode.', 'lendingworks' ),
				'default' => 'yes',
			],
			'fulfillment' => [
				'title'       => __( 'Allow manual fulfillment ?', 'lendingworks' ),
				'description' => __( 'If set to no, the order will be automatically be fulfilled when marked as completed. <br />Otherwise you will need to click the \'Fulfill order\' button on the order details page.', 'lendingworks' ),
				'type'        => 'select',
				'default'     => __( 'No', 'lendingworks' ),
				'options'     => [
					'yes' => __( 'Yes', 'lendingworks' ),
					'no'  => __( 'No', 'lendingworks' ),
				],
			],
			'min_total'   => [
				'title'       => __( 'Minimum order total', 'lendingworks' ),
				'type'        => 'text',
				'default'     => '0.50',
				'description' => __( 'You can set your minimum finance total, the lowest amount we can provide finance for is £50.', 'lendingworks' ),
			],
			'max_total'   => [
				'title'       => __( 'Maximum order total', 'lendingworks' ),
				'type'        => 'text',
				'description' => __( 'You can set your maximum finance total, the highest amount we can provide finance for is £20,000.', 'lendingworks' ),
			],
		];
	}

	/**
	 * Validates settings keyed by admin user.
	 *
	 * @param array $options The plugin settings.
	 *
	 * @return array
	 */
	public function validate_admin_options( $options ) {
		if ( $options['max_total'] > 25000 ) {
			$options['max_total'] = 25000;
			WC_Admin_Settings::add_error( __( 'Maximum order total must be lower or equal to 25,000' ) );
		}

		if ( $options['min_total'] < 50 ) {
			$options['min_total'] = 50;
			WC_Admin_Settings::add_error( __( 'Minimum order total must be greater or equal to 50' ) );
		}

		return $options;
	}

	/**
	 * Return whether or not this gateway still requires setup to function.
	 *
	 * @inheritdoc
	 *
	 * @return bool
	 */
	public function needs_setup() {
		return empty( $this->api_key );
	}

	/**
	 * Checks if the order can be refunded via this gateway.
	 *
	 * @param \WC_Order $order The order to refund.
	 *
	 * @return bool
	 */
	public function can_refund_order( $order ) {
		return false;
	}

	/**
	 * Checks if the gateway is available for use.
	 *
	 * @return bool
	 */
	public function is_available() {
		return ( 'yes' === $this->enabled
			&& $this->get_order_total() >= $this->min_total
			&& $this->get_order_total() <= $this->max_total
			&& ! $this->needs_setup()
		);
	}

	/**
	 * Process Payment.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		return $this->checkout->checkout( $order_id );
	}

	/**
	 * Returns the checkout handler.
	 *
	 * @return Checkout
	 */
	public function get_checkout() {
		return $this->checkout;
	}

	/**
	 * Returns the checkout handler.
	 *
	 * @param Checkout $checkout The checkout instance to set on the gateway.
	 *
	 * @return $this
	 */
	public function set_checkout( Checkout $checkout ) {
		$this->checkout = $checkout;

		return $this;
	}

	/**
	 * Returns the API key.
	 *
	 * @return string
	 */
	public function get_api_key() {
		return $this->api_key;
	}

	/**
	 * Sets the API key.
	 *
	 * @param string $api_key The api key.
	 *
	 * @return self
	 */
	public function set_api_key( $api_key ) {
		$this->api_key = $api_key;

		return $this;
	}

	/**
	 * Returns whether the plugin is in test mode.
	 *
	 * @return bool
	 */
	public function get_test_mode() {
		return $this->test_mode;
	}

	/**
	 * Sets the plugin in test mode.
	 *
	 * @param bool $test_mode The test mode set to true or false.
	 *
	 * @return self
	 */
	public function set_test_mode( $test_mode ) {
		$this->test_mode = $test_mode;

		return $this;
	}

	/**
	 * Return whether the payment gateway allow manual fulfillment.
	 *
	 * @return bool
	 */
	public function is_fulfillment() {
		return $this->fulfillment;
	}

	/**
	 * Sets whether the payment gateway allow manual fulfillment.
	 *
	 * @param bool $fulfillment The fulfillment flag set to true or false.
	 *
	 * @return self
	 */
	public function set_fulfillment( $fulfillment ) {
		$this->fulfillment = $fulfillment;

		return $this;
	}

	/**
	 * Return the minimum total order amount for this payment method.
	 *
	 * @return float
	 */
	public function get_min_total() {
		return $this->min_total;
	}

	/**
	 * Sets the minimum total order amount for this payment method.
	 *
	 * @param float $min_total The minimum order total allowed by this payment method.
	 *
	 * @return self
	 */
	public function set_min_total( $min_total ) {
		$this->min_total = $min_total;

		return $this;
	}

	/**
	 * Return the maximum total order amount for this payment method.
	 *
	 * @return float
	 */
	public function get_max_total() {
		return $this->max_total;
	}

	/**
	 * Sets the maximum total order amount for this payment method.
	 *
	 * @param float $max_total The maximum order total allowed by this payment method.
	 *
	 * @return self
	 */
	public function set_max_total( $max_total ) {
		$this->max_total = $max_total;

		return $this;
	}
}
