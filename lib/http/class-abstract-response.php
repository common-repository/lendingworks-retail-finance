<?php
/**
 * Abstract_Response
 *
 * Abstract class modelling a base HTTP response received from Lending Works.
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

use UnexpectedValueException;
use \WP_Error;

/**
 * Abstract_Response
 *
 * Abstract class modelling a base HTTP response received from Lending Works.
 */
abstract class Abstract_Response {
	/**
	 * The result returned by the remote server.
	 *
	 * @var array|WP_Error $result The request result.
	 */
	protected $result;

	/**
	 * Abstract Response constructor.
	 *
	 * @param array|WP_Error $result The result from the remote server.
	 */
	public function __construct( $result ) {
		$this->result = $result;
	}

	/**
	 * Checks if the response is an error.
	 *
	 * @return bool
	 */
	public function is_error() {
		return $this->result instanceof WP_Error || $this->result['response']['code'] > 399;
	}

	/**
	 * Gets the error message.
	 *
	 * @return string
	 */
	public function get_error_message() {
		if ( $this->result instanceof WP_Error ) {
			return $this->result->get_error_message();
		}

		$body = $this->get_body();
		if ( isset( $body['message'] ) ) {
			return $body['message'];
		}

		return isset( $this->result['response']['message'] ) ? $this->result['response']['message'] : '';
	}

	/**
	 * Gets the unserialized body content.
	 *
	 * @return array|mixed|object
	 *
	 * @throws UnexpectedValueException Exception fired when json_decode failed.
	 */
	protected function get_body() {
		$json = json_decode( $this->result['body'], true );

		if ( null === $json || JSON_ERROR_NONE !== json_last_error() ) {
			throw new UnexpectedValueException( json_last_error_msg() );
		}

		return $json;
	}
}
