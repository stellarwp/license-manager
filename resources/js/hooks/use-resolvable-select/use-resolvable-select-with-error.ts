/**
 * Wrapper around useResolvableSelect that routes resolution errors to the
 * ErrorModalContext instead of throwing them during render.
 *
 * When any resolver fails the error is passed to addError() so the error modal
 * opens while the rest of the UI stays rendered. When all resolvers succeed the
 * previously pushed error is automatically removed (auto-clear).
 *
 * @package LiquidWeb\Harbor
 */
import { useEffect, useRef, type DependencyList } from 'react';
import { __ } from '@wordpress/i18n';
import useResolvableSelect from './use-resolvable-select';
import HarborError from '@/errors/harbor-error';
import { ErrorCode } from '@/errors/error-code';
import { useErrorModal } from '@/context/error-modal-context';
import type { MapResolvableSelect, ResolvableSelectResponse } from './types';

/**
 * The consumer must return a record of resolvable results so the hook
 * can inspect each one for errors.
 */
type ResolvableRecord = Record<string, ResolvableSelectResponse<unknown>>;

/**
 * Collect all errors among a set of resolvable results and normalize each
 * as a HarborError.
 */
function findErrors( results: ResolvableRecord ): HarborError[] {
    const errors: HarborError[] = [];
    for ( const key in results ) {
        const entry = results[ key ];
        if ( entry.status === 'ERROR' ) {
            errors.push( HarborError.syncFrom(
                entry.error,
                ErrorCode.ResolutionFailed,
                __( 'Liquid Web Software failed to load your data.', '%TEXTDOMAIN%' ),
            ) );
        }
    }
    return errors;
}

/**
 * Like useResolvableSelect, but routes resolution errors to the
 * ErrorModalContext instead of throwing during render.
 *
 * The component tree renders with whatever data is available (usually the
 * store's default empty values). The modal opens automatically and clears
 * itself when the resolver eventually succeeds (e.g. after a Retry).
 *
 * @example
 * ```ts
 * const { features, catalog } = useResolvableSelectWithError(
 *     ( resolve ) => ( {
 *         features: resolve( harborStore ).getFeatures(),
 *         catalog: resolve( harborStore ).getCatalog(),
 *     } ),
 *     [],
 * );
 * ```
 */
export default function useResolvableSelectWithError<
    T extends ResolvableRecord,
>(
    mapResolvableSelect: MapResolvableSelect<T>,
    deps: DependencyList,
): T {
    const result = useResolvableSelect( mapResolvableSelect, deps );
    const { addError, removeError } = useErrorModal();

    // Track the codes pushed by this hook instance so we can clear exactly
    // those entries when all resolvers recover.
    const lastErrorCodesRef = useRef<string[]>( [] );

    useEffect( () => {
        const found = findErrors( result );

        if ( found.length > 0 ) {
            lastErrorCodesRef.current = found.map( ( e ) => e.code );
            found.forEach( ( error ) => addError( error ) );
        } else if ( lastErrorCodesRef.current.length > 0 ) {
            lastErrorCodesRef.current.forEach( ( code ) => removeError( code ) );
            lastErrorCodesRef.current = [];
        }
    }, [ result, addError, removeError ] );

    return result;
}
