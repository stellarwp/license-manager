<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Licensing\Contracts;

/**
 * Contract for retrieving the unified license key.
 *
 * @since 1.0.0
 */
interface License_Key_Provider {

	/**
	 * Get the stored unified license key.
	 *
	 * Returns null if no key exists.
	 *
	 * @since 1.0.0
	 *
	 * @return ?string The license key, or null if not set.
	 */
	public function get_key(): ?string;
}
