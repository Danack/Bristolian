<?php

declare(strict_types=1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\UpdateRoomNoteParam;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class UpdateRoomNoteParamTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Parameters\UpdateRoomNoteParam
     */
    public function testWorks(): void
    {
        $param = UpdateRoomNoteParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Updated title',
            'markdown' => 'Updated body',
            'document_timestamp' => '2020-01-02T03:04',
        ]));

        $this->assertSame('Updated title', $param->title);
        $this->assertSame('Updated body', $param->markdown);
        $this->assertSame('2020-01-02T03:04', $param->document_timestamp);
    }
}
