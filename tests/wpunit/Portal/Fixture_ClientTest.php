<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Tests\Portal;

use LiquidWeb\Harbor\Portal\Portal_Collection;
use LiquidWeb\Harbor\Portal\Clients\Fixture_Client;
use LiquidWeb\Harbor\Portal\Results\Portal_Tier;
use LiquidWeb\Harbor\Portal\Results\Product_Portal;
use LiquidWeb\Harbor\Tests\HarborTestCase;

final class Fixture_ClientTest extends HarborTestCase {

	private Fixture_Client $client;

	protected function setUp(): void {
		parent::setUp();

		$this->client = new Fixture_Client( codecept_data_dir( 'portal/default.json' ) );
	}

	public function test_get_portal_returns_all_products(): void {
		$result = $this->client->get_portal();

		$this->assertInstanceOf( Portal_Collection::class, $result );
		$this->assertCount( 4, $result );

		foreach ( $result as $portal ) {
			$this->assertInstanceOf( Product_Portal::class, $portal );
		}
	}

	public function test_get_portal_contains_expected_slugs(): void {
		$result = $this->client->get_portal();

		$this->assertNotNull( $result->get( 'kadence' ) );
		$this->assertNotNull( $result->get( 'the-events-calendar' ) );
		$this->assertNotNull( $result->get( 'give' ) );
		$this->assertNotNull( $result->get( 'learndash' ) );
	}

	public function test_tiers_have_slug_and_rank(): void {
		$result = $this->client->get_portal();

		foreach ( $result as $portal ) {
			$tiers = $portal->get_tiers();

			$this->assertNotEmpty( $tiers, sprintf( '%s should have tiers', $portal->get_product_slug() ) );

			foreach ( $tiers as $tier ) {
				$this->assertInstanceOf( Portal_Tier::class, $tier );
				$this->assertNotEmpty( $tier->get_slug() );
				$this->assertGreaterThanOrEqual( 0, $tier->get_rank() );
			}
		}
	}

	public function test_tier_rank_ordering(): void {
		$result = $this->client->get_portal();

		foreach ( $result as $portal ) {
			$prev = null;

			foreach ( $portal->get_tiers() as $tier ) {
				if ( $prev !== null ) {
					$this->assertGreaterThan(
						$prev->get_rank(),
						$tier->get_rank(),
						sprintf( '%s: tier %s should rank higher than %s', $portal->get_product_slug(), $tier->get_slug(), $prev->get_slug() )
					);
				}

				$prev = $tier;
			}
		}
	}

	public function test_all_three_feature_types_present(): void {
		$result = $this->client->get_portal();
		$types  = [];

		foreach ( $result as $portal ) {
			foreach ( $portal->get_features() as $feature ) {
				$types[ $feature->get_kind() ] = true;
			}
		}

		$this->assertArrayHasKey( 'plugin', $types );
		$this->assertArrayHasKey( 'theme', $types );
	}

	public function test_plugin_features_have_plugin_file(): void {
		$result = $this->client->get_portal();

		foreach ( $result as $portal ) {
			foreach ( $portal->get_features() as $feature ) {
				if ( $feature->get_kind() === 'plugin' ) {
					$this->assertNotNull( $feature->get_plugin_file(), sprintf( '%s should have plugin_file', $feature->get_slug() ) );
				}
			}
		}
	}

	public function test_get_portal_caches_result(): void {
		$first  = $this->client->get_portal();
		$second = $this->client->get_portal();

		$this->assertSame( $first, $second );
	}
}
