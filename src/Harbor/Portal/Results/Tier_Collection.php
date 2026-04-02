<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Portal\Results;

use LiquidWeb\Harbor\Utils\Collection;

/**
 * A collection of Portal_Tier objects, keyed by slug.
 *
 * @since 1.0.0
 *
 * @extends Collection<Portal_Tier>
 */
final class Tier_Collection extends Collection {

	/**
	 * Adds a tier to the collection.
	 *
	 * @since 1.0.0
	 *
	 * @param Portal_Tier $tier Tier instance.
	 *
	 * @return Portal_Tier
	 */
	public function add( Portal_Tier $tier ): Portal_Tier {
		if ( ! $this->offsetExists( $tier->get_slug() ) ) {
			$this->offsetSet( $tier->get_slug(), $tier );
		}

		return $this->offsetGet( $tier->get_slug() ) ?? $tier;
	}

	/**
	 * Alias of offsetGet().
	 *
	 * @since 1.0.0
	 *
	 * @param string $offset The tier slug.
	 *
	 * @return Portal_Tier|null
	 */
	public function get( $offset ): ?Portal_Tier { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Narrows return type for IDE support.
		return parent::get( $offset );
	}
}
