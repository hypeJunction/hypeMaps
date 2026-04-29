import { test, expect } from '@playwright/test';
import { loginAs } from '../helpers/elgg';

/**
 * Admin plugin settings page smoke test.
 *
 * NOTE: This plugin has a security-sensitive settings action that
 * serializes arbitrary input and passes values to the plugin that later
 * invoke unserialize(). The migration MUST replace this with json-based
 * storage. This test only asserts the admin page renders without errors.
 */
test.describe('hypeMaps admin settings', () => {
  test('admin plugin settings page renders', async ({ page }) => {
    await loginAs(page, 'admin');
    const response = await page.goto('/admin/plugin_settings/hypemaps');
    const status = response?.status() ?? 0;
    expect(status).toBeLessThan(500);

    // If the plugin is active, the form should be there
    const form = page.locator('form[action*="action/hypeMaps/settings/save"], form[action*="action/plugins/settings/save"]');
    const count = await form.count();
    // Don't fail if plugin is inactive — just assert the page rendered
    if (count > 0) {
      await expect(form.first()).toBeVisible();
    }
  });
});
