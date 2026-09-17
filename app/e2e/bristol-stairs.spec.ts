import {test, expect, type Page} from '@playwright/test';
import {loginAsTestUser} from './helpers/auth';
import {mockGeolocation} from './helpers/browser';
import {fixturePath, randomBristolCoordinates} from './helpers/paths';
import {waitForMapMarkersLoaded} from './helpers/rooms';

async function readTotals(page: Page): Promise<{steps: number; flights: number}> {
  const text = await page.locator('body').innerText();
  const match = text.match(
    /There are currently entries for (\d+) steps in (\d+) flights of stairs\./
  );
  if (!match) {
    throw new Error('Could not find total steps and flights text on page.');
  }
  return {steps: Number(match[1]), flights: Number(match[2])};
}

async function clickFirstMarkerIfPresent(page: Page): Promise<boolean> {
  await waitForMapMarkersLoaded(page);
  const clicked = await page.evaluate(`(() => {
    if (typeof markers_loaded === "undefined" || markers_loaded !== true) {
      return false;
    }
    if (typeof markers === "undefined" || markers === null) {
      return false;
    }
    var firstMarker = null;
    markers.eachLayer(function(marker) {
      if (firstMarker === null) {
        firstMarker = marker;
      }
    });
    if (firstMarker === null) {
      return false;
    }
    firstMarker.fire("click");
    return true;
  })()`);
  if (clicked) {
    await page.waitForTimeout(500);
  }
  return Boolean(clicked);
}

test.describe('Bristol Stairs Map', () => {
  test('map page loads and displays correctly', async ({page}) => {
    await page.goto('/tools/bristol_stairs');
    await expect(page.getByText('A map of Bristol Stairs')).toBeVisible();
    await expect(page.locator('#bristol_stairs_map')).toBeVisible();
    await expect(
      page.locator('.leaflet-control-zoom-in').or(page.getByRole('button', {name: /Zoom in/i}))
    ).toBeVisible();
    await expect(
      page.locator('.leaflet-control-zoom-out').or(page.getByRole('button', {name: /Zoom out/i}))
    ).toBeVisible();
  });

  test('upload button is not visible when not logged in', async ({page}) => {
    await page.goto('/tools/bristol_stairs');
    await expect(page.getByRole('button', {name: 'Upload image'})).toHaveCount(0);
  });

  test('map displays markers when stairs data is available', async ({page}) => {
    await page.goto('/tools/bristol_stairs');
    await waitForMapMarkersLoaded(page);
    await expect(page.locator('#bristol_stairs_map')).toBeVisible();
    const markerCount = await page.evaluate(`(() => {
      if (typeof markers === "undefined" || markers === null) {
        return 0;
      }
      var count = 0;
      markers.eachLayer(function() {
        count++;
      });
      return count;
    })()`);
    if (Number(markerCount) > 0) {
      const hasLayers = await page.evaluate(
        `typeof map !== "undefined" && map !== null && typeof markers !== "undefined" && map.hasLayer(markers)`
      );
      expect(hasLayers).toBe(true);
    }
  });

  test('clicking a marker selects a stair', async ({page}) => {
    await page.goto('/tools/bristol_stairs');
    const clicked = await clickFirstMarkerIfPresent(page);
    if (!clicked) {
      test.skip();
      return;
    }
    await expect(page).toHaveURL(/\/tools\/bristol_stairs\/[^/]+$/);
    await expect(page.getByText('Click a marker on the map to view the stairs.')).toHaveCount(0);
  });

  test('upload stair image with 8 steps', async ({page}) => {
    await loginAsTestUser(page);
    await page.goto('/tools/bristol_stairs');
    const initial = await readTotals(page);
    await page.getByRole('button', {name: 'Upload image'}).click();
    await expect(page.getByRole('heading', {name: 'Drag a file here to upload'})).toBeVisible({
      timeout: 10_000,
    });

    const {latitude, longitude} = randomBristolCoordinates();
    await mockGeolocation(page, latitude, longitude);
    await page.locator('input[type="file"]').setInputFiles(fixturePath('stairs', 'stairs_test_a_8.jpeg'));

    const uploadButton = page.getByRole('button', {name: /^Upload$/});
    await expect(uploadButton).toBeVisible({timeout: 10_000});
    await uploadButton.click();
    await page.waitForURL(/\/tools\/bristol_stairs\/[^/]+$/, {timeout: 15_000});

    const stepsInput = page.locator('input[name="steps"], #steps');
    await stepsInput.fill('8');
    await stepsInput.dispatchEvent('input');
    await page.getByRole('button', {name: /Save/}).click();
    await page.waitForTimeout(2000);
    await expect(stepsInput).toHaveValue('8');

    const stairId = page.url().match(/\/tools\/bristol_stairs\/([^/]+)$/)?.[1];
    expect(stairId).toBeTruthy();

    let markerFound = false;
    for (let attempt = 1; attempt <= 15; attempt++) {
      await page.goto(`/tools/bristol_stairs?playwright_cache_bust=${Date.now()}_${attempt}`);
      await waitForMapMarkersLoaded(page);
      markerFound = Boolean(
        await page.evaluate(`(() => {
        if (typeof markers === "undefined" || markers === null) {
          return false;
        }
        var found = false;
        markers.eachLayer(function(marker) {
          if (String(marker.stairId) === ${JSON.stringify(stairId)}) {
            found = true;
          }
        });
        return found;
      })()`)
      );      if (markerFound) {
        break;
      }
      await page.waitForTimeout(1500);
    }
    expect(markerFound).toBe(true);

    await page.goto(`/tools/bristol_stairs?playwright_cache_bust=${Date.now()}_totals`);
    const after = await readTotals(page);
    expect(after.steps - initial.steps).toBe(8);
    expect(after.flights - initial.flights).toBe(1);
  });

  test('upload stair image with 9 steps', async ({page}) => {
    await loginAsTestUser(page);
    await page.goto('/tools/bristol_stairs');
    const initial = await readTotals(page);
    await page.getByRole('button', {name: 'Upload image'}).click();
    await expect(page.getByRole('heading', {name: 'Drag a file here to upload'})).toBeVisible({
      timeout: 10_000,
    });

    const {latitude, longitude} = randomBristolCoordinates();
    await mockGeolocation(page, latitude, longitude);
    await page.locator('input[type="file"]').setInputFiles(fixturePath('stairs', 'stairs_test_b_9.jpeg'));

    const uploadButton = page.getByRole('button', {name: /^Upload$/});
    await expect(uploadButton).toBeVisible({timeout: 10_000});
    await uploadButton.click();
    await page.waitForURL(/\/tools\/bristol_stairs\/[^/]+$/, {timeout: 15_000});

    const stepsInput = page.locator('input[name="steps"], #steps');
    await stepsInput.fill('9');
    await stepsInput.dispatchEvent('input');
    await page.getByRole('button', {name: /Save/}).click();
    await page.waitForTimeout(2000);
    await expect(stepsInput).toHaveValue('9');

    await page.goto(`/tools/bristol_stairs?playwright_cache_bust=${Date.now()}_totals`);
    const after = await readTotals(page);
    expect(after.steps - initial.steps).toBe(9);
    expect(after.flights - initial.flights).toBe(1);
  });

  test('update stair description', async ({page}) => {
    await loginAsTestUser(page);
    await page.goto('/tools/bristol_stairs');
    const clicked = await clickFirstMarkerIfPresent(page);
    if (!clicked) {
      test.skip();
      return;
    }
    await expect(page).toHaveURL(/\/tools\/bristol_stairs\/[^/]+$/);
    const description = 'Test description for Playwright test';
    await page.locator('#desc, input[name="desc"]').fill(description);
    await page.getByRole('button', {name: /Save/}).click();
    await expect(page.locator('#desc, input[name="desc"]')).toHaveValue(description);
  });

  test('update stair position', async ({page}) => {
    await loginAsTestUser(page);
    await page.goto('/tools/bristol_stairs');
    const clicked = await clickFirstMarkerIfPresent(page);
    if (!clicked) {
      test.skip();
      return;
    }
    await expect(page).toHaveURL(/\/tools\/bristol_stairs\/[^/]+$/);

    const {latitude, longitude} = randomBristolCoordinates();
    await page.getByRole('button', {name: 'Edit Position'}).click();
    await expect(page.getByRole('button', {name: 'Update Position'})).toBeVisible({timeout: 5_000});
    await page.waitForTimeout(500);

    await page.evaluate(`(() => {
      if (typeof map === "undefined" || map === null) {
        throw new Error("Map is not available");
      }
      map.setView([${latitude}, ${longitude}], map.getZoom());
      map.fire("move");
    })()`);

    await expect(page.getByText(new RegExp(`Position latitude ${latitude.toString().slice(0, 8)}`))).toBeVisible({
      timeout: 5_000,
    });
    await page.getByRole('button', {name: 'Update Position'}).click();
    await expect(page.getByRole('button', {name: 'Edit Position'})).toBeVisible({timeout: 10_000});
    await page.waitForTimeout(1000);

    const stairId = page.url().match(/\/tools\/bristol_stairs\/([^/]+)$/)?.[1];
    expect(stairId).toBeTruthy();

    await page.evaluate(`(() => {
      window.scrollTo(0, 0);
      if (typeof map !== "undefined" && map !== null) {
        map.setZoom(11);
        map.invalidateSize();
      }
    })()`);
    await page.waitForTimeout(1000);

    const position = await page.evaluate(`(() => {
      if (typeof markers === "undefined" || markers === null) {
        return {found: false};
      }
      var actualLat = 0;
      var actualLng = 0;
      var found = false;
      markers.eachLayer(function(marker) {
        if (String(marker.stairId) === ${JSON.stringify(stairId)}) {
          var latLng = marker.getLatLng();
          actualLat = latLng.lat;
          actualLng = latLng.lng;
          found = true;
        }
      });
      if (!found) {
        return {found: false};
      }
      return {
        found: true,
        isApproximate:
          Math.abs(actualLat - ${latitude}) < 0.0001 &&
          Math.abs(actualLng - ${longitude}) < 0.0001,
        actualLat: actualLat,
        actualLng: actualLng
      };
    })()`) as {found: boolean; isApproximate?: boolean; actualLat?: number; actualLng?: number};
    expect(position.found).toBe(true);
    expect(
      position.isApproximate,
      `expected (~${latitude}, ~${longitude}) got (${position.actualLat}, ${position.actualLng})`
    ).toBe(true);
  });

  test('GET update endpoint returns error message', async ({page}) => {
    await loginAsTestUser(page);
    await page.goto('/api/bristol_stairs_update/test_stair_123');
    await expect(
      page.getByText(
        'This endpoint expects a POST request. Please send a POST request instead of GET.'
      )
    ).toBeVisible();
  });
});
