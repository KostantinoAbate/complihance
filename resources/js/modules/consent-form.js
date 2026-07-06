import { updateConsentMode } from './consent-mode';
import { refreshBlockedContent } from './blocked-content';

function getCategoryInputByVendorInput(form, vendorInput) {
    return form.querySelector(
        `[data-complihance-category="${vendorInput.dataset.complihanceVendorCategory}"]`
    );
}

function getVendorInputsByCategoryInput(form, categoryInput) {
    return Array.from(form.querySelectorAll(
        `[data-complihance-vendor-category="${categoryInput.dataset.complihanceCategory}"]`
    ));
}

function syncCategoryFromVendors(form, categoryInput) {
    const vendors = getVendorInputsByCategoryInput(form, categoryInput);

    if (!vendors.length) return;

    const checkedVendors = vendors.filter((vendor) => vendor.checked);

    categoryInput.checked = checkedVendors.length === vendors.length;
    categoryInput.indeterminate = checkedVendors.length > 0 && checkedVendors.length < vendors.length;
}

function syncVendorsFromCategory(form, categoryInput) {
    const vendors = getVendorInputsByCategoryInput(form, categoryInput);

    vendors.forEach((vendor) => {
        if (!vendor.disabled) {
            vendor.checked = categoryInput.checked;
        }
    });

    categoryInput.indeterminate = false;
}

export function initConsentForm(container) {
    if (container.dataset.complihanceInitialized === 'true') return;

    container.dataset.complihanceInitialized = 'true';

    const form = container.querySelector('[data-complihance-form]');
    const backdrop = document.querySelector('[data-complihance-backdrop]');

    if (!form) return;

    let isSavingConsent = false;
    let pendingSave = false;
    let autoSaveTimeout = null;
    let isSyncing = false;

    const isPreferences = container.matches('[data-complihance-preferences]');
    const isBanner = container.matches('[data-complihance-banner]');

    const getSelectedCategories = () => {
        return Array.from(form.querySelectorAll('input[name="categories[]"]'))
            .filter((input) => input.checked || input.indeterminate || input.dataset.required === 'true')
            .map((input) => input.value);
    };

    const getSelectedVendors = () => {
        return Array.from(form.querySelectorAll('input[name="vendors[]"]:checked'))
            .map((input) => input.value);
    };

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

    const setFormLoading = (loading) => {
        /**
         * Nel partial preferenze evitiamo di disabilitare i checkbox:
         * il flash spesso nasce proprio da disabled -> enabled ad ogni autosave.
         */
        if (isPreferences) {
            container
                .querySelector('[data-complihance-save]')
                ?.toggleAttribute('disabled', loading);

            return;
        }

        form.querySelectorAll('button, input').forEach((element) => {
            if (element.dataset.required === 'true') return;

            element.disabled = loading;
        });
    };

    const showFormError = (message) => {
        let error = container.querySelector('[data-complihance-error]');

        if (!error) {
            error = document.createElement('p');
            error.dataset.complihanceError = 'true';
            error.className = 'complihance-error';
            form.appendChild(error);
        }

        error.textContent = message;
        error.classList.remove('complihance-hidden');
    };

    const clearFormError = () => {
        container
            .querySelector('[data-complihance-error]')
            ?.classList.add('complihance-hidden');
    };

    const syncCategoryFromVendorsSafely = (categoryInput) => {
        isSyncing = true;
        syncCategoryFromVendors(form, categoryInput);
        isSyncing = false;
    };

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

            const feedback = container.querySelector('[data-complihance-preferences-feedback]');

            if (feedback) {
                feedback.classList.remove('complihance-hidden');
            }

            window.dispatchEvent(new CustomEvent('complihance:consent-saved', {
                detail: response,
            }));
        } catch (error) {
            showFormError(
                window.ComplihanceConfig?.texts?.errors?.save_consent
                || 'Unable to save your cookie preferences. Please try again.'
            );

            window.dispatchEvent(new CustomEvent('complihance:consent-error', {
                detail: error,
            }));
        } finally {
            isSavingConsent = false;
            setFormLoading(false);

            if (pendingSave) {
                saveConsent();
            }
        }
    };

    const scheduleAutoSave = () => {
        if (!isPreferences || isSyncing) return;

        clearTimeout(autoSaveTimeout);

        autoSaveTimeout = setTimeout(() => {
            saveConsent();
        }, 350);
    };

    form.querySelectorAll('[data-complihance-category]').forEach((categoryInput) => {
        syncCategoryFromVendorsSafely(categoryInput);

        categoryInput.addEventListener('change', () => {
            syncVendorsFromCategorySafely(categoryInput);
            scheduleAutoSave();
        });
    });

    form.querySelectorAll('[data-complihance-vendor]').forEach((vendorInput) => {
        vendorInput.addEventListener('change', () => {
            const categoryInput = getCategoryInputByVendorInput(form, vendorInput);

            if (categoryInput) {
                syncCategoryFromVendorsSafely(categoryInput);
            }

            scheduleAutoSave();
        });
    });

    container.querySelector('[data-complihance-save]')?.addEventListener('click', () => {
        saveConsent();
    });

    container.querySelector('[data-complihance-accept-all]')?.addEventListener('click', () => {
        form.querySelectorAll('input[name="categories[]"]').forEach((input) => {
            input.checked = true;
            input.indeterminate = false;
        });

        form.querySelectorAll('input[name="vendors[]"]').forEach((input) => {
            input.checked = true;
        });

        saveConsent();
    });

    container.querySelector('[data-complihance-reject]')?.addEventListener('click', () => {
        form.querySelectorAll('input[name="categories[]"]').forEach((input) => {
            input.checked = input.dataset.required === 'true';
            input.indeterminate = false;
        });

        form.querySelectorAll('input[name="vendors[]"]').forEach((input) => {
            input.checked = false;
        });

        saveConsent();
    });

    container.querySelectorAll('[data-complihance-revoke]').forEach((button) => {
        button.addEventListener('click', async () => {
            await window.Complihance.revoke();

            window.location.href = window.ComplihanceConfig?.afterRevokeRedirectUrl || '/';
        });
    });
}
