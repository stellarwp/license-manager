<?php declare( strict_types=1 );

namespace LiquidWeb\Harbor\Tests\Portal;

use LiquidWeb\Harbor\Config;
use LiquidWeb\Harbor\Legacy\License_Repository as Legacy_License_Repository;
use LiquidWeb\Harbor\Licensing\Repositories\License_Repository;
use LiquidWeb\Harbor\Portal\Herald_Url_Builder;
use LiquidWeb\Harbor\Site\Data;
use LiquidWeb\Harbor\Tests\HarborTestCase;

final class Herald_Url_BuilderTest extends HarborTestCase {

	private const TEST_HERALD_BASE = 'https://herald.test.example.com';
	private const TEST_LICENSE_KEY = 'LWSW-TEST-KEY-9999';
	private const TEST_DOMAIN      = 'site.example.com';

	protected function setUp(): void {
		parent::setUp();
		Config::set_herald_base_url( self::TEST_HERALD_BASE );
	}

	protected function tearDown(): void {
		delete_option( License_Repository::KEY_OPTION_NAME );
		remove_all_filters( 'lw-harbor/legacy_licenses' );
		Config::reset();
		parent::tearDown();
	}

	private function make_builder( ?string $license_key, string $domain ): Herald_Url_Builder {
		if ( $license_key !== null ) {
			update_option( License_Repository::KEY_OPTION_NAME, $license_key );
		} else {
			delete_option( License_Repository::KEY_OPTION_NAME );
		}

		$site_data = $this->makeEmpty(
			Data::class,
			[ 'get_domain' => $domain ]
		);

		return new Herald_Url_Builder( new License_Repository(), new Legacy_License_Repository(), $site_data );
	}

	/**
	 * Registers a single legacy license entry via the filter.
	 *
	 * @param array<string, mixed> $overrides Field overrides on the default entry.
	 */
	private function register_legacy_license( array $overrides = [] ): void {
		$defaults = [
			'key'        => 'legacy-key-1234',
			'slug'       => 'kad-blocks-pro',
			'name'       => 'Kadence Blocks Pro',
			'product'    => 'kadence',
			'is_active'  => true,
			'page_url'   => 'https://example.com/manage',
			'expires_at' => '',
		];

		$entry = array_merge( $defaults, $overrides );

		add_filter(
			'lw-harbor/legacy_licenses',
			static function ( array $licenses ) use ( $entry ) {
				$licenses[] = $entry;

				return $licenses;
			}
		);
	}

	public function test_build_returns_correct_url(): void {
		$builder = $this->make_builder( self::TEST_LICENSE_KEY, self::TEST_DOMAIN );
		$url     = $builder->build( 'kad-blocks-pro' );

		$this->assertSame(
			self::TEST_HERALD_BASE . '/download/kad-blocks-pro/latest/' . self::TEST_LICENSE_KEY . '/zip?site=' . self::TEST_DOMAIN,
			$url
		);
	}

	public function test_build_url_encodes_slug(): void {
		$builder = $this->make_builder( self::TEST_LICENSE_KEY, self::TEST_DOMAIN );
		$url     = $builder->build( 'slug with spaces' );

		$this->assertStringContainsString( '/download/slug%20with%20spaces/', $url );
	}

	public function test_build_url_encodes_license_key(): void {
		$builder = $this->make_builder( 'KEY WITH SPACES', self::TEST_DOMAIN );
		$url     = $builder->build( 'some-plugin' );

		$this->assertStringContainsString( '/latest/KEY%20WITH%20SPACES/', $url );
	}

	public function test_build_url_encodes_domain(): void {
		$builder = $this->make_builder( self::TEST_LICENSE_KEY, 'my site.example.com' );
		$url     = $builder->build( 'some-plugin' );

		$this->assertStringContainsString( '?site=my%20site.example.com', $url );
	}

	public function test_build_returns_empty_when_no_license_key(): void {
		$builder = $this->make_builder( null, self::TEST_DOMAIN );

		$this->assertSame( '', $builder->build( 'kad-blocks-pro' ) );
	}

	public function test_build_returns_empty_when_domain_is_empty(): void {
		$builder = $this->make_builder( self::TEST_LICENSE_KEY, '' );

		$this->assertSame( '', $builder->build( 'kad-blocks-pro' ) );
	}

	public function test_build_uses_configured_herald_base_url(): void {
		Config::set_herald_base_url( 'https://custom-herald.example.com' );

		$builder = $this->make_builder( self::TEST_LICENSE_KEY, self::TEST_DOMAIN );
		$url     = $builder->build( 'some-plugin' );

		$this->assertStringStartsWith( 'https://custom-herald.example.com/', $url );
	}

	public function test_build_strips_trailing_slash_from_base_url(): void {
		Config::set_herald_base_url( 'https://herald.example.com/' );

		$builder = $this->make_builder( self::TEST_LICENSE_KEY, self::TEST_DOMAIN );
		$url     = $builder->build( 'some-plugin' );

		$this->assertStringStartsWith( 'https://herald.example.com/download/', $url );
		$this->assertStringNotContainsString( '//download/', $url );
	}

	public function test_build_returns_legacy_url_for_matching_active_legacy_license(): void {
		$this->register_legacy_license(
			[
				'key'  => 'legacy-key-abc',
				'slug' => 'kad-blocks-pro',
			]
		);

		$builder = $this->make_builder( null, self::TEST_DOMAIN );
		$url     = $builder->build( 'kad-blocks-pro' );

		$this->assertSame(
			self::TEST_HERALD_BASE . '/legacy/download?plugin=kad-blocks-pro&key=legacy-key-abc&site=' . self::TEST_DOMAIN,
			$url
		);
	}

	public function test_build_prefers_legacy_url_when_both_legacy_and_unified_present(): void {
		$this->register_legacy_license(
			[
				'key'  => 'legacy-key-abc',
				'slug' => 'kad-blocks-pro',
			]
		);

		$builder = $this->make_builder( self::TEST_LICENSE_KEY, self::TEST_DOMAIN );
		$url     = $builder->build( 'kad-blocks-pro' );

		$this->assertStringContainsString( '/legacy/download?', $url );
		$this->assertStringContainsString( 'key=legacy-key-abc', $url );
		$this->assertStringNotContainsString( self::TEST_LICENSE_KEY, $url );
	}

	public function test_build_falls_back_to_unified_url_when_legacy_is_inactive(): void {
		$this->register_legacy_license(
			[
				'key'       => 'legacy-key-abc',
				'slug'      => 'kad-blocks-pro',
				'is_active' => false,
			]
		);

		$builder = $this->make_builder( self::TEST_LICENSE_KEY, self::TEST_DOMAIN );
		$url     = $builder->build( 'kad-blocks-pro' );

		$this->assertStringContainsString( '/download/kad-blocks-pro/latest/' . self::TEST_LICENSE_KEY . '/zip', $url );
		$this->assertStringNotContainsString( '/legacy/download', $url );
	}

	public function test_build_falls_back_to_unified_url_when_legacy_key_is_empty(): void {
		$this->register_legacy_license(
			[
				'key'  => '',
				'slug' => 'kad-blocks-pro',
			]
		);

		$builder = $this->make_builder( self::TEST_LICENSE_KEY, self::TEST_DOMAIN );
		$url     = $builder->build( 'kad-blocks-pro' );

		$this->assertStringContainsString( '/download/kad-blocks-pro/latest/' . self::TEST_LICENSE_KEY . '/zip', $url );
		$this->assertStringNotContainsString( '/legacy/download', $url );
	}

	public function test_build_legacy_url_returns_unified_when_slug_does_not_match(): void {
		$this->register_legacy_license(
			[
				'key'  => 'legacy-key-abc',
				'slug' => 'some-other-plugin',
			]
		);

		$builder = $this->make_builder( self::TEST_LICENSE_KEY, self::TEST_DOMAIN );
		$url     = $builder->build( 'kad-blocks-pro' );

		$this->assertStringContainsString( '/download/kad-blocks-pro/latest/' . self::TEST_LICENSE_KEY . '/zip', $url );
		$this->assertStringNotContainsString( '/legacy/download', $url );
	}

	public function test_build_legacy_url_rawurlencodes_all_params(): void {
		$this->register_legacy_license(
			[
				'key'  => 'KEY WITH SPACES',
				'slug' => 'slug with spaces',
			]
		);

		$builder = $this->make_builder( null, 'my site.example.com' );
		$url     = $builder->build( 'slug with spaces' );

		$this->assertStringContainsString( 'plugin=slug%20with%20spaces', $url );
		$this->assertStringContainsString( 'key=KEY%20WITH%20SPACES', $url );
		$this->assertStringContainsString( 'site=my%20site.example.com', $url );
	}

	public function test_build_returns_empty_when_domain_is_empty_even_with_legacy_license(): void {
		$this->register_legacy_license(
			[
				'key'  => 'legacy-key-abc',
				'slug' => 'kad-blocks-pro',
			]
		);

		$builder = $this->make_builder( self::TEST_LICENSE_KEY, '' );

		$this->assertSame( '', $builder->build( 'kad-blocks-pro' ) );
	}
}
