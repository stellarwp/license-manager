<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Portal;

/**
 * WP_Error codes for the Portal system.
 *
 * @since 1.0.0
 */
final class Error_Code {

	/**
	 * The requested product slug was not found in the portal.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public const PRODUCT_NOT_FOUND = 'lw-harbor-portal-product-not-found';

	/**
	 * The portal response could not be decoded.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public const INVALID_RESPONSE = 'lw-harbor-portal-invalid-response';
}
