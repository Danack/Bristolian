<?php

namespace Bristolian\AppController;

use Bristolian\BristolianException;
use Bristolian\Response\BristolianFileResponse;
use Bristolian\DataType\LinkParam;
use Bristolian\DataType\SourceLinkHighlightParam;
use Bristolian\DataType\SourceLinkHighlightsAsdasds;
use Bristolian\DataType\SourceLinkParam;
use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Filesystem\RoomFileFilesystem;
use Bristolian\Repo\RoomFileRepo\RoomFileRepo;
use Bristolian\Repo\RoomLinkRepo\RoomLinkRepo;
use Bristolian\Repo\RoomRepo\RoomRepo;
use Bristolian\Repo\RoomSourceLinkRepo\RoomSourceLinkRepo;
use Bristolian\Response\IframeHtmlResponse;
use Bristolian\Service\FileStorageProcessor\UploadError;
use Bristolian\Service\RequestNonce;
use Bristolian\Service\RoomFileStorage\RoomFileStorage;
use Bristolian\Session\UserSession;
use Bristolian\UserUploadedFile\UserSessionFileUploaderHandler;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\JsonResponse;
use SlimDispatcher\Response\StubResponse;
use VarMap\VarMap;
use function DataType\createArrayOfTypeOrError;

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
                ':attr_room_id' => $room->getRoomId(),
                ':html_name' => $room->getName()
            ];
            $content .= esprintf($template, $params);
        }

        $content .= "</tbody></table>";

        return $content;
    }

    public function handleFileUpload_get(): string
    {
        return "You probably meant to do a POST to this endpoint.";
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
        RoomFileStorage $roomFileStorage,
        UserSession $appSession,
        UserSessionFileUploaderHandler $usfuh,
        string $room_id
    ): StubResponse {

        // TODO - check user logged in
        if ($appSession->isLoggedIn() !== true) {
            $data = ['not logged in' => true];
            return new JsonResponse($data, [], 400);
        }

        // Get the user uploaded file.
        $fileOrResponse = $usfuh->processFile(self::ROOM_FILE_UPLOAD_FORM_NAME);
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
            'next' => 'actually upload to file_server.',
            'file_id' => $storedFileOrError->fileStorageId
        ];

        $response = new JsonNoCacheResponse($response);

        return $response;
    }

    /**
     * @param RoomFileRepo $roomfileRepo
     * @param string $room_id
     * @return JsonNoCacheResponse
     * @throws \SlimDispatcher\Response\InvalidDataException
     */
    public function getFiles(
        RoomFileRepo $roomfileRepo,
        string $room_id
    ) {
        $files = $roomfileRepo->getFilesForRoom($room_id);

        return createJsonResponse(['files' => $files]);
    }

    /**
     * @param RoomLinkRepo $roomLinkRepo
     * @param string $room_id
     * @return JsonNoCacheResponse
     * @throws \SlimDispatcher\Response\InvalidDataException
     */
    public function getLinks(
        RoomLinkRepo $roomLinkRepo,
        string $room_id
    ) {
        $links = $roomLinkRepo->getLinksForRoom($room_id);

        return createJsonResponse(['links' => $links]);
    }




    public function getSourcelinks(
        RoomSourceLinkRepo $roomLinkRepo,
        string $room_id
    ): JsonNoCacheResponse {
        $sourcelinks = $roomLinkRepo->getSourceLinksForRoom($room_id);

        return createJsonResponse(['sourcelinks' => $sourcelinks]);
    }




    /**
     */
    public function addLink(
        UserSession $appSession,
        RoomLinkRepo $roomLinkRepo,
        VarMap $varMap,
        string $room_id
    ): JsonResponse {
        // TODO - check user logged in
        if ($appSession->isLoggedIn() !== true) {
            $data = ['not logged in' => true];
            return new JsonResponse($data, [], 400);
        }

        $linkParam = LinkParam::createFromVarMap($varMap);

        // TODO - there needs to be a security check that
        // the user has write access to the room.
        $room_link_id = $roomLinkRepo->addLinkToRoomFromParam(
            $appSession->getUserId(),
            $room_id,
            $linkParam
        );

        $response = [
            'status' => 'success',
            'data' => [
                'room_link_id' => $room_link_id,
            ]
        ];

        return new JsonResponse($response);
    }


    /**
     * @param RoomFileFilesystem $roomFilesystem
     * @param LocalCacheFilesystem $localCacheFilesystem
     * @param RoomRepo $roomRepo
     * @param RoomFileRepo $roomFileRepo
     * @param string $room_id
     * @param string $file_id
     * @return BristolianFileResponse
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
    ): BristolianFileResponse {
        // TODO - validate room, probably

        $fileDetails = $roomFileRepo->getFileDetails($room_id, $file_id);
        if ($fileDetails === null) {
            throw new BristolianException("File not found.");
        }

        $normalized_name = $fileDetails->normalized_name;
        if ($localCacheFilesystem->fileExists($normalized_name) === true) {
            $contents = $localCacheFilesystem->read($normalized_name);
        }
        else {
            $contents = $roomFilesystem->read($normalized_name);
            $localCacheFilesystem->write($normalized_name, $contents);
        }

        $localCacheFilename = $localCacheFilesystem->getFullPath() . "/" . $normalized_name;

        $filenameToServe = realpath($localCacheFilename);

        if ($filenameToServe === false) {
            throw new BristolianException(
                "Failed to retrieve file from object store [" . $normalized_name . "]."
            );
        }

        // check file is available locally
        return new BristolianFileResponse(
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
            'room_id' => $room_id
        ]);

        $template = <<< HTML
    <h1>:html_room_name</h1>
    <p>:html_room_description</p>
    <div class='room_files_panel' data-widgety_json='$widget_data'></div>
    <div class='room_file_upload_panel' data-widgety_json='$widget_data'></div>
    <div class='room_links_panel' data-widgety_json='$widget_data'></div>
    <div class='room_sourcelinks_panel' data-widgety_json='$widget_data'></div>
HTML;
        $params = [
            ':html_room_name' => $room->getName(),
            ':html_room_description' => $room->getPurpose(),
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

        if($sourcelink_id !== null) {
            $params['selected_sourcelink_ids'] = [$sourcelink_id];
        }

        $widget_data = encodeWidgetyData($params);

        $template = <<< HTML
<h1>:html_room_name</h1>
<div class="text_note_layout">
  <span>
    <iframe class='text_note_iframe' id="pdf_iframe"
      src='/iframe/rooms/:attr_room_id/file_annotate/:attr_file_id' 
      title='A file to note text in'></iframe>
  </span>
  <span>
    <div class='text_note_panel' data-widgety_json='$widget_data'></div>
  </span>
</div>
HTML;

        $params = [
            ':html_room_name' => $room->getName(),
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

        $html = <<< HTML
<!DOCTYPE html>

<html lang="en">
  <body>
    <script src="/js/pdf/pdf.mjs" type="module"></script>
    <script src="/js/pdf_view.js" type="module"></script>
    <link rel="stylesheet" href="/css/pdf_viewer.css">
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
        VarMap $varMap,
        string $room_id,
        string $file_id
    ): StubResponse {
        $highLightParam = SourceLinkParam::createFromVarMap($varMap);

        if ($appSession->isLoggedIn() !== true) {
            $data = ['not logged in' => true];
            return new JsonResponse($data, [], 400);
        }

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
