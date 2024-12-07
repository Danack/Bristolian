<?php

namespace Bristolian\AppController;

use Bristolian\App;
use Bristolian\BristolianException;
use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Filesystem\RoomFileFilesystem;
use Bristolian\Repo\AdminRepo\AdminRepo;
use Bristolian\Repo\RoomRepo\RoomRepo;
use Bristolian\Service\FileStorageProcessor\UploadError;
use Bristolian\Service\RoomFileStorage\RoomFileStorage;
use Bristolian\UserSession;
use SlimDispatcher\Response\ImageResponse;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\JsonResponse;
use SlimDispatcher\Response\StubResponse;
use Bristolian\John\UserSessionFileUploaderHandler;
use Bristolian\Repo\RoomFileRepo\RoomFileRepo;
use SlimDispatcher\Response\FileResponse;
use Bristolian\BristolianFileResponse;

class Rooms
{

    public const ROOM_FILE_UPLOAD_FORM_NAME = "room_file";

    public function index(RoomRepo $roomRepo)
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


    public function getFiles(
        RoomFileRepo $roomfileRepo,
        string $room_id
    ) {
        $files = $roomfileRepo->getFilesForRoom($room_id);

        return createJsonResponse('files', $files);
    }

    public function serveFileForRoom(
        RoomFileFilesystem $roomFilesystem,
        LocalCacheFilesystem $localCacheFilesystem,
        RoomRepo $roomRepo,
        RoomFileRepo $roomFileRepo,
        string $room_id,
        string $file_id
    ) {
        // TODO - validate room, probably

        $fileDetails = $roomFileRepo->getFileDetails($room_id, $file_id);

        $normalized_name = $fileDetails->normalized_name;
        if ($localCacheFilesystem->fileExists($normalized_name) === true) {
            $contents = $localCacheFilesystem->read($normalized_name);
        }
        else {
            $contents = $roomFilesystem->read($normalized_name);
            $localCacheFilesystem->write($normalized_name, $contents);
        }

        $filenameToServe = realpath(__DIR__ . "/../../../data/cache/" . $normalized_name);

        if ($filenameToServe === false) {
            throw new BristolianException(
                "Failed to retrieve file from object store [" . $normalized_name . "]."
            );
        }

        // check file is available locally
        return new BristolianFileResponse(
            $filenameToServe,
            $fileDetails->original_filename
        );
    }

    public function showRoom(RoomRepo $roomRepo, $room_id)
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
HTML;
        $params = [
            ':html_room_name' => $room->getName(),
            ':html_room_description' => $room->getPurpose(),
        ];

        $content = esprintf($template, $params);

        return $content;
    }
}
