/**
 * Opt-in screen rendered when the site has not yet consented to external
 * data exchange with Liquid Web services.
 *
 * Lives outside HarborDataProvider so the license / catalog / features
 * resolvers never run pre-consent.
 *
 * @package LiquidWeb\Harbor
 */
import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Button } from '@/components/ui/button';
import { useErrorModal } from '@/context/error-modal-context';
import HarborError from '@/errors/harbor-error';
import { ErrorCode } from '@/errors/error-code';
import { postOptIn } from '@/lib/consent-api';

/**
 * @since TBD
 */
export function OptInScreen() {
    const { addError } = useErrorModal();
    const [ isSubmitting, setIsSubmitting ] = useState( false );

    const domain           = window.harborData?.domain ?? '';
    const licensingBaseUrl = window.harborData?.licensingBaseUrl ?? '';
    const portalBaseUrl    = window.harborData?.portalBaseUrl ?? '';
    const heraldBaseUrl    = window.harborData?.heraldBaseUrl ?? '';

    const endpoints: Array<{ label: string; url: string }> = [
        {
            label: __( 'Liquid Web Licensing service (license validation)', '%TEXTDOMAIN%' ),
            url:   licensingBaseUrl,
        },
        {
            label: __( 'Liquid Web Customer Portal (product catalog)', '%TEXTDOMAIN%' ),
            url:   portalBaseUrl,
        },
        {
            label: __( 'Herald (plugin download host)', '%TEXTDOMAIN%' ),
            url:   heraldBaseUrl,
        },
    ];

    const handleOptIn = async () => {
        setIsSubmitting( true );
        try {
            await postOptIn();
            // Backend rebinds the page on reload: Opt_In_Page -> Feature_Manager_Page.
            window.location.reload();
        } catch ( err ) {
            const harborError = await HarborError.wrap(
                err,
                ErrorCode.ConsentOptInFailed,
                __(
                    'Liquid Web Software Manager could not record your consent. Please try again.',
                    '%TEXTDOMAIN%'
                ),
            );
            addError( harborError );
            setIsSubmitting( false );
        }
    };

    return (
        <div className="min-h-[calc(100vh-32px)] flex items-start justify-center bg-neutral-50 p-8">
            <div className="w-full max-w-2xl rounded-lg border bg-background shadow-sm p-8 space-y-6">
                <header className="space-y-2">
                    <h1 className="!text-2xl !font-semibold !m-0 !p-0">
                        { __( 'Connect to Liquid Web', '%TEXTDOMAIN%' ) }
                    </h1>
                    <p className="text-sm text-muted-foreground !m-0">
                        { __(
                            'The Liquid Web Software Manager needs your permission before it contacts Liquid Web services to validate your license and load your product catalog.',
                            '%TEXTDOMAIN%'
                        ) }
                    </p>
                </header>

                <section className="space-y-2">
                    <h2 className="!text-base !font-semibold !m-0">
                        { __( 'What is sent', '%TEXTDOMAIN%' ) }
                    </h2>
                    <ul className="text-sm text-foreground space-y-1 !m-0 !pl-5 list-disc">
                        <li>
                            { __( 'Your site domain', '%TEXTDOMAIN%' ) }
                            { domain && (
                                <span className="text-muted-foreground">
                                    { ` (${ domain })` }
                                </span>
                            ) }
                        </li>
                        <li>{ __( 'Your unified Liquid Web license key, when stored', '%TEXTDOMAIN%' ) }</li>
                        <li>{ __( 'The Liquid Web Software Manager version', '%TEXTDOMAIN%' ) }</li>
                    </ul>
                </section>

                <section className="space-y-2">
                    <h2 className="!text-base !font-semibold !m-0">
                        { __( 'Where it is sent', '%TEXTDOMAIN%' ) }
                    </h2>
                    <ul className="text-sm text-foreground space-y-1 !m-0 !pl-5 list-disc">
                        { endpoints.map( ( endpoint ) => (
                            <li key={ endpoint.label }>
                                { endpoint.label }
                                { endpoint.url && (
                                    <span className="text-muted-foreground">
                                        { ' — ' }
                                        <code className="text-xs">{ endpoint.url }</code>
                                    </span>
                                ) }
                            </li>
                        ) ) }
                    </ul>
                </section>

                <p className="text-sm text-muted-foreground !m-0">
                    { __(
                        'You can revoke this permission at any time from the Software Manager sidebar.',
                        '%TEXTDOMAIN%'
                    ) }
                </p>

                <div className="flex items-center justify-end gap-3 pt-2">
                    <Button
                        type="button"
                        variant="default"
                        onClick={ handleOptIn }
                        disabled={ isSubmitting }
                    >
                        { isSubmitting
                            ? __( 'Opting in...', '%TEXTDOMAIN%' )
                            : __( 'Opt In', '%TEXTDOMAIN%' )
                        }
                    </Button>
                </div>
            </div>
        </div>
    );
}
