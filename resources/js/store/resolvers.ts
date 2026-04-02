/**
 * Resolvers for the lw @wordpress/data store.
 *
 * Each resolver name matches a selector. @wordpress/data calls the resolver
 * automatically the first time the matching selector is invoked, then marks
 * it as resolved so subsequent calls hit the cache.
 *
 * @package LiquidWeb\Harbor
 */
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { HarborError, ErrorCode } from '@/errors';
import type { Feature, LegacyLicense, ProductPortal, License } from '@/types/api';
import type { Thunk } from './types';
import { forwardResolver, forwardResolverWithoutArgs } from '@/lib/forward-resolver';

/**
 * Fetches all features from the REST API and stores them.
 * Triggered automatically when getFeatures is first called.
 */
export const getFeatures =
	(): Thunk =>
	async ({ dispatch }) => {
		try {
			const features = await apiFetch<Feature[]>({
				path: '/liquidweb/harbor/v1/features',
			});
			dispatch.receiveFeatures(features);
		} catch (err) {
			throw await HarborError.wrap(
				err,
				ErrorCode.FeaturesFetchFailed,
				__('Liquid Web Software Manager failed to load your features.', '%TEXTDOMAIN%')
			);
		}
	};

export const getFeaturesByProduct = forwardResolverWithoutArgs('getFeatures');
export const getFeature = forwardResolverWithoutArgs('getFeatures');
export const isFeatureEnabled = forwardResolverWithoutArgs('getFeatures');

// ---------------------------------------------------------------------------
// Legacy licenses
// ---------------------------------------------------------------------------

/**
 * Fetches legacy licenses from the REST API.
 */
export const getLegacyLicenses =
	(): Thunk =>
	async ({ dispatch }) => {
		try {
			const licenses = await apiFetch<LegacyLicense[]>({
				path: '/liquidweb/harbor/v1/legacy-licenses',
			});
			dispatch.receiveLegacyLicenses(licenses);
		} catch (err) {
			throw await HarborError.wrap(
				err,
				ErrorCode.LegacyLicensesFetchFailed,
				__('Liquid Web Software Manager failed to load legacy licenses.', '%TEXTDOMAIN%')
			);
		}
	};

export const getLegacyLicenseBySlug = forwardResolverWithoutArgs('getLegacyLicenses');
export const hasLegacyLicense = forwardResolverWithoutArgs('getLegacyLicenses');
export const hasLegacyLicenses = forwardResolver('getLegacyLicenses');

// ---------------------------------------------------------------------------
// Portal
// ---------------------------------------------------------------------------

/**
 * Fetches all product portals from the REST API and stores them.
 * Triggered automatically when getPortal is first called.
 */
export const getPortal =
	(): Thunk =>
	async ({ dispatch }) => {
		try {
			const portals = await apiFetch<ProductPortal[]>({
				path: '/liquidweb/harbor/v1/catalog',
			});
			dispatch.receivePortal(portals);
		} catch (err) {
			throw await HarborError.wrap(
				err,
				ErrorCode.PortalFetchFailed,
				__('Liquid Web Software Manager failed to load the product portal.', '%TEXTDOMAIN%')
			);
		}
	};

export const getProductPortal = forwardResolverWithoutArgs('getPortal');
export const getProductTiers   = forwardResolverWithoutArgs('getPortal');
export const getPortalTier    = forwardResolverWithoutArgs('getPortal');

// ---------------------------------------------------------------------------
// License
// ---------------------------------------------------------------------------

/**
 * Fetches the stored license from the REST API.
 * Triggered automatically when getLicenseKey is first called.
 */
export const getLicenseKey =
	(): Thunk =>
	async ({ dispatch }) => {
		try {
			const result = await apiFetch<License>({
				path: '/liquidweb/harbor/v1/license',
			});
			dispatch.receiveLicense(result);
		} catch (err) {
			throw await HarborError.wrap(
				err,
				ErrorCode.LicenseFetchFailed,
				__('Liquid Web Software Manager failed to load your license.', '%TEXTDOMAIN%')
			);
		}
	};

export const hasLicense         = forwardResolver( 'getLicenseKey' );
export const getLicenseProducts = forwardResolverWithoutArgs( 'getLicenseKey' );
