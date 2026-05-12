<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Portal;

/**
 * WP_Error codes for the Catalog system.
 *
 * @since 1.0.0
 */
final class Error_Code {

	/**
	 * The requested product slug was not found in the catalog.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public const PRODUCT_NOT_FOUND = 'lw-harbor-catalog-product-not-found';

	/**
	 * The catalog response could not be decoded.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public const INVALID_RESPONSE = 'lw-harbor-catalog-invalid-response';

	/**
	 * External API communications have not been permitted.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	public const API_COMMUNICATIONS_NOT_PERMITTED = 'lw-harbor-api-communications-not-permitted';
}
