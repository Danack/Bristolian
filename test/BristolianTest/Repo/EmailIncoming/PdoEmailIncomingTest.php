<?php

namespace BristolianTest\Repo\EmailIncoming;

use Bristolian\Model\Types\IncomingEmailParam;
use Bristolian\Repo\EmailIncoming\PdoEmailIncoming;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;

/**
 * @covers \Bristolian\Repo\EmailIncoming\PdoEmailIncoming
 * @group db
 */
class PdoEmailIncomingTest extends BaseTestCase
{
    use TestPlaceholders;

    public function testSaveEmail()
    {
        $incoming_email = $this->getTestIncomingEmeail();
        $pdoEmailIncoming = $this->injector->make(PdoEmailIncoming::class);
        
        // Should not throw exception
        $pdoEmailIncoming->saveEmail($incoming_email);
        
        // If we got here, the email was saved successfully
        // TODO - write useful assertions
//        $this->assertTrue(true);
    }

    public function testSaveEmailWithAllFields()
    {
        $emailParam = new IncomingEmailParam(
            message_id: 'test-message-id-' . uniqid(),
            body_plain: 'This is the plain body text of the email.',
            provider_variables: json_encode(['key' => 'value']),
            raw_email: 'Raw email content here',
            recipient: 'test@example.com',
            retries: '0',
            sender: 'sender@example.com',
            status: IncomingEmailParam::STATUS_INITIAL,
            stripped_text: 'Stripped text content',
            subject: 'Test Email Subject'
        );
        
        $pdoEmailIncoming = $this->injector->make(PdoEmailIncoming::class);
        $pdoEmailIncoming->saveEmail($emailParam);
        // TODO - write useful assertions
        //$this->assertTrue(true);
    }

    public function testSaveMultipleEmails()
    {
        $pdoEmailIncoming = $this->injector->make(PdoEmailIncoming::class);
        
        for ($i = 0; $i < 3; $i++) {
            $emailParam = new IncomingEmailParam(
                message_id: 'test-message-id-' . $i . '-' . uniqid(),
                body_plain: "Body text $i",
                provider_variables: json_encode(['iteration' => $i]),
                raw_email: "Raw email $i",
                recipient: "test$i@example.com",
                retries: '0',
                sender: "sender$i@example.com",
                status: IncomingEmailParam::STATUS_INITIAL,
                stripped_text: "Stripped $i",
                subject: "Subject $i"
            );
            
            $pdoEmailIncoming->saveEmail($emailParam);
        }

        // TODO - write useful assertions
        //$this->assertTrue(true);
    }

    public function testSaveEmailWithLongContent()
    {
        $longText = str_repeat('This is a long email body. ', 100);
        
        $emailParam = new IncomingEmailParam(
            message_id: 'test-long-message-' . uniqid(),
            body_plain: $longText,
            provider_variables: json_encode(['key' => 'value']),
            raw_email: 'Raw: ' . $longText,
            recipient: 'test@example.com',
            retries: '0',
            sender: 'sender@example.com',
            status: IncomingEmailParam::STATUS_INITIAL,
            stripped_text: $longText,
            subject: 'Long Email Subject'
        );
        
        $pdoEmailIncoming = $this->injector->make(PdoEmailIncoming::class);
        $pdoEmailIncoming->saveEmail($emailParam);

        // TODO - write useful assertions
        // $this->assertTrue(true);
    }

    public function testSaveEmailWithSpecialCharacters()
    {
        $emailParam = new IncomingEmailParam(
            message_id: 'test-special-' . uniqid(),
            body_plain: 'Email with special chars: <>&"\'',
            provider_variables: json_encode(['special' => '<>&"\'']),
            raw_email: 'Raw email with special chars',
            recipient: 'test+tag@example.com',
            retries: '0',
            sender: 'sender+tag@example.com',
            status: IncomingEmailParam::STATUS_INITIAL,
            stripped_text: 'Stripped special chars',
            subject: 'Subject with special: <>&"\''
        );
        
        $pdoEmailIncoming = $this->injector->make(PdoEmailIncoming::class);
        $pdoEmailIncoming->saveEmail($emailParam);

        // TODO - write useful assertions
//        $this->assertTrue(true);
    }

    public function testSaveEmailWithUnicodeContent()
    {
        $emailParam = new IncomingEmailParam(
            message_id: 'test-unicode-' . uniqid(),
            body_plain: 'Unicode: 你好世界 🎉 Привет мир',
            provider_variables: json_encode(['unicode' => '你好']),
            raw_email: 'Raw with unicode',
            recipient: 'test@example.com',
            retries: '0',
            sender: 'sender@example.com',
            status: IncomingEmailParam::STATUS_INITIAL,
            stripped_text: 'Stripped unicode',
            subject: 'Subject: 你好世界'
        );
        
        $pdoEmailIncoming = $this->injector->make(PdoEmailIncoming::class);
        $pdoEmailIncoming->saveEmail($emailParam);

        // TODO - write useful assertions
//        $this->assertTrue(true);
    }

    public function testSaveEmailImplementsInterface()
    {
        $pdoEmailIncoming = $this->injector->make(PdoEmailIncoming::class);
        
        $this->assertInstanceOf(
            \Bristolian\Repo\EmailIncoming\EmailIncoming::class,
            $pdoEmailIncoming
        );
    }

    public function testSaveEmailWithEmptyRetries()
    {
        $emailParam = new IncomingEmailParam(
            message_id: 'test-retries-' . uniqid(),
            body_plain: 'Body text',
            provider_variables: json_encode([]),
            raw_email: 'Raw email',
            recipient: 'test@example.com',
            retries: '0',
            sender: 'sender@example.com',
            status: IncomingEmailParam::STATUS_INITIAL,
            stripped_text: 'Stripped text',
            subject: 'Subject'
        );
        
        $pdoEmailIncoming = $this->injector->make(PdoEmailIncoming::class);
        $pdoEmailIncoming->saveEmail($emailParam);

        // TODO - write useful assertions
//        $this->assertTrue(true);
    }
}
