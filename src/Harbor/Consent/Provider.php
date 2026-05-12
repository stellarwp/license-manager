<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Consent;

use LiquidWeb\Harbor\Contracts\Abstract_Provider;

/**
 * Registers the Consent subsystem in the DI container.
 *
 * Must be registered before any subsystem that needs to gate behavior on
 * the site owner's consent (Admin, Http, Licensing, Portal).
 *
 * @since 1.1.0
 */
final class Provider extends Abstract_Provider {

	/**
	 * @inheritDoc
	 */
	public function register(): void {
		$this->container->singleton( Consent_Repository::class );
	}
}
