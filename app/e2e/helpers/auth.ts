import {expect, type Page} from '@playwright/test';

const TEST_USERNAME = 'testing@example.com';
const TEST_PASSWORD = 'testing';

/**
 * Log in as the standard test user, or no-op if already logged in.
 */
export async function loginAsTestUser(page: Page): Promise<void> {
  await page.goto('/login', {waitUntil: 'domcontentloaded', timeout: 60_000});

  const path = new URL(page.url()).pathname;
  if (path !== '/login') {
    await waitForFrontendLoginState(page, true);
    return;
  }

  await page.fill('input[name="username"], #username', TEST_USERNAME);
  await page.fill('input[name="password"], #password', TEST_PASSWORD);
  await page.getByRole('button', {name: 'Login'}).click();
  await page.waitForURL((url) => url.pathname === '/tools', {timeout: 15_000});
  await waitForFrontendLoginState(page, true);
}

export async function logout(page: Page): Promise<void> {
  await page.goto('/logout', {waitUntil: 'domcontentloaded', timeout: 60_000});
  await page.waitForLoadState('domcontentloaded');
}

async function waitForFrontendLoginState(page: Page, expectedLoggedIn: boolean): Promise<void> {
  if (expectedLoggedIn) {
    await expect(page.getByRole('link', {name: 'Logout'})).toBeVisible({timeout: 10_000});
    return;
  }

  await expect(page.getByRole('link', {name: 'Logout'})).toHaveCount(0, {timeout: 10_000});
}
