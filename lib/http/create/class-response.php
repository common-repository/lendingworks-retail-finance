<?php
/**
 * Response
 *
 * Class modelling an HTTP response received from Lending Works when an order was created.
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
use WC_Lending_Works\Lib\Http\Abstract_Response;

/**
 * Response
 *
 * Class modelling an HTTP response received from Lending Works when an order was created.
 */
class Response extends Abstract_Response {

	/**
	 * Returns the Order token from the response json body.
	 *
	 * @return string
	 *
	 * @throws UnexpectedValueException Exception fired when json_decode failed.
	 */
	public function get_order_token() {
		$json = json_decode( $this->result['body'], true );

		if ( null === $json || JSON_ERROR_NONE !== json_last_error() ) {
			throw new UnexpectedValueException( json_last_error_msg() );
		}

		return $this->get_body()['token'];
	}
}
