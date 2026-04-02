<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Tests\Portal;

use LiquidWeb\Harbor\Portal\Portal_Repository;
use LiquidWeb\Harbor\Portal\Clients\Portal_Client;
use LiquidWeb\Harbor\Portal\Clients\Http_Client;
use LiquidWeb\Harbor\Tests\HarborTestCase;

final class ProviderTest extends HarborTestCase {

	public function test_it_registers_portal_client(): void {
		$this->assertInstanceOf(
			Http_Client::class,
			$this->container->get( Portal_Client::class )
		);
	}

	public function test_it_registers_portal_repository(): void {
		$this->assertInstanceOf(
			Portal_Repository::class,
			$this->container->get( Portal_Repository::class )
		);
	}

	public function test_client_is_singleton(): void {
		$first  = $this->container->get( Portal_Client::class );
		$second = $this->container->get( Portal_Client::class );

		$this->assertSame( $first, $second );
	}

	public function test_repository_is_singleton(): void {
		$first  = $this->container->get( Portal_Repository::class );
		$second = $this->container->get( Portal_Repository::class );

		$this->assertSame( $first, $second );
	}
}
