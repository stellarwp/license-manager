import path from 'path';
import { defineConfig, devices } from '@playwright/test';

// WP_ENV_PORT mirrors what wp-env uses (set via `WP_ENV_PORT=XXXX wp-env start`).
// WP_BASE_URL is the full override escape hatch.
const WP_ENV_PORT = process.env.WP_ENV_PORT ?? '8901';
const WP_BASE_URL = process.env.WP_BASE_URL ?? `http://localhost:${ WP_ENV_PORT }`;

// @wordpress/e2e-test-utils-playwright captures WP_BASE_URL into a module
// constant at import time. Mirror our resolved URL into the env var here,
// before global-setup.ts (which imports the package) is loaded by Playwright.
process.env.WP_BASE_URL = WP_BASE_URL;

const STORAGE_STATE_PATH =
	process.env.STORAGE_STATE_PATH ||
	path.join( process.cwd(), 'artifacts/storage-states/admin.json' );

export default defineConfig( {
	testDir: './tests/e2e',
	timeout: 60_000,
	retries: process.env.CI ? 2 : 0,
	workers: 1,
	reporter: process.env.CI ? 'github' : 'list',
	globalSetup: './tests/e2e/global-setup.ts',
	use: {
		baseURL: WP_BASE_URL,
		storageState: STORAGE_STATE_PATH,
		video: 'retain-on-failure',
		screenshot: 'only-on-failure',
		trace: 'retain-on-failure',
	},
	projects: [
		{
			name: 'chromium',
			use: { ...devices[ 'Desktop Chrome' ] },
		},
		{
			name: 'headed',
			use: { ...devices[ 'Desktop Chrome' ], headless: false, launchOptions: { slowMo: 800 } },
		},
	],
} );
