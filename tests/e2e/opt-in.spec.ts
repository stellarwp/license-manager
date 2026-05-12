import { test, expect } from '@wordpress/e2e-test-utils-playwright';

const PAGE_QUERY = 'page=lw-software-manager';

async function revokeConsent( requestUtils: { rest: ( opts: { method: string; path: string } ) => Promise<unknown> } ): Promise<void> {
	await requestUtils.rest( {
		method: 'DELETE',
		path:   '/liquidweb/harbor/v1/consent',
	} );
}

async function grantConsent( requestUtils: { rest: ( opts: { method: string; path: string } ) => Promise<unknown> } ): Promise<void> {
	await requestUtils.rest( {
		method: 'POST',
		path:   '/liquidweb/harbor/v1/consent',
	} );
}

/**
 * The harbor-fixture plugin grants consent at install time so the rest of the
 * suite lands on the Feature Manager. These specs revoke consent before each
 * test, then re-grant it on teardown so subsequent spec files start clean.
 */
test.describe( 'Opt-in screen', () => {
	test.beforeEach( async ( { requestUtils } ) => {
		await revokeConsent( requestUtils );
	} );

	test.afterAll( async ( { requestUtils } ) => {
		await grantConsent( requestUtils );
	} );

	test( 'renders the consent screen when consent has not been granted', async ( { page, admin } ) => {
		await admin.visitAdminPage( 'options-general.php', PAGE_QUERY );

		await expect( page.locator( '#lw-harbor-opt-in-root' ) ).toBeVisible();
		await expect(
			page.getByRole( 'heading', { name: 'Connect to Liquid Web' } )
		).toBeVisible( { timeout: 15_000 } );
		await expect( page.locator( '#lw-harbor-root' ) ).toHaveCount( 0 );
	} );

	test( 'lists the external endpoints', async ( { page, admin } ) => {
		await admin.visitAdminPage( 'options-general.php', PAGE_QUERY );

		await expect( page.getByText( 'Liquid Web Licensing service' ) ).toBeVisible( { timeout: 15_000 } );
		await expect( page.getByText( 'Liquid Web Customer Portal' ) ).toBeVisible();
		await expect( page.getByText( 'Herald' ) ).toBeVisible();
	} );

	test( 'clicking Opt In reloads into the Feature Manager', async ( { page, admin } ) => {
		await admin.visitAdminPage( 'options-general.php', PAGE_QUERY );

		await page.getByRole( 'button', { name: 'Opt In' } ).click();

		// Wait for the post-reload Feature Manager to finish mounting so the
		// page is fully hydrated before the test ends.
		await expect( page.locator( '#lw-harbor-root' ) ).toBeVisible( { timeout: 15_000 } );
		await expect( page.getByText( 'Your Features' ) ).toBeVisible( { timeout: 15_000 } );
		await expect( page.locator( '#lw-harbor-opt-in-root' ) ).toHaveCount( 0 );
	} );
} );

test.describe( 'Revoke consent', () => {
	test.afterAll( async ( { requestUtils } ) => {
		// Restore the granted-by-default state so subsequent spec files
		// (e.g. software-manager.spec.ts) land on the Feature Manager.
		await grantConsent( requestUtils );
	} );

	test( 'revoking from the sidebar reloads into the opt-in screen', async ( {
		page,
		admin,
	} ) => {
		await admin.visitAdminPage( 'options-general.php', PAGE_QUERY );

		// Wait for the Feature Manager to finish loading so the sidebar is interactive.
		await expect( page.getByText( 'Your Features' ) ).toBeVisible( { timeout: 15_000 } );

		await page
			.getByRole( 'button', { name: 'Revoke external data consent' } )
			.click();

		// Confirm in the dialog (the destructive confirm button).
		await page
			.getByRole( 'dialog' )
			.getByRole( 'button', { name: 'Revoke', exact: true } )
			.click();

		// Wait for the React opt-in app to finish mounting after the reload
		// rather than just the static div, so teardown can't race the load.
		await expect(
			page.getByRole( 'heading', { name: 'Connect to Liquid Web' } )
		).toBeVisible( { timeout: 15_000 } );
		await expect( page.locator( '#lw-harbor-root' ) ).toHaveCount( 0 );
	} );
} );
