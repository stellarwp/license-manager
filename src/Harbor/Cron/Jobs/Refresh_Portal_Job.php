<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Cron\Jobs;

use LiquidWeb\Harbor\Portal\Portal_Repository;
use LiquidWeb\Harbor\Traits\With_Debugging;

/**
 * Refreshes the product portal from the remote API.
 *
 * @since 1.0.0
 */
class Refresh_Portal_Job {

	use With_Debugging;

	/**
	 * @since 1.0.0
	 *
	 * @var Portal_Repository
	 */
	private Portal_Repository $portal;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Portal_Repository $portal The portal repository.
	 */
	public function __construct( Portal_Repository $portal ) {
		$this->portal = $portal;
	}

	/**
	 * Fetch fresh portal data from the remote API.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run(): void {
		static::debug_log( 'Scheduled portal refresh starting.' );

		$this->portal->refresh();
	}
}
