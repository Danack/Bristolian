<?php

declare(strict_types = 1);

function getDescription_36(): string
{
    return 'Room videos (YouTube), clips, transcripts, and video tags';
}

function getAllQueries_36(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `video` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL COMMENT 'User who added the video',
  `youtube_video_id` varchar(20) NOT NULL COMMENT 'YouTube video ID e.g. dQw4w9WgXcQ',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT uc_video_id UNIQUE (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Canonical YouTube video reference';
SQL;

    $sql[] = <<< SQL
CREATE TABLE `room_video` (
  `id` varchar(36) NOT NULL,
  `room_id` varchar(36) NOT NULL,
  `video_id` varchar(36) NOT NULL,
  `title` varchar(1024) DEFAULT NULL,
  `description` varchar(12000) DEFAULT NULL,
  `parent_room_video_id` varchar(36) DEFAULT NULL COMMENT 'Set for clips; references source room_video',
  `start_seconds` int DEFAULT NULL COMMENT 'Clip start in seconds; null for full video',
  `end_seconds` int DEFAULT NULL COMMENT 'Clip end in seconds; null for full video',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT uc_room_video_id UNIQUE (id),
  FOREIGN KEY (room_id) REFERENCES room(id),
  FOREIGN KEY (video_id) REFERENCES video(id),
  FOREIGN KEY (parent_room_video_id) REFERENCES room_video(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SQL;

    $sql[] = <<< SQL
CREATE TABLE `room_video_transcript` (
  `id` varchar(36) NOT NULL,
  `room_video_id` varchar(36) NOT NULL,
  `transcript_number` int NOT NULL COMMENT 'Incrementing per room_video (1, 2, 3...)',
  `language` varchar(10) DEFAULT NULL,
  `vtt_content` MEDIUMTEXT NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT uc_room_video_transcript_id UNIQUE (id),
  FOREIGN KEY (room_video_id) REFERENCES room_video(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SQL;

    $sql[] = <<< SQL
CREATE TABLE `room_video_tag` (
  `room_video_id` varchar(36) NOT NULL,
  `tag_id` varchar(36) NOT NULL,
  PRIMARY KEY (`room_video_id`, `tag_id`),
  CONSTRAINT room_video_tag_room_video_fk FOREIGN KEY (`room_video_id`) REFERENCES `room_video` (`id`) ON DELETE CASCADE,
  CONSTRAINT room_video_tag_tag_fk FOREIGN KEY (`tag_id`) REFERENCES `room_tag` (`tag_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SQL;

    return $sql;
}
