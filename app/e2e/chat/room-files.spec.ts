import {test, expect} from '@playwright/test';
import {loginAsTestUser} from '../helpers/auth';
import {clickFirstRoomLink, waitForRoomFilesPanel} from '../helpers/rooms';

test.describe('Room files panel', () => {
  test.beforeEach(async ({page}) => {
    await loginAsTestUser(page);
  });

  test('Files tab loads room files panel', async ({page}) => {
    await page.goto('/rooms');
    await clickFirstRoomLink(page);
    await waitForRoomFilesPanel(page);
    await expect(page.getByText('Files').first()).toBeVisible();
    await expect(page.getByRole('button', {name: 'Refresh'})).toBeVisible();
  });

  test('Room files search panel can be opened and cleared', async ({page}) => {
    await page.goto('/rooms');
    await clickFirstRoomLink(page);
    await waitForRoomFilesPanel(page);
    await page.locator('.room_files_panel_react button.room_content_search_toggle').click();
    await expect(page.getByRole('button', {name: 'Clear'})).toBeVisible();
    await page
      .locator('.room_files_panel_react .room_content_search_form input[placeholder="Filter by name"]')
      .fill('unlikely_filename_xyz_12345');
    await page
      .locator('.room_files_panel_react .room_search_actions button', {hasText: 'Clear'})
      .click();
    await page.locator('.room_files_panel_react button.room_content_search_close').click();
    await expect(page.getByRole('button', {name: 'Refresh'})).toBeVisible();
  });

  test('Edit file panel can be opened for a file when files exist', async ({page}) => {
    await page.goto('/rooms');
    await clickFirstRoomLink(page);
    await waitForRoomFilesPanel(page);
    const fileLinks = page.locator('.room_files_panel_react table a');
    if ((await fileLinks.count()) === 0) {
      return;
    }
    await page.locator('.room_files_panel_react button', {hasText: 'Edit'}).first().click();
    await expect(page.getByRole('heading', {name: 'Edit file'})).toBeVisible();
  });
});
