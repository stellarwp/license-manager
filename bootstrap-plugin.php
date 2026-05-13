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

// Mock a premium plugin so Config::is_there_at_least_one_premium_plugin() returns
// true during the WP boot init phase and Harbor::register_providers() actually runs.
// Without this, the deferred provider registration short-circuits and tests that
// inspect $wp_filter for hooks added by register_hooks() (e.g. plugins_api filter
// from Features\Update\Provider) would observe an empty filter table.
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
