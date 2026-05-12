export interface HarborData {
    restUrl:           string;
    nonce:             string;
    pluginsUrl:        string;
    activationUrl:     string;
    subscriptionsUrl:  string;
    domain:            string;
    version:           string;
    optedIn:           boolean;
    isMultisite:       boolean;
    licensingBaseUrl?: string;
    portalBaseUrl?:    string;
    heraldBaseUrl?:    string;
}
