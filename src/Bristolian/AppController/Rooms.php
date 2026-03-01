<?php

declare(strict_types = 1);

namespace Bristolian\AppController;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Exception\BristolianException;
use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Filesystem\RoomFileFilesystem;
use Bristolian\JsonInput\JsonInput;
use Bristolian\Model\Generated\RoomTag;
use Bristolian\Model\Types\RoomAnnotationWithTags;
use Bristolian\Model\Types\RoomFileWithTags;
use Bristolian\Model\Types\RoomLinkWithTags;
use Bristolian\Parameters\LinkParam;
use Bristolian\Parameters\SetEntityTagsParam;
use Bristolian\Parameters\TagParams;
use Bristolian\Parameters\AnnotationHighlightParam;
use Bristolian\Parameters\AnnotationParam;
use Bristolian\Repo\RoomFileRepo\RoomFileRepo;
use Bristolian\Repo\RoomFileTagRepo\RoomFileTagRepo;
use Bristolian\Repo\RoomLinkRepo\RoomLinkRepo;
use Bristolian\Repo\RoomLinkTagRepo\RoomLinkTagRepo;
use Bristolian\Repo\RoomRepo\RoomRepo;
use Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo;
use Bristolian\Repo\RoomAnnotationTagRepo\RoomAnnotationTagRepo;
use Bristolian\Repo\RoomTagRepo\RoomTagRepo;
use Bristolian\Response\EndpointAccessedViaGetResponse;
use Bristolian\Response\IframeHtmlResponse;
use Bristolian\Response\StoredFileErrorResponse;
use Bristolian\Response\StreamingResponse;
use Bristolian\Response\SuccessResponse;
use Bristolian\Response\Typed\GetRoomsFileAnnotationsResponse;
use Bristolian\Response\Typed\GetRoomsFilesResponse;
use Bristolian\Response\Typed\GetRoomsLinksResponse;
use Bristolian\Response\Typed\GetRoomsAnnotationsResponse;
use Bristolian\Response\Typed\GetRoomsTagsResponse;
use Bristolian\Service\RequestNonce;
use Bristolian\Service\RoomFileStorage\RoomFileStorage;
use Bristolian\Service\RoomFileStorage\UploadError;
use Bristolian\Session\UserSession;
use Bristolian\UserUploadedFile\UserSessionFileUploadHandler;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\StubResponse;
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
     * @param RoomFileTagRepo $roomFileTagRepo
     * @param RoomTagRepo $roomTagRepo
     * @param string $room_id
     * @return GetRoomsFilesResponse
     * @throws \SlimDispatcher\Response\InvalidDataException
     */
    public function getFiles(
        RoomFileRepo $roomfileRepo,
        RoomFileTagRepo $roomFileTagRepo,
        RoomTagRepo $roomTagRepo,
        string $room_id
    ): GetRoomsFilesResponse {
        $files = $roomfileRepo->getFilesForRoom($room_id);
        $roomTags = $roomTagRepo->getTagsForRoom($room_id);
        $roomTagsById = [];
        foreach ($roomTags as $tag) {
            $roomTagsById[$tag->tag_id] = $tag;
        }
        $withTags = [];
        foreach ($files as $file) {
            $tagIds = $roomFileTagRepo->getTagIdsForRoomFile($room_id, $file->id);
            $tags = self::resolveTagIdsToTags($tagIds, $roomTagsById);
            $withTags[] = new RoomFileWithTags(
                $file->id,
                $file->normalized_name,
                $file->original_filename,
                $file->state,
                $file->size,
                $file->user_id,
                $file->created_at,
                $tags
            );
        }
        return new GetRoomsFilesResponse($withTags);
    }

    /**
     * @param RoomLinkRepo $roomLinkRepo
     * @param RoomLinkTagRepo $roomLinkTagRepo
     * @param RoomTagRepo $roomTagRepo
     * @param string $room_id
     * @return GetRoomsLinksResponse
     */
    public function getLinks(
        RoomLinkRepo $roomLinkRepo,
        RoomLinkTagRepo $roomLinkTagRepo,
        RoomTagRepo $roomTagRepo,
        string $room_id
    ): GetRoomsLinksResponse {
        $links = $roomLinkRepo->getLinksForRoom($room_id);
        $roomTags = $roomTagRepo->getTagsForRoom($room_id);
        $roomTagsById = [];
        foreach ($roomTags as $tag) {
            $roomTagsById[$tag->tag_id] = $tag;
        }
        $withTags = [];
        foreach ($links as $link) {
            $tagIds = $roomLinkTagRepo->getTagIdsForRoomLink($link->id);
            $tags = self::resolveTagIdsToTags($tagIds, $roomTagsById);
            $withTags[] = new RoomLinkWithTags(
                $link->id,
                $link->room_id,
                $link->link_id,
                $link->title,
                $link->description,
                $link->created_at,
                $tags
            );
        }
        return new GetRoomsLinksResponse($withTags);
    }

    public function getAnnotationsForFile(
        RoomAnnotationRepo $roomAnnotationRepo,
        RoomAnnotationTagRepo $roomAnnotationTagRepo,
        RoomTagRepo $roomTagRepo,
        string $room_id,
        string $file_id,
    ): GetRoomsFileAnnotationsResponse {
        $annotations = $roomAnnotationRepo->getAnnotationsForRoomAndFile(
            $room_id,
            $file_id
        );
        $roomTags = $roomTagRepo->getTagsForRoom($room_id);
        $roomTagsById = [];
        foreach ($roomTags as $tag) {
            $roomTagsById[$tag->tag_id] = $tag;
        }
        $withTags = [];
        foreach ($annotations as $ann) {
            $tagIds = $roomAnnotationTagRepo->getTagIdsForRoomAnnotation($ann->room_annotation_id);
            $tags = self::resolveTagIdsToTags($tagIds, $roomTagsById);
            $withTags[] = new RoomAnnotationWithTags(
                $ann->id,
                $ann->user_id,
                $ann->file_id,
                $ann->highlights_json,
                $ann->text,
                $ann->title,
                $ann->room_annotation_id,
                $tags
            );
        }
        return new GetRoomsFileAnnotationsResponse($withTags);
    }

    public function getAnnotations(
        RoomAnnotationRepo $roomAnnotationRepo,
        RoomAnnotationTagRepo $roomAnnotationTagRepo,
        RoomTagRepo $roomTagRepo,
        string $room_id
    ): GetRoomsAnnotationsResponse {
        $annotations = $roomAnnotationRepo->getAnnotationsForRoom($room_id);
        $roomTags = $roomTagRepo->getTagsForRoom($room_id);
        $roomTagsById = [];
        foreach ($roomTags as $tag) {
            $roomTagsById[$tag->tag_id] = $tag;
        }
        $withTags = [];
        foreach ($annotations as $ann) {
            $tagIds = $roomAnnotationTagRepo->getTagIdsForRoomAnnotation($ann->room_annotation_id);
            $tags = self::resolveTagIdsToTags($tagIds, $roomTagsById);
            $withTags[] = new RoomAnnotationWithTags(
                $ann->id,
                $ann->user_id,
                $ann->file_id,
                $ann->highlights_json,
                $ann->text,
                $ann->title,
                $ann->room_annotation_id,
                $tags
            );
        }
        return new GetRoomsAnnotationsResponse($withTags);
    }

    /**
     * @param string[] $tagIds
     * @param array<string, RoomTag> $roomTagsById
     * @return RoomTag[]
     */
    private static function resolveTagIdsToTags(array $tagIds, array $roomTagsById): array
    {
        $tags = [];
        foreach ($tagIds as $id) {
            if (isset($roomTagsById[$id])) {
                $tags[] = $roomTagsById[$id];
            }
        }
        return $tags;
    }

    public function getTags(
        RoomTagRepo $roomTagRepo,
        string $room_id
    ): GetRoomsTagsResponse {
        $tags = $roomTagRepo->getTagsForRoom($room_id);
        return new GetRoomsTagsResponse($tags);
    }

    public function addTag(
        RoomTagRepo $roomTagRepo,
        TagParams $tagParam,
        string $room_id
    ): SuccessResponse {
        $roomTagRepo->createTag($room_id, $tagParam);
        return new SuccessResponse();
    }

    public function setFileTags(
        RoomFileRepo $roomFileRepo,
        RoomFileTagRepo $roomFileTagRepo,
        RoomTagRepo $roomTagRepo,
        JsonInput $jsonInput,
        string $room_id,
        string $file_id
    ): SuccessResponse {
        $fileDetails = $roomFileRepo->getFileDetails($room_id, $file_id);
        if ($fileDetails === null) {
            throw new ContentNotFoundException('File not found in room');
        }
        $param = SetEntityTagsParam::fromArray($jsonInput->getData());
        $roomTags = $roomTagRepo->getTagsForRoom($room_id);
        $validIds = [];
        foreach ($roomTags as $t) {
            $validIds[$t->tag_id] = true;
        }
        $filtered = array_filter($param->tag_ids, fn (string $id) => isset($validIds[$id]));
        $roomFileTagRepo->setTagsForRoomFile($room_id, $file_id, array_values($filtered));
        return new SuccessResponse();
    }

    public function setLinkTags(
        RoomLinkRepo $roomLinkRepo,
        RoomLinkTagRepo $roomLinkTagRepo,
        RoomTagRepo $roomTagRepo,
        JsonInput $jsonInput,
        string $room_id,
        string $room_link_id
    ): SuccessResponse {
        $roomLink = $roomLinkRepo->getRoomLink($room_link_id);
        if ($roomLink === null || $roomLink->room_id !== $room_id) {
            throw new ContentNotFoundException('Link not found in room');
        }
        $param = SetEntityTagsParam::fromArray($jsonInput->getData());
        $roomTags = $roomTagRepo->getTagsForRoom($room_id);
        $validIds = [];
        foreach ($roomTags as $t) {
            $validIds[$t->tag_id] = true;
        }
        $filtered = array_filter($param->tag_ids, fn (string $id) => isset($validIds[$id]));
        $roomLinkTagRepo->setTagsForRoomLink($room_link_id, array_values($filtered));
        return new SuccessResponse();
    }

    public function setAnnotationTags(
        RoomAnnotationRepo $roomAnnotationRepo,
        RoomAnnotationTagRepo $roomAnnotationTagRepo,
        RoomTagRepo $roomTagRepo,
        JsonInput $jsonInput,
        string $room_id,
        string $room_annotation_id
    ): SuccessResponse {
        $annotations = $roomAnnotationRepo->getAnnotationsForRoom($room_id);
        $found = false;
        foreach ($annotations as $ann) {
            if ($ann->room_annotation_id === $room_annotation_id) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            throw new ContentNotFoundException('Annotation not found in room');
        }
        $param = SetEntityTagsParam::fromArray($jsonInput->getData());
        $roomTags = $roomTagRepo->getTagsForRoom($room_id);
        $validIds = [];
        foreach ($roomTags as $t) {
            $validIds[$t->tag_id] = true;
        }
        $filtered = array_filter($param->tag_ids, fn (string $id) => isset($validIds[$id]));
        $roomAnnotationTagRepo->setTagsForRoomAnnotation($room_annotation_id, array_values($filtered));
        return new SuccessResponse();
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
  <h1>:html_room_name</h1>
  <p>:html_room_description</p>

  <div class="roompage_tabs">
    <input type="radio" id="room_tab_chat" name="room_tab" class="room_tab_radio" checked>
    <input type="radio" id="room_tab_links" name="room_tab" class="room_tab_radio">
    <input type="radio" id="room_tab_files" name="room_tab" class="room_tab_radio">
    <input type="radio" id="room_tab_annotations" name="room_tab" class="room_tab_radio">
    <input type="radio" id="room_tab_management" name="room_tab" class="room_tab_radio">

    <div class="room_tab_strip">
      <label for="room_tab_chat" class="room_tab_label">Chat</label>
      <label for="room_tab_links" class="room_tab_label">Links</label>
      <label for="room_tab_files" class="room_tab_label">Files</label>
      <label for="room_tab_annotations" class="room_tab_label">Annotations</label>
      <label for="room_tab_management" class="room_tab_label">Room Management</label>
    </div>

    <!-- Tab panels -->
    <div class="room_tabs_panels">
      <div class="room_tab_panel room_tab_panel_chat">
        <div class="chat_panel" data-widgety_json="$widget_data"></div>
      </div>

      <div class="room_tab_panel room_tab_panel_links">
        <div class='room_links_panel' data-widgety_json='$widget_data'></div>
      </div>

      <div class="room_tab_panel room_tab_panel_files">
        <div class='room_files_panel' data-widgety_json='$widget_data'></div>
        <div class='room_file_upload_panel' data-widgety_json='$widget_data'></div>
      </div>

      <div class="room_tab_panel room_tab_panel_annotations">
        <div class='room_annotations_panel' data-widgety_json='$widget_data'></div>
      </div>

      <div class="room_tab_panel room_tab_panel_management">
        <div class='room_management_panel' data-widgety_json='$widget_data'></div>
      </div>
    </div>
  </div>

  <div class="roompage_bottom">
    <div class="chat_bottom_panel" data-widgety_json="$widget_data"></div>
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
        RoomFileRepo $roomFileRepo,
        string $room_id,
        string $file_id,
        string|null $annotation_id
    ): string {
        $room = $roomRepo->getRoomById($room_id);
        // TODO - check for null room

        $storedFile = $roomFileRepo->getFileDetails($room_id, $file_id);
        if ($storedFile === null) {
            throw ContentNotFoundException::file_not_found($room_id, $file_id);
        }
        $file_name = $storedFile->original_filename;

        $params = [
            'room_id' => $room_id,
            'file_id' => $file_id,
        ];

        if ($annotation_id !== null) {
            $params['selected_annotation_id'] = $annotation_id;
        }

        $widget_data = encodeWidgetyData($params);

        $template = <<< HTML
<h1>:html_room_name</h1>
<p><a href="/rooms/:attr_room_id">Back to room</a> · :html_file_name</p>
<div class="text_note_layout">
  <span class="text_note_iframe_container">
    <iframe class='text_note_iframe' id="pdf_iframe"
      src='/iframe/rooms/:attr_room_id/file_annotate/:attr_file_id' 
      title='A file to note text in'></iframe>
  </span>
  <span>
    <div class='annotation_panel' data-widgety_json='$widget_data'></div>
  </span>
</div>
<script src="/js/text_note_iframe_resize.js"></script>
HTML;

        $params = [
            ':html_room_name' => $room->name,
            ':html_file_name' => $file_name,
            ':attr_file_id' => $file_id,
            ':attr_room_id' => $room_id
        ];

        $content = esprintf($template, $params);

        return $content;
    }

    /**
     * Open the file annotation page with no annotation selected.
     * Route: /rooms/{room_id}/file_annotate/{file_id}
     * Use for "annotate this file" – user sees the file and annotation list; they can select one or create new.
     */
    public function annotate_file(
        RoomRepo $roomRepo,
        RoomFileRepo $roomFileRepo,
        string $room_id,
        string $file_id,
    ): string {
        return $this->render_annotate_file(
            $roomRepo,
            $roomFileRepo,
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


    public function handleAddAnnotation(
        RoomRepo $roomRepo,
        RoomFileRepo $roomFileRepo,
        RoomAnnotationRepo $roomAnnotationRepo,
        UserSession $appSession,
        AnnotationParam $highLightParam,
        //        VarMap $varMap,
        string $room_id,
        string $file_id
    ): StubResponse {
//        $highLightParam = AnnotationParam::createFromVarMap($varMap);

        $data = json_decode_safe($highLightParam->highlights_json);

        // We are just creating these objects to validate the data looks correct.
        [$highlights, $validation_errors] = createArrayOfTypeOrError(AnnotationHighlightParam::class, $data);

        if ($validation_errors !== null) {
            return createErrorJsonResponse($validation_errors);
        }

        $room_annotation_id = $roomAnnotationRepo->addAnnotation(
            $appSession->getUserId(),
            $room_id,
            $file_id,
            $highLightParam
        );

        $data = [
            'room_annotation_id' => $room_annotation_id
        ];

        return createJsonResponse($data);
    }

    /**
     * Open the file annotation page with a specific annotation pre-selected.
     * Route: /rooms/{room_id}/file/{file_id}/annotations/{annotation_id}/view
     * Use for deep links – that annotation is selected and its highlights are drawn on the PDF.
     */
    public function viewAnnotation(
        RoomRepo $roomRepo,
        RoomFileRepo $roomFileRepo,
        string $room_id,
        string $file_id,
        string $annotation_id
    ): string {
        return $this->render_annotate_file(
            $roomRepo,
            $roomFileRepo,
            $room_id,
            $file_id,
            $annotation_id
        );
    }
}
