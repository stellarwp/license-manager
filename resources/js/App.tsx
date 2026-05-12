/**
 * @package LiquidWeb\Harbor
 */
import { AppShell } from '@/components/templates/AppShell';
import { OptInScreen } from '@/components/templates/OptInScreen';
import { Toaster } from '@/components/ui/toast';
import { ErrorBoundary } from '@/components/atoms/ErrorBoundary';
import { ErrorModal } from '@/components/organisms/ErrorModal';
import { ToastProvider } from '@/context/toast-context';
import { FilterProvider } from '@/context/filter-context';
import { ErrorModalProvider } from '@/context/error-modal-context';
import { HarborDataProvider } from '@/context/harbor-data-context';
import { ReloadBannerProvider } from '@/context/reload-banner-context';

export const App = () => {
    // Defaults to false (show consent screen) when the field is missing, so the
    // UI stays safe-by-default while the backend wiring lands.
    const optedIn = window.harborData?.optedIn === true;

    if ( ! optedIn ) {
        // The consent screen does not need the data providers — keeping
        // HarborDataProvider out of this branch is what prevents the license /
        // catalog / features resolvers from firing pre-consent.
        return (
            <ToastProvider>
                <ErrorModalProvider>
                    <ErrorBoundary>
                        <OptInScreen />
                        <Toaster />
                    </ErrorBoundary>
                    <ErrorModal />
                </ErrorModalProvider>
            </ToastProvider>
        );
    }

    return (
        <ToastProvider>
            <ReloadBannerProvider>
            <FilterProvider>
                <ErrorModalProvider>
                    <HarborDataProvider>
                        <ErrorBoundary>
                            <AppShell />
                            <Toaster />
                        </ErrorBoundary>
                        { /* ErrorModal sits outside ErrorBoundary so a render crash
                             does not prevent the modal from opening. */ }
                        <ErrorModal />
                    </HarborDataProvider>
                </ErrorModalProvider>
            </FilterProvider>
            </ReloadBannerProvider>
        </ToastProvider>
    );
};
