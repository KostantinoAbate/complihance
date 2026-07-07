import './complihance-api';
import '../scss/complihance.scss';

import { initAccordions } from './modules/accordions';
import { initConsentForm } from './modules/consent-form';
import {
    refreshBlockedContent,
    loadBlockedContent,
    observeBlockedContent,
} from './modules/blocked-content';
import { updateConsentMode } from './modules/consent-mode';

/**
 * @typedef {object} ComplihanceUi
 * @property {typeof refreshBlockedContent} refreshBlockedContent
 * @property {typeof loadBlockedContent} loadBlockedContent
 * @property {typeof updateConsentMode} updateConsentMode
 */

/**
 * Bootstraps the default Complihance frontend UI.
 *
 * @returns {void}
 */
(function () {
    /** @type {ComplihanceUi} */
    const ui = {
        refreshBlockedContent,
        loadBlockedContent,
        updateConsentMode,
    };

    window['Complihance'] = {
        ...(window['Complihance'] || {}),
        ui,
    };

    document
        .querySelectorAll(
            '[data-complihance-banner], [data-complihance-preferences]'
        )
        .forEach((container) => {
            initAccordions(container);
            initConsentForm(container);
        });

    window['Complihance'].onConsentChanged(() => {
        refreshBlockedContent();
    });

    Promise.all([
        window['Complihance'].getConsent(),
        window['Complihance'].getConfiguration(),
    ])
        .catch(() => {
            // Ignore bootstrap errors and continue with UI initialization.
        })
        .finally(() => {
            refreshBlockedContent();
            observeBlockedContent();
        });
})();
