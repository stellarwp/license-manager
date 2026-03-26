import type { Feature, FeatureMismatchType } from '@/types/api';

/**
 * True when a feature requires no paid tier — either it has no tier at all
 * or its tier slug contains "free" (e.g. "give-free").
 *
 * @since 1.0.0
 */
export function isFreeFeature( tier: string | null ): boolean {
    return ! tier || tier.toLowerCase().includes( 'free' );
}

/**
 * Returns the mismatch type for a feature, or null if there is no mismatch.
 *
 * Both fields are pre-computed by the backend resolution layer.
 * No catalog or license cross-referencing is needed at call sites.
 *
 * @since 1.0.0
 */
export function getFeatureMismatch( feature: Feature ): FeatureMismatchType {
    if ( feature.is_available && ! feature.in_catalog_tier ) {
        return 'bonus';
    }
    if ( ! feature.is_available && feature.in_catalog_tier ) {
        return 'revoked';
    }
    return null;
}
