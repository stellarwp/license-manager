<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Portal\Clients;

use LiquidWeb\Harbor\Portal\Portal_Collection;
use WP_Error;

/**
 * Contract for the product portal API client.
 *
 * @since 1.0.0
 */
interface Portal_Client {

	/**
	 * Fetch the full portal for all products.
	 *
	 * @since 1.0.0
	 *
	 * @return Portal_Collection|WP_Error
	 */
	public function get_portal();
}
