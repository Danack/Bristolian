import {expect, type Page} from '@playwright/test';

export async function clickFirstRoomLink(page: Page): Promise<void> {
  const roomLink = page.locator('table a').first();
  await expect(roomLink).toBeVisible({timeout: 10_000});
  await roomLink.click();
  await page.waitForLoadState('domcontentloaded');
}

export async function clickRoomTab(page: Page, tabLabel: string): Promise<void> {
  await page.locator(`button.room_tab_label`, {hasText: tabLabel}).click();
}

export async function waitForRoomTabs(page: Page): Promise<void> {
  await expect(page.locator('.room_tab_strip')).toBeVisible({timeout: 10_000});
  await expect(page.locator('button.room_tab_label').first()).toBeVisible();
}

export async function waitForRoomFilesPanel(page: Page): Promise<void> {
  const filesTab = page.locator('button.room_tab_label', {hasText: 'Files'});
  if (await filesTab.count()) {
    await filesTab.click();
  }
  await expect(page.locator('.room_files_panel_react')).toBeVisible({timeout: 10_000});
  await expect(page.locator('.room_files_panel_react button', {hasText: 'Refresh'})).toBeVisible({
    timeout: 10_000,
  });
}

export async function waitForRoomVideosPanel(page: Page): Promise<void> {
  const videosTab = page.locator('button.room_tab_label', {hasText: 'Videos'});
  if (await videosTab.count()) {
    await videosTab.click();
  }
  await expect(page.locator('.room_videos_panel_react')).toBeVisible({timeout: 10_000});
  await expect(page.locator('.room_videos_panel_react button', {hasText: 'Refresh'})).toBeVisible({
    timeout: 10_000,
  });
}

export async function waitForMapMarkersLoaded(page: Page): Promise<void> {
  try {
    // markers_loaded is a top-level `let` in bristol_stairs_map.js (not on window).
    await page.waitForFunction(
      'typeof markers_loaded !== "undefined" && markers_loaded === true',
      {timeout: 10_000}
    );
  }
  catch {
    // No stairs / markers flag never set — continue
  }
}
