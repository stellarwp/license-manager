<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Admin;

use LiquidWeb\Harbor\Consent\Consent_Repository;
use LiquidWeb\Harbor\Contracts\Abstract_Provider;
use LiquidWeb\Harbor\Contracts\Admin_Page_Interface;

class Provider extends Abstract_Provider {

	/**
	 * Register the service provider.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register() {
		$this->container->singleton( Feature_Manager_Page::class );
		$this->container->singleton( Opt_In_Page::class );

		// Bind the page slot to whichever concrete page matches the current
		// consent state. The singleton resolves once per request, which is
		// fine because the admin_menu hook only fires once.
		$this->container->singleton(
			Admin_Page_Interface::class,
			function () {
				$consent = $this->container->get( Consent_Repository::class );

				return $consent->has_consent()
					? $this->container->get( Feature_Manager_Page::class )
					: $this->container->get( Opt_In_Page::class );
			}
		);

		add_action( 'admin_menu', [ $this, 'register_unified_feature_manager_page' ], 20, 0 );
	}

	/**
	 * Registers the unified feature manager page if this instance
	 * has the highest Harbor version among all active instances.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_unified_feature_manager_page(): void {
		$this->container->get( Admin_Page_Interface::class )->maybe_register_page();
	}
}
