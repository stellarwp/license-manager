<?php declare( strict_types=1 );

namespace wpunit\Utils;

use LiquidWeb\Harbor\Tests\HarborTestCase;
use LiquidWeb\Harbor\Utils\Lifecycle;

/**
 * Tests for the Lifecycle utility.
 *
 * The WP test environment has already fired the bootstrap end hook (wp_loaded)
 * by the time these tests run, so the "open window" branch is not directly
 * observable here. It is exercised in production by the bootstrap-plugin scaffold.
 *
 * @since TBD
 */
class LifecycleTest extends HarborTestCase {

	/**
	 * @test
	 *
	 * @since TBD
	 */
	public function it_should_return_wp_loaded_for_the_bootstrap_end_hook(): void {
		$this->assertSame( 'wp_loaded', Lifecycle::get_bootstrap_end_hook() );
	}

	/**
	 * @test
	 *
	 * @since TBD
	 */
	public function it_should_report_the_bootstrap_window_is_closed_after_wp_loaded_has_fired(): void {
		$this->assertGreaterThan( 0, did_action( Lifecycle::get_bootstrap_end_hook() ) );
		$this->assertFalse( Lifecycle::is_bootstrap_window_open() );
	}
}
