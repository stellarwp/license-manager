<?php
/**
 * Plugin Name: Harbor Test Bootstrap
 * Description: Bootstraps LiquidWeb Harbor during plugins_loaded, before wp_loaded fires.
 * Version: 1.0.0
 * Author: Liquid Web
 */

use StellarWP\ContainerContract\ContainerInterface;
use LiquidWeb\Harbor\Config;
use LiquidWeb\Harbor\Tests\Container;
use LiquidWeb\Harbor\Harbor;

add_action(
	'plugins_loaded',
	static function () {
		// wp-browser in multisite mode injects plugins without updating active_plugins.
		// Harbor's detect_plugin_file() reads that option to identify the host plugin file,
		// so we filter it to ensure this plugin is always present before Harbor::init() runs.
		// e.g. 'harbor/bootstrap-plugin.php'
		$plugin_file = plugin_basename( __FILE__ );
		add_filter(
			'option_active_plugins',
			static function ( $plugins ) use ( $plugin_file ) {
				if ( ! in_array( $plugin_file, (array) $plugins, true ) ) {
					$plugins[] = $plugin_file;
				}
				return $plugins;
			}
		);

		$container = new Container();
		$container->singleton( ContainerInterface::class, $container );
		Config::set_container( $container );
		Harbor::init();
	},
	0
);
