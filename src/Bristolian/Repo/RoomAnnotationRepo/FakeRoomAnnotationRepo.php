<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomAnnotationRepo;

use Bristolian\Model\Types\RoomAnnotationView;
use Bristolian\Parameters\AnnotationParam;
use Ramsey\Uuid\Uuid;

/**
 * Fake implementation of RoomAnnotationRepo for testing.
 */
class FakeRoomAnnotationRepo implements RoomAnnotationRepo
{
    /**
     * @var array<string, array{id: string, user_id: string, file_id: string, highlights_json: string, text: string}>
     * Keyed by annotation_id
     */
    private array $annotations = [];

    /**
     * @var array<string, array{id: string, room_id: string, annotation_id: string, title: string}>
     * Keyed by room_annotation_id
     */
    private array $roomAnnotations = [];

    /**
     * @param array<string, array{id: string, room_id: string, annotation_id: string, title: string}> $initialRoomAnnotations
     * @param array<string, array{id: string, user_id: string, file_id: string, highlights_json: string, text: string}> $initialAnnotations
     */
    public function __construct(
        array $initialRoomAnnotations = [],
        array $initialAnnotations = []
    ) {
        $this->roomAnnotations = $initialRoomAnnotations;
        $this->annotations = $initialAnnotations;
    }

    public function addAnnotation(
        string $user_id,
        string $room_id,
        string $file_id,
        AnnotationParam $annotationParam
    ): string {
        $uuid = Uuid::uuid7();
        $annotation_id = $uuid->toString();

        $this->annotations[$annotation_id] = [
            'id' => $annotation_id,
            'user_id' => $user_id,
            'file_id' => $file_id,
            'highlights_json' => $annotationParam->highlights_json,
            'text' => $annotationParam->text,
        ];

        $uuid = Uuid::uuid7();
        $room_annotation_id = $uuid->toString();

        $this->roomAnnotations[$room_annotation_id] = [
            'id' => $room_annotation_id,
            'room_id' => $room_id,
            'annotation_id' => $annotation_id,
            'title' => $annotationParam->title,
        ];

        return $room_annotation_id;
    }

    /**
     * @param string $room_id
     * @return RoomAnnotationView[]
     */
    public function getAnnotationsForRoom(string $room_id): array
    {
        $results = [];

        foreach ($this->roomAnnotations as $roomAnnotation) {
            if ($roomAnnotation['room_id'] !== $room_id) {
                continue;
            }

            $annotation = $this->annotations[$roomAnnotation['annotation_id']] ?? null;
            if ($annotation === null) {
                continue;
            }

            $results[] = new RoomAnnotationView(
                id: $annotation['id'],
                user_id: $annotation['user_id'],
                file_id: $annotation['file_id'],
                highlights_json: $annotation['highlights_json'],
                text: $annotation['text'],
                title: $roomAnnotation['title'],
                room_annotation_id: $roomAnnotation['id'],
            );
        }

        return $results;
    }

    /**
     * @param string $room_id
     * @param string $file_id
     * @return RoomAnnotationView[]
     */
    public function getAnnotationsForRoomAndFile(
        string $room_id,
        string $file_id,
    ): array {
        $results = [];

        foreach ($this->roomAnnotations as $roomAnnotation) {
            if ($roomAnnotation['room_id'] !== $room_id) {
                continue;
            }

            $annotation = $this->annotations[$roomAnnotation['annotation_id']] ?? null;
            if ($annotation === null || $annotation['file_id'] !== $file_id) {
                continue;
            }

            $results[] = new RoomAnnotationView(
                id: $annotation['id'],
                user_id: $annotation['user_id'],
                file_id: $annotation['file_id'],
                highlights_json: $annotation['highlights_json'],
                text: $annotation['text'],
                title: $roomAnnotation['title'],
                room_annotation_id: $roomAnnotation['id'],
            );
        }

        return $results;
    }
}
