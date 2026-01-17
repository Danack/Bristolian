<?php

declare(strict_types = 1);

/**
 * @param string $filename
 * @return array{0:float, 1:float}|null
 */
function get_image_gps(string $filename): null|array
{
    $exif_data = exif_read_data($filename);

    $required_fields = [
        "GPSLongitude",
        'GPSLongitudeRef',
        "GPSLatitude",
        'GPSLatitudeRef',
    ];

    foreach ($required_fields as $required_field) {
        if (array_key_exists($required_field, $exif_data) !== true) {
            return null;
        }
    }

    $longitude = getGps($exif_data["GPSLongitude"], $exif_data['GPSLongitudeRef']);
    $latitude = getGps($exif_data["GPSLatitude"], $exif_data['GPSLatitudeRef']);

    return [$latitude, $longitude];
}


/**
 * @param string[] $exifCoord
 * @param string $hemi
 * @return float|int
 */
function getGps(array $exifCoord, string $hemi)
{

    $degrees = count($exifCoord) > 0 ? gps2Num($exifCoord[0]) : 0;
    $minutes = count($exifCoord) > 1 ? gps2Num($exifCoord[1]) : 0;
    $seconds = count($exifCoord) > 2 ? gps2Num($exifCoord[2]) : 0;

    $flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;

    return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
}

function gps2Num(string $coordPart): float
{

    $parts = explode('/', $coordPart);

    // PHPStan complains about this code. tbh, think I need some unit tests
//    if (count($parts) <= 0) {
//        return 0;
//    }

    if (count($parts) == 1) {
        return (float)$parts[0];
    }

    return floatval($parts[0]) / floatval($parts[1]);
}
