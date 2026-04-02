<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Features;

use LiquidWeb\Harbor\Portal\Portal_Repository;
use LiquidWeb\Harbor\Portal\Results\Portal_Feature;
use LiquidWeb\Harbor\Portal\Results\Product_Portal;
use LiquidWeb\Harbor\Features\Contracts\Installable;
use LiquidWeb\Harbor\Features\Types\Feature;
use LiquidWeb\Harbor\Features\Types\Plugin;
use LiquidWeb\Harbor\Features\Types\Theme;
use LiquidWeb\Harbor\Licensing\License_Manager;
use LiquidWeb\Harbor\Licensing\Product_Collection;
use LiquidWeb\Harbor\Site\Data;
use LiquidWeb\Harbor\Traits\With_Debugging;
use WP_Error;

/**
 * Joins portal and licensing data to produce a resolved Feature_Collection.
 *
 * For each portal feature, computes is_available and in_portal_tier by checking
 * the product entry's capabilities array and the user's licensed tier rank.
 * dot.org and free-tier (rank 0) features are unconditionally available regardless of capabilities.
 *
 * @since 1.0.0
 */
class Resolve_Feature_Collection {

	use With_Debugging;

	/**
	 * The portal repository.
	 *
	 * @since 1.0.0
	 *
	 * @var Portal_Repository
	 */
	private Portal_Repository $portal;

	/**
	 * The license manager.
	 *
	 * @since 1.0.0
	 *
	 * @var License_Manager
	 */
	private License_Manager $licensing;

	/**
	 * The site data provider.
	 *
	 * @since 1.0.0
	 *
	 * @var Data
	 */
	private Data $site_data;

	/**
	 * Map of portal type strings to Feature subclass names.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, class-string<Feature>>
	 */
	private array $type_map = [
		Feature::TYPE_PLUGIN => Plugin::class,
		Feature::TYPE_THEME  => Theme::class,
	];

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Portal_Repository $portal   The portal repository.
	 * @param License_Manager    $licensing The license manager.
	 * @param Data               $site_data The site data provider.
	 */
	public function __construct(
		Portal_Repository $portal,
		License_Manager $licensing,
		Data $site_data
	) {
		$this->portal   = $portal;
		$this->licensing = $licensing;
		$this->site_data = $site_data;
	}

	/**
	 * Registers a Feature subclass for a given portal type string.
	 *
	 * @since 1.0.0
	 *
	 * @param string                $type          A Feature::TYPE_* constant (e.g. Feature::TYPE_PLUGIN).
	 * @param class-string<Feature> $feature_class The Feature subclass FQCN.
	 *
	 * @return void
	 */
	public function register_type( string $type, string $feature_class ): void { // phpcs:ignore Squiz.Commenting.FunctionComment.IncorrectTypeHint -- class-string<Feature> is a PHPStan type narrowing.
		$this->type_map[ $type ] = $feature_class;
	}

	/**
	 * Fetches portal and licensing data and resolves them into a Feature_Collection.
	 *
	 * Iterates each portal product, finds the matching license entry,
	 * and hydrates Feature objects with computed is_available values.
	 *
	 * @since 1.0.0
	 *
	 * @return Feature_Collection|WP_Error
	 */
	public function __invoke() {
		$portal = $this->portal->get();

		if ( is_wp_error( $portal ) ) {
			static::debug_log_wp_error(
				$portal,
				'Portal fetch failed during feature resolution'
			);

			return $portal;
		}

		$products = $this->licensing->get_products( $this->site_data->get_domain() );

		if ( is_wp_error( $products ) ) {
			if ( $this->licensing->get_key() === null ) {
				$products = new Product_Collection();
			} else {
				static::debug_log_wp_error(
					$products,
					'Licensing fetch failed during feature resolution'
				);

				return $products;
			}
		}

		$collection = new Feature_Collection();

		foreach ( $portal as $product ) {
			if ( ! $product instanceof Product_Portal ) {
				continue;
			}

			$capabilities      = $this->resolve_capabilities( $product, $products );
			$license_tier_rank = $this->resolve_license_tier_rank( $product, $products );

			foreach ( $product->get_features() as $portal_feature ) {
				$feature = $this->hydrate_feature( $portal_feature, $product, $capabilities, $license_tier_rank );

				if ( is_wp_error( $feature ) ) {
					static::debug_log( $feature->get_error_message() );
					continue;
				}

				$collection->add( $feature );
			}
		}

		return $collection;
	}

	/**
	 * Resolves the capabilities granted by the license for a given product.
	 *
	 * Returns the capabilities array from the product entry when a license exists,
	 * or null when no license is present for this product.
	 *
	 * @since 1.0.0
	 *
	 * @param Product_Portal    $product  The portal product.
	 * @param Product_Collection $products The licensing product collection.
	 *
	 * @return string[]|null The capabilities array, or null if the product has no license.
	 */
	private function resolve_capabilities( Product_Portal $product, Product_Collection $products ): ?array {
		$license = $products->get( $product->get_product_slug() );

		if ( null === $license ) {
			return null;
		}

		return $license->get_capabilities();
	}

	/**
	 * Returns the rank of the user's licensed tier for a product, or -1 if unlicensed.
	 *
	 * Used alongside resolve_capabilities() to compute in_portal_tier for each feature.
	 * A rank of -1 means no license covers this product, so no paid-tier features are
	 * considered "in tier".
	 *
	 * @since 1.0.0
	 *
	 * @param Product_Portal    $product  The portal product.
	 * @param Product_Collection $products The licensing product collection.
	 *
	 * @return int The license tier rank, or -1 if no license covers this product.
	 */
	private function resolve_license_tier_rank( Product_Portal $product, Product_Collection $products ): int {
		$license = $products->get( $product->get_product_slug() );

		if ( null === $license ) {
			return -1;
		}

		$tier = $product->get_tier_by_slug( $license->get_tier() );

		return $tier !== null ? $tier->get_rank() : -1;
	}

	/**
	 * Hydrates a Feature object from a portal feature entry.
	 *
	 * Maps portal types (plugin, theme) to Feature subclasses
	 * and computes is_available and in_portal_tier.
	 *
	 * dot.org and free-tier (rank 0) features are unconditionally available regardless of capabilities.
	 * When capabilities is null (no license), all paid-tier features are unavailable and not in tier.
	 *
	 * @since 1.0.0
	 *
	 * @param Portal_Feature $portal_feature   The portal feature entry.
	 * @param Product_Portal $product           The parent portal product.
	 * @param string[]|null   $capabilities      The license capabilities, or null if unlicensed.
	 * @param int             $license_tier_rank The user's licensed tier rank, or -1 if unlicensed.
	 *
	 * @return Feature|WP_Error The hydrated feature, or WP_Error for unknown types.
	 */
	private function hydrate_feature(
		Portal_Feature $portal_feature,
		Product_Portal $product,
		?array $capabilities,
		int $license_tier_rank
	) {
		$portal_kind = $portal_feature->get_kind();
		$class        = $this->type_map[ $portal_kind ] ?? null;

		if ( $class === null ) {
			return new WP_Error(
				Error_Code::UNKNOWN_FEATURE_TYPE,
				sprintf(
					'No Feature subclass registered for portal kind "%s" (feature: %s).',
					$portal_kind,
					$portal_feature->get_slug()
				)
			);
		}

		$minimum_tier = $product->get_tier_by_slug( $portal_feature->get_minimum_tier() );
		$minimum_rank = $minimum_tier !== null ? $minimum_tier->get_rank() : PHP_INT_MAX;

		if ( $portal_feature->is_wporg() || $minimum_rank === 0 ) {
			// WordPress.org and free-tier features are unconditionally available — capabilities and tier are irrelevant.
			$is_available    = true;
			$in_portal_tier = true;
		} elseif ( $capabilities === null ) {
			// No license: paid-tier features are neither available nor in tier.
			$is_available    = false;
			$in_portal_tier = false;
		} else {
			$is_available    = in_array( $portal_feature->get_slug(), $capabilities, true );
			$in_portal_tier = ( $license_tier_rank >= $minimum_rank );
		}

		$data = [
			'slug'              => $portal_feature->get_slug(),
			'product'           => $product->get_product_slug(),
			'tier'              => $portal_feature->get_minimum_tier(),
			'name'              => $portal_feature->get_name(),
			'description'       => $portal_feature->get_description(),
			'type'              => $portal_kind,
			'is_available'      => $is_available,
			'in_portal_tier'   => $in_portal_tier,
			'documentation_url' => $portal_feature->get_documentation_url(),
			'release_date'      => $portal_feature->get_release_date(),
			'plugin_file'       => $portal_feature->get_plugin_file() ?? '',
			'wporg_slug'        => $portal_feature->get_wporg_slug(),
			'version'           => $portal_feature->get_version(),
			'changelog'         => $portal_feature->get_changelog(),
		];

		$feature = $class::from_array( $data );

		if ( $feature instanceof Installable ) {
			$data['installed_version'] = $feature->get_installed_version();
			$feature                   = $class::from_array( $data );
		}

		return $feature;
	}
}
