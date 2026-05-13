<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor;

use LiquidWeb\Harbor\Utils\Cast;

/**
 * Queries the cross-instance premium plugin existence registry.
 *
 * Premium plugins register a callback via the
 * lw_harbor/premium_plugin_existence_callbacks filter that returns true when
 * they should be considered active and Harbor should be initialized.
 * This class wraps that filter and exposes typed queries against the registered callback set.
 *
 * @since TBD
 */
class Premium_Plugin_Registry {

	/**
	 * Whether at least one registered callback reports an active premium plugin.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function any(): bool {
		return ! empty(
			array_filter(
				$this->callbacks(),
				static fn( callable $callback ): bool => Cast::to_bool( $callback() )
			)
		);
	}

	/**
	 * Returns the registered existence-check callbacks.
	 *
	 * @since TBD
	 *
	 * @return array<callable>
	 */
	private function get_callbacks(): array {
		/**
		 * Filters the premium plugin existence callbacks.
		 *
		 * @since TBD
		 *
		 * @param array<callable> $callbacks The callbacks to check if a premium plugin exists.
		 *
		 * @return array<callable>
		 */
		return (array) apply_filters( 'lw_harbor/premium_plugin_existence_callbacks', [] );
	}
}
