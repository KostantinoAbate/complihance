export default async function setup({ page }) {
    await page.addInitScript(() => {
        window.localStorage.setItem('setup_script_ran', 'yes');
    });
}
