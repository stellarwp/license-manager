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
 */
class LifecycleTest extends HarborTestCase {

	/**
	 * Test that the bootstrap end hook returns wp_loaded.
	 *
	 * @return void
	 */
	public function test_returns_wp_loaded_for_the_bootstrap_end_hook(): void {
		$this->assertSame( 'wp_loaded', Lifecycle::get_bootstrap_end_hook() );
	}

	/**
	 * Test that the bootstrap window is closed after wp_loaded has fired.
	 *
	 * @return void
	 */
	public function test_reports_the_bootstrap_window_is_closed_after_wp_loaded_has_fired(): void {
		$this->assertGreaterThan( 0, did_action( Lifecycle::get_bootstrap_end_hook() ) );
		$this->assertFalse( Lifecycle::is_bootstrap_window_open() );
	}
}
