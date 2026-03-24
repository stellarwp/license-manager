<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Features;

use LiquidWeb\Harbor\Features\Types\Feature;
use LiquidWeb\Harbor\Features\Types\Plugin;
use LiquidWeb\Harbor\Features\Types\Theme;

/**
 * Wraps a resolved Feature with data derived from WordPress update transients.
 *
 * Constructed after feature resolution is complete so reading the transient
 * (which fires our site_transient filter) cannot trigger a circular dependency
 * back into the feature resolver.
 *
 * @since 1.0.0
 */
class Feature_Resource {

	/**
	 * The wrapped feature.
	 *
	 * @since 1.0.0
	 *
	 * @var Feature
	 */
	private Feature $feature;

	/**
	 * The version available via the WordPress update transient, or null.
	 *
	 * A non-null value means the transient's response array contains an entry
	 * for this feature, which implies a newer version is available and all
	 * handler gating (dot-org exclusion, license checks) has passed.
	 *
	 * @since 1.0.0
	 *
	 * @var string|null
	 */
	private ?string $update_version;

	/**
	 * @since 1.0.0
	 *
	 * @param Feature     $feature        The resolved feature.
	 * @param string|null $update_version Version from the update transient, or null.
	 */
	public function __construct( Feature $feature, ?string $update_version = null ) {
		$this->feature        = $feature;
		$this->update_version = $update_version;
	}

	/**
	 * Builds a Feature_Resource from a Feature by reading the appropriate transient.
	 *
	 * @since 1.0.0
	 *
	 * @param Feature $feature The resolved feature.
	 *
	 * @return self
	 */
	public static function from_feature( Feature $feature ): self {
		$update_version = null;

		if ( $feature instanceof Plugin ) {
			$update_version = self::resolve_plugin_update_version( $feature );
		} elseif ( $feature instanceof Theme ) {
			$update_version = self::resolve_theme_update_version( $feature );
		}

		return new self( $feature, $update_version );
	}

	/**
	 * Gets the wrapped feature.
	 *
	 * @since 1.0.0
	 *
	 * @return Feature
	 */
	public function get_feature(): Feature {
		return $this->feature;
	}

	/**
	 * Gets the update version from the transient, or null if no update is available.
	 *
	 * @since 1.0.0
	 *
	 * @return string|null
	 */
	public function get_update_version(): ?string {
		return $this->update_version;
	}

	/**
	 * Converts the resource to an array suitable for REST responses.
	 *
	 * Merges the feature's own array with the transient-derived update_version.
	 * Only installable features (plugin/theme) receive the update_version key.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		$data = $this->feature->to_array();

		if ( $this->feature instanceof Plugin || $this->feature instanceof Theme ) {
			$data['update_version'] = $this->update_version;
		}

		return $data;
	}

	/**
	 * Reads update_plugins transient for a plugin feature's update version.
	 *
	 * @since 1.0.0
	 *
	 * @param Plugin $plugin The plugin feature.
	 *
	 * @return string|null The new version, or null if no update entry exists.
	 */
	private static function resolve_plugin_update_version( Plugin $plugin ): ?string {
		$plugin_file = $plugin->get_plugin_file();

		if ( $plugin_file === '' ) {
			return null;
		}

		$transient = get_site_transient( 'update_plugins' );

		if ( ! is_object( $transient ) || empty( $transient->response[ $plugin_file ] ) ) {
			return null;
		}

		$entry = $transient->response[ $plugin_file ];

		// The transient entry can be an object or array depending on the source.
		if ( is_object( $entry ) ) {
			return isset( $entry->new_version ) && is_string( $entry->new_version ) && $entry->new_version !== ''
				? $entry->new_version
				: null;
		}

		if ( is_array( $entry ) ) {
			return isset( $entry['new_version'] ) && is_string( $entry['new_version'] ) && $entry['new_version'] !== ''
				? $entry['new_version']
				: null;
		}

		return null;
	}

	/**
	 * Reads update_themes transient for a theme feature's update version.
	 *
	 * @since 1.0.0
	 *
	 * @param Theme $theme The theme feature.
	 *
	 * @return string|null The new version, or null if no update entry exists.
	 */
	private static function resolve_theme_update_version( Theme $theme ): ?string {
		$slug = $theme->get_slug();

		if ( $slug === '' ) {
			return null;
		}

		$transient = get_site_transient( 'update_themes' );

		if ( ! is_object( $transient ) || empty( $transient->response[ $slug ] ) ) {
			return null;
		}

		$entry = $transient->response[ $slug ];

		// Theme transient entries are always arrays.
		if ( is_array( $entry ) ) {
			return isset( $entry['new_version'] ) && is_string( $entry['new_version'] ) && $entry['new_version'] !== ''
				? $entry['new_version']
				: null;
		}

		return null;
	}
}
