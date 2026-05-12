<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Admin;

use LiquidWeb\Harbor\Config;
use LiquidWeb\Harbor\Contracts\Abstract_Admin_Page;
use LiquidWeb\Harbor\Harbor;
use LiquidWeb\Harbor\Site\Data;

/**
 * Server-side gate for the Liquid Web Software Manager page when the site
 * owner has not yet consented to external API communications.
 *
 * Loads the dedicated `opt-in` bundle (a smaller React entry that mounts
 * only the consent screen) instead of the full Feature Manager bundle so
 * the @wordpress/data resolvers — which fetch from the licensing and
 * catalog services — never run pre-consent.
 *
 * @since TBD
 *
 * @package LiquidWeb\Harbor
 */
class Opt_In_Page extends Abstract_Admin_Page {

	/**
	 * Site data provider.
	 *
	 * @since TBD
	 *
	 * @var Data
	 */
	private Data $site_data;

	/**
	 * Constructor.
	 *
	 * @since TBD
	 *
	 * @param Data $site_data Site data provider.
	 */
	public function __construct( Data $site_data ) {
		$this->site_data = $site_data;
	}

	/**
	 * Registers and enqueues the opt-in React entry's JS and CSS.
	 *
	 * Loads from build-dev/ when WP_DEBUG is true, build/ otherwise.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function enqueue_assets(): void {
		$build_dir       = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'build-dev' : 'build';
		$plugin_root_dir = dirname( dirname( dirname( __DIR__ ) ) );
		$plugin_root_url = trailingslashit(
			plugin_dir_url( $plugin_root_dir . '/index.php' )
		);
		$handle          = 'lw-harbor-opt-in';

		$asset_file = $plugin_root_dir . '/' . $build_dir . '/opt-in.asset.php';

		/** @var array{dependencies: array<string>, version: string} $asset_data */
		$asset_data = file_exists( $asset_file ) ? require $asset_file : [
			'dependencies' => [],
			'version'      => null,
		];

		wp_register_script(
			$handle,
			$plugin_root_url . $build_dir . '/opt-in.js',
			$asset_data['dependencies'],
			$asset_data['version'],
			[ 'in_footer' => true ]
		);

		wp_localize_script(
			$handle,
			'harborData',
			[
				'restUrl'          => rest_url( 'liquidweb/harbor/v1/' ),
				'nonce'            => wp_create_nonce( 'wp_rest' ),
				'pluginsUrl'       => admin_url( 'plugins.php' ),
				'activationUrl'    => '',
				'subscriptionsUrl' => Config::get_portal_base_url() . '/subscriptions/',
				'domain'           => $this->site_data->get_domain(),
				'version'          => Harbor::VERSION,
				'licensingBaseUrl' => Config::get_licensing_base_url(),
				'portalBaseUrl'    => Config::get_portal_base_url(),
				'heraldBaseUrl'    => Config::get_herald_base_url(),
			]
		);

		wp_register_style(
			$handle,
			$plugin_root_url . $build_dir . '/opt-in.css',
			[],
			null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- version is content-hashed into the asset filename by the build pipeline.
		);

		wp_set_script_translations( $handle, '%TEXTDOMAIN%' );
		wp_enqueue_script( $handle );
		wp_enqueue_style( $handle );
	}

	/**
	 * Renders the consent screen mount point.
	 *
	 * The React bundle (opt-in.js + opt-in.css) is registered and enqueued
	 * by enqueue_assets() via maybe_enqueue_assets() on admin_enqueue_scripts.
	 *
	 * The .lw-harbor-ui class activates CSS scoping for Tailwind styles.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function render(): void {
		?>
		<div class="wrap">
			<div id="lw-harbor-opt-in-root" class="lw-harbor-ui"></div>
		</div>
		<?php
	}
}
