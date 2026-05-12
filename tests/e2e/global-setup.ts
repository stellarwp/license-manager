import path from 'path';
import { RequestUtils } from '@wordpress/e2e-test-utils-playwright';

const STORAGE_STATE_PATH =
	process.env.STORAGE_STATE_PATH ||
	path.join( process.cwd(), 'artifacts/storage-states/admin.json' );

// playwright.config.ts seeds process.env.WP_BASE_URL before this module is
// loaded, so reading from the env directly here is sufficient.
const WP_BASE_URL = process.env.WP_BASE_URL ?? 'http://localhost:8901';

async function globalSetup(): Promise<void> {
	const requestUtils = await RequestUtils.setup( {
		baseURL: WP_BASE_URL,
		storageStatePath: STORAGE_STATE_PATH,
	} );

	await requestUtils.setupRest();
}

export default globalSetup;
