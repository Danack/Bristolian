import {test, expect} from '@playwright/test';
import {loginAsTestUser} from '../helpers/auth';
import {clickFirstRoomLink, waitForRoomVideosPanel} from '../helpers/rooms';

test.describe('Room videos panel', () => {
  test.beforeEach(async ({page}) => {
    await loginAsTestUser(page);
  });

  test('Videos tab loads room videos panel', async ({page}) => {
    await page.goto('/rooms');
    await clickFirstRoomLink(page);
    await waitForRoomVideosPanel(page);
    await expect(page.getByRole('button', {name: 'Refresh'})).toBeVisible();
    await expect(page.getByRole('button', {name: 'Add video'})).toBeVisible();
  });

  test('Room videos search panel can be used', async ({page}) => {
    await page.goto('/rooms');
    await clickFirstRoomLink(page);
    await waitForRoomVideosPanel(page);
    await page.locator('.room_videos_panel_react button.room_content_search_toggle').click();
    await expect(page.getByRole('button', {name: 'Clear'})).toBeVisible();
    await page
      .locator('.room_videos_panel_react .room_content_search_form input[placeholder="Filter by title"]')
      .fill('unlikely_title_xyz_12345');
    await page
      .locator('.room_videos_panel_react .room_search_actions button', {hasText: 'Clear'})
      .click();
    await expect(page.locator('.room_videos_panel_react').getByText(/Showing \d+ videos/)).toBeVisible();
  });

  test('Add video modal accepts a valid YouTube URL', async ({page}) => {
    await page.goto('/rooms');
    await clickFirstRoomLink(page);
    await waitForRoomVideosPanel(page);
    await page.getByRole('button', {name: 'Add video'}).click();
    await page
      .locator('.room_video_add_modal input[type="text"]')
      .fill('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
    await expect(
      page.locator('.room_video_add_modal button', {hasText: 'Continue'}).and(page.locator(':not([disabled])'))
    ).toBeVisible();
    await page.locator('.room_video_add_modal_close').click();
    await page.getByRole('button', {name: 'Refresh'}).click();
  });
});
