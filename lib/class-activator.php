<?php
/**
 * Activator
 *
 * Class handling plugin activation. It ensures the environment WordPress is running in is compatible with the requirements
 * of this plugin.
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

/**
 * Activator
 *
 * Class handling plugin activation. It ensures the environment WordPress is running in is compatible with the requirements
 * of this plugin.
 */
class Activator {
	const MIN_PHP_VERSION = '5.6.0';

	/**
	 * Checks if the running PHP version satisfies the minimum requirement for this plugin.
	 */
	public static function activate() {
		if ( version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			// phpcs:disable WordPress.Security.EscapeOutput
			wp_die( __( 'This plugin requires PHP version ', 'lendingworks' ) . self::MIN_PHP_VERSION );
			// phpcs:enable WordPress.Security.EscapeOutput
		}
	}
}
