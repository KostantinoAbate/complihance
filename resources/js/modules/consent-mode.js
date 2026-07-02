function categoriesArrayToObject(categories) {
    return categories.reduce((carry, category) => {
        carry[category] = true;
        return carry;
    }, {});
}

export function updateConsentMode(categories) {
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
