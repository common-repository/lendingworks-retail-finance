<?php
/**
 * Order_Status_Update
 *
 * Class checking validity of Lending Works status and mapping to WooCommerce order status.
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

use WC_Lending_Works\Lib\Pay\Pay;

/**
 * Order_Status_Update
 *
 * Class checking validity of Lending Works status and mapping to WooCommerce order status.
 */
class Order_Status_Update {

	/**
	 * The status to check.
	 *
	 * @var string $status
	 */
	private $status;

	/**
	 * Order_Status_Update constructor.
	 *
	 * @param string $status The loan application status to check.
	 */
	public function __construct( $status ) {
		$this->status = $status;
	}

	/**
	 * Checks whether the loan application is successful and in status approved, accepted or referred.
	 *
	 * @return bool
	 */
	public function is_accepted() {
		return in_array( $this->status, [ Pay::STATUS_ACCEPTED, Pay::STATUS_APPROVED, Pay::STATUS_REFERRED ], true );
	}

	/**
	 * Checks whether the loan application is cancelled and in status cancelled or expired.
	 *
	 * @return bool
	 */
	public function is_cancelled() {
		return in_array( $this->status, [ Pay::STATUS_CANCELLED, Pay::STATUS_EXPIRED ], true );
	}

	/**
	 * Checks whether the loan application is in status declined.
	 *
	 * @return bool
	 */
	public function is_declined() {
		return Pay::STATUS_DECLINED === $this->status;
	}

	/**
	 * Checks whether the loan application is in a valid status.
	 *
	 * @return bool
	 */
	public function is_valid() {
		return $this->is_accepted( $this->status ) || $this->is_cancelled( $this->status ) || $this->is_declined( $this->status );
	}
}
