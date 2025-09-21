<?php

namespace Bristolian\AppController;

use Bristolian\Exception\BristolianException;
use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Filesystem\RoomFileFilesystem;
use Bristolian\Response\BristolianFileResponse;
use Bristolian\Response\StoredFileErrorResponse;
use Bristolian\Session\UserSession;
use SlimDispatcher\Response\ImageResponse;
use SlimDispatcher\Response\JsonResponse;
use Bristolian\Repo\BristolStairsRepo\BristolStairsRepo;
use Bristolian\Filesystem\BristolStairsFilesystem;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo;
use Bristolian\Parameters\BristolStairsInfoParams;
use Bristolian\Parameters\BristolStairsPositionParams;
use Bristolian\SiteHtml\ExtraAssets;
use VarMap\VarMap;

class BristolStairs
{
    public function update_stairs_info_get(){

        return "This is a GET end point. You probably meant to POST.";
    }

    public function update_stairs_info(
        UserSession $appSession,
        BristolStairsRepo $bristolStairsRepo,
        VarMap $varMap
    ) {
        $stairs_info_params = BristolStairsInfoParams::createFromVarMap($varMap);
        $bristolStairsRepo->updateStairInfo($stairs_info_params);

        return new JsonResponse(['success' => true]);
    }



    public function update_stairs_position(
        UserSession $appSession,
        BristolStairsRepo $bristolStairsRepo,
        VarMap $varMap
    ) {
        $stairs_position_params = BristolStairsPositionParams::createFromVarMap($varMap);
        $bristolStairsRepo->updateStairPosition($stairs_position_params);

        return new JsonResponse(['success' => true]);
    }



    public function stairs_page(ExtraAssets $extraAssets): string
    {
        $extraAssets->addCSS("/css/leaflet/leaflet.1.7.1.css");
        $extraAssets->addCSS("/css/leaflet/MarkerCluster.1.4.1.css");
        $extraAssets->addCSS("/css/leaflet/MarkerCluster.Default.1.5.0.min.css");
        $extraAssets->addCSS("/css/bristol_stairs_map.css");

        $extraAssets->addJS("/js/leaflet/leaflet.1.7.1.js");
        $extraAssets->addJS("/js/leaflet/leaflet.markercluster.1.4.1.js");
        $extraAssets->addJS("/js/bristol_stairs_map.js");

        $content = "<h1>A map of Bristol Stairs</h1>";
        $content .= <<< HTML

<div class="bristol_stairs">
  <div class="bristol_stairs_map_class" id="bristol_stairs_map"></div>
  <div class="bristol_stairs_panel"></div>
</div>


<div>
<h2>About</h2>
<p>Bristol has many steps. This page is an attempt to document all of them, to be able to answer the question, how many steps does Bristol have?</p>


<h2>Qualification rules</h2>

<ol>
<li>Stairs need to be on a place where members of the public will walk through, to another location. i.e. steps leading up to a house don't qualify.</li>
<li>There need to be at least two steps between the top and bottom.</li>
<li>The steps need to be within about one meter of each other. Some paths (e.g. in Brandon Hill Park) have steps in them, to make the gradient of the path be not too steep, but they are too far apart to qualify as a flight of stairs.</li>
<li>Stairs cannot be inside a building.</li>
<li>The stairs have to be in Bristol. We use some discretion here for the definition of Bristol. </li>
</ol>

</div>
HTML;

        return $content;
    }





    function getImage(
        BristolStairsFilesystem $roomFilesystem,
        LocalCacheFilesystem $localCacheFilesystem,
        BristolStairImageStorageInfoRepo $bristolStairImageStorageInfoRepo,
        string $stored_stair_image_file_id
    ) {
        $fileDetails = $bristolStairImageStorageInfoRepo->getById($stored_stair_image_file_id);

        $normalized_name = $fileDetails->normalized_name;
        if ($localCacheFilesystem->fileExists($normalized_name) === true) {
            // TODO - why is contents unused?
            $contents = $localCacheFilesystem->read($normalized_name);
        }
        else {
            try {
//                $contents = $roomFilesystem->read($normalized_name);
                $stream = $roomFilesystem->readStream($normalized_name);
            }
            catch (\League\Flysystem\UnableToReadFile $unableToReadFile) {
                return new StoredFileErrorResponse($normalized_name);
            }

            if (!$stream) {
                return new StoredFileErrorResponse($normalized_name);
            }

//            $localCacheFilesystem->write($normalized_name, $contents);
            $localCacheFilesystem->writeStream($normalized_name, $stream);
        }

        $localCacheFilename = $localCacheFilesystem->getFullPath() . "/" . $normalized_name;

        $filenameToServe = realpath($localCacheFilename);

        if ($filenameToServe === false) {
            throw new BristolianException(
                "Failed to retrieve file from object store [" . $normalized_name . "]."
            );
        }




        return new \Bristolian\Response\StreamingResponse(
            $filenameToServe
        );
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
