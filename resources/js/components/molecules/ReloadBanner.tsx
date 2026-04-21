/**
 * Persistent banner shown when feature toggles require a page reload.
 *
 * Uses role="status" + aria-live="polite" so screen readers announce it once
 * when it appears, without interrupting the current focus.
 *
 * @package LiquidWeb\Harbor
 */
import { __ } from '@wordpress/i18n';
import { RefreshCw } from 'lucide-react';
import { useReloadBanner } from '@/context/reload-banner-context';

/**
 * @since 1.0.0
 */
export function ReloadBanner() {
    const { needsReload } = useReloadBanner();

    if ( ! needsReload ) return null;

    return (
        <div
            role="status"
            aria-live="polite"
            className="ml-auto flex items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs text-amber-800"
        >
            <RefreshCw className="w-3.5 h-3.5 shrink-0" aria-hidden="true" />
            <span>{ __( 'Reload required —', '%TEXTDOMAIN%' ) }</span>
            <button
                type="button"
                onClick={ () => window.location.reload() }
                className="font-medium underline underline-offset-2 hover:no-underline"
            >
                { __( 'Reload now', '%TEXTDOMAIN%' ) }
            </button>
        </div>
    );
}
