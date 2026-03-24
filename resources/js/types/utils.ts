/**
 * Type guard utilities for narrowing Feature union types.
 *
 * @package LiquidWeb\Harbor
 */
import type {
	Feature,
	PluginFeature,
	ThemeFeature,
} from '@/types/api';

export function isPluginFeature( feature: Feature ): feature is PluginFeature {
	return feature.type === 'plugin';
}

export function isThemeFeature( feature: Feature ): feature is ThemeFeature {
	return feature.type === 'theme';
}
