<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Admin;

use LiquidWeb\Harbor\Contracts\Abstract_Provider;

class Provider extends Abstract_Provider {
	/**
	 * Option name for allowed external API communications.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	private const OPTION_ALLOWED_EXTERNAL_API_COMMUNICATIONS = 'lw-harbor-allowed-external-api-communications';

	/**
	 * Register the service provider.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register() {
		$this->container->singleton( Feature_Manager_Page::class );

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
		$this->container->get( Feature_Manager_Page::class )->maybe_register_page();
	}

	/**
	 * Checks if external API communications are permitted.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function has_consent(): bool {
		/**
		 * Filters whether external API communications are permitted.
		 *
		 * @since TBD
		 *
		 * @param bool $allowed Whether external API communications are permitted.
		 *
		 * @return bool
		 */
		return (bool) apply_filters( 'lw-harbor/allow_external_api_communications', (bool) get_site_option( self::OPTION_ALLOWED_EXTERNAL_API_COMMUNICATIONS, false ) );
	}

	/**
	 * Grants consent to the terms and conditions.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function grant_consent(): void {
		update_site_option( self::OPTION_ALLOWED_EXTERNAL_API_COMMUNICATIONS, true );
	}

	/**
	 * Revokes consent to the terms and conditions.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function revoke_consent(): void {
		update_site_option( self::OPTION_ALLOWED_EXTERNAL_API_COMMUNICATIONS, false );
	}
}
