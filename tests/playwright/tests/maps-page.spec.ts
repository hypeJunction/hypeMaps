import { test, expect } from '@playwright/test';
import { loginAs } from '../helpers/elgg';

test.describe('hypeMaps page handler', () => {
  test('maps root page loads (logged out — public access)', async ({ page }) => {
    const response = await page.goto('/maps');
    // Either 200 (public) or 302/forbidden — we assert it does not 500
    const status = response?.status() ?? 0;
    expect(status).toBeLessThan(500);
  });

  test('maps page loads when logged in', async ({ page }) => {
    await loginAs(page, 'testuser');
    const response = await page.goto('/maps');
    const status = response?.status() ?? 0;
    expect(status).toBeLessThan(500);
    // Page should render — look for a page shell element
    await expect(page.locator('body')).toBeVisible();
  });

  test('maps/search path loads without 500', async ({ page }) => {
    await loginAs(page, 'testuser');
    const response = await page.goto('/maps/search');
    const status = response?.status() ?? 0;
    expect(status).toBeLessThan(500);
  });

  test('invalid group guid returns not-found, not 500', async ({ page }) => {
    await loginAs(page, 'testuser');
    const response = await page.goto('/maps/group/999999999');
    const status = response?.status() ?? 0;
    expect(status).toBeLessThan(500);
  });
});
