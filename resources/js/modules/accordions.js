/**
 * Initializes Complihance accordion components within the given root element.
 *
 * @param {Document|Element} [root=document] Root element used to search for accordions.
 * @returns {void}
 */
export function initAccordions(root = document) {
    root.querySelectorAll('[data-complihance-accordion]').forEach((accordion) => {
        if (accordion.dataset.complihanceAccordionInitialized === 'true') {
            return;
        }

        const trigger = accordion.querySelector('[data-complihance-accordion-trigger]');
        const panel = accordion.querySelector('[data-complihance-accordion-panel]');
        const icon = accordion.querySelector('[data-complihance-accordion-icon]');

        if (!trigger || !panel) {
            return;
        }

        accordion.dataset.complihanceAccordionInitialized = 'true';

        trigger.addEventListener('click', () => {
            const isOpen = trigger.getAttribute('aria-expanded') === 'true';

            trigger.setAttribute('aria-expanded', String(!isOpen));
            panel.classList.toggle('complihance-accordion__panel--open', !isOpen);

            if (icon) {
                icon.textContent = isOpen ? '+' : '−';
            }
        });
    });
}
