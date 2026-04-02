<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Tests\Portal;

use LiquidWeb\Harbor\Portal\Portal_Collection;
use LiquidWeb\Harbor\Portal\Results\Product_Portal;
use LiquidWeb\Harbor\Tests\HarborTestCase;

final class Portal_CollectionTest extends HarborTestCase {

	public function test_it_adds_and_gets_portals(): void {
		$collection = new Portal_Collection();
		$kadence    = Product_Portal::from_array(
			[
				'product_slug' => 'kadence',
				'tiers'        => [],
				'features'     => [],
			]
		);
		$tec        = Product_Portal::from_array(
			[
				'product_slug' => 'tec',
				'tiers'        => [],
				'features'     => [],
			]
		);

		$collection->add( $kadence );
		$collection->add( $tec );

		$this->assertSame( 2, $collection->count() );
		$this->assertSame( $kadence, $collection->get( 'kadence' ) );
		$this->assertSame( $tec, $collection->get( 'tec' ) );
	}

	public function test_it_does_not_duplicate_portals_with_same_slug(): void {
		$collection = new Portal_Collection();
		$first      = Product_Portal::from_array(
			[
				'product_slug' => 'kadence',
				'tiers'        => [],
				'features'     => [],
			]
		);
		$second     = Product_Portal::from_array(
			[
				'product_slug' => 'kadence',
				'tiers'        => [],
				'features'     => [],
			]
		);

		$collection->add( $first );
		$collection->add( $second );

		$this->assertSame( 1, $collection->count() );
		$this->assertSame( $first, $collection->get( 'kadence' ) );
	}

	public function test_it_returns_null_for_unknown_slug(): void {
		$collection = new Portal_Collection();

		$this->assertNull( $collection->get( 'nonexistent' ) );
	}

	public function test_it_iterates_over_portals(): void {
		$collection = new Portal_Collection();
		$collection->add(
			Product_Portal::from_array(
				[
					'product_slug' => 'kadence',
					'tiers'        => [],
					'features'     => [],
				]
			)
		);
		$collection->add(
			Product_Portal::from_array(
				[
					'product_slug' => 'tec',
					'tiers'        => [],
					'features'     => [],
				]
			)
		);

		$slugs = [];

		foreach ( $collection as $slug => $portal ) {
			$slugs[] = $slug;
		}

		$this->assertSame( [ 'kadence', 'tec' ], $slugs );
	}

	public function test_it_counts_portals(): void {
		$collection = new Portal_Collection();

		$this->assertSame( 0, $collection->count() );

		$collection->add(
			Product_Portal::from_array(
				[
					'product_slug' => 'kadence',
					'tiers'        => [],
					'features'     => [],
				]
			)
		);

		$this->assertSame( 1, $collection->count() );
	}

	public function test_from_array_creates_collection_from_objects(): void {
		$kadence = Product_Portal::from_array(
			[
				'product_slug' => 'kadence',
				'tiers'        => [],
				'features'     => [],
			] 
		);
		$tec     = Product_Portal::from_array(
			[
				'product_slug' => 'tec',
				'tiers'        => [],
				'features'     => [],
			] 
		);

		$collection = Portal_Collection::from_array( [ $kadence, $tec ] );

		$this->assertSame( 2, $collection->count() );
		$this->assertSame( $kadence, $collection->get( 'kadence' ) );
		$this->assertSame( $tec, $collection->get( 'tec' ) );
	}

	public function test_from_array_creates_collection_from_raw_data(): void {
		$data = [
			[
				'product_slug' => 'kadence',
				'tiers'        => [
					[
						'slug'         => 'basic',
						'name'         => 'Basic',
						'rank'         => 1,
						'price'        => 0,
						'currency'     => 'USD',
						'features'     => [],
						'herald_slugs' => [],
					],
				],
				'features'     => [],
			],
			[
				'product_slug' => 'tec',
				'tiers'        => [],
				'features'     => [],
			],
		];

		$collection = Portal_Collection::from_array( $data );

		$this->assertSame( 2, $collection->count() );
		$this->assertInstanceOf( Product_Portal::class, $collection->get( 'kadence' ) );
		$this->assertInstanceOf( Product_Portal::class, $collection->get( 'tec' ) );
		$this->assertSame( 'kadence', $collection->get( 'kadence' )->get_product_slug() );
	}

	public function test_from_array_skips_non_array_items(): void {
		$data = [
			[
				'product_slug' => 'kadence',
				'tiers'        => [],
				'features'     => [],
			],
			'not-an-array',
		];

		$collection = Portal_Collection::from_array( $data );

		$this->assertSame( 1, $collection->count() );
	}

	public function test_from_array_returns_empty_collection_for_empty_input(): void {
		$collection = Portal_Collection::from_array( [] );

		$this->assertSame( 0, $collection->count() );
	}
}
