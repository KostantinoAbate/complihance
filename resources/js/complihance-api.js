(function () {
    const defaultConfig = {
        apiBaseUrl: '/complihance/api',
        csrfToken: document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content'),
    };

    const state = {
        consent: null,
        loaded: false,
        callbacks: [],
        config: {
            ...defaultConfig,
            ...(window.ComplihanceConfig || {}),
        },
    };

    function apiUrl(path) {
        const baseUrl = state.config.apiBaseUrl || '/complihance/api';

        return `${baseUrl.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;
    }

    async function request(path, options = {}) {
        console.log('[Complihance request]', {
            path,
            apiBaseUrl: state.config.apiBaseUrl,
            finalUrl: apiUrl(path),
        });

        const response = await fetch(apiUrl(path), {
            credentials: 'same-origin',
            redirect: 'manual',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(state.config.csrfToken
                    ? { 'X-CSRF-TOKEN': state.config.csrfToken }
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

    function setConsent(payload) {
        state.consent = payload;
        state.loaded = true;

        dispatchConsentChanged(payload);

        return payload;
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

    async function refreshConsent() {
        const payload = await request('/consent');

        return setConsent(payload);
    }

    async function getConsent() {
        if (state.loaded) {
            return state.consent;
        }

        return refreshConsent();
    }

    function getConsentSync() {
        return state.consent;
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

    function vendors() {
        return state.consent?.consent?.vendors || {};
    }

    function hasCategory(category) {
        return acceptedCategories().includes(category);
    }

    function hasVendor(vendor) {
        const selectedVendors = vendors();

        if (Array.isArray(selectedVendors)) {
            return selectedVendors.includes(vendor);
        }

        return selectedVendors[vendor] === true;
    }

    function canUse(category) {
        return hasConsent() && !requiresRenewal() && hasCategory(category);
    }

    function canUseVendor(vendor) {
        return hasConsent() && !requiresRenewal() && hasVendor(vendor);
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

        dispatchConsentChanged(state.consent);

        return state.consent;
    }

    async function acceptAll() {
        const configuration = await request('/configuration');

        const categories = configuration.categories
            .map((category) => category.key)
            .filter(Boolean);

        const vendorPreferences = configuration.vendors
            .map((vendor) => vendor.key)
            .filter(Boolean);

        return savePreferences({
            categories,
            vendors: vendorPreferences,
        });
    }

    async function rejectAll() {
        const configuration = await request('/configuration');

        const categories = configuration.categories
            .filter((category) => category.required === true)
            .map((category) => category.key)
            .filter(Boolean);

        const vendorPreferences = [];

        return savePreferences({
            categories,
            vendors: vendorPreferences,
        });
    }

    function onConsentChanged(callback) {
        if (typeof callback !== 'function') {
            return;
        }

        state.callbacks.push(callback);

        if (state.loaded) {
            callback(state.consent);
        }
    }

    window.Complihance = {
        getConsent,
        getConsentSync,
        refreshConsent,

        hasConsent,
        requiresRenewal,

        hasCategory,
        hasVendor,

        canUse,
        canUseVendor,

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
        state.loaded = true;
    });
})();
