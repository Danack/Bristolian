<?php

declare(strict_types=1);

namespace BristolianTest\Model\Types;

use Bristolian\Exception\BristolianException;
use BristolianGenerated\Model\RoomTag;
use BristolianGenerated\Model\RoomVideoTranscript;
use Bristolian\Model\Types\AdminUser;
use Bristolian\Model\Types\BccTro;
use Bristolian\Model\Types\BccTroDocument;
use Bristolian\Model\Types\Email;
use Bristolian\Model\Types\FoiRequest;
use Bristolian\Model\Types\IncomingEmail;
use Bristolian\Model\Types\IncomingEmailParam;
use Bristolian\Model\Types\Meme;
use Bristolian\Model\Types\MigrationFromCode;
use Bristolian\Model\Types\MigrationThatHasBeenRun;
use Bristolian\Model\Types\ProcessorState;
use Bristolian\Model\Types\RoomAnnotationWithTags;
use Bristolian\Model\Types\RoomFileWithTags;
use Bristolian\Model\Types\RoomLinkWithTags;
use Bristolian\Model\Types\RoomNoteWithTags;
use Bristolian\Model\Types\RoomVideoTranscriptList;
use Bristolian\Model\Types\RoomVideoWithTags;
use Bristolian\Model\Types\UserWebPushSubscription;
use Bristolian\Model\Types\WebPushNotification;
use Bristolian\Parameters\FoiRequestParams;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;
use Safe\DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Model\Types\AdminUser::class, 'getEmailAddress')]
#[CoversMethod(\Bristolian\Model\Types\AdminUser::class, 'getPasswordHash')]
#[CoversMethod(\Bristolian\Model\Types\AdminUser::class, 'getUserId')]
#[CoversMethod(\Bristolian\Model\Types\AdminUser::class, 'new')]
#[CoversMethod(\Bristolian\Model\Types\BccTro::class, '__construct')]
#[CoversMethod(\Bristolian\Model\Types\BccTroDocument::class, '__construct')]
#[CoversMethod(\Bristolian\Model\Types\Email::class, '__construct')]
#[CoversMethod(\Bristolian\Model\Types\FoiRequest::class, '__construct')]
#[CoversMethod(\Bristolian\Model\Types\FoiRequest::class, 'fromParam')]
#[CoversMethod(\Bristolian\Model\Types\FoiRequest::class, 'getCreatedAt')]
#[CoversMethod(\Bristolian\Model\Types\FoiRequest::class, 'getDescription')]
#[CoversMethod(\Bristolian\Model\Types\FoiRequest::class, 'getFoiRequestId')]
#[CoversMethod(\Bristolian\Model\Types\FoiRequest::class, 'getText')]
#[CoversMethod(\Bristolian\Model\Types\FoiRequest::class, 'getUrl')]
#[CoversMethod(\Bristolian\Model\Types\IncomingEmail::class, '__construct')]
#[CoversMethod(\Bristolian\Model\Types\IncomingEmailParam::class, '__construct')]
#[CoversMethod(\Bristolian\Model\Types\IncomingEmailParam::class, 'createFromData')]
#[CoversMethod(\Bristolian\Model\Types\Meme::class, '__construct')]
#[CoversMethod(\Bristolian\Model\Types\MigrationFromCode::class, '__construct')]
#[CoversMethod(\Bristolian\Model\Types\MigrationThatHasBeenRun::class, '__construct')]
#[CoversMethod(\Bristolian\Model\Types\ProcessorState::class, '__construct')]
#[CoversMethod(\Bristolian\Model\Types\RoomAnnotationWithTags::class, '__construct')]
#[CoversMethod(\Bristolian\Model\Types\RoomFileWithTags::class, '__construct')]
#[CoversMethod(\Bristolian\Model\Types\RoomLinkWithTags::class, '__construct')]
#[CoversMethod(\Bristolian\Model\Types\RoomNoteWithTags::class, '__construct')]
#[CoversMethod(\Bristolian\Model\Types\RoomVideoTranscriptList::class, '__construct')]
#[CoversMethod(\Bristolian\Model\Types\RoomVideoWithTags::class, '__construct')]
#[CoversMethod(\Bristolian\Model\Types\UserWebPushSubscription::class, '__construct')]
#[CoversMethod(\Bristolian\Model\Types\UserWebPushSubscription::class, 'getEndpoint')]
#[CoversMethod(\Bristolian\Model\Types\UserWebPushSubscription::class, 'getExpirationTime')]
#[CoversMethod(\Bristolian\Model\Types\UserWebPushSubscription::class, 'getRaw')]
#[CoversMethod(\Bristolian\Model\Types\WebPushNotification::class, 'create')]
#[CoversMethod(\Bristolian\Model\Types\WebPushNotification::class, 'getBody')]
#[CoversMethod(\Bristolian\Model\Types\WebPushNotification::class, 'getData')]
#[CoversMethod(\Bristolian\Model\Types\WebPushNotification::class, 'getSound')]
#[CoversMethod(\Bristolian\Model\Types\WebPushNotification::class, 'getTitle')]
#[CoversMethod(\Bristolian\Model\Types\WebPushNotification::class, 'getVibrate')]

class TypesCoverageTest extends BaseTestCase
{
    private static function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function test_AdminUser_new_and_getters(): void
    {
        $admin = AdminUser::new('user-id-1', 'admin@example.com', 'hashed_pw');

        $this->assertSame('user-id-1', $admin->getUserId());
        $this->assertSame('admin@example.com', $admin->getEmailAddress());
        $this->assertSame('hashed_pw', $admin->getPasswordHash());
    }

    public function test_BccTro(): void
    {
        $document = new BccTroDocument('doc title', 'https://example.com', 'doc-1');
        $tro = new BccTro('TRO Title', 'REF-001', $document, $document, $document);

        $this->assertSame('TRO Title', $tro->title);
        $this->assertSame('REF-001', $tro->reference_code);
        $this->assertSame($document, $tro->statement_of_reasons);
    }

    public function test_BccTroDocument(): void
    {
        $document = new BccTroDocument('Title', 'https://example.com/doc', 'id-123');

        $this->assertSame('Title', $document->title);
        $this->assertSame('https://example.com/doc', $document->href);
        $this->assertSame('id-123', $document->id);
    }

    public function test_Email(): void
    {
        $now = self::now();
        $email = new Email(1, 'body text', 'recipient@example.com', 0, 'pending', 'Subject', $now, $now);

        $this->assertSame(1, $email->id);
        $this->assertSame('body text', $email->body);
        $this->assertSame('recipient@example.com', $email->recipient);
        $this->assertSame(0, $email->retries);
        $this->assertSame('pending', $email->status);
        $this->assertSame('Subject', $email->subject);
    }

    public function test_FoiRequest_constructor_and_getters(): void
    {
        $now = self::now();
        $request = new FoiRequest('foi-1', 'request text', 'https://example.com', 'description text', $now);

        $this->assertSame('foi-1', $request->getFoiRequestId());
        $this->assertSame('request text', $request->getText());
        $this->assertSame('https://example.com', $request->getUrl());
        $this->assertSame('description text', $request->getDescription());
        $this->assertSame($now, $request->getCreatedAt());
    }

    public function test_FoiRequest_fromParam(): void
    {
        $params = FoiRequestParams::createFromVarMap(new ArrayVarMap([
            'text' => 'FOI request text',
            'url' => 'https://example.com/foi',
            'description' => 'A description',
        ]));

        $request = FoiRequest::fromParam('uuid-123', $params);

        $this->assertSame('uuid-123', $request->getFoiRequestId());
        $this->assertSame('FOI request text', $request->getText());
        $this->assertSame('https://example.com/foi', $request->getUrl());
        $this->assertSame('A description', $request->getDescription());
        $this->assertInstanceOf(\DateTimeInterface::class, $request->getCreatedAt());
    }

    public function test_IncomingEmail(): void
    {
        $now = self::now();
        $email = new IncomingEmail(
            1, 'msg-id', 'body plain', '{}', 'raw email', 'recipient@example.com',
            '0', 'sender@example.com', 'initial', 'stripped text', 'Subject', $now, $now
        );

        $this->assertSame(1, $email->id);
        $this->assertSame('msg-id', $email->message_id);
        $this->assertSame('sender@example.com', $email->sender);
    }

    public function test_IncomingEmailParam_createFromData(): void
    {
        $data = [
            'Message-Id' => 'msg-001',
            'body-plain' => 'Hello world',
            'recipient' => 'to@example.com',
            'sender' => 'from@example.com',
            'stripped-text' => 'Hello',
            'subject' => 'Test Subject',
            'raw_email' => 'raw content',
        ];

        $param = IncomingEmailParam::createFromData($data);

        $this->assertSame('msg-001', $param->message_id);
        $this->assertSame('Hello world', $param->body_plain);
        $this->assertSame('to@example.com', $param->recipient);
        $this->assertSame('from@example.com', $param->sender);
        $this->assertSame('Hello', $param->stripped_text);
        $this->assertSame('Test Subject', $param->subject);
        $this->assertSame('raw content', $param->raw_email);
        $this->assertSame('0', $param->retries);
        $this->assertSame(IncomingEmailParam::STATUS_INITIAL, $param->status);
    }

    public function test_IncomingEmailParam_createFromData_throws_on_missing_key(): void
    {
        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage('Missing key');

        IncomingEmailParam::createFromData([]);
    }

    public function test_Meme(): void
    {
        $now = self::now();
        $meme = new Meme('meme-1', 'user-1', 'normalized.png', 'original.png', 'uploaded', 1024, $now, false);

        $this->assertSame('meme-1', $meme->id);
        $this->assertSame('user-1', $meme->user_id);
        $this->assertSame('normalized.png', $meme->normalized_name);
        $this->assertFalse($meme->deleted);
    }

    public function test_MigrationFromCode(): void
    {
        $migration = new MigrationFromCode(1, 'Create users table', ['CREATE TABLE users (id INT)']);

        $this->assertSame(1, $migration->id);
        $this->assertSame('Create users table', $migration->description);
        $this->assertSame(['CREATE TABLE users (id INT)'], $migration->queries_to_run);
    }

    public function test_MigrationThatHasBeenRun(): void
    {
        $now = self::now();
        $migration = new MigrationThatHasBeenRun(1, 'Migration 1', '["CREATE TABLE foo"]', $now);

        $this->assertSame(1, $migration->id);
        $this->assertSame('Migration 1', $migration->description);
        $this->assertSame('["CREATE TABLE foo"]', $migration->json_encoded_queries);
        $this->assertSame($now, $migration->created_at);
    }

    public function test_ProcessorState(): void
    {
        $now = self::now();
        $state = new ProcessorState('proc-1', true, 'ocr', $now);

        $this->assertSame('proc-1', $state->id);
        $this->assertTrue($state->enabled);
        $this->assertSame('ocr', $state->type);
        $this->assertSame($now, $state->updated_at);
    }

    public function test_RoomAnnotationWithTags(): void
    {
        $tag = new RoomTag('tag-1', 'room-1', 'Tag Name', '', self::now());
        $annotation = new RoomAnnotationWithTags(
            'id-1', 'user-1', 'file-1', '{"highlights":[]}', 'text', 'title', 'ra-1', [$tag]
        );

        $this->assertSame('id-1', $annotation->id);
        $this->assertSame('user-1', $annotation->user_id);
        $this->assertCount(1, $annotation->tags);
        $this->assertSame('Tag Name', $annotation->tags[0]->text);
    }

    public function test_RoomFileWithTags(): void
    {
        $tag = new RoomTag('tag-1', 'room-1', 'Tag', '', self::now());
        $file = new RoomFileWithTags(
            'id-1', 'normalized.pdf', 'original.pdf', 'uploaded', 2048, 'user-1', self::now(), null, 'Desc', 'Note', [$tag]
        );

        $this->assertSame('id-1', $file->id);
        $this->assertSame('normalized.pdf', $file->normalized_name);
        $this->assertCount(1, $file->tags);
    }

    public function test_RoomLinkWithTags(): void
    {
        $tag = new RoomTag('tag-1', 'room-1', 'Tag', '', self::now());
        $link = new RoomLinkWithTags(
            'id-1', 'room-1', 'link-1', 'https://example.com', 'Title', 'Description', self::now(), null, [$tag]
        );

        $this->assertSame('id-1', $link->id);
        $this->assertSame('room-1', $link->room_id);
        $this->assertSame('Title', $link->title);
        $this->assertCount(1, $link->tags);
    }

    public function test_RoomNoteWithTags(): void
    {
        $tag = new RoomTag('tag-1', 'room-1', 'Tag', '', self::now());
        $note = new RoomNoteWithTags(
            'id-1',
            'room-1',
            'user-1',
            'Title',
            'markdown',
            self::now(),
            self::now(),
            null,
            [$tag]
        );

        $this->assertSame('id-1', $note->id);
        $this->assertSame('room-1', $note->room_id);
        $this->assertSame('Title', $note->title);
        $this->assertCount(1, $note->tags);
    }

    public function test_RoomVideoTranscriptList(): void
    {
        $transcript = new RoomVideoTranscript('t-1', 'rv-1', 1, 'en', 'WEBVTT', self::now());
        $list = new RoomVideoTranscriptList([$transcript]);

        $this->assertCount(1, $list->transcripts);
        $this->assertSame('t-1', $list->transcripts[0]->id);
    }

    public function test_RoomVideoWithTags(): void
    {
        $tag = new RoomTag('tag-1', 'room-1', 'Tag', '', self::now());
        $video = new RoomVideoWithTags(
            'id-1', 'room-1', 'vid-1', 'dQw4w9WgXcQ', 'Title', 'Desc', 10, 60, self::now(), null, [$tag]
        );

        $this->assertSame('id-1', $video->id);
        $this->assertSame('dQw4w9WgXcQ', $video->youtube_video_id);
        $this->assertSame(10, $video->start_seconds);
        $this->assertSame(60, $video->end_seconds);
        $this->assertCount(1, $video->tags);
    }

    public function test_UserWebPushSubscription(): void
    {
        $subscription = new UserWebPushSubscription(
            'https://push.example.com/sub/abc',
            '2025-12-31T23:59:59Z',
            '{"endpoint":"https://push.example.com/sub/abc"}'
        );

        $this->assertSame('https://push.example.com/sub/abc', $subscription->getEndpoint());
        $this->assertSame('2025-12-31T23:59:59Z', $subscription->getExpirationTime());
        $this->assertSame('{"endpoint":"https://push.example.com/sub/abc"}', $subscription->getRaw());
    }

    public function test_WebPushNotification(): void
    {
        $notification = WebPushNotification::create('Test Title', 'Test Body');

        $this->assertSame('Test Title', $notification->getTitle());
        $this->assertSame('Test Body', $notification->getBody());
        $vibrate = $notification->getVibrate();
        $this->assertNull($vibrate); // @phpstan-ignore method.alreadyNarrowedType
        $this->assertSame('/sounds/meow.mp3', $notification->getSound());
        $data = $notification->getData();
        $this->assertNull($data); // @phpstan-ignore method.alreadyNarrowedType
    }
}
