<?php

declare(strict_types=1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\CreateRoomNoteParam;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class CreateRoomNoteParamTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Parameters\CreateRoomNoteParam
     */
    public function testWorks(): void
    {
        $param = CreateRoomNoteParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Agenda',
            'markdown' => "# Heading\n\nBody",
        ]));

        $this->assertSame('Agenda', $param->title);
        $this->assertSame("# Heading\n\nBody", $param->markdown);
        $this->assertNull($param->document_timestamp);
    }

    /**
     * @covers \Bristolian\Parameters\CreateRoomNoteParam
     */
    public function testTrimsTitle(): void
    {
        $param = CreateRoomNoteParam::createFromVarMap(new ArrayVarMap([
            'title' => '  Agenda  ',
            'markdown' => 'text',
        ]));

        $this->assertSame('Agenda', $param->title);
    }
}
