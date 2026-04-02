<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Cron\Actions;

use LiquidWeb\Harbor\Portal\Portal_Repository;
use LiquidWeb\Harbor\Cron\ValueObjects\CronHook;
use LiquidWeb\Harbor\Features\Types\Feature;

use function get_stylesheet;
use function get_template;
use function is_plugin_active;
use function is_plugin_active_for_network;

/**
 * Unschedules the data refresh cron event when no portal plugins or themes remain active.
 *
 * Reads the stored portal from the DB (no API call) and cross-references its
 * plugin and theme features against the active plugins/theme. If none match, the
 * event is removed. The cron will be rescheduled on the next page load via init
 * if any Harbor instance is still active.
 *
 * Conservative defaults: when the portal is unreadable or contains no installable
 * features, the event is left in place since we cannot confirm Harbor is gone.
 *
 * @since 1.0.0
 */
class Handle_Unschedule_Cron_Data_Refresh {

	/**
	 * @since 1.0.0
	 *
	 * @var Portal_Repository
	 */
	private Portal_Repository $portal;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Portal_Repository $portal The portal repository.
	 */
	public function __construct( Portal_Repository $portal ) {
		$this->portal = $portal;
	}

	/**
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __invoke(): void {
		if ( $this->has_active_portal_feature() ) {
			return;
		}

		wp_clear_scheduled_hook( CronHook::DATA_REFRESH );
	}

	/**
	 * Check whether any plugin or theme listed in the stored portal is still active.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function has_active_portal_feature(): bool {
		$portal = $this->portal->get_cached();

		if ( $portal === null ) {
			return true;
		}

		$found_portal_feature = false;

		foreach ( $portal as $product_portal ) {
			foreach ( $product_portal->get_features() as $portal_feature ) {
				$type = $portal_feature->get_kind();

				if ( $type === Feature::TYPE_PLUGIN ) {
					$plugin_file = $portal_feature->get_plugin_file();

					if ( $plugin_file === null ) {
						continue;
					}

					$found_portal_feature = true;

					if ( is_plugin_active( $plugin_file ) || is_plugin_active_for_network( $plugin_file ) ) {
						return true;
					}
				} elseif ( $type === Feature::TYPE_THEME ) {
					$found_portal_feature = true;
					$slug                  = $portal_feature->get_slug();

					if ( get_stylesheet() === $slug || get_template() === $slug ) {
						return true;
					}
				}
			}
		}

		// If the portal has no installable features we cannot determine whether
		// Harbor is still needed, so leave the cron in place.
		if ( ! $found_portal_feature ) {
			return true;
		}

		return false;
	}
}
