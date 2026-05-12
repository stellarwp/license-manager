/**
 * Single source of truth for the consent REST calls.
 *
 * Contract (subject to confirmation with the backend dev):
 *   POST   /liquidweb/harbor/v1/consent  { network?: boolean }  -> { opted_in: true }
 *   DELETE /liquidweb/harbor/v1/consent  { network?: boolean }  -> { opted_in: false }
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
 * Opt the site (or network, on multisite) in to external data exchange.
 *
 * @param network When true on multisite, applies the opt-in to the entire network.
 * @since 1.0.0
 */
export async function postOptIn( network: boolean = false ): Promise<void> {
    await apiFetch<ConsentResponse>( {
        path:   CONSENT_PATH,
        method: 'POST',
        data:   { network },
    } );
}

/**
 * Revoke the opt-in for the site (or network, on multisite).
 *
 * @param network When true on multisite, revokes the network-level opt-in.
 * @since 1.0.0
 */
export async function deleteConsent( network: boolean = false ): Promise<void> {
    await apiFetch<ConsentResponse>( {
        path:   CONSENT_PATH,
        method: 'DELETE',
        data:   { network },
    } );
}
