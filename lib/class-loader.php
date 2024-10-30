<?php
/**
 * Loader
 *
 * Class responsible for hooking the plugin handler to WordPress actions and filters.
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
 * Loader
 *
 * Class responsible for hooking the plugin handler to WordPress actions and filters.
 */
class Loader {
	/**
	 * The actions to be registered with WordPress.
	 *
	 * @var array $actions
	 */
	private $actions = [];

	/**
	 * The filters to be registered with WordPress.
	 *
	 * @var array $filters
	 */
	private $filters = [];

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @param string $tag The name of the WordPress hook (action or filter) to be added.
	 * @param object $object The object containing the callback method.
	 * @param string $callback The callback method name.
	 * @param int    $priority The action execution priority.
	 * @param int    $accepted_args The action accepted arguments amount.
	 */
	public function add_action( $tag, $object, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions[] = [
			'tag'           => $tag,
			'callable'      => [ $object, $callback ],
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		];
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @param string $tag The name of the WordPress hook (action or filter) to be added.
	 * @param object $object The object containing the callback method.
	 * @param string $callback the callback method name.
	 * @param int    $priority The filter execution priority.
	 * @param int    $accepted_args The action accepted arguments amount.
	 */
	public function add_filter( $tag, $object, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters[] = [
			'tag'           => $tag,
			'callable'      => [ $object, $callback ],
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		];
	}

	/**
	 * Register actions and filters with WordPress.
	 */
	public function run() {
		foreach ( $this->actions as $action ) {
			add_action( $action['tag'], $action['callable'], $action['priority'], $action['accepted_args'] );
		}

		foreach ( $this->filters as $filter ) {
			add_filter( $filter['tag'], $filter['callable'], $filter['priority'], $filter['accepted_args'] );
		}
	}
}
