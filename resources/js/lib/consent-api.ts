/**
 * Single source of truth for the consent REST calls.
 *
 * Contract
 *   POST   /liquidweb/harbor/v1/consent   -> { opted_in: true }
 *   DELETE /liquidweb/harbor/v1/consent   -> { opted_in: false }
 *
 * Both functions resolve on success. Any non-2xx response throws — let
 * callers translate to HarborError via HarborError.wrap().
 *
 * @package LiquidWeb\Harbor
 */
import apiFetch from '@wordpress/api-fetch';

const CONSENT_PATH = '/liquidweb/harbor/v1/consent';

interface ConsentResponse {
    opted_in: boolean;
}

/**
 * Opt the site in to external data exchange.
 *
 * @since TBD
 */
export async function postOptIn(): Promise<void> {
    await apiFetch<ConsentResponse>( {
        path:   CONSENT_PATH,
        method: 'POST',
    } );
}

/**
 * Revoke the opt-in for the site.
 *
 * @since TBD
 */
export async function deleteConsent(): Promise<void> {
    await apiFetch<ConsentResponse>( {
        path:   CONSENT_PATH,
        method: 'DELETE',
    } );
}
