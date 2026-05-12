<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Consent;

/**
 * Persistence layer for the site owner's consent to make external API
 * communications to Liquid Web services.
 *
 * This class is a pure data-access layer — it only reads from and writes
 * to WordPress storage. The has_consent() method also applies a filter so
 * external code can short-circuit the check (e.g. for testing or to force
 * an environment-wide override).
 *
 * @since TBD
 */
final class Consent_Repository {

	/**
	 * Option name for the consent flag.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const OPTION_NAME = 'lw-harbor-allowed-external-api-communications';

	/**
	 * Whether external API communications are permitted.
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
		return (bool) apply_filters(
			'lw-harbor/allow_external_api_communications',
			(bool) get_option( self::OPTION_NAME, false )
		);
	}

	/**
	 * Grants consent to make external API communications.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function grant_consent(): void {
		update_option( self::OPTION_NAME, true );
	}

	/**
	 * Revokes consent to make external API communications.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function revoke_consent(): void {
		update_option( self::OPTION_NAME, false );
	}
}
