<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Tests\Portal\Clients;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use LiquidWeb\Harbor\Portal\Portal_Collection;
use LiquidWeb\Harbor\Portal\Clients\Http_Client;
use LiquidWeb\Harbor\Portal\Error_Code;
use LiquidWeb\Harbor\Tests\Http\Mock_Client;
use LiquidWeb\Harbor\Tests\HarborTestCase;
use WP_Error;

final class Http_ClientTest extends HarborTestCase {

	private Mock_Client $mock;
	private Psr17Factory $factory;
	private Http_Client $client;

	protected function setUp(): void {
		parent::setUp();

		$this->mock    = new Mock_Client();
		$this->factory = new Psr17Factory();
		$this->client  = new Http_Client( $this->mock, $this->factory, 'https://api.example.com' );
	}

	public function test_get_portal_returns_collection_on_success(): void {
		$body = $this->build_portal_json();

		$this->mock->add_response( new Response( 200, [], $body ) );

		$result = $this->client->get_portal();

		$this->assertInstanceOf( Portal_Collection::class, $result );
		$this->assertCount( 2, $result );
		$this->assertNotNull( $result->get( 'kadence' ) );
		$this->assertNotNull( $result->get( 'give' ) );
	}

	public function test_get_portal_sends_correct_request(): void {
		$this->mock->add_response( new Response( 200, [], $this->build_portal_json() ) );

		$this->client->get_portal();

		$request = $this->mock->get_last_request();

		$this->assertSame( 'GET', $request->getMethod() );
		$this->assertSame(
			'https://api.example.com/wp-json/slw/v1/portal',
			(string) $request->getUri()
		);
	}

	public function test_get_portal_returns_error_on_http_500(): void {
		$this->mock->add_response( new Response( 500, [], 'Internal Server Error' ) );

		$result = $this->client->get_portal();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( Error_Code::INVALID_RESPONSE, $result->get_error_code() );
		$this->assertStringContainsString( '500', $result->get_error_message() );
	}

	public function test_get_portal_returns_error_on_http_404(): void {
		$this->mock->add_response( new Response( 404, [], 'Not Found' ) );

		$result = $this->client->get_portal();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( Error_Code::INVALID_RESPONSE, $result->get_error_code() );
	}

	public function test_get_portal_returns_error_on_invalid_json(): void {
		$this->mock->add_response( new Response( 200, [], 'not json' ) );

		$result = $this->client->get_portal();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( Error_Code::INVALID_RESPONSE, $result->get_error_code() );
	}

	public function test_get_portal_returns_error_on_empty_array(): void {
		$this->mock->add_response( new Response( 200, [], '[]' ) );

		$result = $this->client->get_portal();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( Error_Code::INVALID_RESPONSE, $result->get_error_code() );
		$this->assertStringContainsString( 'empty', $result->get_error_message() );
	}

	public function test_get_portal_returns_error_when_entry_missing_product_slug(): void {
		$json = wp_json_encode(
			[
				[
					'tiers'    => [],
					'features' => [],
				],
			]
		);

		$this->mock->add_response( new Response( 200, [], $json ) );

		$result = $this->client->get_portal();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( Error_Code::INVALID_RESPONSE, $result->get_error_code() );
		$this->assertStringContainsString( 'product_slug', $result->get_error_message() );
	}

	public function test_get_portal_parses_tiers_and_features(): void {
		$this->mock->add_response( new Response( 200, [], $this->build_portal_json() ) );

		$result  = $this->client->get_portal();
		$kadence = $result->get( 'kadence' );

		$this->assertNotNull( $kadence );
		$this->assertCount( 2, $kadence->get_tiers() );
		$this->assertCount( 1, $kadence->get_features() );
	}

	/**
	 * Build a minimal valid portal JSON string.
	 *
	 * @return string
	 */
	private function build_portal_json(): string {
		return (string) wp_json_encode(
			[
				[
					'product_id'   => 'kadence-001',
					'product_slug' => 'kadence',
					'product_name' => 'Kadence',
					'tiers'        => [
						[
							'slug'         => 'kadence-basic',
							'name'         => 'Basic',
							'rank'         => 1,
							'price'        => 0,
							'currency'     => 'USD',
							'features'     => [],
							'herald_slugs' => [],
						],
						[
							'slug'         => 'kadence-pro',
							'name'         => 'Pro',
							'rank'         => 2,
							'price'        => 14900,
							'currency'     => 'USD',
							'features'     => [],
							'herald_slugs' => [],
						],
					],
					'features'     => [
						[
							'slug'         => 'kad-blocks-pro',
							'kind'         => 'plugin',
							'minimum_tier' => 'kadence-basic',
							'main_file'    => 'kadence-blocks-pro/kadence-blocks-pro.php',
							'wporg_slug'   => null,
							'name'         => 'Blocks Pro',
							'description'  => 'Premium blocks.',
						],
					],
				],
				[
					'product_id'   => 'give-001',
					'product_slug' => 'give',
					'product_name' => 'GiveWP',
					'tiers'        => [
						[
							'slug'         => 'give-basic',
							'name'         => 'Basic',
							'rank'         => 1,
							'price'        => 0,
							'currency'     => 'USD',
							'features'     => [],
							'herald_slugs' => [],
						],
					],
					'features'     => [],
				],
			]
		);
	}
}
