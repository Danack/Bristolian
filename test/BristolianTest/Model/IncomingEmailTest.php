<?php

namespace BristolianTest\Model;

use Bristolian\Exception\BristolianException;
use Bristolian\Model\Types\IncomingEmail;
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
    public function testIncomingEmailParam_construct(): void
    {
        $param = new IncomingEmailParam(
            message_id: '<msg-1>',
            body_plain: 'Body',
            provider_variables: '{}',
            raw_email: 'raw',
            recipient: 'r@b.com',
            retries: '0',
            sender: 's@b.com',
            status: IncomingEmailParam::STATUS_INITIAL,
            stripped_text: 'stripped',
            subject: 'Subject'
        );

        $this->assertSame('<msg-1>', $param->message_id);
        $this->assertSame(IncomingEmailParam::STATUS_INITIAL, $param->status);
    }

    /**
     * @covers \Bristolian\Model\Types\IncomingEmailParam::createFromData
     */
    public function testIncomingEmailParam_createFromData(): void
    {
        $json = file_get_contents(__DIR__ . '/../../data/mailgun/incoming_email_2025_01_18_06_57_45.json');
        $data = json_decode($json, true);
        $data['raw_email'] = $json;
        $incomingEmail = IncomingEmailParam::createFromData($data);

        $this->assertInstanceOf(IncomingEmailParam::class, $incomingEmail);
    }

    /**
     * @covers \Bristolian\Model\Types\IncomingEmailParam::createFromData
     */
    public function testIncomingEmailParam_createFromData_throws_when_key_missing(): void
    {
        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage('Missing key Message-Id');

        IncomingEmailParam::createFromData([]);
    }

    /**
     * @covers \Bristolian\Model\Types\IncomingEmail
     */
    public function testIncomingEmail_construct(): void
    {
        $now = new \DateTimeImmutable();
        $email = new IncomingEmail(
            id: 1,
            message_id: '<msg-123>',
            body_plain: 'Body',
            provider_variables: '{}',
            raw_email: 'raw',
            recipient: 'a@b.com',
            retries: '0',
            sender: 's@b.com',
            status: 'initial',
            stripped_text: 'stripped',
            subject: 'Subject',
            created_at: $now,
            updated_at: $now
        );

        $this->assertSame(1, $email->id);
        $this->assertSame('<msg-123>', $email->message_id);
        $this->assertSame('Subject', $email->subject);
    }
}
