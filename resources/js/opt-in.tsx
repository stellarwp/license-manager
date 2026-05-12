/**
 * Entry point for the consent / opt-in screen bundle.
 *
 * Loaded by Opt_In_Page when the site has not yet consented to external
 * data exchange. Renders only what is needed for the consent flow — no
 * @wordpress/data store, no resolvers, no Feature Manager UI.
 *
 * @package LiquidWeb\Harbor
 */
import { createRoot } from 'react-dom/client';
import { OptInScreen } from '@/components/templates/OptInScreen';
import { ErrorBoundary } from '@/components/atoms/ErrorBoundary';
import { ErrorModal } from '@/components/organisms/ErrorModal';
import { ErrorModalProvider } from '@/context/error-modal-context';
import '@css/globals.css';

const rootElement = document.getElementById( 'lw-harbor-opt-in-root' );

if ( rootElement ) {
    window.addEventListener( 'DOMContentLoaded', () => {
        createRoot( rootElement ).render(
            <ErrorModalProvider>
                <ErrorBoundary>
                    <OptInScreen />
                </ErrorBoundary>
                {/* ErrorModal lives outside ErrorBoundary so a render crash
                    does not prevent the modal from opening. */}
                <ErrorModal />
            </ErrorModalProvider>
        );
    } );
}
