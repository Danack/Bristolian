<?php

namespace Bristolian\CliController;

use Bristolian\Parameters\AddVideoClipParam;
use Bristolian\Parameters\AddVideoParam;
use Bristolian\Parameters\AnnotationParam;
use Bristolian\Parameters\LinkParam;
use Bristolian\Parameters\TagParams;
use Bristolian\Repo\AdminRepo\AdminRepo;
use Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo;
use Bristolian\Repo\RoomAnnotationTagRepo\RoomAnnotationTagRepo;
use Bristolian\Repo\RoomFileRepo\RoomFileRepo;
use Bristolian\Repo\RoomLinkRepo\RoomLinkRepo;
use Bristolian\Repo\RoomRepo\RoomRepo;
use Bristolian\Repo\RoomTagRepo\RoomTagRepo;
use Bristolian\Repo\RoomVideoRepo\RoomVideoRepo;
use Bristolian\Repo\VideoRepo\VideoRepo;
use Bristolian\Service\CliOutput\CliOutput;
use Bristolian\Service\RoomFileStorage\RoomFileStorage;
use Bristolian\Service\RoomFileStorage\UploadError;
use Bristolian\UploadedFiles\UploadedFile;
use DataType\Exception\JsonDecodeException;
use DataType\Exception\ValidationException;
use Bristolian\Exception\TooManyRoomTagsException;
use VarMap\ArrayVarMap;

/**
 * Code for managing rooms from the command line.
 */
class Rooms
{
    public function __construct(
        private CliOutput $cliOutput
    ) {
    }

    public function createFromCli(
        AdminRepo $adminRepo,
        RoomRepo $roomRepo,
        string $name,
        string $purpose
    ): void {

        $user_id = $adminRepo->getAdminUserId(getAdminEmailAddress());
        if ($user_id === null) {
            $this->cliOutput->write("Failed to find admin user");
            $this->cliOutput->exit(-1);
        }

        $roomRepo->createRoom(
            $user_id,
            $name,
            $purpose
        );
    }

    public function addFileFromCli(
        AdminRepo $adminRepo,
        RoomRepo $roomRepo,
        RoomFileStorage $roomFileStorage,
        string $room_name,
        string $file_path
    ): void {
        $user_id = $adminRepo->getAdminUserId(getAdminEmailAddress());
        if ($user_id === null) {
            $this->cliOutput->write("Failed to find admin user\n");
            $this->cliOutput->exit(-1);
        }

        $matching_rooms = $roomRepo->getRoomByName($room_name);

        if (count($matching_rooms) === 0) {
            $this->cliOutput->write("No room found with name: " . $room_name . "\n");
            $this->cliOutput->exit(-1);
        }

        if (count($matching_rooms) > 1) {
            $this->cliOutput->write(
                "Multiple rooms have the name \"" . $room_name . "\"; names must be unique for this command.\n"
            );
            $this->cliOutput->exit(-1);
        }

        $room = $matching_rooms[0];

        if (file_exists($file_path) !== true) {
            $this->cliOutput->write("File not found: " . $file_path . "\n");
            $this->cliOutput->exit(-1);
        }

        $uploaded_file = UploadedFile::fromFile($file_path);
        $file_id_or_error = $roomFileStorage->storeFileForRoomAndUser(
            $user_id,
            $room->id,
            $uploaded_file
        );

        if ($file_id_or_error instanceof UploadError) {
            $this->cliOutput->write("Failed to upload file: " . $file_id_or_error->error_message . "\n");
            $this->cliOutput->exit(-1);
        }

        $this->cliOutput->write("File added to room with stored file id: " . $file_id_or_error . "\n");
    }

    public function addFileAnnotationFromCli(
        AdminRepo $adminRepo,
        RoomRepo $roomRepo,
        RoomFileRepo $roomFileRepo,
        RoomAnnotationRepo $roomAnnotationRepo,
        string $room_name,
        string $original_filename,
        string $annotation_json
    ): void {
        $user_id = $adminRepo->getAdminUserId(getAdminEmailAddress());
        if ($user_id === null) {
            $this->cliOutput->write("Failed to find admin user\n");
            $this->cliOutput->exit(-1);
        }

        $matching_rooms = $roomRepo->getRoomByName($room_name);

        if (count($matching_rooms) === 0) {
            $this->cliOutput->write("No room found with name: " . $room_name . "\n");
            $this->cliOutput->exit(-1);
        }

        if (count($matching_rooms) > 1) {
            $this->cliOutput->write(
                "Multiple rooms have the name \"" . $room_name . "\"; names must be unique for this command.\n"
            );
            $this->cliOutput->exit(-1);
        }

        $room = $matching_rooms[0];

        $files = $roomFileRepo->getFilesInRoomByOriginalFilename($room->id, $original_filename);
        if (count($files) === 0) {
            $this->cliOutput->write(
                "No file with original filename \"" . $original_filename . "\" in that room.\n"
            );
            $this->cliOutput->exit(-1);
        }

        if (count($files) > 1) {
            $this->cliOutput->write(
                "Multiple files named \"" . $original_filename . "\" in that room; resolve duplicates first.\n"
            );
            $this->cliOutput->exit(-1);
        }

        try {
            $annotation_param = AnnotationParam::createFromJson($annotation_json);
        }
        catch (JsonDecodeException $e) {
            $this->cliOutput->write("Invalid JSON: " . $e->getMessage() . "\n");
            $this->cliOutput->exit(-1);
        }
        catch (ValidationException $e) {
            $this->cliOutput->write("Invalid annotation parameters: " . $e->getMessage() . "\n");
            $this->cliOutput->exit(-1);
        }

        $room_annotation_id = $roomAnnotationRepo->addAnnotation(
            $user_id,
            $room->id,
            $files[0]->id,
            $annotation_param
        );

        $this->cliOutput->write("Annotation added with room_annotation id: " . $room_annotation_id . "\n");
    }

    public function addLinkFromCli(
        AdminRepo $adminRepo,
        RoomRepo $roomRepo,
        RoomLinkRepo $roomLinkRepo,
        string $room_name,
        string $url,
        ?string $title,
        ?string $description
    ): void {
        $user_id = $adminRepo->getAdminUserId(getAdminEmailAddress());
        if ($user_id === null) {
            $this->cliOutput->write("Failed to find admin user\n");
            $this->cliOutput->exit(-1);
        }

        $matching_rooms = $roomRepo->getRoomByName($room_name);

        if (count($matching_rooms) === 0) {
            $this->cliOutput->write("No room found with name: " . $room_name . "\n");
            $this->cliOutput->exit(-1);
        }

        if (count($matching_rooms) > 1) {
            $this->cliOutput->write(
                "Multiple rooms have the name \"" . $room_name . "\"; names must be unique for this command.\n"
            );
            $this->cliOutput->exit(-1);
        }

        $room = $matching_rooms[0];

        try {
            $link_param = LinkParam::createFromArray([
                'url' => $url,
                'title' => $title ?? '',
                'description' => $description ?? '',
            ]);
        } catch (ValidationException $e) {
            $this->cliOutput->write("Invalid link parameters: " . $e->getMessage() . "\n");
            $this->cliOutput->exit(-1);
        }

        $room_link_id = $roomLinkRepo->addLinkToRoomFromParam(
            $user_id,
            $room->id,
            $link_param
        );

        $this->cliOutput->write("Link added to room with room_link id: " . $room_link_id . "\n");
    }

    public function addVideoFromCli(
        AdminRepo $adminRepo,
        RoomRepo $roomRepo,
        VideoRepo $videoRepo,
        RoomVideoRepo $roomVideoRepo,
        string $room_name,
        string $url,
        ?string $title,
        ?string $description
    ): void {
        $user_id = $adminRepo->getAdminUserId(getAdminEmailAddress());
        if ($user_id === null) {
            $this->cliOutput->write("Failed to find admin user\n");
            $this->cliOutput->exit(-1);
        }

        $matching_rooms = $roomRepo->getRoomByName($room_name);

        if (count($matching_rooms) === 0) {
            $this->cliOutput->write("No room found with name: " . $room_name . "\n");
            $this->cliOutput->exit(-1);
        }

        if (count($matching_rooms) > 1) {
            $this->cliOutput->write(
                "Multiple rooms have the name \"" . $room_name . "\"; names must be unique for this command.\n"
            );
            $this->cliOutput->exit(-1);
        }

        $room = $matching_rooms[0];

        try {
            $param = AddVideoParam::createFromArray([
                'url' => $url,
                'title' => $title ?? '',
                'description' => $description ?? '',
            ]);
        } catch (ValidationException $e) {
            $this->cliOutput->write("Invalid video parameters: " . $e->getMessage() . "\n");
            $this->cliOutput->exit(-1);
        }

        $video_id = $videoRepo->create($user_id, $param->youtube_video_id);
        $room_video = $roomVideoRepo->addVideo($room->id, $video_id, $param->title, $param->description);

        $this->cliOutput->write("Video added to room with room_video id: " . $room_video->id . "\n");
    }

    public function addVideoClipFromCli(
        AdminRepo $adminRepo,
        RoomRepo $roomRepo,
        VideoRepo $videoRepo,
        RoomVideoRepo $roomVideoRepo,
        string $room_name,
        string $url,
        string $start_time,
        string $end_time,
        ?string $title,
        ?string $description
    ): void {
        $user_id = $adminRepo->getAdminUserId(getAdminEmailAddress());
        if ($user_id === null) {
            $this->cliOutput->write("Failed to find admin user\n");
            $this->cliOutput->exit(-1);
        }

        $matching_rooms = $roomRepo->getRoomByName($room_name);

        if (count($matching_rooms) === 0) {
            $this->cliOutput->write("No room found with name: " . $room_name . "\n");
            $this->cliOutput->exit(-1);
        }

        if (count($matching_rooms) > 1) {
            $this->cliOutput->write(
                "Multiple rooms have the name \"" . $room_name . "\"; names must be unique for this command.\n"
            );
            $this->cliOutput->exit(-1);
        }

        $room = $matching_rooms[0];

        try {
            $param = AddVideoClipParam::createFromArray([
                'url' => $url,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'title' => $title ?? '',
                'description' => $description ?? '',
            ]);
        } catch (ValidationException $e) {
            $this->cliOutput->write("Invalid video clip parameters: " . $e->getMessage() . "\n");
            $this->cliOutput->exit(-1);
        }

        $video_id = $videoRepo->create($user_id, $param->youtube_video_id);
        $room_video = $roomVideoRepo->addClip(
            $room->id,
            $video_id,
            $param->title,
            $param->description,
            $param->start_seconds,
            $param->end_seconds
        );

        $this->cliOutput->write("Video clip added to room with room_video id: " . $room_video->id . "\n");
    }

    public function addRoomTagFromCli(
        AdminRepo $adminRepo,
        RoomRepo $roomRepo,
        RoomTagRepo $roomTagRepo,
        string $room_name,
        string $tag_text,
        ?string $description
    ): void {
        if ($adminRepo->getAdminUserId(getAdminEmailAddress()) === null) {
            $this->cliOutput->write("Failed to find admin user\n");
            $this->cliOutput->exit(-1);
        }

        $matching_rooms = $roomRepo->getRoomByName($room_name);

        if (count($matching_rooms) === 0) {
            $this->cliOutput->write("No room found with name: " . $room_name . "\n");
            $this->cliOutput->exit(-1);
        }

        if (count($matching_rooms) > 1) {
            $this->cliOutput->write(
                "Multiple rooms have the name \"" . $room_name . "\"; names must be unique for this command.\n"
            );
            $this->cliOutput->exit(-1);
        }

        $room = $matching_rooms[0];

        try {
            $tag_param = TagParams::createFromVarMap(new ArrayVarMap([
                'text' => $tag_text,
                'description' => $description ?? '',
            ]));
        // @codeCoverageIgnoreStart
        } catch (ValidationException $e) {

            // Not reached from typed CLI args: TagParams uses BasicString without extra constraints.
            $this->cliOutput->write("Invalid tag parameters: " . $e->getMessage() . "\n");
            $this->cliOutput->exit(-1);
        }
        // @codeCoverageIgnoreEnd

        try {
            $room_tag = $roomTagRepo->createTag($room->id, $tag_param);
        } catch (TooManyRoomTagsException $e) {
            $this->cliOutput->write($e->getMessage() . "\n");
            $this->cliOutput->exit(-1);
        }

        $this->cliOutput->write("Tag created with tag_id: " . $room_tag->tag_id . "\n");
    }

    public function addAnnotationTagFromCli(
        AdminRepo $adminRepo,
        RoomRepo $roomRepo,
        RoomAnnotationRepo $roomAnnotationRepo,
        RoomAnnotationTagRepo $roomAnnotationTagRepo,
        RoomTagRepo $roomTagRepo,
        string $room_name,
        string $annotation_title,
        string $tag_text,
        ?string $description
    ): void {
        if ($adminRepo->getAdminUserId(getAdminEmailAddress()) === null) {
            $this->cliOutput->write("Failed to find admin user\n");
            $this->cliOutput->exit(-1);
        }

        $matching_rooms = $roomRepo->getRoomByName($room_name);

        if (count($matching_rooms) === 0) {
            $this->cliOutput->write("No room found with name: " . $room_name . "\n");
            $this->cliOutput->exit(-1);
        }

        if (count($matching_rooms) > 1) {
            $this->cliOutput->write(
                "Multiple rooms have the name \"" . $room_name . "\"; names must be unique for this command.\n"
            );
            $this->cliOutput->exit(-1);
        }

        $room = $matching_rooms[0];

        $matchingAnnotations = $roomAnnotationRepo->getAnnotationsForRoomAndTitle($room->id, $annotation_title);
        if (count($matchingAnnotations) === 0) {
            $this->cliOutput->write("No annotation with that title in this room.\n");
            $this->cliOutput->exit(-1);
        }
        if (count($matchingAnnotations) > 1) {
            $this->cliOutput->write("Multiple annotations in this room have that title. Use a unique title.\n");
            $this->cliOutput->exit(-1);
        }
        $room_annotation_id = $matchingAnnotations[0]->room_annotation_id;

        $room_tag = null;
        foreach ($roomTagRepo->getTagsForRoom($room->id) as $tag) {
            if ($tag->text === $tag_text) {
                $room_tag = $tag;
                break;
            }
        }

        if ($room_tag === null) {
            try {
                $tag_param = TagParams::createFromVarMap(new ArrayVarMap([
                    'text' => $tag_text,
                    'description' => $description ?? '',
                ]));
            // @codeCoverageIgnoreStart
            } catch (ValidationException $e) {

                // Not reached from typed CLI args: TagParams uses BasicString without extra constraints.
                $this->cliOutput->write("Invalid tag parameters: " . $e->getMessage() . "\n");
                $this->cliOutput->exit(-1);
            }
            // @codeCoverageIgnoreEnd

            try {
                $room_tag = $roomTagRepo->createTag($room->id, $tag_param);
            } catch (TooManyRoomTagsException $e) {
                $this->cliOutput->write($e->getMessage() . "\n");
                $this->cliOutput->exit(-1);
            }
        }

        $existing_ids = $roomAnnotationTagRepo->getTagIdsForRoomAnnotation($room_annotation_id);
        if (in_array($room_tag->tag_id, $existing_ids, true)) {
            $this->cliOutput->write("Annotation already has this tag (tag_id: " . $room_tag->tag_id . ").\n");
            return;
        }

        $merged = array_merge($existing_ids, [$room_tag->tag_id]);
        $roomAnnotationTagRepo->setTagsForRoomAnnotation($room_annotation_id, $merged);

        $this->cliOutput->write("Tag attached to annotation. tag_id: " . $room_tag->tag_id . "\n");
    }
}
