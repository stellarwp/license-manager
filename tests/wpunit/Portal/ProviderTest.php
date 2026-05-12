<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Tests\Catalog;

use LiquidWeb\Harbor\Consent\Consent_Repository;
use LiquidWeb\Harbor\Portal\Catalog_Repository;
use LiquidWeb\Harbor\Portal\Clients\Portal_Client;
use LiquidWeb\Harbor\Portal\Clients\Http_Client;
use LiquidWeb\Harbor\Portal\Clients\Null_Client;
use LiquidWeb\Harbor\Tests\HarborTestCase;

final class ProviderTest extends HarborTestCase {

	public function test_it_registers_http_client_when_consent_granted(): void {
		$this->container->get( Consent_Repository::class )->grant_consent();

		$this->assertInstanceOf(
			Http_Client::class,
			$this->container->get( Portal_Client::class )
		);
	}

	public function test_it_registers_null_client_when_consent_revoked(): void {
		$this->container->get( Consent_Repository::class )->revoke_consent();

		$this->assertInstanceOf(
			Null_Client::class,
			$this->container->get( Portal_Client::class )
		);
	}

	public function test_it_registers_catalog_repository(): void {
		$this->assertInstanceOf(
			Catalog_Repository::class,
			$this->container->get( Catalog_Repository::class )
		);
	}

	public function test_client_is_singleton(): void {
		$first  = $this->container->get( Portal_Client::class );
		$second = $this->container->get( Portal_Client::class );

		$this->assertSame( $first, $second );
	}

	public function test_repository_is_singleton(): void {
		$first  = $this->container->get( Catalog_Repository::class );
		$second = $this->container->get( Catalog_Repository::class );

		$this->assertSame( $first, $second );
	}
}
