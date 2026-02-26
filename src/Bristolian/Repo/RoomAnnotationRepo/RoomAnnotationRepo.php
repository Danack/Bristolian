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
}
