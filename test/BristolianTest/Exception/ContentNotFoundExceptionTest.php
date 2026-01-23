<?php

namespace BristolianTest\Exception;

use Bristolian\Exception\ContentNotFoundException;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class ContentNotFoundExceptionTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Exception\ContentNotFoundException
     */
    public function testWorks_stairs_id_not_found()
    {
        $stairs_id = "stairs_123";

        $exception = ContentNotFoundException::stairs_id_not_found($stairs_id);

        $this->assertInstanceOf(ContentNotFoundException::class, $exception);
        $this->assertStringContainsString($stairs_id, $exception->getMessage());
        $this->assertStringContainsString("stairs with id", $exception->getMessage());
    }

    /**
     * @covers \Bristolian\Exception\ContentNotFoundException
     */
    public function testWorks_meme_id_not_found()
    {
        $meme_id = "meme_456";

        $exception = ContentNotFoundException::meme_id_not_found($meme_id);

        $this->assertInstanceOf(ContentNotFoundException::class, $exception);
        $this->assertStringContainsString($meme_id, $exception->getMessage());
        $this->assertStringContainsString("meme with id", $exception->getMessage());
    }
}
