<?php

namespace BristolianTest\Model;

use Bristolian\Model\Types\IncomingEmailParam;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class IncomingEmailTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Types\IncomingEmailParam
     */
    public function testWorks()
    {
        $json = file_get_contents(__DIR__ . '/../../data/mailgun/incoming_email_2025_01_18_06_57_45.json');
        $data = json_decode($json, true);
        $data['raw_email'] = $json;
        $incomingEmail = IncomingEmailParam::createFromData($data);
    }
}
