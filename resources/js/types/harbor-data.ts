export interface HarborData {
    restUrl:           string;
    nonce:             string;
    pluginsUrl:        string;
    activationUrl:     string;
    subscriptionsUrl:  string;
    domain:            string;
    version:           string;
    optedIn:           boolean;
    licensingBaseUrl?: string;
    portalBaseUrl?:    string;
    heraldBaseUrl?:    string;
}
