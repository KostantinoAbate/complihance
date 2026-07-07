import { updateConsentMode } from './consent-mode';
import { refreshBlockedContent } from './blocked-content';

/**
 * @typedef {object} ConsentPayload
 * @property {string} source
 * @property {Array<string>} categories
 * @property {Array<string>} [vendors]
 */

/**
 * @typedef {object} ComplihanceTextConfig
 * @property {{
 *     save_consent?: string
 * }} [errors]
 */

/**
 * @typedef {object} ComplihanceConfig
 * @property {ComplihanceTextConfig} [texts]
 * @property {string} [afterRevokeRedirectUrl]
 */

/**
 * @param {Element} element
 * @param {string} key
 * @returns {string|null}
 */
function getData(element, key) {
    return element.getAttribute(`data-${key}`) || null;
}

/**
 * @param {Element} element
 * @param {string} key
 * @param {string} value
 * @returns {void}
 */
function setData(element, key, value) {
    element.setAttribute(`data-${key}`, value);
}

/**
 * @param {Element} element
 * @param {string} selector
 * @returns {HTMLInputElement[]}
 */
function queryInputAll(element, selector) {
    /** @type {NodeListOf<HTMLInputElement>} */
    const inputs = element.querySelectorAll(selector);

    return Array.from(inputs);
}

/**
 * Returns the category input associated with a vendor input.
 *
 * @param {HTMLFormElement|Element} form Consent form element.
 * @param {HTMLInputElement} vendorInput Vendor checkbox input.
 * @returns {HTMLInputElement|null}
 */
function getCategoryInputByVendorInput(form, vendorInput) {
    const vendorCategory = getData(vendorInput, 'complihance-vendor-category');

    if (!vendorCategory) {
        return null;
    }

    return form.querySelector(
        `[data-complihance-category="${vendorCategory}"]`
    );
}

/**
 * Returns all vendor inputs associated with a category input.
 *
 * @param {HTMLFormElement|Element} form Consent form element.
 * @param {HTMLInputElement} categoryInput Category checkbox input.
 * @returns {HTMLInputElement[]}
 */
function getVendorInputsByCategoryInput(form, categoryInput) {
    const category = getData(categoryInput, 'complihance-category');

    if (!category) {
        return [];
    }

    return queryInputAll(
        form,
        `[data-complihance-vendor-category="${category}"]`
    );
}

/**
 * Updates a category checkbox state based on its vendor checkboxes.
 *
 * @param {HTMLFormElement|Element} form Consent form element.
 * @param {HTMLInputElement} categoryInput Category checkbox input.
 * @returns {void}
 */
function syncCategoryFromVendors(form, categoryInput) {
    const vendors = getVendorInputsByCategoryInput(form, categoryInput);

    if (!vendors.length) {
        return;
    }

    const checkedVendors = vendors.filter((vendor) => vendor.checked);

    categoryInput.checked = checkedVendors.length === vendors.length;
    categoryInput.indeterminate = checkedVendors.length > 0 && checkedVendors.length < vendors.length;
}

/**
 * Updates vendor checkbox states based on their category checkbox.
 *
 * @param {HTMLFormElement|Element} form Consent form element.
 * @param {HTMLInputElement} categoryInput Category checkbox input.
 * @returns {void}
 */
function syncVendorsFromCategory(form, categoryInput) {
    const vendors = getVendorInputsByCategoryInput(form, categoryInput);

    vendors.forEach((vendor) => {
        if (!vendor.disabled) {
            vendor.checked = categoryInput.checked;
        }
    });

    categoryInput.indeterminate = false;
}

/**
 * Initializes a Complihance consent form.
 *
 * @param {Element} container Banner or preferences container element.
 * @returns {void}
 */
export function initConsentForm(container) {
    if (getData(container, 'complihance-initialized') === 'true') {
        return;
    }

    const form = container.querySelector('[data-complihance-form]');
    const backdrop = document.querySelector('[data-complihance-backdrop]');

    if (!form) {
        return;
    }

    setData(container, 'complihance-initialized', 'true');

    let isSavingConsent = false;
    let pendingSave = false;
    let autoSaveTimeout = null;
    let isSyncing = false;

    const isPreferences = container.matches('[data-complihance-preferences]');
    const isBanner = container.matches('[data-complihance-banner]');

    const getSelectedCategories = () => {
        return queryInputAll(form, 'input[name="categories[]"]')
            .filter((input) => input.checked || input.indeterminate || getData(input, 'required') === 'true')
            .map((input) => input.value);
    };

    const getSelectedVendors = () => {
        return queryInputAll(form, 'input[name="vendors[]"]:checked')
            .map((input) => input.value);
    };

    /**
     * @returns {ConsentPayload}
     */
    const getConsentPayload = () => {
        const payload = {
            source: isPreferences ? 'preferences' : 'banner',
            categories: getSelectedCategories(),
        };

        if (form.querySelector('[data-complihance-vendor]')) {
            payload.vendors = getSelectedVendors();
        }

        return payload;
    };

    /**
     * @param {boolean} loading
     * @returns {void}
     */
    const setFormLoading = (loading) => {
        if (isPreferences) {
            container
                .querySelector('[data-complihance-save]')
                ?.toggleAttribute('disabled', loading);

            return;
        }

        form.querySelectorAll('button, input').forEach((element) => {
            if (getData(element, 'required') === 'true') {
                return;
            }

            element.disabled = loading;
        });
    };

    /**
     * @param {string} message
     * @returns {void}
     */
    const showFormError = (message) => {
        let error = container.querySelector('[data-complihance-error]');

        if (!error) {
            error = document.createElement('p');
            setData(error, 'complihance-error', 'true');
            error.className = 'complihance-error';
            form.appendChild(error);
        }

        error.textContent = message;
        error.classList.remove('complihance-hidden');
    };

    const clearFormError = () => {
        const error = container.querySelector('[data-complihance-error]');

        if (error) {
            error.classList.add('complihance-hidden');
        }
    };

    /**
     * @param {HTMLInputElement} categoryInput
     * @returns {void}
     */
    const syncCategoryFromVendorsSafely = (categoryInput) => {
        isSyncing = true;
        syncCategoryFromVendors(form, categoryInput);
        isSyncing = false;
    };

    /**
     * @param {HTMLInputElement} categoryInput
     * @returns {void}
     */
    const syncVendorsFromCategorySafely = (categoryInput) => {
        isSyncing = true;
        syncVendorsFromCategory(form, categoryInput);
        isSyncing = false;
    };

    const saveConsent = async () => {
        if (isSavingConsent) {
            pendingSave = true;

            return;
        }

        isSavingConsent = true;
        pendingSave = false;

        setFormLoading(true);
        clearFormError();

        try {
            const payload = getConsentPayload();

            const response = isPreferences
                ? await window.Complihance.updatePreferences(payload)
                : await window.Complihance.savePreferences(payload);

            updateConsentMode(payload.categories);
            refreshBlockedContent();

            if (isBanner) {
                container.remove();
                backdrop?.remove();
            }

            const successPayload = {
                response,
                payload,
                source: isPreferences ? 'preferences' : 'banner',
                container,
                form,
            };

            const wasHandled = window.Complihance.dispatchPreferenceUpdated?.(successPayload);

            if (!wasHandled) {
                const feedback = container.querySelector('[data-complihance-preferences-feedback]');

                if (feedback) {
                    feedback.classList.remove('complihance-hidden');
                }
            }

            window.dispatchEvent(new CustomEvent('complihance:consent-saved', {
                detail: response,
            }));
        } catch (error) {
            /** @type {ComplihanceConfig} */
            const config = window.ComplihanceConfig || {};

            const errorMessage =
                config.texts?.errors?.save_consent
                || 'Unable to save your cookie preferences. Please try again.';

            const errorPayload = {
                error,
                message: errorMessage,
                source: isPreferences ? 'preferences' : 'banner',
                container,
                form,
            };

            const wasHandled = window.Complihance.dispatchPreferenceUpdateError?.(errorPayload);

            if (wasHandled) {
                clearFormError();
            } else {
                showFormError(errorMessage);
            }

            window.dispatchEvent(new CustomEvent('complihance:consent-error', {
                detail: errorPayload,
            }));
        } finally {
            isSavingConsent = false;
            setFormLoading(false);

            if (pendingSave) {
                void saveConsent();
            }
        }
    };

    const scheduleAutoSave = () => {
        if (!isPreferences || isSyncing) {
            return;
        }

        clearTimeout(autoSaveTimeout);

        autoSaveTimeout = setTimeout(() => {
            void saveConsent();
        }, 350);
    };

    queryInputAll(form, '[data-complihance-category]').forEach((categoryInput) => {
        syncCategoryFromVendorsSafely(categoryInput);

        categoryInput.addEventListener('change', () => {
            syncVendorsFromCategorySafely(categoryInput);
            scheduleAutoSave();
        });
    });

    queryInputAll(form, '[data-complihance-vendor]').forEach((vendorInput) => {
        vendorInput.addEventListener('change', () => {
            const categoryInput = getCategoryInputByVendorInput(form, vendorInput);

            if (categoryInput) {
                syncCategoryFromVendorsSafely(categoryInput);
            }

            scheduleAutoSave();
        });
    });

    container.querySelector('[data-complihance-save]')?.addEventListener('click', () => {
        void saveConsent();
    });

    container.querySelector('[data-complihance-accept-all]')?.addEventListener('click', () => {
        queryInputAll(form, 'input[name="categories[]"]').forEach((input) => {
            input.checked = true;
            input.indeterminate = false;
        });

        queryInputAll(form, 'input[name="vendors[]"]').forEach((input) => {
            input.checked = true;
        });

        void saveConsent();
    });

    container.querySelector('[data-complihance-reject]')?.addEventListener('click', () => {
        queryInputAll(form, 'input[name="categories[]"]').forEach((input) => {
            input.checked = getData(input, 'required') === 'true';
            input.indeterminate = false;
        });

        queryInputAll(form, 'input[name="vendors[]"]').forEach((input) => {
            input.checked = false;
        });

        void saveConsent();
    });

    container.querySelectorAll('[data-complihance-revoke]').forEach((button) => {
        button.addEventListener('click', async () => {
            await window.Complihance.revoke();

            /** @type {ComplihanceConfig} */
            const config = window.ComplihanceConfig || {};

            window.location.href = config.afterRevokeRedirectUrl || '/';
        });
    });
}
