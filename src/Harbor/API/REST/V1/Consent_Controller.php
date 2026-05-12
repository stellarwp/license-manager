<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\API\REST\V1;

use LiquidWeb\Harbor\Admin\Provider as Admin_Provider;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * WP REST API controller for reading the product catalog.
 *
 * @since TBD
 */
final class Catalog_Controller extends WP_REST_Controller {

	/**
	 * The REST API namespace.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $namespace = 'liquidweb/harbor/v1';

	/**
	 * The REST API route base.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $rest_base = 'consent';

	/**
	 * The admin provider.
	 *
	 * @since TBD
	 *
	 * @var Admin_Provider
	 */
	private Admin_Provider $admin_provider;

	/**
	 * Constructor.
	 *
	 * @since TBD
	 *
	 * @param Admin_Provider $admin_provider The admin provider.
	 *
	 * @return void
	 */
	public function __construct( Admin_Provider $admin_provider ) {
		$this->admin_provider = $admin_provider;
	}

	/**
	 * Registers the routes.
	 *
	 * @since TBD
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
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
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
	 * Grants consent to the terms and conditions.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response
	 */
	public function grant_consent( WP_REST_Request $request ): WP_REST_Response {
		$this->admin_provider->grant_consent();
		return new WP_REST_Response( null, 200 );
	}

	/**
	 * Revokes consent to the terms and conditions.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response
	 */
	public function revoke_consent( WP_REST_Request $request ): WP_REST_Response {
		$this->admin_provider->revoke_consent();
		return new WP_REST_Response( null, 200 );
	}

	/**
	 * Permission callback: require manage_options capability.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Gets the schema for a single feature response.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>
	 */
	public function get_item_schema(): array {
		if ( $this->schema ) {
			/** @var array<string, mixed> */
			return $this->add_additional_fields_schema( $this->schema );
		}

		$this->schema = [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title'   => 'feature',
			'oneOf'   => [
				[
					'title'                => 'plugin',
					'type'                 => 'object',
					'additionalProperties' => true,
					'properties'           => array_merge(
						$base_properties,
						[ 'type' => array_merge( $base_properties['type'], [ 'enum' => [ Feature::TYPE_PLUGIN ] ] ) ],
						$plugin_properties,
						$installable_properties
					),
				],
				[
					'title'                => 'theme',
					'type'                 => 'object',
					'additionalProperties' => true,
					'properties'           => array_merge(
						$base_properties,
						[ 'type' => array_merge( $base_properties['type'], [ 'enum' => [ Feature::TYPE_THEME ] ] ) ],
						$installable_properties
					),
				],
				[
					'title'                => 'service',
					'type'                 => 'object',
					'additionalProperties' => true,
					'properties'           => array_merge(
						$base_properties,
						[ 'type' => array_merge( $base_properties['type'], [ 'enum' => [ Feature::TYPE_SERVICE ] ] ) ]
					),
				],
			],
		];

		/** @var array<string, mixed> */
		return $this->add_additional_fields_schema( $this->schema );
	}
}
