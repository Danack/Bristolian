<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\VideoRepo;

use Bristolian\Exception\ContentNotFoundException;
use BristolianGenerated\Model\Video;
use Bristolian\Repo\VideoRepo\PdoVideoRepo;
use Bristolian\Repo\VideoRepo\VideoRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Support\HasTestWorld;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @group db
 */

#[CoversClass(PdoVideoRepo::class)]
class PdoVideoRepoTest extends BaseTestCase
{
    use HasTestWorld;

    private ?string $userId = null;
    private ?string $videoId = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->ensureStandardSetup();
        $this->userId = $this->standardTestData()->getTestingUserId();
        $repo = $this->getRepo();
        $this->videoId = $repo->create($this->userId, 'dQw4w9WgXcQ');
    }

    private function getRepo(): VideoRepo
    {
        return $this->injector->make(PdoVideoRepo::class);
    }

    public function test_create_returns_video_id(): void
    {
        $repo = $this->getRepo();
        assert($this->userId !== null);
        $id = $repo->create($this->userId, 'abc123xyz');
        $this->assertNotEmpty($id);
        $this->assertNotEquals($this->videoId, $id);
    }

    public function test_getById_returns_video(): void
    {
        $repo = $this->getRepo();
        assert($this->videoId !== null);
        $video = $repo->getById($this->videoId);
        $this->assertInstanceOf(Video::class, $video);
        $this->assertSame($this->videoId, $video->id);
        $this->assertSame('dQw4w9WgXcQ', $video->youtube_video_id);
    }

    public function test_getById_throws_for_nonexistent_id(): void
    {
        $repo = $this->getRepo();
        $this->expectException(ContentNotFoundException::class);
        $this->expectExceptionMessage('not found');
        $repo->getById('00000000-0000-0000-0000-000000000000');
    }
}
