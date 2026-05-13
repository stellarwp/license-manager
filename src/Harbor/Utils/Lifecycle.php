<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Utils;

/**
 * Harbor bootstrap lifecycle: WordPress hook names and state queries.
 *
 * Hook names are exposed as methods rather than public constants so they
 * can carry runtime deprecation notices via _doing_it_wrong() if a name
 * ever needs to change.
 *
 * @since TBD
 */
final class Lifecycle {

	/**
	 * Hook that closes Harbor's bootstrap window. After this fires the
	 * instance and global-function registries refuse further writes.
	 *
	 * Currently bound to WordPress core's wp_loaded action.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public static function get_bootstrap_end_hook(): string {
		return 'wp_loaded';
	}

	/**
	 * Whether the cross-instance registries still accept writes.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public static function is_bootstrap_window_open(): bool {
		return ! did_action( self::get_bootstrap_end_hook() );
	}
}
