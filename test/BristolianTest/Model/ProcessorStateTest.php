<?php

namespace BristolianTest\Model;

use Bristolian\Model\Types\ProcessorState;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class ProcessorStateTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Types\ProcessorState
     */
    public function testConstruct()
    {
        $id = 'processor-123';
        $enabled = true;
        $type = 'email_processor';
        $updatedAt = new \DateTimeImmutable();

        $state = new ProcessorState($id, $enabled, $type, $updatedAt);

        $this->assertSame($id, $state->id);
        $this->assertSame($enabled, $state->enabled);
        $this->assertSame($type, $state->type);
        $this->assertSame($updatedAt, $state->updated_at);
    }

    /**
     * @covers \Bristolian\Model\Types\ProcessorState
     */
    public function testConstructWithDisabled()
    {
        $state = new ProcessorState(
            'processor-456',
            false,
            'test_processor',
            new \DateTimeImmutable()
        );

        $this->assertFalse($state->enabled);
    }
}
