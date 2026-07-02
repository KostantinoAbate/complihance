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

function forgetCookie(name) {
    document.cookie = `${name}=; Max-Age=0; path=/`;
}

export function initConsentForm(container) {
    const form = container.querySelector('[data-complihance-form]');
    const backdrop = document.querySelector('[data-complihance-backdrop]');

    if (!form) return;

    let isSavingConsent = false;

    const getSelectedCategories = () => {
        return Array.from(form.querySelectorAll('input[name="categories[]"]'))
            .filter((input) => input.checked || input.indeterminate)
            .map((input) => input.value);
    };

    const getSelectedVendors = () => {
        return Array.from(form.querySelectorAll('input[name="vendors[]"]:checked'))
            .map((input) => input.value);
    };

    const getConsentPayload = () => {
        const payload = {
            categories: getSelectedCategories(),
        };

        if (form.querySelector('[data-complihance-vendor]')) {
            payload.vendors = getSelectedVendors();
        }

        return payload;
    };

    const setFormLoading = (loading) => {
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
            error.className = 'mt-3 text-sm text-red-600';
            form.appendChild(error);
        }

        error.textContent = message;
        error.classList.remove('hidden');
    };

    const clearFormError = () => {
        container
            .querySelector('[data-complihance-error]')
            ?.classList.add('hidden');
    };

    const saveConsent = async () => {
        if (isSavingConsent) return;

        isSavingConsent = true;
        setFormLoading(true);
        clearFormError();

        try {
            const payload = getConsentPayload();

            const response = container.matches('[data-complihance-preferences]')
                ? await window.Complihance.updatePreferences(payload)
                : await window.Complihance.savePreferences(payload);

            updateConsentMode(payload.categories);
            refreshBlockedContent();

            if (container.matches('[data-complihance-banner]')) {
                container.remove();
                backdrop?.remove();
            }

            const feedback = container.querySelector('[data-complihance-preferences-feedback]');

            if (feedback) {
                feedback.classList.remove('hidden');
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
        }
    };

    form.querySelectorAll('[data-complihance-category]').forEach((categoryInput) => {
        syncCategoryFromVendors(form, categoryInput);

        categoryInput.addEventListener('change', () => {
            syncVendorsFromCategory(form, categoryInput);

            if (container.matches('[data-complihance-preferences]')) {
                saveConsent();
            }
        });
    });

    form.querySelectorAll('[data-complihance-vendor]').forEach((vendorInput) => {
        vendorInput.addEventListener('change', () => {
            const categoryInput = getCategoryInputByVendorInput(form, vendorInput);

            if (categoryInput) {
                syncCategoryFromVendors(form, categoryInput);
            }

            if (container.matches('[data-complihance-preferences]')) {
                saveConsent();
            }
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
            const categoryInput = getCategoryInputByVendorInput(form, input);

            input.checked = categoryInput?.dataset.required === 'true';
        });

        saveConsent();
    });

    document.querySelectorAll('[data-complihance-revoke]').forEach((button) => {
        button.addEventListener('click', async () => {
            await window.Complihance.revoke();

            forgetCookie('complihance_consent');
            forgetCookie('complihance_anonymous_id');

            window.location.href = window.ComplihanceConfig?.afterRevokeRedirectUrl || '/';
        });
    });
}
