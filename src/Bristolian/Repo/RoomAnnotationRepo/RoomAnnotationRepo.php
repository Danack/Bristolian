<?php

namespace Bristolian\Repo\RoomAnnotationRepo;

use Bristolian\Model\Types\RoomAnnotationView;
use Bristolian\Parameters\AnnotationParam;

interface RoomAnnotationRepo
{
    public function addAnnotation(
        string $user_id,
        string $room_id,
        string $file_id,
        AnnotationParam $annotationParam
    ): string;


    /**
     * @param string $room_id
     * @return RoomAnnotationView[]
     */
    public function getAnnotationsForRoom(string $room_id): array;

    /**
     * @param string $room_id
     * @param string $file_id
     * @return RoomAnnotationView[]
     */
    public function getAnnotationsForRoomAndFile(
        string $room_id,
        string $file_id,
    ): array;

    /**
     * @param string $room_id
     * @param string $title
     * @return RoomAnnotationView[]
     */
    public function getAnnotationsForRoomAndTitle(
        string $room_id,
        string $title
    ): array;

    /**
     * Update room-specific title and canonical annotation text. Throws if the room annotation
     * is not in the room.
     *
     * @throws \Bristolian\Exception\ContentNotFoundException
     */
    public function updateTitleAndText(
        string $room_id,
        string $room_annotation_id,
        string $title,
        string $text
    ): void;
}
