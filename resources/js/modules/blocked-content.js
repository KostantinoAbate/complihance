/**
 * @typedef {object} ComplihanceVendorConfig
 * @property {string} key
 * @property {string|null} [name]
 * @property {string|null} [label]
 * @property {string|null} [vendor]
 * @property {string|null} [category]
 */

/**
 * @typedef {object} ComplihanceCategoryConfig
 * @property {string} key
 * @property {boolean} [required]
 */

/**
 * @typedef {object} ComplihanceBlockedContentText
 * @property {string} [title]
 * @property {string} [description]
 * @property {string} [button]
 */

/**
 * @typedef {object} ComplihanceConfig
 * @property {{
 *     placeholder?: ComplihanceBlockedContentText
 * }} [blockedContent]
 */

/**
 * @typedef {object} ConsentData
 * @property {Array<string>} [accepted_categories]
 * @property {Array<string>|Object<string, boolean>} [vendors]
 */

/**
 * @typedef {object} CurrentConsentResponse
 * @property {ConsentData} [consent]
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
 * @param {string} key
 * @returns {boolean}
 */
function hasData(element, key) {
    return element.hasAttribute(`data-${key}`);
}

/**
 * Returns the vendors configured in the frontend Complihance configuration.
 *
 * @returns {Array<ComplihanceVendorConfig>}
 */
function getConfiguredVendors() {
    return window.Complihance.getConfigurationSync()?.vendors || [];
}

/**
 * Normalizes a vendor identifier to its configured vendor key.
 *
 * @param {string|null} vendor Vendor key, name, label, or alias.
 * @returns {string|null}
 */
function normalizeVendorKey(vendor) {
    if (!vendor) {
        return null;
    }

    const configuredVendor = getConfiguredVendors().find((item) => {
        return item.key === vendor
            || item.name === vendor
            || item.label === vendor
            || item.vendor === vendor;
    });

    return configuredVendor?.key || vendor;
}

/**
 * Infers the consent category associated with a vendor.
 *
 * @param {string|null} vendor Vendor key, name, label, or alias.
 * @returns {string|null}
 */
function inferCategoryFromVendor(vendor) {
    if (!vendor) {
        return null;
    }

    const vendorKey = normalizeVendorKey(vendor);

    const configuredVendor = getConfiguredVendors().find((item) => {
        return item.key === vendorKey;
    });

    return configuredVendor?.category || null;
}

/**
 * Returns the category required by a blocked content element.
 *
 * @param {Element} element Blocked content element.
 * @returns {string|null}
 */
function getBlockedContentCategory(element) {
    return getData(element, 'complihance-category');
}

/**
 * Returns the vendor required by a blocked content element.
 *
 * @param {Element} element Blocked content element.
 * @returns {string|null}
 */
function getBlockedContentVendor(element) {
    return normalizeVendorKey(getData(element, 'complihance-vendor'));
}

/**
 * Returns the special requirement declared by a blocked content element.
 *
 * @param {Element} element Blocked content element.
 * @returns {string|null}
 */
function getBlockedContentRequirement(element) {
    return getData(element, 'complihance-requires');
}

/**
 * Determines whether inline consent is enabled for a blocked content element.
 *
 * @param {Element} element Blocked content element.
 * @returns {boolean}
 */
function hasInlineConsent(element) {
    return getData(element, 'complihance-inline-consent') !== 'false';
}

/**
 * Stores existing src attributes in data attributes and removes them to prevent loading.
 *
 * @param {Element} element Blocked content element.
 * @returns {void}
 */
function captureExistingSource(element) {
    const src = element.getAttribute('src');

    if (!hasData(element, 'complihance-src') && src) {
        setData(element, 'complihance-src', src);
    }

    if (src) {
        element.removeAttribute('src');
    }

    element.querySelectorAll('[src]').forEach((child) => {
        const childSrc = child.getAttribute('src');

        if (!hasData(child, 'complihance-src') && childSrc) {
            setData(child, 'complihance-src', childSrc);
        }

        child.removeAttribute('src');
        setData(child, 'complihance-loaded', 'false');
    });
}

/**
 * Determines whether a blocked content element can be loaded.
 *
 * @param {Element} element Blocked content element.
 * @returns {boolean}
 */
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

/**
 * Returns the placeholder text used for blocked content.
 *
 * @returns {{title: string, description: string, button: string}}
 */
function getBlockedContentText() {
    /** @type {ComplihanceConfig} */
    const config = window.ComplihanceConfig || {};

    const text = config.blockedContent?.placeholder || {};

    return {
        title: text.title || 'Blocked content',
        description: text.description || 'This content requires consent.',
        button: text.button || 'Accept and view',
    };
}

/**
 * Removes src attributes from nested elements previously captured by Complihance.
 *
 * @param {Element} element Blocked content element.
 * @returns {void}
 */
function blockNestedSources(element) {
    element.querySelectorAll('[data-complihance-src]').forEach((child) => {
        child.removeAttribute('src');
        setData(child, 'complihance-loaded', 'false');
    });
}

/**
 * Restores src attributes for nested elements previously captured by Complihance.
 *
 * @param {Element} element Blocked content element.
 * @returns {void}
 */
function loadNestedSources(element) {
    element.querySelectorAll('[data-complihance-src]').forEach((child) => {
        const src = getData(child, 'complihance-src');

        if (!src) {
            return;
        }

        if (child.getAttribute('src') !== src) {
            child.setAttribute('src', src);
        }

        setData(child, 'complihance-loaded', 'true');
    });
}

/**
 * Accepts the consent required by a blocked content element and refreshes the UI.
 *
 * @param {Element} element Blocked content element.
 * @returns {Promise<void>}
 */
async function acceptInlineConsent(element) {
    const vendor = getBlockedContentVendor(element);
    const requirement = getBlockedContentRequirement(element);

    let category = getBlockedContentCategory(element);

    if (!category && vendor) {
        category = inferCategoryFromVendor(vendor);
    }

    /** @type {CurrentConsentResponse} */
    const currentConsent = await window.Complihance.refreshConsent();

    const configuration = await window.Complihance.getConfiguration();

    const currentCategories = currentConsent?.consent?.accepted_categories || [];
    const categories = new Set(currentCategories);

    if (requirement === 'all-optional') {
        /** @type {Array<ComplihanceCategoryConfig>} */
        const configuredCategories = configuration.categories || [];

        configuredCategories
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
        /** @type {Array<ComplihanceVendorConfig>} */
        const configuredVendors = configuration.vendors || [];

        configuredVendors
            .filter((configuredVendor) => configuredVendor.category === category)
            .map((configuredVendor) => configuredVendor.key)
            .filter(Boolean)
            .forEach((vendorKey) => {
                selectedVendors.add(vendorKey);
            });
    }

    await window.Complihance.updatePreferences({
        categories: Array.from(categories),
        vendors: Array.from(selectedVendors),
    });

    refreshBlockedContent();
}

/**
 * Creates and inserts a placeholder before a blocked content element.
 *
 * @param {Element} element Blocked content element.
 * @returns {void}
 */
function createBlockedContentPlaceholder(element) {
    captureExistingSource(element);

    if (
        element.previousElementSibling
        && getData(element.previousElementSibling, 'complihance-placeholder-element') === 'true'
    ) {
        element.hidden = true;
        blockNestedSources(element);

        return;
    }

    const text = getBlockedContentText();
    const placeholder = document.createElement('div');

    setData(placeholder, 'complihance-placeholder-element', 'true');
    placeholder.className = 'complihance-blocked-content';

    const rect = element.getBoundingClientRect();

    if (rect.width > 0) {
        placeholder.style.setProperty('--complihance-blocked-content-width', `${rect.width}px`);
    }

    if (rect.height > 0) {
        placeholder.style.setProperty('--complihance-blocked-content-height', `${rect.height}px`);
    }

    const inner = document.createElement('div');
    inner.className = 'complihance-blocked-content__inner';

    const title = document.createElement('p');
    title.className = 'complihance-blocked-content__title';
    title.textContent = text.title;

    const description = document.createElement('p');
    description.className = 'complihance-blocked-content__description';
    description.textContent = text.description;

    inner.appendChild(title);
    inner.appendChild(description);

    if (hasInlineConsent(element)) {
        const button = document.createElement('button');

        button.type = 'button';
        button.className = 'complihance-blocked-content__button';
        setData(button, 'complihance-inline-consent-button', '');
        button.textContent = text.button;

        button.addEventListener('click', () => {
            void acceptInlineConsent(element);
        });

        inner.appendChild(button);
    }

    placeholder.appendChild(inner);

    blockNestedSources(element);

    element.parentNode?.insertBefore(placeholder, element);
    element.hidden = true;
}

/**
 * Removes the placeholder associated with a blocked content element.
 *
 * @param {Element} element Blocked content element.
 * @returns {void}
 */
function removeBlockedContentPlaceholder(element) {
    const placeholder = element.previousElementSibling;

    if (
        placeholder
        && getData(placeholder, 'complihance-placeholder-element') === 'true'
    ) {
        placeholder.remove();
    }

    element.hidden = false;
}

/**
 * Loads a blocked content element after consent requirements are satisfied.
 *
 * @param {Element} element Blocked content element.
 * @returns {void}
 */
export function loadBlockedContent(element) {
    const src = getData(element, 'complihance-src');

    if (src && element.getAttribute('src') !== src) {
        element.setAttribute('src', src);
    }

    setData(element, 'complihance-loaded', 'true');

    loadNestedSources(element);
    removeBlockedContentPlaceholder(element);
}

/**
 * Refreshes all blocked content elements on the current page.
 *
 * @returns {void}
 */
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

/**
 * Observes DOM changes and refreshes blocked content when new elements are added.
 *
 * @returns {MutationObserver}
 */
export function observeBlockedContent() {
    const observer = new MutationObserver((mutations) => {
        let shouldRefresh = false;

        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (!(node instanceof Element)) {
                    return;
                }

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
