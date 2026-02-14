<?php

namespace Bristolian\AppController;

use Bristolian\Exception\BristolianException;
use Bristolian\Filesystem\BristolStairsFilesystem;
use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Parameters\BristolStairsGpsParams;
use Bristolian\Parameters\BristolStairsInfoParams;
use Bristolian\Parameters\BristolStairsPositionParams;
use Bristolian\Parameters\OpenmapNearbyParams;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo;
use Bristolian\Repo\BristolStairsRepo\BristolStairsRepo;
use Bristolian\Response\EndpointAccessedViaGetResponse;
use Bristolian\Response\StoredFileErrorResponse;
use Bristolian\Response\StreamingResponse;
use Bristolian\Response\SuccessResponse;
use Bristolian\Response\Typed\GetBristolStairsResponse;
use Bristolian\Response\UploadBristolStairsImageResponse;
use Bristolian\Service\BristolStairImageStorage\BristolStairImageStorage;
use Bristolian\Service\BristolStairImageStorage\UploadError;
use Bristolian\Session\UserSession;
use Bristolian\SiteHtml\ExtraAssets;
use Bristolian\UserUploadedFile\UserSessionFileUploadHandler;
use Bristolian\Model\Generated\BristolStairInfo;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\StubResponse;
use VarMap\VarMap;

class BristolStairs
{
    public const BRISTOL_STAIRS_FILE_UPLOAD_FORM_NAME = "stair_file";

    public function update_stairs_info_get(): EndpointAccessedViaGetResponse
    {
        return new EndpointAccessedViaGetResponse();
    }

    public function update_stairs_info(
        UserSession $userSession,
        BristolStairsRepo $bristolStairsRepo,
        BristolStairsInfoParams $stairs_info_params
    ): SuccessResponse {

        $bristolStairsRepo->updateStairInfo($stairs_info_params);
        return new SuccessResponse();
    }

    public function update_stairs_position(
        UserSession $appSession,
        BristolStairsRepo $bristolStairsRepo,
        BristolStairsPositionParams $stairs_position_params
    ): SuccessResponse {

        $bristolStairsRepo->updateStairPosition($stairs_position_params);

        // Usage after storing a post:
        purgeVarnish("/api/bristol_stairs");

        return new SuccessResponse();
    }

    public function stairs_page_stair_selected(
        ExtraAssets $extraAssets,
        BristolStairsRepo $bristolStairsRepo,
        int $stair_id
    ): string {
        $stair_info = $bristolStairsRepo->getStairInfoById($stair_id);

        return $this->render_stairs_page(
            $extraAssets,
            $bristolStairsRepo,
            $stair_info
        );
    }

    public function stairs_page(ExtraAssets $extraAssets, BristolStairsRepo $bristolStairsRepo): string
    {
        return $this->render_stairs_page(
            $extraAssets,
            $bristolStairsRepo,
            null
        );
    }

    private function render_stairs_page(
        ExtraAssets $extraAssets,
        BristolStairsRepo $bristolStairsRepo,
        BristolStairInfo $selected_stair = null
    ): string {
        $extraAssets->addCSS("/css/leaflet/leaflet.1.7.1.css");
        $extraAssets->addCSS("/css/leaflet/MarkerCluster.1.4.1.css");
        $extraAssets->addCSS("/css/leaflet/MarkerCluster.Default.1.5.0.min.css");

        $extraAssets->addJS("/js/leaflet/leaflet.1.7.1.js");
        $extraAssets->addJS("/js/leaflet/leaflet.markercluster.1.4.1.js");
        $extraAssets->addJS("/js/bristol_stairs_map.js");

        $data = [
            'selected_stair_info' => $selected_stair
        ];

        [$error, $values] = convertToValue($data);
        $widget_json = json_encode_safe($values);
        $widget_data = htmlspecialchars($widget_json);

        [$flights_of_stairs, $total_steps] = $bristolStairsRepo->get_total_number_of_steps();

        $content = "<h1>A map of Bristol Stairs</h1>";
        $content .= <<< HTML

<div class="bristol_stairs_container">
  <div class="bristol_stairs_map" id="bristol_stairs_map" ></div>
  <div class="bristol_stairs_panel" data-widgety_json="$widget_data"></div>
</div>

<div>
<h2>About</h2>
<p>Bristol has many steps. This page is an attempt to document all of them, to be able to answer the question, how many steps does Bristol have?</p>

<p>There are currently entries for $total_steps steps in $flights_of_stairs flights of stairs.</p>

<h2>Qualification rules</h2>

<ol>
<li>Stairs need to be in a place where members of the public will walk through, to another location or to a public amenity. i.e. steps leading up to a house don't qualify, steps leading up to a park bench do.</li>
<li>There need to be at least two steps between the top and bottom.</li>
<li>The steps need to be within about one meter of each other. Some paths (e.g. in Brandon Hill Park) have steps in them, to make the gradient of the path be not too steep, but they are too far apart to qualify as a flight of stairs.</li>
<li>Stairs cannot be inside a building.</li>
<li>The stairs have to be in Bristol. We use some discretion here for the definition of Bristol.</li>
<li>These rules are more what you'd call guidelines, than actual rules.</li>
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
        try {
            ensureFileCachedFromStream($localCacheFilesystem, $roomFilesystem, $normalized_name);
        }
        catch (\League\Flysystem\UnableToReadFile $unableToReadFile) {
            return new StoredFileErrorResponse($normalized_name);
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


    public function getData(BristolStairsRepo $stairs_repo): GetBristolStairsResponse
    {
        $markers = $stairs_repo->getAllStairsInfo();

        return new GetBristolStairsResponse($markers);
    }

    /**
     * Returns the 20 closest OSM stair locations (from openmap_stair_info.json) to the given coordinates.
     * Only node elements are used (they have lat/lon); way elements are skipped.
     * Requires the user to be logged in.
     */
    public function getOpenmapNearby(
        UserSession $userSession,
        OpenmapNearbyParams $params
    ): JsonNoCacheResponse {
        if ($userSession->isLoggedIn() !== true) {
            return new JsonNoCacheResponse(['error' => 'Not logged in'], [], 401);
        }

        $path = __DIR__ . '/../../../app/public/openmap_stair_info.json';
        if (!is_readable($path)) {
            return new JsonNoCacheResponse(['error' => 'OpenMap data not available'], [], 500);
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);
        if (!isset($data['elements']) || !is_array($data['elements'])) {
            return new JsonNoCacheResponse(['error' => 'Invalid OpenMap data'], [], 500);
        }

        $latitude = $params->latitude;
        $longitude = $params->longitude;

        $locations = [];
        foreach ($data['elements'] as $element) {
            if (($element['type'] ?? '') !== 'node') {
                continue;
            }
            $element_latitude = $element['lat'] ?? null;
            $element_longitude = $element['lon'] ?? null;
            if ($element_latitude === null || $element_longitude === null) {
                continue;
            }
            $distance_squared = ($element_latitude - $latitude) ** 2 + ($element_longitude - $longitude) ** 2;
            $name = $element['tags']['name'] ?? null;
            $locations[] = [
                'latitude' => (float) $element_latitude,
                'longitude' => (float) $element_longitude,
                'id' => $element['id'],
                'type' => 'node',
                'name' => $name,
                '_distance_squared' => $distance_squared,
            ];
        }

        usort($locations, static fn ($a, $b) => $a['_distance_squared'] <=> $b['_distance_squared']);
        $nearest = array_slice($locations, 0, 20);
        foreach ($nearest as &$location) {
            unset($location['_distance_squared']);
        }

        return new JsonNoCacheResponse([
            'result' => 'success',
            'data' => ['locations' => $nearest],
        ]);
    }


    //        // TODO - check user logged in
//        TODO - check the post 'is user logged in' check working.
//        if ($appSession->isLoggedIn() !== true) {
//            $data = ['not logged in' => true];
//            return new JsonResponse($data, [], 400);
//        }

    public function handleFileUpload(
        BristolStairImageStorage     $bristolStairImageStorage,
        UserSession                  $appSession,
        UserSessionFileUploadHandler $usfuh,
        VarMap                       $varMap
    ): StubResponse {

        $gpsParams = BristolStairsGpsParams::createFromVarMap($varMap);

        // Get the user uploaded file.
        $fileOrResponse = $usfuh->fetchUploadedFile(self::BRISTOL_STAIRS_FILE_UPLOAD_FORM_NAME);
        if ($fileOrResponse instanceof StubResponse) {
            return $fileOrResponse;
        }

        $stairInfoOrError = $bristolStairImageStorage->storeFileForUser(
            $appSession->getUserId(),
            $fileOrResponse,
            get_supported_bristolian_stair_image_extensions(),
            $gpsParams
        );

        if ($stairInfoOrError instanceof UploadError) {
            $data = [
                'result' => 'error',
                'error' => $stairInfoOrError->error_message
            ];
            // todo - change to helper function
            return new JsonNoCacheResponse($data, [], 400);
        }

        $response = new UploadBristolStairsImageResponse($stairInfoOrError);

        // Usage after storing a post:
        purgeVarnish("/api/bristol_stairs");

        return $response;
    }
}
