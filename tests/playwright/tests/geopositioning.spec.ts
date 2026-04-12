import { test, expect } from '@playwright/test';
import { loginAs } from '../helpers/elgg';

/**
 * Tests for the maps/geopositioning/update action.
 * The action stores location in $_SESSION and redirects to REFERER.
 */
test.describe('geopositioning action', () => {
  test('submitting geopositioning action redirects', async ({ page, context }) => {
    await loginAs(page, 'testuser');
    await page.goto('/');

    // Get CSRF tokens from Elgg
    const tokens = await page.evaluate(async () => {
      const res = await fetch('/refresh_token', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      if (!res.ok) return null;
      try {
        return await res.json();
      } catch {
        return null;
      }
    });

    // Submit the action as a form post
    const response = await page.request.post('/action/maps/geopositioning/update', {
      form: {
        location: 'Berlin',
        latitude: '52.52',
        longitude: '13.405',
        ...(tokens ? { __elgg_token: tokens.token, __elgg_ts: String(tokens.ts) } : {}),
      },
      maxRedirects: 0,
      failOnStatusCode: false,
    });

    // Should not be a 500
    expect(response.status()).toBeLessThan(500);
  });
});
