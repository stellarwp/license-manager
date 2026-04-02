<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Portal;

use LiquidWeb\Harbor\Portal\Results\Product_Portal;
use LiquidWeb\Harbor\Utils\Collection;

/**
 * A collection of Product_Portal objects, keyed by product slug.
 *
 * @since 1.0.0
 *
 * @extends Collection<Product_Portal>
 */
final class Portal_Collection extends Collection {

	/**
	 * Adds a product portal to the collection.
	 *
	 * @since 1.0.0
	 *
	 * @param Product_Portal $portal Product portal instance.
	 *
	 * @return Product_Portal
	 */
	public function add( Product_Portal $portal ): Product_Portal {
		if ( ! $this->offsetExists( $portal->get_product_slug() ) ) {
			$this->offsetSet( $portal->get_product_slug(), $portal );
		}

		return $this->offsetGet( $portal->get_product_slug() ) ?? $portal;
	}

	/**
	 * Alias of offsetGet().
	 *
	 * @since 1.0.0
	 *
	 * @param string $offset The product slug.
	 *
	 * @return Product_Portal|null
	 */
	public function get( $offset ): ?Product_Portal { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Narrows return type for IDE support.
		return parent::get( $offset );
	}

	/**
	 * Converts the collection to an array of raw data arrays.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function to_array(): array {
		$data = [];

		foreach ( $this as $portal ) {
			$data[] = $portal->to_array();
		}

		return $data;
	}

	/**
	 * Creates a Portal_Collection from an array of Product_Portal objects or raw data arrays.
	 *
	 * @since 1.0.0
	 *
	 * @param array<Product_Portal|array<string, mixed>> $data Product portals or raw arrays.
	 *
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$collection = new self();

		foreach ( $data as $item ) {
			if ( $item instanceof Product_Portal ) {
				$collection->add( $item );
			} elseif ( is_array( $item ) ) {
				$collection->add( Product_Portal::from_array( $item ) );
			}
		}

		return $collection;
	}
}
