<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\IncomingEmailParam;

/**
 * @coversNothing
 */
class IncomingEmailTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\IncomingEmailParam
     */
    public function testWorks()
    {
        $json = file_get_contents(__DIR__ . '/../../data/mailgun/incoming_email_2025_01_18_06_57_45.json');
        $data = json_decode($json, true);
        $data['raw_email'] = $json;
        $incomingEmail = IncomingEmailParam::createFromData($data);
    }
}
