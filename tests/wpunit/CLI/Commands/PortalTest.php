<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Tests\CLI\Commands;

use LiquidWeb\Harbor\Portal\Portal_Collection;
use LiquidWeb\Harbor\Portal\Portal_Repository;
use LiquidWeb\Harbor\Portal\Results\Portal_Feature;
use LiquidWeb\Harbor\Portal\Results\Portal_Tier;
use LiquidWeb\Harbor\Portal\Results\Product_Portal;
use LiquidWeb\Harbor\Portal\Results\Tier_Collection;
use LiquidWeb\Harbor\CLI\Commands\Portal as Portal_Command;
use LiquidWeb\Harbor\Tests\CLI\Spy_Logger;
use LiquidWeb\Harbor\Tests\Traits\With_Uopz;
use LiquidWeb\Harbor\Tests\HarborTestCase;
use WP_CLI;
use WP_Error;

/**
 * Tests for the WP-CLI `wp harbor portal` command.
 *
 * @since 1.0.0
 */
final class PortalTest extends HarborTestCase {

	use With_Uopz;

	/** @var Spy_Logger */
	private Spy_Logger $logger;

	/** @var Portal_Collection */
	private Portal_Collection $portals;

	protected function setUp(): void {
		parent::setUp();

		if ( function_exists( 'uopz_allow_exit' ) ) {
			uopz_allow_exit( false );
		}

		$utils_file = dirname( ( new \ReflectionClass( WP_CLI::class ) )->getFileName() ) . '/utils.php';
		if ( file_exists( $utils_file ) ) {
			require_once $utils_file;
		}

		$this->logger = new Spy_Logger();
		WP_CLI::set_logger( $this->logger );

		$this->portals = $this->build_test_portals();
	}

	protected function tearDown(): void {
		if ( function_exists( 'uopz_allow_exit' ) ) {
			uopz_allow_exit( true );
		}

		parent::tearDown();
	}

	// ------------------------------------------------------------------
	// list
	// ------------------------------------------------------------------

	public function test_list_outputs_all_products_as_json(): void {
		$command = $this->make_command( $this->portals );

		$items = $this->run_list_json( $command );

		$this->assertCount( 2, $items );
		$this->assertSame( 'kadence', $items[0]['product_slug'] );
		$this->assertSame( 'givewp', $items[1]['product_slug'] );
	}

	public function test_list_shows_tier_and_feature_counts(): void {
		$command = $this->make_command( $this->portals );

		$items = $this->run_list_json( $command );

		$this->assertSame( '2', $items[0]['tiers'] );
		$this->assertSame( '2', $items[0]['features'] );
		$this->assertSame( '1', $items[1]['tiers'] );
		$this->assertSame( '1', $items[1]['features'] );
	}

	public function test_list_calls_error_on_wp_error(): void {
		$repository = $this->makeEmpty(
			Portal_Repository::class,
			[
				'get' => new WP_Error( 'api_error', 'Could not fetch portal.' ),
			]
		);

		$command = new Portal_Command( $repository );
		$command->list_( [], [] );

		$this->assertSame( 'Could not fetch portal.', $this->logger->last_error );
	}

	// ------------------------------------------------------------------
	// tiers
	// ------------------------------------------------------------------

	public function test_tiers_outputs_tiers_for_product(): void {
		$command = $this->make_command( $this->portals );

		$items = $this->run_tiers_json( $command, 'kadence' );

		$this->assertCount( 2, $items );
		$this->assertSame( 'starter', $items[0]['slug'] );
		$this->assertSame( 'Starter', $items[0]['name'] );
		$this->assertSame( 1, $items[0]['rank'] );
		$this->assertSame( 'pro', $items[1]['slug'] );
		$this->assertSame( 2, $items[1]['rank'] );
	}

	public function test_tiers_calls_error_for_nonexistent_product(): void {
		$command = $this->make_command( $this->portals );

		$command->tiers( [ 'nonexistent' ], [] );

		$this->assertSame( 'Product "nonexistent" not found in portal.', $this->logger->last_error );
	}

	public function test_tiers_logs_no_tiers_for_empty_tier_collection(): void {
		$portal  = new Product_Portal( 'empty-id', 'empty-product', 'Empty Product', new Tier_Collection(), [] );
		$portals = new Portal_Collection();
		$portals->add( $portal );

		$command = $this->make_command( $portals );
		$command->tiers( [ 'empty-product' ], [] );

		$this->assertSame( 'No tiers found.', $this->logger->last_info );
	}

	// ------------------------------------------------------------------
	// features
	// ------------------------------------------------------------------

	public function test_features_outputs_features_for_product(): void {
		$command = $this->make_command( $this->portals );

		$items = $this->run_features_json( $command, 'kadence' );

		$this->assertCount( 2, $items );
		$this->assertSame( 'kadence-blocks-pro', $items[0]['slug'] );
		$this->assertSame( 'plugin', $items[0]['kind'] );
		$this->assertSame( 'starter', $items[0]['minimum_tier'] );
	}

	public function test_features_shows_wporg_slug(): void {
		$command = $this->make_command( $this->portals );

		$items = $this->run_features_json( $command, 'kadence' );

		$this->assertSame( 'kadence-blocks', $items[0]['wporg_slug'] );
		$this->assertSame( '', $items[1]['wporg_slug'] );
	}

	public function test_features_joins_authors_array(): void {
		$command = $this->make_command( $this->portals );

		$items = $this->run_features_json( $command, 'kadence' );

		$this->assertSame( 'Starter Templates', $items[0]['name'] );
	}

	public function test_features_calls_error_for_nonexistent_product(): void {
		$command = $this->make_command( $this->portals );

		$command->features( [ 'nonexistent' ], [] );

		$this->assertSame( 'Product "nonexistent" not found in portal.', $this->logger->last_error );
	}

	public function test_features_logs_no_features_for_empty_list(): void {
		$tiers = new Tier_Collection();
		$tiers->add(
			Portal_Tier::from_array(
				[
					'slug' => 'starter',
					'name' => 'Starter',
					'rank' => 1,
				] 
			) 
		);
		$portal  = new Product_Portal( 'empty-id', 'empty-product', 'Empty Product', $tiers, [] );
		$portals = new Portal_Collection();
		$portals->add( $portal );

		$command = $this->make_command( $portals );
		$command->features( [ 'empty-product' ], [] );

		$this->assertSame( 'No features found.', $this->logger->last_info );
	}

	// ------------------------------------------------------------------
	// refresh
	// ------------------------------------------------------------------

	public function test_refresh_calls_success_on_success(): void {
		$repository = $this->makeEmpty(
			Portal_Repository::class,
			[
				'refresh' => $this->portals,
				'get'     => $this->portals,
			]
		);

		$command = new Portal_Command( $repository );

		ob_start();
		$command->refresh( [], [ 'format' => 'json' ] );
		ob_end_clean();

		$this->assertSame( 'Portal refreshed.', $this->logger->last_success );
	}

	public function test_refresh_calls_error_on_failure(): void {
		$repository = $this->makeEmpty(
			Portal_Repository::class,
			[
				'refresh' => new WP_Error( 'api_error', 'API is down.' ),
			]
		);

		$command = new Portal_Command( $repository );
		$command->refresh( [], [] );

		$this->assertSame( 'API is down.', $this->logger->last_error );
	}

	// ------------------------------------------------------------------
	// status
	// ------------------------------------------------------------------

	public function test_status_shows_never_fetched(): void {
		$repository = $this->makeEmpty(
			Portal_Repository::class,
			[
				'get_last_success_at' => null,
				'get_last_failure_at' => null,
				'get_last_error'      => null,
			]
		);

		$command = new Portal_Command( $repository );
		$command->status( [], [] );

		$this->assertSame( 'Portal has never been fetched.', $this->logger->last_info );
	}

	public function test_status_shows_last_success_timestamp(): void {
		$timestamp = 1700000000;

		$repository = $this->makeEmpty(
			Portal_Repository::class,
			[
				'get_last_success_at' => $timestamp,
				'get_last_failure_at' => null,
				'get_last_error'      => null,
			] 
		);

		$command = new Portal_Command( $repository );
		$command->status( [], [] );

		$expected = sprintf( 'Last successful fetch: %s', gmdate( 'Y-m-d H:i:s', $timestamp ) );
		$this->assertContains( $expected, $this->logger->info_messages );
	}

	public function test_status_shows_last_failure_and_error(): void {
		$timestamp = 1700000000;
		$error     = new WP_Error( 'timeout', 'Connection timed out.' );

		$repository = $this->makeEmpty(
			Portal_Repository::class,
			[
				'get_last_success_at' => null,
				'get_last_failure_at' => $timestamp,
				'get_last_error'      => $error,
			] 
		);

		$command = new Portal_Command( $repository );
		$command->status( [], [] );

		$expected_failure = sprintf( 'Last failed fetch: %s', gmdate( 'Y-m-d H:i:s', $timestamp ) );
		$this->assertContains( $expected_failure, $this->logger->info_messages );
		$this->assertSame( 'Last error: Connection timed out. (timeout)', $this->logger->last_warning );
	}

	// ------------------------------------------------------------------
	// delete
	// ------------------------------------------------------------------

	public function test_delete_calls_success(): void {
		$repository = $this->makeEmpty(
			Portal_Repository::class,
			[
				'delete_portal' => null,
			] 
		);

		$command = new Portal_Command( $repository );
		$command->delete( [], [] );

		$this->assertSame( 'Portal cache deleted.', $this->logger->last_success );
	}

	// ------------------------------------------------------------------
	// Helpers
	// ------------------------------------------------------------------

	/**
	 * Builds a test Portal_Collection with two products.
	 *
	 * @return Portal_Collection
	 */
	private function build_test_portals(): Portal_Collection {
		$kadence_tiers = new Tier_Collection();
		$kadence_tiers->add(
			Portal_Tier::from_array(
				[
					'slug'     => 'starter',
					'name'     => 'Starter',
					'rank'     => 1,
					'price'    => 4900,
					'currency' => 'USD',
				]
			)
		);
		$kadence_tiers->add(
			Portal_Tier::from_array(
				[
					'slug'     => 'pro',
					'name'     => 'Pro',
					'rank'     => 2,
					'price'    => 9900,
					'currency' => 'USD',
				]
			)
		);

		$kadence_features = [
			Portal_Feature::from_array(
				[
					'slug'              => 'kadence-blocks-pro',
					'kind'              => 'plugin',
					'minimum_tier'      => 'starter',
					'name'              => 'Starter Templates',
					'description'       => 'Pro blocks for Kadence.',
					'category'          => 'Design',
					'main_file'         => 'kadence-blocks-pro/kadence-blocks-pro.php',
					'wporg_slug'        => 'kadence-blocks',
					'documentation_url' => 'https://example.com/docs',
				]
			),
			Portal_Feature::from_array(
				[
					'slug'              => 'kadence-pro-addon',
					'kind'              => 'plugin',
					'minimum_tier'      => 'pro',
					'name'              => 'Pro Addon',
					'description'       => 'A pro-only addon.',
					'category'          => 'Design',
					'wporg_slug'        => null,
					'main_file'         => 'kadence-pro-addon/kadence-pro-addon.php',
					'documentation_url' => 'https://example.com/docs/addon',
				]
			),
		];

		$kadence = new Product_Portal( 'kadence-id', 'kadence', 'Kadence', $kadence_tiers, $kadence_features );

		$givewp_tiers = new Tier_Collection();
		$givewp_tiers->add(
			Portal_Tier::from_array(
				[
					'slug'     => 'basic',
					'name'     => 'Basic',
					'rank'     => 1,
					'price'    => 2900,
					'currency' => 'USD',
				]
			)
		);

		$givewp_features = [
			Portal_Feature::from_array(
				[
					'slug'              => 'give-recurring',
					'kind'              => 'plugin',
					'minimum_tier'      => 'basic',
					'name'              => 'Recurring Donations',
					'description'       => 'Accept recurring donations.',
					'category'          => 'Fundraising',
					'main_file'         => 'give-recurring/give-recurring.php',
					'wporg_slug'        => null,
					'documentation_url' => 'https://example.com/docs/recurring',
				]
			),
		];

		$givewp = new Product_Portal( 'givewp-id', 'givewp', 'GiveWP', $givewp_tiers, $givewp_features );

		$collection = new Portal_Collection();
		$collection->add( $kadence );
		$collection->add( $givewp );

		return $collection;
	}

	/**
	 * Creates a Portal_Command with a mocked repository returning the given portal.
	 *
	 * @param Portal_Collection $portals
	 *
	 * @return Portal_Command
	 */
	private function make_command( Portal_Collection $portals ): Portal_Command {
		$repository = $this->makeEmpty(
			Portal_Repository::class,
			[
				'get' => $portals,
			] 
		);

		return new Portal_Command( $repository );
	}

	/**
	 * Runs list_ with --format=json and returns decoded items.
	 *
	 * @param Portal_Command $command
	 *
	 * @return list<array<string, mixed>>
	 */
	private function run_list_json( Portal_Command $command ): array {
		ob_start();
		$command->list_( [], [ 'format' => 'json' ] );
		$output = ob_get_clean();

		$decoded = json_decode( (string) $output, true );

		return is_array( $decoded ) ? $decoded : [];
	}

	/**
	 * Runs tiers with --format=json and returns decoded items.
	 *
	 * @param Portal_Command $command
	 * @param string          $product_slug
	 *
	 * @return list<array<string, mixed>>
	 */
	private function run_tiers_json( Portal_Command $command, string $product_slug ): array {
		ob_start();
		$command->tiers( [ $product_slug ], [ 'format' => 'json' ] );
		$output = ob_get_clean();

		$decoded = json_decode( (string) $output, true );

		return is_array( $decoded ) ? $decoded : [];
	}

	/**
	 * Runs features with --format=json and returns decoded items.
	 *
	 * @param Portal_Command $command
	 * @param string          $product_slug
	 *
	 * @return list<array<string, mixed>>
	 */
	private function run_features_json( Portal_Command $command, string $product_slug ): array {
		ob_start();
		$command->features(
			[ $product_slug ],
			[
				'format' => 'json',
				'fields' => 'slug,kind,minimum_tier,name,description,category,plugin_file,wporg_slug,documentation_url',
			] 
		);
		$output = ob_get_clean();

		$decoded = json_decode( (string) $output, true );

		return is_array( $decoded ) ? $decoded : [];
	}
}
