<?php

namespace Bristolian\AppController;

use SlimDispatcher\Response\ImageResponse;
use SlimDispatcher\Response\JsonResponse;
use Bristolian\Repo\BristolStairsRepo\BristolStairsRepo;

function randomFloat($min, $max) {
    return $min + lcg_value() * ($max - $min);
}


class BristolStairs
{
    function getImage(string $bristol_stairs_id)
    {
        return new ImageResponse(
            file_get_contents(__DIR__ . '/../../../stairs_temp/IMG_6339.JPG'),
            ImageResponse::TYPE_JPG
        );
    }

    function getDetails(string $bristol_stairs_id)
    {
        $stairs_info = [
            'description' => 'Yo, these are some stairs',
            'image_filename' => 'IMG_6339.JPG',
            'steps' => 42,
        ];

        return new JsonResponse([
            'status' => 'ok',
            'data' => $stairs_info,
        ]);
    }


    function getData(BristolStairsRepo $stairs_repo)
    {


        $markers = $stairs_repo->getAllStairsInfo();


        return new JsonResponse([
            'status' => 'ok',
            'data' => $markers,
        ]);
    }
}
