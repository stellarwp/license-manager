<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Contracts;

/**
 * Contract for an admin page that can be registered under the unified
 * Liquid Web Software Manager menu slot.
 *
 * The DI container binds one of the concrete implementations of this
 * interface based on the site owner's consent state, so the slot can be
 * filled by Opt_In_Page when consent is not granted, or by
 * Feature_Manager_Page once it is.
 *
 * @since TBD
 */
interface Admin_Page_Interface {

	/**
	 * Shared admin page slug under Settings -> Liquid Web Products.
	 *
	 * All implementations of this interface must register themselves at this
	 * slug so the URL stays consistent when the bound implementation flips.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const PAGE_SLUG = 'lw-software-manager';

	/**
	 * Registers the admin page if this Harbor instance is the version leader.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function maybe_register_page(): void;
}
