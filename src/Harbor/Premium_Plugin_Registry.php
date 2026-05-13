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
		/**
		 * Filters whether a premium plugin exists.
		 *
		 * @since TBD
		 *
		 * @return bool
		 */
		return (bool) apply_filters( 'lw_harbor/premium_plugin_exists', false );
	}
}
