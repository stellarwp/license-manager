<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor;

use LiquidWeb\Harbor\Utils\Cast;
use RuntimeException;
use StellarWP\ContainerContract\ContainerInterface;

class Config {

	/**
	 * The default base URL for the StellarWP licensing service.
	 *
	 * @since 1.0.0
	 */
	public const DEFAULT_LICENSING_BASE_URL = 'https://licensing.stellarwp.com';

	/**
	 * The default base URL for the Commerce Portal (catalog API).
	 *
	 * @since 1.0.0
	 */
	public const DEFAULT_PORTAL_BASE_URL = 'https://my.software.stellarwp.com';

	/**
	 * The default base URL for the Herald download service.
	 *
	 * @since 1.0.0
	 */
	public const DEFAULT_HERALD_BASE_URL = 'https://herald.stellarwp.com';

	/**
	 * Container object.
	 *
	 * @since 1.0.0
	 *
	 * @var ContainerInterface
	 */
	protected static $container;

	/**
	 * The base URL for the StellarWP licensing service.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected static $licensing_base_url = self::DEFAULT_LICENSING_BASE_URL;

	/**
	 * The base URL for the Commerce Portal (catalog API).
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected static $portal_base_url = self::DEFAULT_PORTAL_BASE_URL;

	/**
	 * The base URL for the Herald download service.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected static $herald_base_url = self::DEFAULT_HERALD_BASE_URL;

	/**
	 * Cached result of detect_plugin_file(). False means not yet computed.
	 *
	 * @since 1.0.0
	 *
	 * @var string|null|false
	 */
	protected static $detected_plugin_file = false;

	/**
	 * Get the container.
	 *
	 * @since 1.0.0
	 *
	 * @throws RuntimeException If the container has not been set.
	 *
	 * @return ContainerInterface
	 */
	public static function get_container() {
		if ( self::$container === null ) {
			throw new RuntimeException(
				__( 'You must provide a container via LiquidWeb\Harbor\Config::set_container() before attempting to fetch it.', '%TEXTDOMAIN%' )
			);
		}

		return self::$container;
	}

	/**
	 * Returns whether the container has been set.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function has_container(): bool {
		return self::$container !== null;
	}

	/**
	 * Resets this class back to the defaults.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function reset(): void {
		static::$licensing_base_url    = self::DEFAULT_LICENSING_BASE_URL;
		static::$portal_base_url       = self::DEFAULT_PORTAL_BASE_URL;
		static::$herald_base_url       = self::DEFAULT_HERALD_BASE_URL;
		static::$detected_plugin_file  = false;
	}

	/**
	 * Returns the plugin file path (relative to WP_PLUGIN_DIR) of the plugin
	 * hosting this Harbor instance, or null if it cannot be determined.
	 *
	 * Auto-detected by locating the container class file within the active
	 * plugin tree — no manual configuration required. Result is cached after
	 * the first call.
	 *
	 * @since 1.0.0
	 *
	 * @return string|null
	 */
	public static function get_plugin_file(): ?string {
		if ( static::$detected_plugin_file === false ) {
			static::$detected_plugin_file = static::detect_plugin_file();
		}

		return static::$detected_plugin_file;
	}

	/**
	 * Detects the host plugin file by reflecting on the container class.
	 *
	 * The container is always vendor-bundled inside the host plugin, so its
	 * file path starts with that plugin's directory. We walk active_plugins
	 * to find the match.
	 *
	 * @since 1.0.0
	 *
	 * @return string|null
	 */
	protected static function detect_plugin_file(): ?string {
		if ( ! static::has_container() ) {
			return null;
		}

		try {
			$reflection = new \ReflectionClass( get_class( static::get_container() ) );
			$class_file = $reflection->getFileName();
		} catch ( \Exception $e ) {
			return null;
		}

		if ( ! is_string( $class_file ) ) {
			return null;
		}

		foreach ( (array) get_option( 'active_plugins', [] ) as $plugin_file ) {
			if ( ! is_string( $plugin_file ) ) {
				continue;
			}

			$plugin_dir = trailingslashit( WP_PLUGIN_DIR ) . trailingslashit( dirname( $plugin_file ) );

			if ( strpos( $class_file, $plugin_dir ) === 0 ) {
				return $plugin_file;
			}
		}

		return null;
	}

	/**
	 * Set the container object.
	 *
	 * @since 1.0.0
	 *
	 * @param ContainerInterface $container Container object.
	 *
	 * @return void
	 */
	public static function set_container( ContainerInterface $container ): void {
		self::$container = $container;
	}

	/**
	 * Set the base URL for the StellarWP licensing service.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url The API base URL (no trailing slash).
	 *
	 * @return void
	 */
	public static function set_licensing_base_url( string $url ): void {
		static::$licensing_base_url = rtrim( $url, '/' );
	}

	/**
	 * Get the base URL for the StellarWP licensing service.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_licensing_base_url(): string {
		if ( defined( 'LW_HARBOR_LICENSING_BASE_URL' ) ) {
			$url = Cast::to_string( LW_HARBOR_LICENSING_BASE_URL );

			return rtrim( $url, '/' );
		}

		return static::$licensing_base_url;
	}

	/**
	 * Set the base URL for the Commerce Portal (catalog API).
	 *
	 * @since 1.0.0
	 *
	 * @param string $url The portal base URL (no trailing slash).
	 *
	 * @return void
	 */
	public static function set_portal_base_url( string $url ): void {
		static::$portal_base_url = rtrim( $url, '/' );
	}

	/**
	 * Get the base URL for the Commerce Portal (catalog API).
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_portal_base_url(): string {
		if ( defined( 'LW_HARBOR_PORTAL_BASE_URL' ) ) {
			$url = Cast::to_string( LW_HARBOR_PORTAL_BASE_URL );

			return rtrim( $url, '/' );
		}

		return static::$portal_base_url;
	}

	/**
	 * Set the base URL for the Herald download service.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url The Herald base URL (no trailing slash).
	 *
	 * @return void
	 */
	public static function set_herald_base_url( string $url ): void {
		static::$herald_base_url = rtrim( $url, '/' );
	}

	/**
	 * Get the base URL for the Herald download service.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_herald_base_url(): string {
		if ( defined( 'LW_HARBOR_HERALD_BASE_URL' ) ) {
			$url = Cast::to_string( LW_HARBOR_HERALD_BASE_URL );

			return rtrim( $url, '/' );
		}

		return static::$herald_base_url;
	}
}
