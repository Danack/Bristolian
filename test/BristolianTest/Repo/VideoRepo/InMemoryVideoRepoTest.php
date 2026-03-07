<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\VideoRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\Video;
use Bristolian\Repo\VideoRepo\InMemoryVideoRepo;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Bristolian\Repo\VideoRepo\InMemoryVideoRepo
 * @group standard_repo
 */
class InMemoryVideoRepoTest extends TestCase
{
    public function test_create_returns_id(): void
    {
        $repo = new InMemoryVideoRepo();
        $id = $repo->create('user-1', 'dQw4w9WgXcQ');

        $this->assertNotEmpty($id);
    }

    public function test_create_returns_unique_ids(): void
    {
        $repo = new InMemoryVideoRepo();
        $id1 = $repo->create('user-1', 'dQw4w9WgXcQ');
        $id2 = $repo->create('user-1', 'oHg5SJYRHA0');

        $this->assertNotSame($id1, $id2);
    }

    public function test_getById_returns_created_video(): void
    {
        $repo = new InMemoryVideoRepo();
        $id = $repo->create('user-1', 'dQw4w9WgXcQ');

        $video = $repo->getById($id);

        $this->assertInstanceOf(Video::class, $video);
        $this->assertSame($id, $video->id);
        $this->assertSame('user-1', $video->user_id);
        $this->assertSame('dQw4w9WgXcQ', $video->youtube_video_id);
        $this->assertInstanceOf(\DateTimeInterface::class, $video->created_at);
    }

    public function test_getById_throws_for_nonexistent_id(): void
    {
        $repo = new InMemoryVideoRepo();

        $this->expectException(ContentNotFoundException::class);
        $repo->getById('nonexistent-id');
    }

    public function test_multiple_videos_are_independent(): void
    {
        $repo = new InMemoryVideoRepo();
        $id1 = $repo->create('user-1', 'dQw4w9WgXcQ');
        $id2 = $repo->create('user-2', 'oHg5SJYRHA0');

        $video1 = $repo->getById($id1);
        $video2 = $repo->getById($id2);

        $this->assertSame('user-1', $video1->user_id);
        $this->assertSame('dQw4w9WgXcQ', $video1->youtube_video_id);
        $this->assertSame('user-2', $video2->user_id);
        $this->assertSame('oHg5SJYRHA0', $video2->youtube_video_id);
    }
}
