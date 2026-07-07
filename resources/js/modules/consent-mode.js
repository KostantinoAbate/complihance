/**
 * @typedef {Record<string, boolean>} CategoryLookup
 */

/**
 * @typedef {(command: string, action: string, params: Record<string, string>) => void} Gtag
 */

/**
 * Converts a category list into a lookup object.
 *
 * @param {string[]|CategoryLookup|null|undefined} categories Selected categories.
 * @returns {CategoryLookup}
 */
function categoriesToObject(categories) {
    if (!categories) {
        return {};
    }

    if (!Array.isArray(categories)) {
        return categories;
    }

    /** @type {CategoryLookup} */
    const lookup = {};

    categories.forEach((category) => {
        lookup[category] = true;
    });

    return lookup;
}

/**
 * Updates Google Consent Mode according to the selected consent categories.
 *
 * @param {string[]|CategoryLookup|null|undefined} categories Selected categories.
 * @returns {void}
 */
export function updateConsentMode(categories) {
    /** @type {unknown} */
    const maybeGtag = window['gtag'];

    if (typeof maybeGtag !== 'function') {
        return;
    }

    /** @type {Gtag} */
    const gtag = maybeGtag;

    const selectedCategories = categoriesToObject(categories);
    const analyticsGranted = selectedCategories.analytics === true;
    const marketingGranted = selectedCategories.marketing === true;
    const functionalGranted = selectedCategories.functional === true;

    gtag('consent', 'update', {
        analytics_storage: analyticsGranted ? 'granted' : 'denied',
        ad_storage: marketingGranted ? 'granted' : 'denied',
        ad_user_data: marketingGranted ? 'granted' : 'denied',
        ad_personalization: marketingGranted ? 'granted' : 'denied',
        functionality_storage: functionalGranted ? 'granted' : 'denied',
        personalization_storage: functionalGranted ? 'granted' : 'denied',
        security_storage: 'granted',
    });
}
