<?php
/**
 * Abstract_Request
 *
 * Abstract class modelling a base HTTP request sent to Lending Works.
 *
 * @package WordPress
 * @subpackage WooCommerce
 * @version 1.0.0
 * @author  Lending Works Ltd
 * @license https://www.gnu.org/licenses/gpl-2.0.txt GNU/GPLv2
 * @link https://www.lendingworks.co.uk/
 * @since  1.0.0
 */

namespace WC_Lending_Works\Lib\Http;

use WC_Lending_Works\Lib\LW_Framework;

/**
 * Abstract_Request
 *
 * Abstract class modelling a base HTTP request sent to Lending Works.
 */
abstract class Abstract_Request {
	/**
	 * The base URL of LendingWorks server.
	 *
	 * @var string $base_url
	 */
	protected $base_url;

	/**
	 * The api key.
	 *
	 * @var string $api_key
	 */
	protected $api_key;

	/**
	 * The WooCommerce adapter providing access to framework static methods.
	 *
	 * @var LW_Framework
	 */
	protected $woocommerce;

	/**
	 * Abstract Request constructor.
	 *
	 * @param string       $base_url The request URL.
	 * @param string       $api_key The api key to authorize the request.
	 * @param LW_Framework $woocommerce The framework adapter.
	 */
	public function __construct( $base_url, $api_key, LW_Framework $woocommerce ) {
		$this->base_url    = $base_url;
		$this->api_key     = $api_key;
		$this->woocommerce = $woocommerce;
	}

	/**
	 * Returns the url of Lendinworks api order creation endpoint.
	 *
	 * @return string
	 */
	abstract public function get_url();

	/**
	 * Returns the headers for Lendinworks api order creation endpoint.
	 *
	 * @return array
	 */
	public function get_headers() {
		return [
			'Content-type'  => 'application/json',
			'Authorization' => 'RetailApiKey ' . $this->api_key,
		];
	}

	/**
	 * Returns the request body containing the order details.
	 *
	 * @param \WC_Order $order The ordder to send a request for.
	 *
	 * @return false|string
	 */
	abstract public function get_body( \WC_Order $order );
}
