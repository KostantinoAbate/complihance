(function () {
    /**
     * @typedef {object} ConsentRecord
     * @property {string[]} [accepted_categories]
     * @property {string[]|Record<string, boolean>} [vendors]
     */

    /**
     * @typedef {object} ConsentResponse
     * @property {boolean} [has_consent]
     * @property {boolean} [requires_renewal]
     * @property {ConsentRecord|null} [consent]
     */

    /**
     * @typedef {object} CategoryConfig
     * @property {string} key
     * @property {boolean} [required]
     */

    /**
     * @typedef {object} VendorConfig
     * @property {string} key
     */

    /**
     * @typedef {object} ComplihanceConfiguration
     * @property {CategoryConfig[]} [categories]
     * @property {VendorConfig[]} [vendors]
     */

    /**
     * @typedef {object} ComplihanceOptions
     * @property {string} [apiBaseUrl]
     * @property {string|null} [csrfToken]
     */

    /**
     * @typedef {(payload: ConsentResponse|null) => void} ConsentChangedCallback
     * @typedef {(payload: object) => void} PreferenceCallback
     */

    /**
     * @typedef {object} ComplihanceState
     * @property {ConsentResponse|null} consent
     * @property {boolean} consentLoaded
     * @property {Promise<ConsentResponse>|null} consentPromise
     * @property {ComplihanceConfiguration|null} configuration
     * @property {Promise<ComplihanceConfiguration>|null} configurationPromise
     * @property {PreferenceCallback|null} preferenceUpdatedCallback
     * @property {PreferenceCallback|null} preferenceUpdateErrorCallback
     * @property {ConsentChangedCallback[]} callbacks
     * @property {ComplihanceOptions} options
     */

    const defaultOptions = {
        apiBaseUrl: '/complihance/api',
        csrfToken: document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content'),
    };

    /** @type {ComplihanceOptions} */
    const config = window['ComplihanceConfig'] || {};

    /** @type {ComplihanceState} */
    const state = {
        consent: null,
        consentLoaded: false,
        consentPromise: null,

        configuration: null,
        configurationPromise: null,

        preferenceUpdatedCallback: null,
        preferenceUpdateErrorCallback: null,

        callbacks: [],

        options: {
            ...defaultOptions,
            ...config,
        },
    };

    /**
     * Builds an absolute Complihance API URL for the given path.
     *
     * @param {string} path API path.
     * @returns {string}
     */
    function apiUrl(path) {
        const baseUrl = state.options.apiBaseUrl || '/complihance/api';

        return `${baseUrl.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;
    }

    /**
     * Sends a JSON request to the Complihance API.
     *
     * @param {string} path API path.
     * @param {RequestInit} [options={}] Fetch options.
     * @returns {Promise<object>}
     */
    async function request(path, options = {}) {
        const response = await fetch(apiUrl(path), {
            credentials: 'same-origin',
            redirect: 'manual',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(state.options.csrfToken
                    ? { 'X-CSRF-TOKEN': state.options.csrfToken }
                    : {}),
                ...(options.headers || {}),
            },
            ...options,
        });

        if (!response.ok) {
            const error = new Error('Complihance API request failed');

            error['status'] = response.status;
            error['response'] = response;

            throw error;
        }

        return response.json();
    }

    /**
     * @param {object|null} [payload=state.consent]
     * @returns {void}
     */
    function dispatchConsentChanged(payload = state.consent) {
        window.dispatchEvent(
            new CustomEvent('complihance:consent-changed', {
                detail: payload,
            })
        );

        state.callbacks.forEach((callback) => {
            callback(/** @type {ConsentResponse|null} */ (payload));
        });
    }

    /**
     * @param {object} payload
     * @returns {ConsentResponse}
     */
    function setConsent(payload) {
        state.consent = /** @type {ConsentResponse} */ (payload);
        state.consentLoaded = true;

        dispatchConsentChanged(state.consent);

        return state.consent;
    }

    /**
     * @returns {Promise<ConsentResponse>}
     */
    function refreshConsent() {
        state.consentPromise = /** @type {Promise<ConsentResponse>} */ (
            request('/consent')
                .then((payload) => setConsent(payload))
                .finally(() => {
                    state.consentPromise = null;
                })
        );

        return state.consentPromise;
    }

    /**
     * @returns {Promise<ConsentResponse|null>}
     */
    async function getConsent() {
        if (state.consentLoaded) {
            return state.consent;
        }

        if (state.consentPromise) {
            return state.consentPromise;
        }

        return refreshConsent();
    }

    /**
     * @returns {ConsentResponse|null}
     */
    function getConsentSync() {
        return state.consent;
    }

    /**
     * @returns {Promise<ComplihanceConfiguration>}
     */
    function getConfiguration() {
        if (state.configuration) {
            return Promise.resolve(state.configuration);
        }

        if (state.configurationPromise) {
            return state.configurationPromise;
        }

        state.configurationPromise = /** @type {Promise<ComplihanceConfiguration>} */ (
            request('/configuration')
                .then((configuration) => {
                    state.configuration = /** @type {ComplihanceConfiguration} */ (configuration);

                    return state.configuration;
                })
                .finally(() => {
                    state.configurationPromise = null;
                })
        );

        return state.configurationPromise;
    }

    /**
     * @returns {ComplihanceConfiguration|null}
     */
    function getConfigurationSync() {
        return state.configuration;
    }

    /**
     * @returns {boolean}
     */
    function hasConsent() {
        return state.consent?.has_consent === true;
    }

    /**
     * @returns {boolean}
     */
    function requiresRenewal() {
        return state.consent?.requires_renewal === true;
    }

    /**
     * @returns {string[]}
     */
    function acceptedCategories() {
        return state.consent?.consent?.accepted_categories || [];
    }

    /**
     * @returns {string[]|Record<string, boolean>}
     */
    function acceptedVendors() {
        return state.consent?.consent?.vendors || [];
    }

    /**
     * @param {string} category
     * @returns {boolean}
     */
    function hasCategory(category) {
        return acceptedCategories().includes(category);
    }

    /**
     * @param {string} vendor
     * @returns {boolean}
     */
    function hasVendor(vendor) {
        const vendors = acceptedVendors();

        if (Array.isArray(vendors)) {
            return vendors.includes(vendor);
        }

        return vendors[vendor] === true;
    }

    /**
     * @param {string} category
     * @returns {boolean}
     */
    function canUse(category) {
        return hasConsent() && !requiresRenewal() && hasCategory(category);
    }

    /**
     * @param {string} vendor
     * @returns {boolean}
     */
    function canUseVendor(vendor) {
        return hasConsent() && !requiresRenewal() && hasVendor(vendor);
    }

    /**
     * @returns {string[]}
     */
    function optionalCategories() {
        return (state.configuration?.categories || [])
            .filter((category) => category.required !== true)
            .map((category) => category.key)
            .filter(Boolean);
    }

    /**
     * @returns {boolean}
     */
    function canUseAllOptionalCategories() {
        if (!hasConsent() || requiresRenewal()) {
            return false;
        }

        const categories = optionalCategories();

        if (!categories.length) {
            return false;
        }

        return categories.every((category) => hasCategory(category));
    }

    /**
     * @param {object} preferences
     * @returns {Promise<ConsentResponse>}
     */
    async function savePreferences(preferences) {
        const payload = await request('/consent', {
            method: 'POST',
            body: JSON.stringify(preferences),
        });

        return setConsent(payload);
    }

    /**
     * @param {object} preferences
     * @returns {Promise<ConsentResponse>}
     */
    async function updatePreferences(preferences) {
        const payload = await request('/consent', {
            method: 'PATCH',
            body: JSON.stringify(preferences),
        });

        return setConsent(payload);
    }

    /**
     * @returns {Promise<ConsentResponse>}
     */
    async function revoke() {
        const payload = await request('/consent', {
            method: 'DELETE',
        });

        state.consent = {
            has_consent: false,
            requires_renewal: true,
            consent: null,
            ...payload,
        };

        state.consentLoaded = true;

        dispatchConsentChanged(state.consent);

        return state.consent;
    }

    /**
     * @returns {Promise<ConsentResponse>}
     */
    async function acceptAll() {
        const configuration = await getConfiguration();

        return savePreferences({
            source: 'banner',
            categories: (configuration.categories || [])
                .map((category) => category.key)
                .filter(Boolean),

            vendors: (configuration.vendors || [])
                .map((vendor) => vendor.key)
                .filter(Boolean),
        });
    }

    /**
     * @returns {Promise<ConsentResponse>}
     */
    async function rejectAll() {
        const configuration = await getConfiguration();

        return savePreferences({
            source: 'banner',
            categories: (configuration.categories || [])
                .filter((category) => category.required === true)
                .map((category) => category.key)
                .filter(Boolean),

            vendors: [],
        });
    }

    /**
     * @param {ConsentChangedCallback} callback
     * @returns {void}
     */
    function onConsentChanged(callback) {
        if (typeof callback !== 'function') {
            return;
        }

        state.callbacks.push(callback);

        if (state.consentLoaded) {
            callback(state.consent);
        }
    }

    /**
     * @param {PreferenceCallback} callback
     * @returns {void}
     */
    function onPreferenceUpdated(callback) {
        if (typeof callback !== 'function') {
            return;
        }

        state.preferenceUpdatedCallback = callback;
    }

    /**
     * @param {PreferenceCallback} callback
     * @returns {void}
     */
    function onPreferenceUpdateError(callback) {
        if (typeof callback !== 'function') {
            return;
        }

        state.preferenceUpdateErrorCallback = callback;
    }

    /**
     * @param {object} payload
     * @returns {boolean}
     */
    function dispatchPreferenceUpdated(payload) {
        window.dispatchEvent(
            new CustomEvent('complihance:preference-updated', {
                detail: payload,
            })
        );

        if (!state.preferenceUpdatedCallback) {
            return false;
        }

        state.preferenceUpdatedCallback(payload);

        return true;
    }

    /**
     * @param {object} payload
     * @returns {boolean}
     */
    function dispatchPreferenceUpdateError(payload) {
        window.dispatchEvent(
            new CustomEvent('complihance:preference-update-error', {
                detail: payload,
            })
        );

        if (!state.preferenceUpdateErrorCallback) {
            return false;
        }

        state.preferenceUpdateErrorCallback(payload);

        return true;
    }

    window['Complihance'] = {
        ...(window['Complihance'] || {}),

        'getConsent': getConsent,
        'getConsentSync': getConsentSync,
        'refreshConsent': refreshConsent,

        'getConfiguration': getConfiguration,
        'getConfigurationSync': getConfigurationSync,

        'hasConsent': hasConsent,
        'requiresRenewal': requiresRenewal,
        'hasCategory': hasCategory,
        'hasVendor': hasVendor,
        'canUse': canUse,
        'canUseVendor': canUseVendor,
        'canUseAllOptionalCategories': canUseAllOptionalCategories,

        'savePreferences': savePreferences,
        'updatePreferences': updatePreferences,
        'acceptAll': acceptAll,
        'rejectAll': rejectAll,
        'revoke': revoke,

        'onConsentChanged': onConsentChanged,
        'dispatchConsentChanged': dispatchConsentChanged,
        'onPreferenceUpdated': onPreferenceUpdated,
        'onPreferenceUpdateError': onPreferenceUpdateError,
        'dispatchPreferenceUpdated': dispatchPreferenceUpdated,
        'dispatchPreferenceUpdateError': dispatchPreferenceUpdateError,

        '_state': state,
    };

    refreshConsent().catch(() => {
        state.consentLoaded = true;
    });

    getConfiguration().catch(() => {
        state.configuration = null;
    });
})();
