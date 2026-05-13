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

// Mock a premium plugin so Premium_Plugin_Registry::any() returns true during
// the WP boot phase and Harbor::init() actually registers its providers. Without
// this the premium-plugin gate inside Harbor::init() short-circuits and tests
// that depend on provider bindings (License_Repository, Portal_Client, etc.)
// or on hooks added by provider register_hooks() observe an empty container.
add_filter(
	'lw_harbor/premium_plugin_existence_callbacks',
	static function ( array $callbacks ): array {
		$callbacks[] = static fn(): bool => true;
		return $callbacks;
	}
);

add_action(
	'plugins_loaded',
	static function () {
		$container = new Container();
		$container->singleton( ContainerInterface::class, $container );
		Config::set_plugin_basename( plugin_basename( __FILE__ ) );
		Config::set_container( $container );
		Harbor::init();
	},
	0
);
