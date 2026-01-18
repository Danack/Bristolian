<?php

namespace BristolianTest\Repo\EmailIncoming;

use Bristolian\Model\Types\IncomingEmailParam;
use Bristolian\Repo\EmailIncoming\FakeEmailIncoming;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Repo\EmailIncoming\FakeEmailIncoming
 */
class FakeEmailIncomingTest extends BaseTestCase
{
    public function testSaveEmailStoresEmail()
    {
        $fakeEmailIncoming = new FakeEmailIncoming();
        
        $emailParam = new IncomingEmailParam(
            message_id: 'test-message-1',
            body_plain: 'Test body',
            provider_variables: json_encode(['key' => 'value']),
            raw_email: 'Raw email content',
            recipient: 'test@example.com',
            retries: '0',
            sender: 'sender@example.com',
            status: IncomingEmailParam::STATUS_INITIAL,
            stripped_text: 'Stripped text',
            subject: 'Test Subject'
        );
        
        $fakeEmailIncoming->saveEmail($emailParam);
        
        $emails = $fakeEmailIncoming->getEmails();
        $this->assertCount(1, $emails);
        $this->assertSame($emailParam, $emails[0]);
    }

    public function testGetEmailsReturnsEmptyArrayInitially()
    {
        $fakeEmailIncoming = new FakeEmailIncoming();
        
        $emails = $fakeEmailIncoming->getEmails();

        $this->assertEmpty($emails);
    }

    public function testSaveMultipleEmails()
    {
        $fakeEmailIncoming = new FakeEmailIncoming();
        
        $email1 = new IncomingEmailParam(
            message_id: 'test-message-1',
            body_plain: 'Body 1',
            provider_variables: json_encode([]),
            raw_email: 'Raw 1',
            recipient: 'test1@example.com',
            retries: '0',
            sender: 'sender1@example.com',
            status: IncomingEmailParam::STATUS_INITIAL,
            stripped_text: 'Stripped 1',
            subject: 'Subject 1'
        );
        
        $email2 = new IncomingEmailParam(
            message_id: 'test-message-2',
            body_plain: 'Body 2',
            provider_variables: json_encode([]),
            raw_email: 'Raw 2',
            recipient: 'test2@example.com',
            retries: '0',
            sender: 'sender2@example.com',
            status: IncomingEmailParam::STATUS_INITIAL,
            stripped_text: 'Stripped 2',
            subject: 'Subject 2'
        );
        
        $fakeEmailIncoming->saveEmail($email1);
        $fakeEmailIncoming->saveEmail($email2);
        
        $emails = $fakeEmailIncoming->getEmails();
        $this->assertCount(2, $emails);
        $this->assertSame($email1, $emails[0]);
        $this->assertSame($email2, $emails[1]);
    }

    public function testEmailsAreMaintainedInOrder()
    {
        $fakeEmailIncoming = new FakeEmailIncoming();
        
        $emailParams = [];
        for ($i = 0; $i < 5; $i++) {
            $emailParams[$i] = new IncomingEmailParam(
                message_id: "test-message-$i",
                body_plain: "Body $i",
                provider_variables: json_encode(['index' => $i]),
                raw_email: "Raw $i",
                recipient: "test$i@example.com",
                retries: '0',
                sender: "sender$i@example.com",
                status: IncomingEmailParam::STATUS_INITIAL,
                stripped_text: "Stripped $i",
                subject: "Subject $i"
            );
            
            $fakeEmailIncoming->saveEmail($emailParams[$i]);
        }
        
        $savedEmails = $fakeEmailIncoming->getEmails();
        $this->assertCount(5, $savedEmails);
        
        for ($i = 0; $i < 5; $i++) {
            $this->assertSame($emailParams[$i], $savedEmails[$i]);
        }
    }

    public function testImplementsEmailIncomingInterface()
    {
        $fakeEmailIncoming = new FakeEmailIncoming();
        
        $this->assertInstanceOf(
            \Bristolian\Repo\EmailIncoming\EmailIncoming::class,
            $fakeEmailIncoming
        );
    }

    public function testGetEmailsReturnsAllSavedEmails()
    {
        $fakeEmailIncoming = new FakeEmailIncoming();
        
        $count = 10;
        for ($i = 0; $i < $count; $i++) {
            $emailParam = new IncomingEmailParam(
                message_id: "id-$i",
                body_plain: "Body $i",
                provider_variables: '{}',
                raw_email: "Raw $i",
                recipient: "test@example.com",
                retries: '0',
                sender: "sender@example.com",
                status: IncomingEmailParam::STATUS_INITIAL,
                stripped_text: "Text $i",
                subject: "Subject $i"
            );
            $fakeEmailIncoming->saveEmail($emailParam);
        }
        
        $emails = $fakeEmailIncoming->getEmails();
        $this->assertCount($count, $emails);
    }

    public function testSaveEmailDoesNotModifyOriginal()
    {
        $fakeEmailIncoming = new FakeEmailIncoming();
        
        $originalEmail = new IncomingEmailParam(
            message_id: 'original-message',
            body_plain: 'Original body',
            provider_variables: json_encode(['original' => true]),
            raw_email: 'Original raw',
            recipient: 'original@example.com',
            retries: '0',
            sender: 'original-sender@example.com',
            status: IncomingEmailParam::STATUS_INITIAL,
            stripped_text: 'Original stripped',
            subject: 'Original Subject'
        );
        
        $fakeEmailIncoming->saveEmail($originalEmail);
        
        $emails = $fakeEmailIncoming->getEmails();
        $this->assertSame($originalEmail, $emails[0]);
        $this->assertSame('original-message', $originalEmail->message_id);
    }

    public function testCanRetrieveSpecificEmailByIndex()
    {
        $fakeEmailIncoming = new FakeEmailIncoming();
        
        $email1 = new IncomingEmailParam(
            message_id: 'first',
            body_plain: 'First',
            provider_variables: '{}',
            raw_email: 'Raw 1',
            recipient: 'test@example.com',
            retries: '0',
            sender: 'sender@example.com',
            status: IncomingEmailParam::STATUS_INITIAL,
            stripped_text: 'Text 1',
            subject: 'Subject 1'
        );
        
        $email2 = new IncomingEmailParam(
            message_id: 'second',
            body_plain: 'Second',
            provider_variables: '{}',
            raw_email: 'Raw 2',
            recipient: 'test@example.com',
            retries: '0',
            sender: 'sender@example.com',
            status: IncomingEmailParam::STATUS_INITIAL,
            stripped_text: 'Text 2',
            subject: 'Subject 2'
        );
        
        $fakeEmailIncoming->saveEmail($email1);
        $fakeEmailIncoming->saveEmail($email2);
        
        $emails = $fakeEmailIncoming->getEmails();
        $this->assertSame('first', $emails[0]->message_id);
        $this->assertSame('second', $emails[1]->message_id);
    }
}
