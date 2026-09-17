import * as path from 'path';

/** Absolute path to a fixture under test/fixtures (container and host share /var/app). */
export function fixturePath(...relativeParts: string[]): string {
  return path.join('/var/app/test/fixtures', ...relativeParts);
}

/** Bristol bounding box used for random stair GPS in Behat. */
export const BRISTOL_BOUNDS = {
  southWest: {lat: 51.3325441, lng: -2.8657612},
  northEast: {lat: 51.6014432, lng: -2.2960328},
};

export function randomBristolCoordinates(): {latitude: number; longitude: number} {
  const latitude =
    BRISTOL_BOUNDS.southWest.lat +
    Math.random() * (BRISTOL_BOUNDS.northEast.lat - BRISTOL_BOUNDS.southWest.lat);
  const longitude =
    BRISTOL_BOUNDS.southWest.lng +
    Math.random() * (BRISTOL_BOUNDS.northEast.lng - BRISTOL_BOUNDS.southWest.lng);
  return {latitude, longitude};
}
