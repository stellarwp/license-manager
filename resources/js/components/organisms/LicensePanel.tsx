/**
 * License sidebar panel.
 *
 * Always visible. Fetches license and catalog data from the store and passes
 * it to LicenseSection and UpsellSection.
 *
 * @package LiquidWeb\Harbor
 */
import { useMemo, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { LicenseSection } from '@/components/organisms/LicenseSection';
import { UpsellSection } from '@/components/organisms/UpsellSection';
import { Button } from '@/components/ui/button';
import { Dialog, DialogHeader, DialogContent, DialogFooter } from '@/components/ui/dialog';
import { store as harborStore } from '@/store';
import { PRODUCTS } from '@/data/products';
import { useToast } from '@/context/toast-context';
import { useErrorModal } from '@/context/error-modal-context';
import { HarborError } from '@/errors';

/**
 * @since 1.0.0
 */
export function LicensePanel() {
    const { addToast }      = useToast();
    const { addError }      = useErrorModal();
    const { deleteLicense, refreshLicense, refreshCatalog, revokeConsent } = useDispatch( harborStore );

    const { licenseKey, licenseProducts, catalogs, isRefreshing, isLicenseLoading, isRevoking } = useSelect(
        ( select ) => ({
            licenseKey:       select( harborStore ).getLicenseKey(),
            licenseProducts:  select( harborStore ).getLicenseProducts(),
            catalogs:         select( harborStore ).getCatalog(),
            isRefreshing:     select( harborStore ).isLicenseRefreshing(),
            // @ts-expect-error -- hasFinishedResolution is injected at runtime by @wordpress/data but absent from the store's TypeScript surface.
            isLicenseLoading: ! select( harborStore ).hasFinishedResolution( 'getLicenseKey', [] ),
            isRevoking:       select( harborStore ).isConsentRevoking(),
        }),
        []
    );

    const [ isRevokeDialogOpen, setIsRevokeDialogOpen ] = useState( false );
    const [ revokeNetwork, setRevokeNetwork ] = useState( false );

    const isMultisite = window.harborData?.isMultisite === true;

    // Flat tier slug -> display name and rank lookups from all catalog tiers.
    const { tierNameMap, tierRankMap } = useMemo( () => {
        const names: Record<string, string> = {};
        const ranks: Record<string, number> = {};
        catalogs.forEach( ( catalog ) => {
            catalog.tiers.forEach( ( t ) => {
                names[ t.tier_slug ] = t.name;
                ranks[ t.tier_slug ] = t.rank;
            } );
        } );
        return { tierNameMap: names, tierRankMap: ranks };
    }, [ catalogs ] );

    const activationUrl = licenseKey && window.harborData ? window.harborData.activationUrl : null;

    // Product slug -> lowest paid-tier purchase URL map from the catalog.
    const upsellUrlMap = useMemo( () => {
        const map: Record<string, string> = {};
        catalogs.forEach( ( catalog ) => {
            const sorted   = catalog.tiers.slice().sort( ( a, b ) => a.rank - b.rank );
            const paidTier = sorted.find( ( t ) => t.rank > 0 );
            if ( paidTier?.purchase_url ) {
                map[ catalog.product_slug ] = paidTier.purchase_url;
            }
        } );
        return map;
    }, [ catalogs ] );

    const licensedSlugs  = new Set( licenseProducts.map( ( lp ) => lp.product_slug ) );
    const upsellProducts = PRODUCTS.filter( ( p ) => ! licensedSlugs.has( p.slug ) );

    const handleRemove = async (): Promise<HarborError | null> => {
        const result = await deleteLicense();
        if ( result instanceof HarborError ) {
            addError( result );
            return result;
        }
        addToast( __( 'License removed.', '%TEXTDOMAIN%' ), 'default' );
        return null;
    };

    const handleRefresh = async () => {
        const [ licenseResult, catalogResult ] = await Promise.all( [
            refreshLicense(),
            refreshCatalog(),
        ] );
        if ( licenseResult instanceof HarborError ) {
            addError( licenseResult );
        }
        if ( catalogResult instanceof HarborError ) {
            addError( catalogResult );
        }
        if ( ! ( licenseResult instanceof HarborError ) && ! ( catalogResult instanceof HarborError ) ) {
            addToast( __( 'License refreshed.', '%TEXTDOMAIN%' ), 'success' );
        }
    };

    const handleConfirmRevoke = async () => {
        const result = await revokeConsent( revokeNetwork );
        if ( result instanceof HarborError ) {
            addError( result );
            setIsRevokeDialogOpen( false );
        }
        // On success the thunk reloads the page, so no further UI work is needed.
    };

    return (
        <div className="sticky top-4 w-[280px] shrink-0 space-y-6">
            <LicenseSection
                licenseKey={ licenseKey }
                licenseProducts={ licenseProducts }
                tierNameMap={ tierNameMap }
                tierRankMap={ tierRankMap }
                onRemove={ handleRemove }
                onRefresh={ handleRefresh }
                isRefreshing={ isRefreshing }
                isLoading={ isLicenseLoading }
                activationUrl={ activationUrl }
            />
            { ! isLicenseLoading && (
                <UpsellSection
                    products={ upsellProducts }
                    upsellUrlMap={ upsellUrlMap }
                />
            ) }

            <div className="pt-4 border-t">
                <Button
                    type="button"
                    variant="destructive"
                    size="sm"
                    onClick={ () => {
                        setRevokeNetwork( false );
                        setIsRevokeDialogOpen( true );
                    } }
                    disabled={ isRevoking }
                    className="w-full"
                >
                    { __( 'Revoke external data consent', '%TEXTDOMAIN%' ) }
                </Button>
            </div>

            <Dialog
                open={ isRevokeDialogOpen }
                onClose={ () => ( ! isRevoking ) && setIsRevokeDialogOpen( false ) }
                maxWidth="max-w-md"
            >
                <DialogHeader
                    title={ __( 'Revoke external data consent?', '%TEXTDOMAIN%' ) }
                    onClose={ () => ( ! isRevoking ) && setIsRevokeDialogOpen( false ) }
                />
                <DialogContent>
                    <p className="text-sm text-foreground !m-0">
                        { __(
                            'Liquid Web Software Manager will stop contacting Liquid Web services until you opt in again. Cached license and product data remain on your site.',
                            '%TEXTDOMAIN%'
                        ) }
                    </p>
                    { isMultisite && (
                        <label className="flex items-start gap-2 text-sm mt-4">
                            <input
                                type="checkbox"
                                checked={ revokeNetwork }
                                onChange={ ( e ) => setRevokeNetwork( e.target.checked ) }
                                disabled={ isRevoking }
                                className="mt-1"
                            />
                            <span>
                                { __( 'Revoke network-wide', '%TEXTDOMAIN%' ) }
                            </span>
                        </label>
                    ) }
                </DialogContent>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={ () => setIsRevokeDialogOpen( false ) }
                        disabled={ isRevoking }
                    >
                        { __( 'Cancel', '%TEXTDOMAIN%' ) }
                    </Button>
                    <Button
                        type="button"
                        variant="destructive"
                        onClick={ handleConfirmRevoke }
                        disabled={ isRevoking }
                    >
                        { isRevoking
                            ? __( 'Revoking...', '%TEXTDOMAIN%' )
                            : __( 'Revoke', '%TEXTDOMAIN%' )
                        }
                    </Button>
                </DialogFooter>
            </Dialog>
        </div>
    );
}
