<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Portal\Clients;

use LiquidWeb\Harbor\Portal\Portal_Collection;
use LiquidWeb\Harbor\Portal\Error_Code;
use LiquidWeb\Harbor\Portal\Results\Product_Portal;
use WP_Error;

/**
 * A fixture-based portal client that reads from a JSON file.
 *
 * @since 1.0.0
 */
final class Fixture_Client implements Portal_Client {

	/**
	 * The path to the fixture JSON file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected string $fixture_file;

	/**
	 * In-memory cache of the parsed portal.
	 *
	 * @since 1.0.0
	 *
	 * @var Portal_Collection|WP_Error|null
	 */
	protected $cache;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $fixture_file Path to the fixture JSON file.
	 */
	public function __construct( string $fixture_file ) {
		$this->fixture_file = $fixture_file;
	}

	/**
	 * Fetch the full portal for all products.
	 *
	 * @since 1.0.0
	 *
	 * @return Portal_Collection|WP_Error
	 */
	public function get_portal() {
		if ( $this->cache !== null ) {
			return $this->cache;
		}

		$json = @file_get_contents( $this->fixture_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPress.PHP.NoSilencedErrors.Discouraged, WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown -- local fixture file, error silenced intentionally.

		if ( $json === false ) {
			$this->cache = new WP_Error(
				Error_Code::INVALID_RESPONSE,
				'Portal fixture file could not be read.'
			);

			return $this->cache;
		}

		$data = json_decode( $json, true );

		if ( ! is_array( $data ) ) {
			$this->cache = new WP_Error(
				Error_Code::INVALID_RESPONSE,
				'Portal fixture file could not be decoded.'
			);

			return $this->cache;
		}

		$portals = new Portal_Collection();

		foreach ( $data as $item ) {
			if ( ! is_array( $item ) || ! isset( $item['product_slug'] ) ) {
				$this->cache = new WP_Error(
					Error_Code::INVALID_RESPONSE,
					'Portal entry missing product_slug.'
				);

				return $this->cache;
			}

			/** @var array<string, mixed> $item */
			$portals->add( Product_Portal::from_array( $item ) );
		}

		if ( $portals->count() === 0 ) {
			$this->cache = new WP_Error(
				Error_Code::INVALID_RESPONSE,
				'Portal fixture file is empty.'
			);

			return $this->cache;
		}

		$this->cache = $portals;

		return $this->cache;
	}
}
