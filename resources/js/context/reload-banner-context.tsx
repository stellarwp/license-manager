/**
 * @package LiquidWeb\Harbor
 */
import { createContext, useContext, useState, type ReactNode } from 'react';

interface ReloadBannerContextValue {
    needsReload:    boolean;
    setNeedsReload: ( value: boolean ) => void;
}

const ReloadBannerContext = createContext<ReloadBannerContextValue>( {
    needsReload:    false,
    setNeedsReload: () => {},
} );

/**
 * @since 1.0.0
 */
export function ReloadBannerProvider( { children }: { children: ReactNode } ) {
    const [ needsReload, setNeedsReload ] = useState( false );

    return (
        <ReloadBannerContext.Provider value={ { needsReload, setNeedsReload } }>
            { children }
        </ReloadBannerContext.Provider>
    );
}

/**
 * @since 1.0.0
 */
export const useReloadBanner = () => useContext( ReloadBannerContext );
