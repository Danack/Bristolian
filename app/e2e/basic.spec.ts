import {test, expect} from '@playwright/test';

test.describe('Basic', () => {
  test('Home page', async ({page}) => {
    await page.goto('/');
    await expect(page.getByText('Eldon House music')).toBeVisible();
  });
});
