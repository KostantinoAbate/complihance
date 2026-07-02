import './complihance-api';
import '../css/complihance.css';

import { initAccordions } from './modules/accordions';
import { initConsentForm } from './modules/consent-form';
import {
    refreshBlockedContent,
    loadBlockedContent,
    observeBlockedContent,
} from './modules/blocked-content';
import { updateConsentMode } from './modules/consent-mode';

(function () {
    window.Complihance = {
        ...(window.Complihance || {}),
        ui: {
            refreshBlockedContent,
            loadBlockedContent,
            updateConsentMode,
        },
    };

    document
        .querySelectorAll(
            '[data-complihance-banner], [data-complihance-preferences]'
        )
        .forEach((container) => {
            initAccordions(container);
            initConsentForm(container);
        });

    window.Complihance.onConsentChanged(() => {
        refreshBlockedContent();
    });

    Promise.all([
        window.Complihance.getConsent(),
        window.Complihance.getPackageConfiguration(),
    ])
        .catch(() => {
            // ignore bootstrap errors
        })
        .finally(() => {
            refreshBlockedContent();
            observeBlockedContent();
        });
})();
