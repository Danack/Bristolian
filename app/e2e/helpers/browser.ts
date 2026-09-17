import type {Page} from '@playwright/test';

/** Mock navigator.geolocation.getCurrentPosition before a file upload. */
export async function mockGeolocation(
  page: Page,
  latitude: number,
  longitude: number
): Promise<void> {
  await page.evaluate(
    ({latitude, longitude}) => {
      if (!navigator.geolocation) {
        return;
      }
      navigator.geolocation.getCurrentPosition = (success) => {
        if (success) {
          success({
            coords: {
              latitude,
              longitude,
              accuracy: 10,
              altitude: null,
              altitudeAccuracy: null,
              heading: null,
              speed: null,
            },
            timestamp: Date.now(),
          } as GeolocationPosition);
        }
      };
    },
    {latitude, longitude}
  );
}

/** Stub clipboard.writeText so copy actions succeed in headless Chromium. */
export async function stubClipboardWriteText(page: Page): Promise<void> {
  await page.evaluate(() => {
    if (!navigator.clipboard) {
      (navigator as Navigator & {clipboard: Clipboard}).clipboard = {} as Clipboard;
    }
    navigator.clipboard.writeText = () => Promise.resolve();
  });
}
