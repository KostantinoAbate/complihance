(function () {
    const defaultOptions = {
        apiBaseUrl: '/complihance/api',
        csrfToken: document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content'),
    };

    const state = {
        consent: null,
        consentLoaded: false,
        consentPromise: null,

        configuration: null,
        configurationPromise: null,

        callbacks: [],

        options: {
            ...defaultOptions,
            ...(window.ComplihanceConfig || {}),
        },
    };

    function apiUrl(path) {
        const baseUrl = state.options.apiBaseUrl || '/complihance/api';

        return `${baseUrl.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;
    }

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

            error.status = response.status;
            error.response = response;

            throw error;
        }

        return response.json();
    }

    function dispatchConsentChanged(payload = state.consent) {
        window.dispatchEvent(
            new CustomEvent('complihance:consent-changed', {
                detail: payload,
            })
        );

        state.callbacks.forEach((callback) => {
            callback(payload);
        });
    }

    function setConsent(payload) {
        state.consent = payload;
        state.consentLoaded = true;

        dispatchConsentChanged(payload);

        return payload;
    }

    async function refreshConsent() {
        state.consentPromise = request('/consent')
            .then((payload) => setConsent(payload))
            .finally(() => {
                state.consentPromise = null;
            });

        return state.consentPromise;
    }

    async function getConsent() {
        if (state.consentLoaded) {
            return state.consent;
        }

        if (state.consentPromise) {
            return state.consentPromise;
        }

        return refreshConsent();
    }

    function getConsentSync() {
        return state.consent;
    }

    async function getConfiguration() {
        if (state.configuration) {
            return state.configuration;
        }

        if (state.configurationPromise) {
            return state.configurationPromise;
        }

        state.configurationPromise = request('/configuration')
            .then((configuration) => {
                state.configuration = configuration;

                return configuration;
            })
            .finally(() => {
                state.configurationPromise = null;
            });

        return state.configurationPromise;
    }

    function getConfigurationSync() {
        return state.configuration;
    }

    function hasConsent() {
        return Boolean(state.consent?.has_consent);
    }

    function requiresRenewal() {
        return Boolean(state.consent?.requires_renewal);
    }

    function acceptedCategories() {
        return state.consent?.consent?.accepted_categories || [];
    }

    function acceptedVendors() {
        return state.consent?.consent?.vendors || {};
    }

    function hasCategory(category) {
        return acceptedCategories().includes(category);
    }

    function hasVendor(vendor) {
        const vendors = acceptedVendors();

        if (Array.isArray(vendors)) {
            return vendors.includes(vendor);
        }

        return vendors[vendor] === true;
    }

    function canUse(category) {
        return hasConsent() && !requiresRenewal() && hasCategory(category);
    }

    function canUseVendor(vendor) {
        return hasConsent() && !requiresRenewal() && hasVendor(vendor);
    }

    function optionalCategories() {
        return state.configuration?.categories
            ?.filter((category) => category.required !== true)
            ?.map((category) => category.key)
            ?.filter(Boolean) || [];
    }

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

    async function savePreferences(preferences) {
        const payload = await request('/consent', {
            method: 'POST',
            body: JSON.stringify(preferences),
        });

        return setConsent(payload);
    }

    async function updatePreferences(preferences) {
        const payload = await request('/consent', {
            method: 'PATCH',
            body: JSON.stringify(preferences),
        });

        return setConsent(payload);
    }

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

    async function acceptAll() {
        const configuration = await getConfiguration();

        return savePreferences({
            categories: configuration.categories
                .map((category) => category.key)
                .filter(Boolean),

            vendors: configuration.vendors
                .map((vendor) => vendor.key)
                .filter(Boolean),
        });
    }

    async function rejectAll() {
        const configuration = await getConfiguration();

        return savePreferences({
            categories: configuration.categories
                .filter((category) => category.required === true)
                .map((category) => category.key)
                .filter(Boolean),

            vendors: [],
        });
    }

    function onConsentChanged(callback) {
        if (typeof callback !== 'function') {
            return;
        }

        state.callbacks.push(callback);

        if (state.consentLoaded) {
            callback(state.consent);
        }
    }

    window.Complihance = {
        ...(window.Complihance || {}),

        getConsent,
        getConsentSync,
        refreshConsent,

        getConfiguration,
        getConfigurationSync,

        hasConsent,
        requiresRenewal,
        hasCategory,
        hasVendor,
        canUse,
        canUseVendor,
        canUseAllOptionalCategories,

        savePreferences,
        updatePreferences,
        acceptAll,
        rejectAll,
        revoke,

        onConsentChanged,
        dispatchConsentChanged,

        _state: state,
    };

    refreshConsent().catch(() => {
        state.consentLoaded = true;
    });

    getConfiguration().catch(() => {
        state.configuration = null;
    });
})();
