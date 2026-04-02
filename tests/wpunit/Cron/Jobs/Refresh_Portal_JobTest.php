<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Tests\Cron\Jobs;

use LiquidWeb\Harbor\Portal\Portal_Repository;
use LiquidWeb\Harbor\Cron\Jobs\Refresh_Portal_Job;
use LiquidWeb\Harbor\Tests\HarborTestCase;

final class Refresh_Portal_JobTest extends HarborTestCase {

	public function test_run_calls_portal_refresh(): void {
		$called = false;

		$portal = $this->makeEmpty(
			Portal_Repository::class,
			[
				'refresh' => static function () use ( &$called ) {
					$called = true;
				},
			]
		);

		$job = new Refresh_Portal_Job( $portal );
		$job->run();

		$this->assertTrue( $called );
	}
}
