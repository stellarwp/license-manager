<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\CLI\Commands;

use LiquidWeb\Harbor\Portal\Portal_Repository;
use LiquidWeb\Harbor\Portal\Results\Portal_Tier;
use LiquidWeb\Harbor\CLI\Display;
use LiquidWeb\Harbor\Portal\Results\Product_Portal;
use LiquidWeb\Harbor\Utils\Cast;
use WP_CLI;
use WP_CLI\Formatter;
use WP_CLI_Command;

/**
 * Manage the product portal.
 *
 * ## EXAMPLES
 *
 *     # List all products in the portal
 *     wp harbor portal list
 *
 *     # Show tiers for a product
 *     wp harbor portal tiers kadence
 *
 *     # Show features for a product
 *     wp harbor portal features kadence
 *
 *     # Force refresh from the API
 *     wp harbor portal refresh
 *
 *     # Show portal status
 *     wp harbor portal status
 *
 * @since 1.0.0
 */
class Portal extends WP_CLI_Command {

	/**
	 * The portal repository.
	 *
	 * @since 1.0.0
	 *
	 * @var Portal_Repository
	 */
	private Portal_Repository $repository;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Portal_Repository $repository The portal repository.
	 */
	public function __construct( Portal_Repository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Lists all products in the portal.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Output format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - csv
	 *   - yaml
	 *   - count
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # List all products
	 *     wp harbor portal list
	 *
	 *     # List as JSON
	 *     wp harbor portal list --format=json
	 *
	 * @subcommand list
	 *
	 * @param array<int, string>    $args       Positional arguments.
	 * @param array<string, string> $assoc_args Associative arguments.
	 *
	 * @return void
	 */
	public function list_( array $args, array $assoc_args ): void {
		$portals = $this->repository->get();

		if ( is_wp_error( $portals ) ) {
			WP_CLI::error( $portals->get_error_message() );

			return; // WP_CLI::error() exits, but PHPStan needs this for type narrowing.
		}

		$items = [];

		foreach ( $portals as $portal ) {
			/** @var Product_Portal $portal */
			$items[] = [
				'product_slug' => $portal->get_product_slug(),
				'tiers'        => (string) $portal->get_tiers()->count(),
				'features'     => (string) count( $portal->get_features() ),
			];
		}

		$formatter = new Formatter(
			$assoc_args,
			[ 'product_slug', 'tiers', 'features' ]
		);

		$formatter->display_items( $items );
	}

	/**
	 * Shows tiers for a product.
	 *
	 * ## OPTIONS
	 *
	 * <product_slug>
	 * : The product slug.
	 *
	 * [--fields=<fields>]
	 * : Comma-separated list of fields to display.
	 *
	 * [--format=<format>]
	 * : Output format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - csv
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # Show tiers for a product
	 *     wp harbor portal tiers kadence
	 *
	 * @param array<int, string>    $args       Positional arguments.
	 * @param array<string, string> $assoc_args Associative arguments.
	 *
	 * @return void
	 */
	public function tiers( array $args, array $assoc_args ): void {
		$portal = $this->get_product_portal( $args[0] );

		if ( $portal === null ) {
			return;
		}

		$items = [];

		foreach ( $portal->get_tiers() as $tier ) {
			/** @var Portal_Tier $tier */
			$items[] = $tier->to_array();
		}

		if ( empty( $items ) ) {
			WP_CLI::log( 'No tiers found.' );

			return;
		}

		$formatter = new Formatter(
			$assoc_args,
			[ 'slug', 'name', 'rank', 'price', 'currency' ]
		);

		$formatter->display_items( $items );
	}

	/**
	 * Shows features for a product.
	 *
	 * ## OPTIONS
	 *
	 * <product_slug>
	 * : The product slug.
	 *
	 * [--fields=<fields>]
	 * : Comma-separated list of fields to display.
	 *
	 * [--format=<format>]
	 * : Output format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - csv
	 *   - yaml
	 * ---
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * * slug
	 * * kind
	 * * minimum_tier
	 * * name
	 * * description
	 * * category
	 * * plugin_file
	 * * wporg_slug
	 * * download_url
	 * * version
	 * * authors
	 * * documentation_url
	 * * homepage
	 *
	 * ## EXAMPLES
	 *
	 *     # Show features for a product
	 *     wp harbor portal features kadence
	 *
	 *     # Show as JSON
	 *     wp harbor portal features kadence --format=json
	 *
	 * @param array<int, string>    $args       Positional arguments.
	 * @param array<string, string> $assoc_args Associative arguments.
	 *
	 * @return void
	 */
	public function features( array $args, array $assoc_args ): void {
		$portal = $this->get_product_portal( $args[0] );

		if ( $portal === null ) {
			return;
		}

		$features = $portal->get_features();

		if ( empty( $features ) ) {
			WP_CLI::log( 'No features found.' );

			return;
		}

		$items = [];

		foreach ( $features as $feature ) {
			$item = $feature->to_array();

			$item['wporg_slug'] ??= '';

			if ( is_array( $item['authors'] ) ) {
				$item['authors'] = implode( ', ', array_map( [ Cast::class, 'to_string' ], $item['authors'] ) );
			} else {
				$item['authors'] = '';
			}

			$items[] = $item;
		}

		$formatter = new Formatter(
			$assoc_args,
			[ 'slug', 'kind', 'minimum_tier', 'name', 'category' ]
		);

		$formatter->display_items( $items );
	}

	/**
	 * Force refreshes the portal from the API.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Output format for the resulting portal.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - csv
	 *   - yaml
	 *   - count
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # Refresh the portal
	 *     wp harbor portal refresh
	 *
	 * @param array<int, string>    $args       Positional arguments.
	 * @param array<string, string> $assoc_args Associative arguments.
	 *
	 * @return void
	 */
	public function refresh( array $args, array $assoc_args ): void {
		$result = $this->repository->refresh();

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );

			return; // WP_CLI::error() exits, but PHPStan needs this for type narrowing.
		}

		WP_CLI::success( 'Portal refreshed.' );

		$this->list_( $args, $assoc_args );
	}

	/**
	 * Shows the portal cache status.
	 *
	 * Displays when the portal was last fetched, whether the last fetch
	 * succeeded or failed, and the error message if applicable.
	 *
	 * ## EXAMPLES
	 *
	 *     # Show portal status
	 *     wp harbor portal status
	 *
	 * @param array<int, string>    $args       Positional arguments.
	 * @param array<string, string> $assoc_args Associative arguments.
	 *
	 * @return void
	 */
	public function status( array $args, array $assoc_args ): void {
		$last_success = $this->repository->get_last_success_at();
		$last_failure = $this->repository->get_last_failure_at();
		$last_error   = $this->repository->get_last_error();

		if ( $last_success === null && $last_failure === null ) {
			WP_CLI::log( 'Portal has never been fetched.' );

			return;
		}

		if ( $last_success !== null ) {
			WP_CLI::log(
				sprintf(
					'Last successful fetch: %s',
					gmdate( 'Y-m-d H:i:s', $last_success )
				)
			);
		}

		if ( $last_failure !== null ) {
			WP_CLI::log(
				sprintf(
					'Last failed fetch: %s',
					gmdate( 'Y-m-d H:i:s', $last_failure )
				)
			);
		}

		if ( $last_error !== null ) {
			WP_CLI::warning(
				sprintf(
					'Last error: %s (%s)',
					$last_error->get_error_message(),
					$last_error->get_error_code()
				)
			);
		}
	}

	/**
	 * Deletes the stored portal cache.
	 *
	 * The next request for the portal will fetch fresh data from the API.
	 *
	 * ## EXAMPLES
	 *
	 *     # Delete cached portal
	 *     wp harbor portal delete
	 *
	 * @param array<int, string>    $args       Positional arguments.
	 * @param array<string, string> $assoc_args Associative arguments.
	 *
	 * @return void
	 */
	public function delete( array $args, array $assoc_args ): void {
		$this->repository->delete_portal();

		WP_CLI::success( 'Portal cache deleted.' );
	}

	/**
	 * Fetches a single product portal by slug, with error handling.
	 *
	 * @since 1.0.0
	 *
	 * @param string $product_slug The product slug.
	 *
	 * @return Product_Portal|null The product portal, or null on error (error already printed).
	 */
	private function get_product_portal( string $product_slug ): ?Product_Portal {
		$portals = $this->repository->get();

		if ( is_wp_error( $portals ) ) {
			WP_CLI::error( $portals->get_error_message() );

			return null; // WP_CLI::error() exits.
		}

		$portal = $portals->get( $product_slug );

		if ( $portal === null ) {
			WP_CLI::error( sprintf( 'Product "%s" not found in portal.', $product_slug ) );

			return null; // WP_CLI::error() exits.
		}

		return $portal;
	}
}
