<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\EmailIncoming;

use Bristolian\Model\Types\IncomingEmailParam;
use Bristolian\Repo\EmailIncoming\EmailIncoming;
use Bristolian\Repo\EmailIncoming\FakeEmailIncoming;

/**
 * @coversNothing
 * @group standard_repo
 */
class FakeEmailIncomingTest extends EmailIncomingFixture
{
    public function getTestInstance(): EmailIncoming
    {
        return new FakeEmailIncoming();
    }

    /**
     * Fake-specific test: verify emails can be retrieved via getEmails()
     */
    public function test_getEmails_returns_empty_array_initially(): void
    {
        $fakeEmailIncoming = new FakeEmailIncoming();

        $emails = $fakeEmailIncoming->getEmails();

        $this->assertEmpty($emails);
    }

    /**
     * Fake-specific test: verify emails are stored and can be retrieved
     */
    public function test_saveEmail_stores_email_and_can_be_retrieved(): void
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

    /**
     * Fake-specific test: verify multiple emails can be saved and retrieved
     */
    public function test_saveEmail_stores_multiple_emails(): void
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

    /**
     * Fake-specific test: verify emails are maintained in order
     */
    public function test_emails_are_maintained_in_order(): void
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

    /**
     * Fake-specific test: verify it implements the interface
     */
    public function test_implements_EmailIncoming_interface(): void
    {
        $fakeEmailIncoming = new FakeEmailIncoming();

        $this->assertInstanceOf(
            \Bristolian\Repo\EmailIncoming\EmailIncoming::class,
            $fakeEmailIncoming
        );
    }

    /**
     * Fake-specific test: verify getEmails returns all saved emails
     */
    public function test_getEmails_returns_all_saved_emails(): void
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

    /**
     * Fake-specific test: verify saveEmail does not modify original
     */
    public function test_saveEmail_does_not_modify_original(): void
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

    /**
     * Fake-specific test: verify can retrieve specific email by index
     */
    public function test_can_retrieve_specific_email_by_index(): void
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
