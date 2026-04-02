<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Features\Update;

use LiquidWeb\Harbor\Portal\Portal_Collection;
use LiquidWeb\Harbor\Portal\Portal_Repository;
use LiquidWeb\Harbor\Portal\Results\Portal_Feature;
use LiquidWeb\Harbor\Features\Contracts\Installable;
use LiquidWeb\Harbor\Features\Feature_Repository;
use LiquidWeb\Harbor\Traits\With_Debugging;
use WP_Error;

/**
 * Resolves update data by joining the Feature_Repository and Portal.
 *
 * The Feature_Repository determines which features the site is licensed for
 * (availability). The Portal provides the download URL and latest version.
 *
 * Only features where is_available() returns true are included,
 * ensuring the update API only serves updates the site is licensed for.
 * Dot-org features are excluded since WordPress.org serves their updates.
 *
 * Works for both Plugin and Theme features — the caller passes the desired
 * feature type constant, and the handler is responsible for reading any
 * type-specific fields (e.g. plugin_file, stylesheet) from the Feature object.
 *
 * @since 1.0.0
 */
class Resolve_Update_Data {

	use With_Debugging;

	/**
	 * The feature repository.
	 *
	 * @since 1.0.0
	 *
	 * @var Feature_Repository
	 */
	private Feature_Repository $feature_repository;

	/**
	 * The portal repository.
	 *
	 * @since 1.0.0
	 *
	 * @var Portal_Repository
	 */
	private Portal_Repository $portal_repository;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Feature_Repository $feature_repository The feature repository.
	 * @param Portal_Repository $portal_repository The portal repository.
	 */
	public function __construct(
		Feature_Repository $feature_repository,
		Portal_Repository $portal_repository
	) {
		$this->feature_repository = $feature_repository;
		$this->portal_repository = $portal_repository;
	}

	/**
	 * Fetches available Installable features of the given type and transforms them into update data.
	 *
	 * Joins feature availability from the Feature_Repository with download
	 * URLs and versions from the Portal_Repository.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The feature type to resolve (a Feature::TYPE_* constant).
	 *
	 * @return array<string, array<string, mixed>>|WP_Error Keyed by slug, each entry contains update fields.
	 */
	public function __invoke( string $type ) {
		$features = $this->feature_repository->get();

		if ( is_wp_error( $features ) ) {
			static::debug_log_wp_error(
				$features,
				'Resolve_Update_Data: feature repository failed'
			);

			return $features;
		}

		$portal = $this->portal_repository->get();

		if ( is_wp_error( $portal ) ) {
			static::debug_log_wp_error(
				$portal,
				'Resolve_Update_Data: portal repository failed'
			);

			return $portal;
		}

		$portal_features = $this->build_portal_feature_map( $portal );
		$available        = $features->filter( null, null, true, $type );
		$updates          = [];

		foreach ( $available as $feature ) {
			if ( ! $feature instanceof Installable ) {
				continue;
			}

			$slug            = $feature->get_slug();
			$portal_feature = $portal_features[ $slug ] ?? null;

			if ( $portal_feature === null || $portal_feature->is_wporg() ) {
				continue;
			}

			$updates[ $slug ] = $feature->get_update_data( $portal_feature );
		}

		return $updates;
	}

	/**
	 * Builds a flat map of feature slug to Portal_Feature from the portal.
	 *
	 * @since 1.0.0
	 *
	 * @param Portal_Collection $portal The portal collection.
	 *
	 * @return array<string, Portal_Feature>
	 */
	private function build_portal_feature_map( Portal_Collection $portal ): array {
		$map = [];

		foreach ( $portal as $product ) {
			foreach ( $product->get_features() as $portal_feature ) {
				$map[ $portal_feature->get_slug() ] = $portal_feature;
			}
		}

		return $map;
	}
}
