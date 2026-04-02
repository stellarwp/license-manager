<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Tests\Cron\Actions;

use LiquidWeb\Harbor\Portal\Portal_Collection;
use LiquidWeb\Harbor\Portal\Portal_Repository;
use LiquidWeb\Harbor\Cron\Actions\Handle_Unschedule_Cron_Data_Refresh;
use LiquidWeb\Harbor\Cron\ValueObjects\CronHook;
use LiquidWeb\Harbor\Tests\HarborTestCase;

/**
 * Tests the Handle_Unschedule_Cron_Data_Refresh action.
 *
 * @since 1.0.0
 */
final class Handle_Unschedule_Cron_Data_Refresh_Test extends HarborTestCase {

	/**
	 * Test that the action does not unschedule when no portal is cached (e.g. never fetched or error).
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_does_not_unschedule_when_no_portal_cached(): void {
		wp_schedule_event( time(), 'twicedaily', CronHook::DATA_REFRESH );

		$portal = $this->makeEmpty(
			Portal_Repository::class,
			[ 'get_cached' => null ]
		);

		$action = new Handle_Unschedule_Cron_Data_Refresh( $portal );
		( $action )();

		$this->assertNotFalse( wp_next_scheduled( CronHook::DATA_REFRESH ) );
	}

	/**
	 * Test that the action does not unschedule when the portal has no installable features.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_does_not_unschedule_when_portal_has_no_installable_features(): void {
		wp_schedule_event( time(), 'twicedaily', CronHook::DATA_REFRESH );

		$portal = $this->makeEmpty(
			Portal_Repository::class,
			[ 'get_cached' => Portal_Collection::from_array( [] ) ]
		);

		$action = new Handle_Unschedule_Cron_Data_Refresh( $portal );
		( $action )();

		$this->assertNotFalse( wp_next_scheduled( CronHook::DATA_REFRESH ) );
	}

	/**
	 * Test that the action does not unschedule when the portal plugin is active.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_does_not_unschedule_when_portal_plugin_is_active(): void {
		wp_schedule_event( time(), 'twicedaily', CronHook::DATA_REFRESH );
		update_option( 'active_plugins', [ 'give/give.php' ] );

		$portal = $this->makeEmpty(
			Portal_Repository::class,
			[ 'get_cached' => $this->make_portal_with_plugin( 'give/give.php' ) ]
		);

		$action = new Handle_Unschedule_Cron_Data_Refresh( $portal );
		( $action )();

		$this->assertNotFalse( wp_next_scheduled( CronHook::DATA_REFRESH ) );
	}

	/**
	 * Test that the action unschedules when all portal plugins are inactive.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_unschedules_when_all_portal_plugins_inactive(): void {
		wp_schedule_event( time(), 'twicedaily', CronHook::DATA_REFRESH );
		update_option( 'active_plugins', [] );

		$portal = $this->makeEmpty(
			Portal_Repository::class,
			[ 'get_cached' => $this->make_portal_with_plugin( 'give/give.php' ) ]
		);

		$action = new Handle_Unschedule_Cron_Data_Refresh( $portal );
		( $action )();

		$this->assertFalse( wp_next_scheduled( CronHook::DATA_REFRESH ) );
	}

	/**
	 * Test that the action does not unschedule when the portal theme is active.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_does_not_unschedule_when_portal_theme_is_active(): void {
		wp_schedule_event( time(), 'twicedaily', CronHook::DATA_REFRESH );

		$active_theme_slug = get_stylesheet();

		$portal = $this->makeEmpty(
			Portal_Repository::class,
			[ 'get_cached' => $this->make_portal_with_theme( $active_theme_slug ) ]
		);

		$action = new Handle_Unschedule_Cron_Data_Refresh( $portal );
		( $action )();

		$this->assertNotFalse( wp_next_scheduled( CronHook::DATA_REFRESH ) );
	}

	/**
	 * Test that the action unschedules when the portal theme is not active.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_unschedules_when_portal_theme_is_inactive(): void {
		wp_schedule_event( time(), 'twicedaily', CronHook::DATA_REFRESH );

		$portal = $this->makeEmpty(
			Portal_Repository::class,
			[ 'get_cached' => $this->make_portal_with_theme( 'some-inactive-theme' ) ]
		);

		$action = new Handle_Unschedule_Cron_Data_Refresh( $portal );
		( $action )();

		$this->assertFalse( wp_next_scheduled( CronHook::DATA_REFRESH ) );
	}

	/**
	 * Build a minimal portal collection containing one plugin feature.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_file Plugin basename, e.g. 'give/give.php'.
	 *
	 * @return Portal_Collection
	 */
	private function make_portal_with_plugin( string $plugin_file ): Portal_Collection {
		return Portal_Collection::from_array(
			[
				[
					'product_slug' => 'test-product',
					'tiers'        => [],
					'features'     => [
						[
							'slug'              => 'test-feature',
							'kind'              => 'plugin',
							'minimum_tier'      => '',
							'main_file'         => $plugin_file,
							'wporg_slug'        => null,
							'name'              => 'Test Feature',
							'description'       => '',
							'category'          => '',
							'documentation_url' => '',
						],
					],
				],
			]
		);
	}

	/**
	 * Build a minimal portal collection containing one theme feature.
	 *
	 * @since 1.0.0
	 *
	 * @param string $theme_slug Theme stylesheet slug, e.g. 'twentytwentyfour'.
	 *
	 * @return Portal_Collection
	 */
	private function make_portal_with_theme( string $theme_slug ): Portal_Collection {
		return Portal_Collection::from_array(
			[
				[
					'product_slug' => 'test-product',
					'tiers'        => [],
					'features'     => [
						[
							'slug'              => $theme_slug,
							'kind'              => 'theme',
							'minimum_tier'      => '',
							'main_file'         => null,
							'wporg_slug'        => null,
							'name'              => 'Test Theme',
							'description'       => '',
							'category'          => '',
							'documentation_url' => '',
						],
					],
				],
			]
		);
	}
}
