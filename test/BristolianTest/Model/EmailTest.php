<?php

namespace BristolianTest\Model;

use Bristolian\Model\Types\Email;
use BristolianTest\BaseTestCase;
use Safe\DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Model\Types\Email::class)]

class EmailTest extends BaseTestCase
{
    public function testConstruct()
    {
        $id = 123;
        $body = 'Email body content';
        $recipient = 'recipient@example.com';
        $retries = 0;
        $status = 'pending';
        $subject = 'Test Subject';
        $createdAt = new DateTimeImmutable();
        $updatedAt = new DateTimeImmutable();

        $email = new Email(
            $id,
            $body,
            $recipient,
            $retries,
            $status,
            $subject,
            $createdAt,
            $updatedAt
        );

        $this->assertSame($id, $email->id);
        $this->assertSame($body, $email->body);
        $this->assertSame($recipient, $email->recipient);
        $this->assertSame($retries, $email->retries);
        $this->assertSame($status, $email->status);
        $this->assertSame($subject, $email->subject);
        $this->assertSame($createdAt, $email->created_at);
        $this->assertSame($updatedAt, $email->updated_at);
    }
}
