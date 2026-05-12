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
	 * @inheritDoc
	 */
	public function get_catalog() {
		return new WP_Error(
			Error_Code::API_COMMUNICATIONS_NOT_PERMITTED,
			'External API communications have not been permitted.'
		);
	}
}
