declare module "exifr" {
  // minimal typing for what you need
  export function gps(file: Blob): Promise<{
    latitude: number;
    longitude: number;
    altitude?: number;
  } | undefined>;

  // you can add more functions from exifr if you use them
}