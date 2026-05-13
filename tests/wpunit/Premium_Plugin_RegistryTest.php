<?php declare( strict_types=1 );

namespace wpunit;

use LiquidWeb\Harbor\Premium_Plugin_Registry;
use LiquidWeb\Harbor\Tests\HarborTestCase;
use stdClass;

/**
 * Tests for the Premium_Plugin_Registry class.
 */
final class Premium_Plugin_RegistryTest extends HarborTestCase {

	/**
	 * Premium Plugin Registry instance.
	 *
	 * @var Premium_Plugin_Registry
	 */
	private Premium_Plugin_Registry $registry;

	/**
	 * Saved state of the premium-plugin existence filter as it was on entry
	 * to setUp(). bootstrap-plugin.php registers a callback at WP boot time
	 * to keep the production gate happy for every other test; we have to
	 * restore it in tearDown() so removing all filters here doesn't leak.
	 *
	 * @var \WP_Hook|null
	 */
	private $saved_filter;

	/**
	 * Set up the test environment.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		global $wp_filter;
		$this->saved_filter = $wp_filter['lw_harbor/premium_plugin_exists'] ?? null;
		unset( $wp_filter['lw_harbor/premium_plugin_exists'] );

		$this->registry = new Premium_Plugin_Registry();
	}

	/**
	 * Tear down the test environment.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		global $wp_filter;
		unset( $wp_filter['lw_harbor/premium_plugin_exists'] );
		if ( $this->saved_filter !== null ) {
			$wp_filter['lw_harbor/premium_plugin_exists'] = $this->saved_filter;
		}

		parent::tearDown();
	}

	/**
	 * Test that the registry returns false when no callbacks are registered.
	 *
	 * @return void
	 */
	public function test_returns_false_when_no_callbacks_are_registered(): void {
		$this->assertFalse( $this->registry->any() );
	}

	/**
	 * Test that the registry returns true when a registered callback returns true.
	 *
	 * @return void
	 */
	public function test_returns_true_when_a_registered_callback_returns_true(): void {
		add_filter(
			'lw_harbor/premium_plugin_exists',
			'__return_true'
		);

		$this->assertTrue( $this->registry->any() );
	}

	/**
	 * Test that the registry returns false when all registered callbacks return false.
	 *
	 * @return void
	 */
	public function test_returns_false_when_all_registered_callbacks_return_false(): void {
		add_filter(
			'lw_harbor/premium_plugin_exists',
			'__return_false'
		);

		$this->assertFalse( $this->registry->any() );
	}

	/**
	 * Test that the registry returns true when at least one of many callbacks returns true.
	 *
	 * @return void
	 */
	public function test_returns_true_when_at_least_one_of_many_callbacks_returns_true(): void {
		add_filter(
			'lw_harbor/premium_plugin_exists',
			'__return_false'
		);
		add_filter(
			'lw_harbor/premium_plugin_exists',
			'__return_true'
		);

		$this->assertTrue( $this->registry->any() );
	}

	/**
	 * Test that the registry returns false when a non-scalar callback returns.
	 *
	 * @return void
	 */
	public function test_returns_false_when_a_non_scalar_callback_returns(): void {
		add_filter(
			'lw_harbor/premium_plugin_exists',
			static fn() => new stdClass()
		);
		add_filter(
			'lw_harbor/premium_plugin_exists',
			static fn() => [ 'nonempty' ]
		);
		add_filter(
			'lw_harbor/premium_plugin_exists',
			static fn() => null
		);

		$this->assertFalse( $this->registry->any() );
	}
}
