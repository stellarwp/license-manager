/**
 * @package LiquidWeb\Harbor
 */
import { AppShell } from '@/components/templates/AppShell';
import { Toaster } from '@/components/ui/toast';
import { ErrorBoundary } from '@/components/ErrorBoundary';
import { ErrorModal } from '@/components/organisms/ErrorModal';
import { ToastProvider } from '@/context/toast-context';
import { FilterProvider } from '@/context/filter-context';
import { ErrorModalProvider } from '@/context/error-modal-context';

export const App = () => {
    return (
        <ToastProvider>
            <FilterProvider>
                <ErrorModalProvider>
                    <ErrorBoundary>
                        <AppShell />
                        <Toaster />
                    </ErrorBoundary>
                    { /* ErrorModal sits outside ErrorBoundary so a render crash
                         does not prevent the modal from opening. */ }
                    <ErrorModal />
                </ErrorModalProvider>
            </FilterProvider>
        </ToastProvider>
    );
};
