<?php

declare(strict_types = 1);

namespace Bristolian\AppController;

use Bristolian\App;
use Bristolian\Exception\BristolianException;
use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Filesystem\RoomFileFilesystem;
use Bristolian\Parameters\LinkParam;
use Bristolian\Parameters\SourceLinkHighlightParam;
use Bristolian\Parameters\SourceLinkParam;
use Bristolian\Repo\RoomFileRepo\RoomFileRepo;
use Bristolian\Repo\RoomLinkRepo\RoomLinkRepo;
use Bristolian\Repo\RoomRepo\RoomRepo;
use Bristolian\Repo\RoomSourceLinkRepo\RoomSourceLinkRepo;
use Bristolian\Response\IframeHtmlResponse;
use Bristolian\Response\StreamingResponse;
use Bristolian\Response\StoredFileErrorResponse;
use Bristolian\Response\SuccessResponse;
use Bristolian\Service\RoomFileStorage\UploadError;
use Bristolian\Service\RequestNonce;
use Bristolian\Service\RoomFileStorage\RoomFileStorage;
use Bristolian\Session\UserSession;
use Bristolian\UserUploadedFile\UserSessionFileUploadHandler;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\JsonResponse;
use SlimDispatcher\Response\StubResponse;
use Bristolian\Response\EndpointAccessedViaGetResponse;
use VarMap\VarMap;
use function DataType\createArrayOfTypeOrError;
use Bristolian\Response\Typed\GetRoomsFilesResponse;
use Bristolian\Response\Typed\GetRoomsLinksResponse;
use Bristolian\Response\Typed\GetRoomsSourcelinksResponse;
use Bristolian\Response\Typed\GetRoomsFileSourcelinksResponse;

class Rooms
{

    public const ROOM_FILE_UPLOAD_FORM_NAME = "room_file";

    public function index(RoomRepo $roomRepo): string
    {
        $content = "<h1>List of rooms</h1>";

        $rooms = $roomRepo->getAllRooms();
        $content .= sprintf("There are currently %d rooms", count($rooms));

        $content .= "<table><tbody>";
        $template = "<tr><td><a href='/rooms/:attr_room_id'>:html_name</a></td></tr>";

        foreach ($rooms as $room) {
            $params = [
                ':attr_room_id' => $room->id,
                ':html_name' => $room->name
            ];
            $content .= esprintf($template, $params);
        }

        $content .= "</tbody></table>";

        return $content;
    }

    public function handleFileUpload_get(): EndpointAccessedViaGetResponse
    {
        return new EndpointAccessedViaGetResponse();
    }


//    public function createRoom(
//        AdminRepo $adminRepo,
//        RoomRepo $roomRepo,
//        UserSession $appSession,
//        string $name,
//        string $purpose
//    ) {
//
//        // TODO - check user logged in
//        if ($appSession->isLoggedIn() !== true) {
//            $data = ['not logged in' => true];
//            return new JsonResponse($data, [], 400);
//        }
//
//        $result = $roomRepo->createRoom(
//            $appSession->getUserId(),
//            $name,
//            $purpose
//        );
//
//        return createJsonResponse('room', $result);
//    }



    public function handleFileUpload(
        RoomFileStorage              $roomFileStorage,
        UserSession                  $appSession,
        UserSessionFileUploadHandler $usfuh,
        string                       $room_id
    ): StubResponse {

//        // TODO - check user logged in
//        if ($appSession->isLoggedIn() !== true) {
//            $data = ['not logged in' => true];
//            return new JsonResponse($data, [], 400);
//        }

        // Get the user uploaded file.
        $fileOrResponse = $usfuh->fetchUploadedFile(self::ROOM_FILE_UPLOAD_FORM_NAME);
        if ($fileOrResponse instanceof StubResponse) {
            return $fileOrResponse;
        }

        $storedFileOrError = $roomFileStorage->storeFileForRoomAndUser(
            $appSession->getUserId(),
            $room_id,
            $fileOrResponse
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
            'file_id' => $storedFileOrError
        ];

        return new JsonNoCacheResponse($response);
    }

    /**
     * @param RoomFileRepo $roomfileRepo
     * @param string $room_id
     * @return GetRoomsFilesResponse
     * @throws \SlimDispatcher\Response\InvalidDataException
     */
    public function getFiles(
        RoomFileRepo $roomfileRepo,
        string $room_id
    ): GetRoomsFilesResponse {
        $files = $roomfileRepo->getFilesForRoom($room_id);

        return new GetRoomsFilesResponse($files);

//        return createJsonResponse(['files' => $files]);
    }

    /**
     * @param RoomLinkRepo $roomLinkRepo
     * @param string $room_id
     * @return GetRoomsLinksResponse
     */
    public function getLinks(
        RoomLinkRepo $roomLinkRepo,
        string $room_id
    ) {
        $links = $roomLinkRepo->getLinksForRoom($room_id);

        return new GetRoomsLinksResponse($links);
    }




    public function getSourcelinksForFile(
        RoomSourceLinkRepo $roomLinkRepo,
        string $room_id,
        string $file_id,
    ): GetRoomsFileSourcelinksResponse {
        $sourcelinks = $roomLinkRepo->getSourceLinksForRoomAndFile(
            $room_id,
            $file_id
        );

        return new GetRoomsFileSourcelinksResponse($sourcelinks);
    }


    public function getSourcelinks(
        RoomSourceLinkRepo $roomLinkRepo,
        string $room_id
    ): GetRoomsSourcelinksResponse {
        $sourcelinks = $roomLinkRepo->getSourceLinksForRoom($room_id);

        return new GetRoomsSourcelinksResponse($sourcelinks);
    }




    /**
     */
    public function addLink(
        UserSession $appSession,
        RoomLinkRepo $roomLinkRepo,
        LinkParam $linkParam,
        //        VarMap $varMap,
        string $room_id
    ): SuccessResponse {
//        // TODO - check user logged in
//        if ($appSession->isLoggedIn() !== true) {
//            $data = ['not logged in' => true];
//            return new JsonResponse($data, [], 400);
//        }

//        $linkParam = LinkParam::createFromVarMap($varMap);

        // TODO - there needs to be a security check that
        // the user has write access to the room.
        $room_link_id = $roomLinkRepo->addLinkToRoomFromParam(
            $appSession->getUserId(),
            $room_id,
            $linkParam
        );

        // room_link_id currently unused by frontend; SuccessResponse is sufficient.
        return new SuccessResponse();
    }


    /**
     * @param RoomFileFilesystem $roomFilesystem
     * @param LocalCacheFilesystem $localCacheFilesystem
     * @param RoomRepo $roomRepo
     * @param RoomFileRepo $roomFileRepo
     * @param string $room_id
     * @param string $file_id
     * @return StubResponse|StreamingResponse
     * @throws BristolianException
     * @throws \League\Flysystem\FilesystemException
     * @throws \SlimDispatcher\Response\ResponseException
     */
    public function serveFileForRoom(
        RoomFileFilesystem $roomFilesystem,
        LocalCacheFilesystem $localCacheFilesystem,
        RoomRepo $roomRepo,
        RoomFileRepo $roomFileRepo,
        string $room_id,
        string $file_id
    ): StubResponse|StreamingResponse {
        // TODO - validate room, probably

        $fileDetails = $roomFileRepo->getFileDetails($room_id, $file_id);
        if ($fileDetails === null) {
            throw new BristolianException("File not found.");
        }

        $normalized_name = $fileDetails->normalized_name;
        try {
            // TODO - why is contents unused?
            $contents = ensureFileCachedFromString(
                $localCacheFilesystem,
                $roomFilesystem,
                $normalized_name
            );
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

    public function showRoom(RoomRepo $roomRepo, string $room_id): string
    {
        $room = $roomRepo->getRoomById($room_id);

        if ($room === null) {
            // TODO - handle 404 pages properly
            return "Room not found.";
        }

        $widget_data = encodeWidgetyData([
            'room_id' => $room_id,
            'accepted_file_extensions' => get_supported_room_file_extensions()
        ]);

        $template = <<< HTML
<div class="roompage">
  <div class="roompage__left">
    <div class="chat_panel" data-widgety_json="$widget_data">
    </div>
  </div>
  <div class="roompage__right"> 
    <h1>:html_room_name</h1>
    <p>:html_room_description</p>
    <div class='room_files_panel' data-widgety_json='$widget_data'></div>
    <div class='room_file_upload_panel' data-widgety_json='$widget_data'></div>
    <div class='room_links_panel' data-widgety_json='$widget_data'></div>
    <div class='room_sourcelinks_panel' data-widgety_json='$widget_data'></div>
  </div>
</div>
HTML;
        $params = [
            ':html_room_name' => $room->name,
            ':html_room_description' => $room->purpose,
        ];

        $content = esprintf($template, $params);

        return $content;
    }

    private function render_annotate_file(
        RoomRepo $roomRepo,
        string $room_id,
        string $file_id,
        string|null $sourcelink_id
    ): string {
        $room = $roomRepo->getRoomById($room_id);
        // TODO - check for null room

        $params = [
            'room_id' => $room_id,
            'file_id' => $file_id,
        ];

        if ($sourcelink_id !== null) {
            $params['selected_sourcelink_id'] = $sourcelink_id;
        }

        $widget_data = encodeWidgetyData($params);

        $template = <<< HTML
<h1>:html_room_name</h1>
<div class="text_note_layout">
  <span class="text_note_iframe_container">
    <iframe class='text_note_iframe' id="pdf_iframe"
      src='/iframe/rooms/:attr_room_id/file_annotate/:attr_file_id' 
      title='A file to note text in'></iframe>
  </span>
  <span>
    <div class='source_link_panel' data-widgety_json='$widget_data'></div>
  </span>
</div>
<script src="/js/text_note_iframe_resize.js"></script>
HTML;

        $params = [
            ':html_room_name' => $room->name,
            ':attr_file_id' => $file_id,
            ':attr_room_id' => $room_id
        ];

        $content = esprintf($template, $params);

        return $content;
    }

    public function annotate_file(
        RoomRepo $roomRepo,
        string $room_id,
        string $file_id,
    ): string {
        return $this->render_annotate_file(
            $roomRepo,
            $room_id,
            $file_id,
            null
        );
    }





    public function iframe_show_file(
        RequestNonce $requestNonce,
        \Bristolian\SiteHtml\AssetLinkEmitter $assetLinkEmitter,
        RoomRepo $roomRepo,
        RoomFileRepo $roomFileRepo,
        string $room_id,
        string $file_id
    ): IframeHtmlResponse|string {
        $storedFile = $roomFileRepo->getFileDetails($room_id, $file_id);

        if ($storedFile === null) {
            return "File not found.";
        }

        $stored_file_url = getRouteForStoredFile($room_id, $storedFile);

        $widget_data = encodeWidgetyData([
            'stored_file_url' => $stored_file_url
        ]);


        $assetSuffix = $assetLinkEmitter->getAssetSuffix();

        $html = <<< HTML
<!DOCTYPE html>

<html lang="en">
  <body>
    <script src="/js/pdf_view.js" type="module"></script>
    <link rel="stylesheet" href="/css/pdf-5.3.31.css$assetSuffix">

    <link rel="stylesheet" href="/css/pdf-custom.css$assetSuffix">
 
  <script nonce="{$requestNonce->getRandom()}">
    // (function () {

      // Queue to store incoming messages
      const messageQueue = [];

      // Flag to indicate when the iframe script is ready
      // let isReady = false;

      // Temporary listener to queue messages
      function queueMessage(event) {
        // if (!isReady) {
          messageQueue.push(event.data);
        // }
      }

      // Add event listener for postMessage
      window.addEventListener('message', queueMessage);
      console.log("Message queue setup");
  </script>
    
    <div id="viewer" class="pdfViewer" data-widgety_json='$widget_data' />
  </body>
</html>
HTML;

        return new IframeHtmlResponse($html);
    }


    public function handleAddSourceLink(
        RoomRepo $roomRepo,
        RoomFileRepo $roomFileRepo,
        RoomSourceLinkRepo $roomSourceLinkRepo,
        UserSession $appSession,
        SourceLinkParam $highLightParam,
        //        VarMap $varMap,
        string $room_id,
        string $file_id
    ): StubResponse {
//        $highLightParam = SourceLinkParam::createFromVarMap($varMap);

        $data = json_decode_safe($highLightParam->highlights_json);

        // We are just creating these objects to validate the data looks correct.
        [$highlights, $validation_errors] = createArrayOfTypeOrError(SourceLinkHighlightParam::class, $data);

        if ($validation_errors !== null) {
            return createErrorJsonResponse($validation_errors);
        }

        $room_sourcelink_id = $roomSourceLinkRepo->addSourceLink(
            $appSession->getUserId(),
            $room_id,
            $file_id,
            $highLightParam
        );

        $data = [
            'room_sourcelink_id' => $room_sourcelink_id
        ];

        return createJsonResponse($data);
    }

    public function viewSourcelink(
        RoomRepo $roomRepo,
        string $room_id,
        string $file_id,
        string $sourcelink_id
    ): string {
        return $this->render_annotate_file(
            $roomRepo,
            $room_id,
            $file_id,
            $sourcelink_id
        );
    }
}
