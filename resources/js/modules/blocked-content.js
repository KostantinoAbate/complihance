function getConfiguredVendors() {
    return window.Complihance.getConfigurationSync()?.vendors || [];
}

function normalizeVendorKey(vendor) {
    if (!vendor) return null;

    const configuredVendor = getConfiguredVendors().find((item) => {
        return item.key === vendor
            || item.name === vendor
            || item.label === vendor
            || item.vendor === vendor;
    });

    return configuredVendor?.key || vendor;
}

function inferCategoryFromVendor(vendor) {
    if (!vendor) return null;

    const vendorKey = normalizeVendorKey(vendor);

    const configuredVendor = getConfiguredVendors().find((item) => item.key === vendorKey);

    return configuredVendor?.category || null;
}

function getBlockedContentCategory(element) {
    return element.dataset.complihanceCategory || null;
}

function getBlockedContentVendor(element) {
    return normalizeVendorKey(element.dataset.complihanceVendor || null);
}

function getBlockedContentRequirement(element) {
    return element.dataset.complihanceRequires || null;
}

function hasInlineConsent(element) {
    return element.dataset.complihanceInlineConsent !== 'false';
}

function captureExistingSource(element) {
    if (!element.dataset.complihanceSrc && element.getAttribute('src')) {
        element.dataset.complihanceSrc = element.getAttribute('src');
    }

    if (element.getAttribute('src')) {
        element.removeAttribute('src');
    }

    element.querySelectorAll('[src]').forEach((child) => {
        if (!child.dataset.complihanceSrc && child.getAttribute('src')) {
            child.dataset.complihanceSrc = child.getAttribute('src');
        }

        child.removeAttribute('src');
        child.dataset.complihanceLoaded = 'false';
    });
}

function canLoadBlockedContent(element) {
    const vendor = getBlockedContentVendor(element);
    const requirement = getBlockedContentRequirement(element);

    let category = getBlockedContentCategory(element);

    if (!category && vendor) {
        category = inferCategoryFromVendor(vendor);
    }

    if (requirement === 'all-optional') {
        return window.Complihance.canUseAllOptionalCategories();
    }

    if (!category) return false;

    if (!window.Complihance.canUse(category)) return false;

    if (!vendor) return true;

    return window.Complihance.canUseVendor(vendor);
}

function getBlockedContentText() {
    const text = window.ComplihanceConfig?.blockedContent?.placeholder || {};

    return {
        title: text.title || 'Blocked content',
        description: text.description || 'This content requires consent.',
        button: text.button || 'Accept and view',
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

        if (!src) return;

        if (child.getAttribute('src') !== src) {
            child.setAttribute('src', src);
        }

        child.dataset.complihanceLoaded = 'true';
    });
}

async function acceptInlineConsent(element) {
    const vendor = getBlockedContentVendor(element);
    const requirement = getBlockedContentRequirement(element);

    let category = getBlockedContentCategory(element);

    if (!category && vendor) {
        category = inferCategoryFromVendor(vendor);
    }

    const currentConsent = await window.Complihance.refreshConsent();
    const configuration = await window.Complihance.getConfiguration();

    const currentCategories = currentConsent?.consent?.accepted_categories || [];
    const categories = new Set(currentCategories);

    if (requirement === 'all-optional') {
        configuration.categories
            .filter((configuredCategory) => configuredCategory.required !== true)
            .map((configuredCategory) => configuredCategory.key)
            .filter(Boolean)
            .forEach((configuredCategoryKey) => {
                categories.add(configuredCategoryKey);
            });
    }

    if (category) {
        categories.add(category);
    }

    let vendors = currentConsent?.consent?.vendors || [];

    if (!Array.isArray(vendors)) {
        vendors = Object.entries(vendors)
            .filter(([, accepted]) => accepted === true)
            .map(([vendorKey]) => vendorKey);
    }

    const selectedVendors = new Set(
        vendors.map(normalizeVendorKey).filter(Boolean)
    );

    if (vendor) {
        selectedVendors.add(vendor);
    } else if (category) {
        configuration.vendors
            .filter((configuredVendor) => configuredVendor.category === category)
            .map((configuredVendor) => configuredVendor.key)
            .filter(Boolean)
            .forEach((vendorKey) => {
                selectedVendors.add(vendorKey);
            });
    }

    const payload = {
        categories: Array.from(categories),
        vendors: Array.from(selectedVendors),
    };

    console.log('[Complihance inline consent payload]', payload);

    const response = await window.Complihance.updatePreferences(payload);

    console.log('[Complihance inline consent response]', response);

    refreshBlockedContent();
}

function createBlockedContentPlaceholder(element) {
    captureExistingSource(element);

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
            <p class="complihance-blocked-content__title">${text.title}</p>
            <p class="complihance-blocked-content__description">${text.description}</p>
            ${
        hasInlineConsent(element)
            ? `<button type="button" class="complihance-blocked-content__button" data-complihance-inline-consent-button>${text.button}</button>`
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

export function loadBlockedContent(element) {
    const src = element.dataset.complihanceSrc;

    if (src && element.getAttribute('src') !== src) {
        element.setAttribute('src', src);
    }

    element.dataset.complihanceLoaded = 'true';

    loadNestedSources(element);
    removeBlockedContentPlaceholder(element);
}

export function refreshBlockedContent() {
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

export function observeBlockedContent() {
    const observer = new MutationObserver((mutations) => {
        let shouldRefresh = false;

        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (!(node instanceof Element)) return;

                if (
                    node.matches('[data-complihance-blocked]')
                    || node.querySelector('[data-complihance-blocked]')
                ) {
                    shouldRefresh = true;
                }
            });
        });

        if (shouldRefresh) {
            refreshBlockedContent();
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });

    return observer;
}
