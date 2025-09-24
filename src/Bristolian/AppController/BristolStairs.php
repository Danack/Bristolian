<?php

namespace Bristolian\AppController;

use Bristolian\Exception\BristolianException;
use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Filesystem\RoomFileFilesystem;
use Bristolian\Response\BristolianFileResponse;
use Bristolian\Response\StoredFileErrorResponse;
use Bristolian\Service\ObjectStore\BristolianStairImageObjectStore;
use Bristolian\Service\ObjectStore\FileObjectStore;
use Bristolian\Session\UserSession;
use Bristolian\UploadedFiles\UploadedFile;
use SlimDispatcher\Response\ImageResponse;
use SlimDispatcher\Response\JsonResponse;
use Bristolian\Repo\BristolStairsRepo\BristolStairsRepo;
use Bristolian\Response\StreamingResponse;
use Bristolian\Filesystem\BristolStairsFilesystem;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo;
use Bristolian\Parameters\BristolStairsInfoParams;
use Bristolian\Parameters\BristolStairsPositionParams;
use Bristolian\UserUploadedFile\UserSessionFileUploadHandler;
use Bristolian\SiteHtml\ExtraAssets;
use SlimDispatcher\Response\StubResponse;
use SlimDispatcher\Response\JsonNoCacheResponse;
use Bristolian\Service\BristolStairImageStorage\UploadError;
use Bristolian\Service\BristolStairImageStorage\BristolStairImageStorage;


use VarMap\VarMap;

class BristolStairs
{

    public const BRISTOL_STAIRS_FILE_UPLOAD_FORM_NAME = "stair_file";

    public function update_stairs_info_get(): string
    {

        return "This is a GET end point. You probably meant to POST.";
    }

    public function update_stairs_info(
        UserSession $appSession,
        BristolStairsRepo $bristolStairsRepo,
        VarMap $varMap
    ): JsonResponse {
        $stairs_info_params = BristolStairsInfoParams::createFromVarMap($varMap);
        $bristolStairsRepo->updateStairInfo($stairs_info_params);

        return new JsonResponse(['success' => true]);
    }



    public function update_stairs_position(
        UserSession $appSession,
        BristolStairsRepo $bristolStairsRepo,
        VarMap $varMap
    ): JsonResponse {
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


    public function getImage(
        BristolStairsFilesystem $roomFilesystem,
        LocalCacheFilesystem $localCacheFilesystem,
        BristolStairImageStorageInfoRepo $bristolStairImageStorageInfoRepo,
        string $stored_stair_image_file_id
    ): StreamingResponse|StoredFileErrorResponse {
        $fileDetails = $bristolStairImageStorageInfoRepo->getById($stored_stair_image_file_id);

        $normalized_name = $fileDetails->normalized_name;
        if ($localCacheFilesystem->fileExists($normalized_name) !== true) {
            try {
                $stream = $roomFilesystem->readStream($normalized_name);
            }
            catch (\League\Flysystem\UnableToReadFile $unableToReadFile) {
                return new StoredFileErrorResponse($normalized_name);
            }
            $localCacheFilesystem->writeStream($normalized_name, $stream);
        }

        $localCacheFilename = $localCacheFilesystem->getFullPath() . "/" . $normalized_name;
        $filenameToServe = realpath($localCacheFilename);

        if ($filenameToServe === false) {
            throw new BristolianException(
                "Failed to retrieve file from object store [" . $normalized_name . "]."
            );
        }

        return new StreamingResponse(
            $filenameToServe
        );
    }


    public function getData(BristolStairsRepo $stairs_repo): JsonResponse
    {
        $markers = $stairs_repo->getAllStairsInfo();

        return new JsonResponse([
            'status' => 'ok',
            'data' => $markers,
        ]);
    }


    public function handleFileUpload(
        BristolStairImageStorage $bristolStairImageStorage,
        UserSession                       $appSession,
        UserSessionFileUploadHandler      $usfuh,
    ): StubResponse {

//        // TODO - check user logged in
//        if ($appSession->isLoggedIn() !== true) {
//            $data = ['not logged in' => true];
//            return new JsonResponse($data, [], 400);
//        }

        // Get the user uploaded file.
        $fileOrResponse = $usfuh->fetchUploadedFile(self::BRISTOL_STAIRS_FILE_UPLOAD_FORM_NAME);
        if ($fileOrResponse instanceof StubResponse) {
            return $fileOrResponse;
        }

        $storedFileOrError = $bristolStairImageStorage->storeFileForUser(
            $appSession->getUserId(),
            $fileOrResponse,
            get_supported_bristolian_stair_image_extensions(),
        );

        if ($storedFileOrError instanceof UploadError) {
            $data = [
                'result' => 'error',
                'error' => $storedFileOrError->error_message
            ];
            // todo - change to helper function
            return new JsonNoCacheResponse($data, [], 400);
        }

        $response = [
            'result' => 'success',
//            'file_id' => $storedFileOrError->fileStorageId
        ];

        $response = new JsonNoCacheResponse($response);

        return $response;
    }
}
