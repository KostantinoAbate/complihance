import './complihance-api';
import '../css/complihance.css';

(function () {
    function initAccordions(root = document) {
        root.querySelectorAll('[data-complihance-accordion]').forEach((accordion) => {
            const trigger = accordion.querySelector('[data-complihance-accordion-trigger]');
            const panel = accordion.querySelector('[data-complihance-accordion-panel]');
            const icon = accordion.querySelector('[data-complihance-accordion-icon]');

            if (!trigger || !panel || !icon) return;

            trigger.addEventListener('click', () => {
                const isOpen = trigger.getAttribute('aria-expanded') === 'true';

                trigger.setAttribute('aria-expanded', String(!isOpen));
                panel.classList.toggle('grid-rows-[1fr]', !isOpen);
                panel.classList.toggle('grid-rows-[0fr]', isOpen);
                icon.textContent = isOpen ? '+' : '−';
            });
        });
    }

    function categoriesArrayToObject(categories) {
        return categories.reduce((carry, category) => {
            carry[category] = true;
            return carry;
        }, {});
    }

    function updateConsentMode(categories) {
        if (typeof window.gtag !== 'function') return;

        const selectedCategories = categoriesArrayToObject(categories);

        window.gtag('consent', 'update', {
            analytics_storage: selectedCategories.analytics ? 'granted' : 'denied',
            ad_storage: selectedCategories.marketing ? 'granted' : 'denied',
            ad_user_data: selectedCategories.marketing ? 'granted' : 'denied',
            ad_personalization: selectedCategories.marketing ? 'granted' : 'denied',
            functionality_storage: selectedCategories.functional ? 'granted' : 'denied',
            personalization_storage: selectedCategories.functional ? 'granted' : 'denied',
            security_storage: 'granted',
        });
    }

    function getBlockedContentCategory(element) {
        return element.dataset.complihanceCategory || null;
    }

    function getBlockedContentVendor(element) {
        return element.dataset.complihanceVendor || null;
    }

    function getBlockedContentPlaceholder(element) {
        return element.dataset.complihancePlaceholder || 'default';
    }

    function hasInlineConsent(element) {
        return element.dataset.complihanceInlineConsent !== 'false';
    }

    function canLoadBlockedContent(element) {
        const category = getBlockedContentCategory(element);
        const vendor = getBlockedContentVendor(element);

        if (!category) {
            return false;
        }

        if (!window.Complihance.canUse(category)) {
            return false;
        }

        if (!vendor) {
            return true;
        }

        return window.Complihance.canUseVendor(vendor);
    }

    function getBlockedContentText(element) {
        const category = getBlockedContentCategory(element);
        const vendor = getBlockedContentVendor(element);
        const placeholder = getBlockedContentPlaceholder(element);

        const placeholders = window.ComplihanceConfig?.blockedContent?.placeholders || {};
        const text = placeholders[placeholder] || placeholders.default || {};

        return {
            title: text.title || 'Contenuto bloccato',
            description: (text.description || 'Questo contenuto richiede il consenso :category.')
                .replace(':category', category || '')
                .replace(':vendor', vendor || ''),
            button: text.button || 'Accetta e visualizza',
        };
    }

    function blockNestedSources(element) {
        element.querySelectorAll('[data-complihance-src]').forEach((child) => {
            child.removeAttribute('src');
            child.dataset.complihanceLoaded = 'false';
        });
    }

    function loadNestedSources(element) {
        element.querySelectorAll('[data-complihance-src]').forEach((child) => {
            const src = child.dataset.complihanceSrc;

            if (!src) {
                return;
            }

            if (child.getAttribute('src') !== src) {
                child.setAttribute('src', src);
            }

            child.dataset.complihanceLoaded = 'true';
        });
    }

    async function acceptInlineConsent(element) {
        const category = getBlockedContentCategory(element);
        const vendor = getBlockedContentVendor(element);

        const currentConsent = await window.Complihance.refreshConsent();

        const categories = new Set(currentConsent?.consent?.accepted_categories || []);

        if (category) {
            categories.add(category);
        }

        let vendors = currentConsent?.consent?.vendors || [];

        if (!Array.isArray(vendors)) {
            vendors = Object.entries(vendors)
                .filter(([, accepted]) => accepted === true)
                .map(([vendorKey]) => vendorKey);
        }

        const selectedVendors = new Set(vendors);

        if (vendor) {
            selectedVendors.add(vendor);
        }

        await window.Complihance.updatePreferences({
            categories: Array.from(categories),
            vendors: Array.from(selectedVendors),
        });

        refreshBlockedContent();
    }

    function createBlockedContentPlaceholder(element) {
        if (element.previousElementSibling?.dataset?.complihancePlaceholderElement === 'true') {
            element.hidden = true;
            blockNestedSources(element);
            return;
        }

        const text = getBlockedContentText(element);

        const placeholder = document.createElement('div');

        placeholder.dataset.complihancePlaceholderElement = 'true';
        placeholder.className = 'complihance-blocked-content';

        const rect = element.getBoundingClientRect();

        if (rect.width > 0) {
            placeholder.style.setProperty('--complihance-blocked-content-width', `${rect.width}px`);
        }

        if (rect.height > 0) {
            placeholder.style.setProperty('--complihance-blocked-content-height', `${rect.height}px`);
        }

        placeholder.innerHTML = `
        <div class="complihance-blocked-content__inner">
            <p class="complihance-blocked-content__title">
                ${text.title}
            </p>

            <p class="complihance-blocked-content__description">
                ${text.description}
            </p>

            ${
            hasInlineConsent(element)
                ? `<button type="button" class="complihance-blocked-content__button" data-complihance-inline-consent-button>
                        ${text.button}
                    </button>`
                : ''
        }
        </div>
    `;

        placeholder
            .querySelector('[data-complihance-inline-consent-button]')
            ?.addEventListener('click', () => {
                acceptInlineConsent(element);
            });

        blockNestedSources(element);

        element.parentNode.insertBefore(placeholder, element);
        element.hidden = true;
    }

    function removeBlockedContentPlaceholder(element) {
        const placeholder = element.previousElementSibling;

        if (placeholder?.dataset?.complihancePlaceholderElement === 'true') {
            placeholder.remove();
        }

        element.hidden = false;
    }

    function loadBlockedContent(element) {
        const src = element.dataset.complihanceSrc;

        if (src && element.getAttribute('src') !== src) {
            element.setAttribute('src', src);
        }

        element.dataset.complihanceLoaded = 'true';

        loadNestedSources(element);

        removeBlockedContentPlaceholder(element);
    }

    function refreshBlockedContent() {
        document
            .querySelectorAll('[data-complihance-blocked]')
            .forEach((element) => {
                if (canLoadBlockedContent(element)) {
                    loadBlockedContent(element);
                } else {
                    createBlockedContentPlaceholder(element);
                }
            });
    }

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

    function initConsentForm(container) {
        const form = container.querySelector('[data-complihance-form]');
        const backdrop = document.querySelector('[data-complihance-backdrop]');

        if (!form) return;

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

        const saveConsent = async () => {
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
    }

    window.Complihance = {
        ...(window.Complihance || {}),
        updateConsentMode,
        refreshBlockedContent,
        loadBlockedContent,
    };

    document
        .querySelectorAll('[data-complihance-banner], [data-complihance-preferences]')
        .forEach((container) => {
            initAccordions(container);
            initConsentForm(container);
        });

    window.Complihance.onConsentChanged(() => {
        refreshBlockedContent();
    });

    window.Complihance.getConsent()
        .then(() => {
            refreshBlockedContent();
        })
        .catch(() => {
            refreshBlockedContent();
        });
})();
