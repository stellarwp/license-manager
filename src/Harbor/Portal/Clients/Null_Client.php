<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Portal\Clients;

use LiquidWeb\Harbor\Portal\Error_Code;
use WP_Error;

/**
 * Null client implementation of the catalog API client.
 *
 * @since TBD
 */
final class Null_Client implements Portal_Client {
	/**
	 * Fetches the full catalog for all products.
	 *
	 * @since TBD
	 *
	 * @return WP_Error it will always return an error because external API communications have not been permitted.
	 */
	public function get_catalog() {
		return new WP_Error(
			Error_Code::API_COMMUNICATIONS_NOT_PERMITTED,
			'External API communications have not been permitted.'
		);
	}
}
