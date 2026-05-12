<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Contracts;

use LiquidWeb\Harbor\Utils\Version;

/**
 * Shared implementation for admin pages that occupy the unified Liquid Web
 * Software Manager menu slot.
 *
 * Handles the version-leader gate, submenu registration, the
 * `lw-harbor/hide_menu_item` filter, and the hook-suffix check for asset
 * enqueueing. Concrete subclasses provide the page-specific render and
 * asset enqueue behavior, and may opt in to additional hooks via
 * `register_additional_hooks()`.
 *
 * @since 1.1.0
 */
abstract class Abstract_Admin_Page implements Admin_Page_Interface {

	/**
	 * Hook suffix returned by add_submenu_page().
	 * Empty string until the page is registered.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	protected string $page_hook = '';

	/**
	 * @inheritDoc
	 */
	public function maybe_register_page(): void {
		if ( ! Version::should_handle( 'admin_page' ) ) {
			return;
		}

		$this->page_hook = (string) add_submenu_page(
			'options-general.php',
			__( 'Liquid Web Software Manager', '%TEXTDOMAIN%' ),
			__( 'Liquid Web Products', '%TEXTDOMAIN%' ),
			'manage_options',
			self::PAGE_SLUG,
			[ $this, 'render' ]
		);

		/**
		 * Filters whether to hide the Liquid Web Products item from the Settings menu.
		 *
		 * Hiding the menu item does not unregister the page. The Software Manager
		 * UI remains accessible at options-general.php?page=lw-software-manager
		 * for users who reach it via a direct link or a product plugin's submenu.
		 *
		 * @since 1.1.0
		 *
		 * @param bool $hide Whether to hide the menu item. Default false.
		 *
		 * @return bool
		 */
		if ( apply_filters( 'lw-harbor/hide_menu_item', false ) ) {
			remove_submenu_page( 'options-general.php', self::PAGE_SLUG );
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'maybe_enqueue_assets' ] );

		$this->register_additional_hooks();
	}

	/**
	 * Enqueues this page's assets only when the current admin screen matches
	 * the hook suffix returned by add_submenu_page().
	 *
	 * @since 1.1.0
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 *
	 * @return void
	 */
	public function maybe_enqueue_assets( string $hook_suffix ): void {
		if ( $hook_suffix !== $this->page_hook ) {
			return;
		}

		$this->enqueue_assets();
	}

	/**
	 * Hook for subclasses to register additional WordPress hooks that should
	 * only fire when this concrete page is the bound implementation.
	 *
	 * Default: no-op.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	protected function register_additional_hooks(): void {
		// No-op by default.
	}

	/**
	 * Registers and enqueues the page's JS and CSS bundle.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	abstract protected function enqueue_assets(): void;

	/**
	 * Renders the page's HTML output.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	abstract public function render(): void;
}
