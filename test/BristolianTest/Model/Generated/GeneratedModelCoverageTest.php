<?php

declare(strict_types = 1);

namespace BristolianTest\Model\Generated;

use Bristolian\Model\Generated\ApiToken;
use Bristolian\Model\Generated\AvatarImageObjectInfo;
use Bristolian\Model\Generated\BccTroInformation;
use Bristolian\Model\Generated\ChatMessage;
use Bristolian\Model\Generated\EmailIncoming;
use Bristolian\Model\Generated\EmailSendQueue;
use Bristolian\Model\Generated\FoiRequests;
use Bristolian\Model\Generated\MemeTag;
use Bristolian\Model\Generated\MemeText;
use Bristolian\Model\Generated\Migrations;
use Bristolian\Model\Generated\PdoSimpleTest;
use Bristolian\Model\Generated\Processor;
use Bristolian\Model\Generated\RoomAnnotation;
use Bristolian\Model\Generated\RoomAnnotationTag;
use Bristolian\Model\Generated\RoomFile;
use Bristolian\Model\Generated\RoomFileTag;
use Bristolian\Model\Generated\RoomLinkTag;
use Bristolian\Model\Generated\RoomTag;
use Bristolian\Model\Generated\RoomVideo;
use Bristolian\Model\Generated\RoomVideoTag;
use Bristolian\Model\Generated\RoomVideoTranscript;
use Bristolian\Model\Generated\RunTimeRecorder;
use Bristolian\Model\Generated\Annotation;
use Bristolian\Model\Generated\StoredMeme;
use Bristolian\Model\Generated\TinnedFishProduct;
use Bristolian\Model\Generated\UserAuthEmailPassword;
use Bristolian\Model\Generated\UserDisplayName;
use Bristolian\Model\Generated\UserProfile;
use Bristolian\Model\Generated\UserWebpushSubscription;
use Bristolian\Model\Generated\Video;
use Bristolian\Model\Generated\BristolStairInfo;
use Bristolian\Model\Generated\Link;
use Bristolian\Model\Generated\ProcessorRunRecord;
use Bristolian\Model\Generated\Room;
use Bristolian\Model\Generated\RoomFileObjectInfo;
use Bristolian\Model\Generated\RoomLink;
use Bristolian\Model\Generated\StairImageObjectInfo;
use Bristolian\Model\Generated\User;
use Bristolian\Repo\UserProfileRepo\FakeUserProfileRepo;
use function createBlankUserProfileForUserId;
use BristolianTest\BaseTestCase;

/**
 * Minimal coverage tests for auto-generated Model classes.
 *
 * @coversNothing
 */
class GeneratedModelCoverageTest extends BaseTestCase
{
    private static function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    /** @covers \Bristolian\Model\Generated\ApiToken */
    public function test_ApiToken(): void
    {
        $o = new ApiToken('id', 'token', 'name', self::now(), 0, null);
        $this->assertSame('id', $o->id);
    }

    /** @covers \Bristolian\Model\Generated\AvatarImageObjectInfo */
    public function test_AvatarImageObjectInfo(): void
    {
        $o = new AvatarImageObjectInfo('id', 'norm', 'orig', 'active', 100, 'uid', self::now());
        $this->assertSame('id', $o->id);
    }

    /** @covers \Bristolian\Model\Generated\BccTroInformation */
    public function test_BccTroInformation(): void
    {
        $o = new BccTroInformation(1, 'data', self::now());
        $this->assertSame(1, $o->id);
    }

    /** @covers \Bristolian\Model\Generated\ChatMessage */
    public function test_ChatMessage(): void
    {
        $o = new ChatMessage(1, 'text', 'uid', 'rid', null, self::now());
        $this->assertSame(1, $o->id);
    }

    /** @covers \Bristolian\Model\Generated\EmailIncoming */
    public function test_EmailIncoming(): void
    {
        $now = self::now();
        $o = new EmailIncoming(1, 'mid', 'body', 'r', 's', 'st', 'subj', '{}', 'raw', 'ok', 0, $now, $now);
        $this->assertSame(1, $o->id);
    }

    /** @covers \Bristolian\Model\Generated\EmailSendQueue */
    public function test_EmailSendQueue(): void
    {
        $now = self::now();
        $o = new EmailSendQueue(1, 'r', 'subj', 'body', 'ok', 0, $now, $now);
        $this->assertSame(1, $o->id);
    }

    /** @covers \Bristolian\Model\Generated\FoiRequests */
    public function test_FoiRequests(): void
    {
        $o = new FoiRequests('fid', 'text', 'url', 'desc', self::now());
        $this->assertSame('fid', $o->foi_request_id);
    }

    /** @covers \Bristolian\Model\Generated\MemeTag */
    public function test_MemeTag(): void
    {
        $o = new MemeTag('id', 'uid', 'mid', 'type', 'text', self::now());
        $this->assertSame('id', $o->id);
    }

    /** @covers \Bristolian\Model\Generated\MemeText */
    public function test_MemeText(): void
    {
        $o = new MemeText(1, 'text', 'mid', self::now());
        $this->assertSame(1, $o->id);
    }

    /** @covers \Bristolian\Model\Generated\Migrations */
    public function test_Migrations(): void
    {
        $o = new Migrations(1, 'desc', '[]', self::now());
        $this->assertSame(1, $o->id);
    }

    /** @covers \Bristolian\Model\Generated\PdoSimpleTest */
    public function test_PdoSimpleTest(): void
    {
        $o = new PdoSimpleTest(1, 's', 2, self::now());
        $this->assertSame(1, $o->id);
    }

    /** @covers \Bristolian\Model\Generated\Processor */
    public function test_Processor(): void
    {
        $o = new Processor(1, 'type', 1, self::now());
        $this->assertSame(1, $o->id);
    }

    /** @covers \Bristolian\Model\Generated\RoomFile */
    public function test_RoomFile(): void
    {
        $o = new RoomFile('rid', 'fid', null, null, null, null, self::now());
        $this->assertSame('rid', $o->room_id);
    }

    /** @covers \Bristolian\Model\Generated\RoomAnnotation */
    public function test_RoomAnnotation(): void
    {
        $o = new RoomAnnotation('id', 'rid', 'aid', null, self::now());
        $this->assertSame('id', $o->id);
    }

    /** @covers \Bristolian\Model\Generated\RunTimeRecorder */
    public function test_RunTimeRecorder(): void
    {
        $o = new RunTimeRecorder(1, 'task', 'ok', self::now(), null);
        $this->assertSame(1, $o->id);
    }

    /** @covers \Bristolian\Model\Generated\Annotation */
    public function test_Annotation(): void
    {
        $o = new Annotation('id', 'uid', 'fid', '{}', 'text', self::now());
        $this->assertSame('id', $o->id);
    }

    /** @covers \Bristolian\Model\Generated\StoredMeme */
    public function test_StoredMeme(): void
    {
        $o = new StoredMeme('id', 'norm', 'orig', 'active', 100, 'uid', self::now(), 0);
        $this->assertSame('id', $o->id);
    }

    /** @covers \Bristolian\Model\Generated\RoomTag */
    public function test_RoomTag(): void
    {
        $roomTag = new RoomTag('tag_identifier_value', 'room_identifier_value', 'tag text content', 'tag description text', self::now());
        $this->assertSame('tag_identifier_value', $roomTag->tag_id);
        $this->assertSame('room_identifier_value', $roomTag->room_id);
    }

    /** @covers \Bristolian\Model\Generated\TinnedFishProduct */
    public function test_TinnedFishProduct(): void
    {
        $now = self::now();
        $o = new TinnedFishProduct('id', 'barcode', 'name', 'brand', null, null, null, null, null, 'ok', $now, $now);
        $this->assertSame('id', $o->id);
    }

    /** @covers \Bristolian\Model\Generated\UserAuthEmailPassword */
    public function test_UserAuthEmailPassword(): void
    {
        $o = new UserAuthEmailPassword('uid', 'a@b.com', 'hash', self::now());
        $this->assertSame('uid', $o->user_id);
    }

    /** @covers \Bristolian\Model\Generated\UserDisplayName */
    public function test_UserDisplayName(): void
    {
        $o = new UserDisplayName(1, 'uid', 'name', 1, self::now());
        $this->assertSame(1, $o->id);
    }

    /** @covers \Bristolian\Model\Generated\UserProfile */
    public function test_UserProfile(): void
    {
        $now = self::now();
        $o = new UserProfile('uid', null, null, $now, $now);
        $this->assertSame('uid', $o->user_id);
    }

    /** @covers \Bristolian\Repo\UserProfileRepo\FakeUserProfileRepo */
    public function test_createBlankUserProfileForUserId(): void
    {
        class_exists(FakeUserProfileRepo::class); // load file containing createBlankUserProfileForUserId
        $o = createBlankUserProfileForUserId('uid');
        $this->assertSame('uid', $o->user_id);
    }

    /** @covers \Bristolian\Model\Generated\UserWebpushSubscription */
    public function test_UserWebpushSubscription(): void
    {
        $o = new UserWebpushSubscription(1, 'uid', 'ep', 'exp', 'raw', self::now());
        $this->assertSame(1, $o->user_webpush_subscription_id);
    }

    /** @covers \Bristolian\Model\Generated\RoomAnnotationTag */
    public function test_RoomAnnotationTag(): void
    {
        $o = new RoomAnnotationTag('annotation_id', 'tag_id');
        $this->assertSame('annotation_id', $o->room_annotation_id);
        $this->assertSame('tag_id', $o->tag_id);
    }

    /** @covers \Bristolian\Model\Generated\RoomFileTag */
    public function test_RoomFileTag(): void
    {
        $o = new RoomFileTag('room_id', 'file_id', 'tag_id');
        $this->assertSame('room_id', $o->room_id);
        $this->assertSame('tag_id', $o->tag_id);
    }

    /** @covers \Bristolian\Model\Generated\RoomLinkTag */
    public function test_RoomLinkTag(): void
    {
        $o = new RoomLinkTag('link_id', 'tag_id');
        $this->assertSame('link_id', $o->room_link_id);
        $this->assertSame('tag_id', $o->tag_id);
    }

    /** @covers \Bristolian\Model\Generated\RoomVideo */
    public function test_RoomVideo(): void
    {
        $o = new RoomVideo('id', 'room_id', 'video_id', 'title', 'desc', 10, 60, self::now());
        $this->assertSame('id', $o->id);
        $this->assertSame('room_id', $o->room_id);
    }

    /** @covers \Bristolian\Model\Generated\RoomVideoTag */
    public function test_RoomVideoTag(): void
    {
        $o = new RoomVideoTag('room_video_id', 'tag_id');
        $this->assertSame('room_video_id', $o->room_video_id);
        $this->assertSame('tag_id', $o->tag_id);
    }

    /** @covers \Bristolian\Model\Generated\RoomVideoTranscript */
    public function test_RoomVideoTranscript(): void
    {
        $o = new RoomVideoTranscript('id', 'rv_id', 1, 'en', 'WEBVTT', self::now());
        $this->assertSame('id', $o->id);
        $this->assertSame('rv_id', $o->room_video_id);
    }

    /** @covers \Bristolian\Model\Generated\Video */
    public function test_Video(): void
    {
        $o = new Video('id', 'uid', 'yt_id', self::now());
        $this->assertSame('id', $o->id);
        $this->assertSame('yt_id', $o->youtube_video_id);
    }

    /** @covers \Bristolian\Model\Generated\BristolStairInfo */
    public function test_BristolStairInfo(): void
    {
        $o = new BristolStairInfo(1, 'desc', 51.45, -2.58, 'file_id', 20, 0, self::now(), null);
        $this->assertSame(1, $o->id);
        $this->assertSame(20, $o->steps);
    }

    /** @covers \Bristolian\Model\Generated\Link */
    public function test_Link(): void
    {
        $o = new Link('id', 'uid', 'https://example.com', self::now());
        $this->assertSame('id', $o->id);
        $this->assertSame('https://example.com', $o->url);
    }

    /** @covers \Bristolian\Model\Generated\ProcessorRunRecord */
    public function test_ProcessorRunRecord(): void
    {
        $o = new ProcessorRunRecord(1, 'type', 'debug', self::now(), 'ok', null);
        $this->assertSame(1, $o->id);
        $this->assertSame('type', $o->processor_type);
    }

    /** @covers \Bristolian\Model\Generated\Room */
    public function test_Room(): void
    {
        $o = new Room('id', 'uid', 'name', 'purpose', self::now());
        $this->assertSame('id', $o->id);
        $this->assertSame('name', $o->name);
    }

    /** @covers \Bristolian\Model\Generated\RoomFileObjectInfo */
    public function test_RoomFileObjectInfo(): void
    {
        $o = new RoomFileObjectInfo('id', 'norm', 'orig', 'active', 100, 'uid', self::now());
        $this->assertSame('id', $o->id);
        $this->assertSame('norm', $o->normalized_name);
    }

    /** @covers \Bristolian\Model\Generated\RoomLink */
    public function test_RoomLink(): void
    {
        $o = new RoomLink('id', 'room_id', 'link_id', 'title', 'desc', self::now());
        $this->assertSame('id', $o->id);
        $this->assertSame('room_id', $o->room_id);
    }

    /** @covers \Bristolian\Model\Generated\StairImageObjectInfo */
    public function test_StairImageObjectInfo(): void
    {
        $o = new StairImageObjectInfo('id', 'norm', 'orig', 'active', 100, 'uid', self::now());
        $this->assertSame('id', $o->id);
        $this->assertSame('norm', $o->normalized_name);
    }

    /** @covers \Bristolian\Model\Generated\User */
    public function test_User(): void
    {
        $o = new User('id', self::now());
        $this->assertSame('id', $o->id);
    }
}
