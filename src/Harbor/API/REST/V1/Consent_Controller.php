<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\API\REST\V1;

use LiquidWeb\Harbor\Consent\Consent_Repository;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * WP REST API controller for granting / revoking external API consent.
 *
 * @since 1.1.0
 */
final class Consent_Controller extends WP_REST_Controller {

	/**
	 * The REST API namespace.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	protected $namespace = 'liquidweb/harbor/v1';

	/**
	 * The REST API route base.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	protected $rest_base = 'consent';

	/**
	 * The consent repository.
	 *
	 * @since 1.1.0
	 *
	 * @var Consent_Repository
	 */
	private Consent_Repository $consent;

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 *
	 * @param Consent_Repository $consent The consent repository.
	 *
	 * @return void
	 */
	public function __construct( Consent_Repository $consent ) {
		$this->consent = $consent;
	}

	/**
	 * Registers the routes.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'grant_consent' ],
					'permission_callback' => [ $this, 'check_permissions' ],
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'revoke_consent' ],
					'permission_callback' => [ $this, 'check_permissions' ],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * Grants consent to make external API communications.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response
	 */
	public function grant_consent( WP_REST_Request $request ): WP_REST_Response {
		$this->consent->grant_consent();
		return new WP_REST_Response( [ 'opted_in' => true ], 200 );
	}

	/**
	 * Revokes consent to make external API communications.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response
	 */
	public function revoke_consent( WP_REST_Request $request ): WP_REST_Response {
		$this->consent->revoke_consent();
		return new WP_REST_Response( [ 'opted_in' => false ], 200 );
	}

	/**
	 * Permission callback: require manage_options capability.
	 *
	 * @since 1.1.0
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Gets the schema for the consent response.
	 *
	 * @since 1.1.0
	 *
	 * @return array<string, mixed>
	 */
	public function get_item_schema(): array {
		if ( $this->schema ) {
			/** @var array<string, mixed> */
			return $this->add_additional_fields_schema( $this->schema );
		}

		$this->schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'consent',
			'type'       => 'null',
			'properties' => [],
		];

		/** @var array<string, mixed> */
		return $this->add_additional_fields_schema( $this->schema );
	}
}
