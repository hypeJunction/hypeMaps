import { Page } from '@playwright/test';
import mysql from 'mysql2/promise';

const DB_CONFIG = {
  host: process.env.ELGG_DB_HOST || 'db',
  port: Number(process.env.ELGG_DB_PORT || 3306),
  user: process.env.ELGG_DB_USER || 'elgg',
  password: process.env.ELGG_DB_PASS || 'elgg',
  database: process.env.ELGG_DB_NAME || 'elgg',
};

export async function loginAs(page: Page, username: string, password: string = 'testpass123') {
  await page.goto('/login');
  // Elgg renders two login forms — a hidden header dropdown and a visible sidebar form.
  // Target the visible sidebar form (.elgg-module-aside) to avoid the hidden dropdown.
  await page.locator('.elgg-module-aside input[name="username"]').fill(username);
  await page.locator('.elgg-module-aside input[name="password"]').fill(password);
  await page.locator('.elgg-module-aside').getByRole('button', { name: /log in/i }).click();
  await page.waitForLoadState('networkidle');
}

export async function queryDb(sql: string, params: any[] = []) {
  const conn = await mysql.createConnection(DB_CONFIG);
  const [rows] = await conn.execute(sql, params);
  await conn.end();
  return rows as any[];
}

export async function getPluginSetting(pluginId: string, name: string): Promise<string | null> {
  const rows = await queryDb(
    `SELECT ps.value FROM elgg_private_settings ps
     JOIN elgg_entities e ON ps.entity_guid = e.guid
     JOIN elgg_metadata m ON m.entity_guid = e.guid AND m.name = 'title'
     WHERE e.type = 'object' AND e.subtype = 'plugin'
     AND m.value = ? AND ps.name = ?`,
    [pluginId, name]
  );
  return rows.length ? rows[0].value : null;
}
