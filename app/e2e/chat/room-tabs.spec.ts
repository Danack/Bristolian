import {test, expect} from '@playwright/test';
import {loginAsTestUser} from '../helpers/auth';
import {clickFirstRoomLink, clickRoomTab, waitForRoomTabs} from '../helpers/rooms';

test.describe('Room tabs URL anchors', () => {
  test.beforeEach(async ({page}) => {
    await loginAsTestUser(page);
  });

  test('Selecting a room tab updates the URL hash', async ({page}) => {
    await page.goto('/rooms');
    await clickFirstRoomLink(page);
    await waitForRoomTabs(page);
    await clickRoomTab(page, 'Files');
    await expect(page).toHaveURL(/#files$/);
    const filesTab = page.locator('button.room_tab_label', {hasText: 'Files'});
    await expect(filesTab).toHaveAttribute('aria-selected', 'true');
    await expect(filesTab).toHaveClass(/active/);
  });

  test('Opening a room URL with a hash shows that tab', async ({page}) => {
    await page.goto('/rooms');
    await clickFirstRoomLink(page);
    await waitForRoomTabs(page);
    await clickRoomTab(page, 'Links');
    await expect(page).toHaveURL(/#links$/);
    await page.reload();
    await waitForRoomTabs(page);
    await expect(page).toHaveURL(/#links$/);
    const linksTab = page.locator('button.room_tab_label', {hasText: 'Links'});
    await expect(linksTab).toHaveAttribute('aria-selected', 'true');
    await expect(linksTab).toHaveClass(/active/);
  });
});
