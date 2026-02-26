<?php

namespace Bristolian\Repo\RoomAnnotationRepo;

use Bristolian\Database\annotation;
use Bristolian\Database\room_annotation;
use Bristolian\Model\Types\RoomAnnotationView;
use Bristolian\Parameters\AnnotationParam;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;

class PdoRoomAnnotationRepo implements RoomAnnotationRepo
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    public function addAnnotation(
        string $user_id,
        string $room_id,
        string $file_id,
        AnnotationParam $annotationParam
    ): string {
        $uuid = Uuid::uuid7();
        $annotation_id = $uuid->toString();
        $sql = annotation::INSERT;

        $params = [
            ':id' => $annotation_id,
            ':user_id' => $user_id,
            ':file_id' => $file_id,
            ':highlights_json' => $annotationParam->highlights_json,
            ':text' => $annotationParam->text
        ];
        $this->pdoSimple->execute($sql, $params);

        $sql2 = room_annotation::INSERT;
        $uuid = Uuid::uuid7();
        $room_annotation_id = $uuid->toString();
        $params2 = [
            ':id' => $room_annotation_id,
            ':room_id' => $room_id,
            ':annotation_id' => $annotation_id,
            ':title' => $annotationParam->title
        ];

        $this->pdoSimple->execute($sql2, $params2);

        return $room_annotation_id;
    }

    /**
     * @param string $room_id
     * @return RoomAnnotationView[]
     */
    public function getAnnotationsForRoom(string $room_id): array
    {
        $sql = <<< SQL
select  
    a.id,
    a.user_id,
    a.file_id,
    a.highlights_json,
    a.text,
    ra.title,
    ra.id as room_annotation_id
from
  annotation a
left join
  room_annotation ra
on 
 a.id = ra.annotation_id
where
  room_id = :room_id
SQL;

        $params = [
            ':room_id' => $room_id
        ];

        return $this->pdoSimple->fetchAllAsObjectConstructor(
            $sql,
            $params,
            RoomAnnotationView::class
        );
    }


    /**
     * @param string $room_id
     * @param string $file_id
     * @return RoomAnnotationView[]
     */
    public function getAnnotationsForRoomAndFile(
        string $room_id,
        string $file_id
    ): array {

        $sql = <<< SQL
select  
    a.id,
    a.user_id,
    a.file_id,
    a.highlights_json,
    a.text,
    ra.title,
    ra.id as room_annotation_id
from
  annotation a
left join
  room_annotation ra
on 
 a.id = ra.annotation_id
where
  ra.room_id = :room_id
and
  a.file_id = :file_id
SQL;

        $params = [
            ':room_id' => $room_id,
            ':file_id' => $file_id
        ];

        return $this->pdoSimple->fetchAllAsObjectConstructor(
            $sql,
            $params,
            RoomAnnotationView::class
        );
    }
}
