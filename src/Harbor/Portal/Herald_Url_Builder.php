<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Portal;

use LiquidWeb\Harbor\Config;
use LiquidWeb\Harbor\Legacy\License_Repository as Legacy_License_Repository;
use LiquidWeb\Harbor\Licensing\Repositories\License_Repository;
use LiquidWeb\Harbor\Portal\Contracts\Download_Url_Builder;
use LiquidWeb\Harbor\Site\Data;

/**
 * Builds Herald download URLs for catalog features.
 *
 * Herald is the StellarWP download service. Two URL formats are produced depending
 * on which license type covers the requested slug:
 *
 * - Unified license:  {herald_base_url}/download/{slug}/latest/{license_key}/zip?site={domain}
 * - Legacy license:   {herald_base_url}/legacy/download?plugin={slug}&key={legacy_key}&site={domain}
 *
 * Legacy keys take precedence when both are present so a legacy-only customer's
 * stored key drives their downloads even when a Unified key is also installed.
 *
 * @since 1.0.0
 */
final class Herald_Url_Builder implements Download_Url_Builder {

	/**
	 * The Unified license key provider.
	 *
	 * @since 1.0.0
	 *
	 * @var License_Repository
	 */
	private License_Repository $license_repository;

	/**
	 * The legacy license repository.
	 *
	 * @since TBD
	 *
	 * @var Legacy_License_Repository
	 */
	private Legacy_License_Repository $legacy_repository;

	/**
	 * Site data provider.
	 *
	 * @since 1.0.0
	 *
	 * @var Data
	 */
	private Data $site_data;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param License_Repository        $license_repository The Unified license key provider.
	 * @param Legacy_License_Repository $legacy_repository  The legacy license repository.
	 * @param Data                      $site_data          Site data provider.
	 */
	public function __construct(
		License_Repository $license_repository,
		Legacy_License_Repository $legacy_repository,
		Data $site_data
	) {
		$this->license_repository = $license_repository;
		$this->legacy_repository  = $legacy_repository;
		$this->site_data          = $site_data;
	}

	/**
	 * Builds a Herald download URL for the given feature slug.
	 *
	 * Returns the legacy `/legacy/download` URL when a matching active legacy
	 * license exists for the slug. Otherwise falls back to the Unified
	 * `/download/{slug}/latest/{key}/zip` URL. Returns an empty string when
	 * neither a license nor a domain is available.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The catalog feature slug.
	 *
	 * @return string
	 */
	public function build( string $slug ): string {
		$domain = $this->site_data->get_domain();

		if ( $domain === '' ) {
			return '';
		}

		$legacy = $this->legacy_repository->find( $slug );

		if ( $legacy !== null && $legacy->is_active && $legacy->key !== '' ) {
			return add_query_arg(
				[
					'plugin' => rawurlencode( $slug ),
					'key'    => rawurlencode( $legacy->key ),
					'site'   => rawurlencode( $domain ),
				],
				Config::get_herald_base_url() . '/legacy/download'
			);
		}

		$license_key = $this->license_repository->get_key();

		if ( $license_key === null ) {
			return '';
		}

		$url = Config::get_herald_base_url()
			. '/download/'
			. rawurlencode( $slug )
			. '/latest/'
			. rawurlencode( $license_key )
			. '/zip';

		return add_query_arg(
			[
				'site' => rawurlencode( $domain ),
			],
			$url
		);
	}
}
