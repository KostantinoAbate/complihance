/**
 * @param {Document|Element} root
 */
export function initAccordions(root = document) {
    root.querySelectorAll('[data-complihance-accordion]').forEach((accordion) => {
        const trigger = accordion.querySelector('[data-complihance-accordion-trigger]');
        const panel = accordion.querySelector('[data-complihance-accordion-panel]');
        const icon = accordion.querySelector('[data-complihance-accordion-icon]');

        if (!trigger || !panel || !icon) return;

        trigger.addEventListener('click', () => {
            const isOpen = trigger.getAttribute('aria-expanded') === 'true';

            trigger.setAttribute('aria-expanded', String(!isOpen));
            panel.classList.toggle('complihance-accordion__panel--open', !isOpen);
            icon.textContent = isOpen ? '+' : '−';
        });
    });
}
