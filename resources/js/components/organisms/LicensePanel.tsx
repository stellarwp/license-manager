/**
 * License sidebar panel.
 *
 * Always visible. Fetches license and portal data from the store and passes
 * it to LicenseSection and UpsellSection.
 *
 * @package LiquidWeb\Harbor
 */
import { useMemo } from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { LicenseSection } from '@/components/organisms/LicenseSection';
import { UpsellSection } from '@/components/organisms/UpsellSection';
import { store as harborStore } from '@/store';
import { PRODUCTS } from '@/data/products';
import { useToast } from '@/context/toast-context';
import { HarborError } from '@/errors';

/**
 * @since 1.0.0
 */
export function LicensePanel() {
    const { addToast }      = useToast();
    const { deleteLicense } = useDispatch( harborStore );

    const { licenseKey, licenseProducts, portals } = useSelect(
        ( select ) => ({
            licenseKey:      select( harborStore ).getLicenseKey(),
            licenseProducts: select( harborStore ).getLicenseProducts(),
            portals:        select( harborStore ).getPortal(),
        }),
        []
    );

    // Flat tier slug → display name lookup from all portal tiers.
    const tierNameMap = useMemo( () => {
        const map: Record<string, string> = {};
        portals.forEach( ( portal ) => {
            portal.tiers.forEach( ( t ) => {
                map[ t.slug ] = t.name;
            } );
        } );
        return map;
    }, [ portals ] );

    // Product slug → lowest-tier purchase URL map from the portal.
    const upsellUrlMap = useMemo( () => {
        const map: Record<string, string> = {};
        portals.forEach( ( portal ) => {
            const sorted = portal.tiers.slice().sort( ( a, b ) => a.rank - b.rank );
            if ( sorted[ 0 ]?.purchase_url ) {
                map[ portal.product_slug ] = sorted[ 0 ].purchase_url;
            }
        } );
        return map;
    }, [ portals ] );

    const licensedSlugs  = new Set( licenseProducts.map( ( lp ) => lp.product_slug ) );
    const upsellProducts = PRODUCTS.filter( ( p ) => ! licensedSlugs.has( p.slug ) );

    const handleRemove = async () => {
        const result = await deleteLicense();
        if ( result instanceof HarborError ) {
            addToast( result.message, 'error' );
        } else {
            addToast( __( 'License removed.', '%TEXTDOMAIN%' ), 'default' );
        }
    };

    return (
        <div className="sticky top-4 w-[280px] shrink-0 space-y-6">
            <LicenseSection
                licenseKey={ licenseKey }
                licenseProducts={ licenseProducts }
                tierNameMap={ tierNameMap }
                onRemove={ handleRemove }
            />
            <UpsellSection
                products={ upsellProducts }
                upsellUrlMap={ upsellUrlMap }
            />
        </div>
    );
}
