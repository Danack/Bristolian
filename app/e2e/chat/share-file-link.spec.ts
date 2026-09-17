import {test, expect} from '@playwright/test';
import {loginAsTestUser, logout} from '../helpers/auth';
import {clickFirstRoomLink, waitForRoomFilesPanel} from '../helpers/rooms';

test.describe('Share file link to chat', () => {
  test('Post to chat button is visible when logged in', async ({page}) => {
    await loginAsTestUser(page);
    await page.goto('/rooms');
    await clickFirstRoomLink(page);
    await waitForRoomFilesPanel(page);
    const fileLinks = page.locator('.room_files_panel_react table a');
    if ((await fileLinks.count()) === 0) {
      return;
    }
    await expect(
      page.locator('.room_files_panel_react button', {hasText: 'Post to chat'}).first()
    ).toBeVisible();
  });

  test('Post to chat button is not visible when logged out', async ({page}) => {
    await loginAsTestUser(page);
    await logout(page);
    await page.goto('/rooms');
    await clickFirstRoomLink(page);
    await waitForRoomFilesPanel(page);
    await expect(page.locator('.room_files_panel_react button', {hasText: 'Post to chat'})).toHaveCount(0);
  });

  test('Post to chat button inserts text at cursor position', async ({page}) => {
    await loginAsTestUser(page);
    await page.goto('/rooms');
    await clickFirstRoomLink(page);
    await waitForRoomFilesPanel(page);

    const fileLinks = page.locator('.room_files_panel_react table a');
    if ((await fileLinks.count()) === 0) {
      return;
    }

    const messageInput = page.locator('.message-input');
    await messageInput.fill('foobar');
    await messageInput.evaluate((element) => {
      const input = element as HTMLInputElement | HTMLTextAreaElement;
      const position = 3; // after "foo"
      input.setSelectionRange(position, position);
      input.focus();
    });

    await page.locator('.room_files_panel_react button', {hasText: 'Post to chat'}).first().click();

    const value = await messageInput.inputValue();
    expect(value.startsWith('foo')).toBe(true);
    expect(value.endsWith('bar')).toBe(true);
    expect(value).toMatch(/\[.+\]\(https?:\/\/.+\)/);
  });
});
