import { __, sprintf } from '@wordpress/i18n';
import { ExternalLink } from 'lucide-react';
import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';

interface PurchaseLinkProps {
    /** Tier name to upgrade to (e.g. "Agency") */
    tierName: string;
    /** Upgrade destination URL */
    upgradeUrl: string;
    /** "upgrade" shows "Upgrade to Tier", "learn-more" shows "Learn more" */
    mode?: 'upgrade' | 'learn-more';
    className?: string;
}

/**
 * @since 1.0.0
 */
export function PurchaseLink( { tierName, upgradeUrl, mode = 'upgrade', className }: PurchaseLinkProps ) {
    const label =
        mode === 'upgrade'
            ? /* translators: %s is the name of the license tier to upgrade to */
              sprintf( __( 'Upgrade to %s', '%TEXTDOMAIN%' ), tierName )
            : __( 'Learn more', '%TEXTDOMAIN%' );

    return (
        <a
            href={ upgradeUrl }
            target="_blank"
            rel="noopener noreferrer"
            className={ cn( buttonVariants( { variant: 'outline', size: 'sm' } ), 'h-7 gap-1 text-xs', className ) }
        >
            { label }
            <ExternalLink className="w-3 h-3" />
        </a>
    );
}
