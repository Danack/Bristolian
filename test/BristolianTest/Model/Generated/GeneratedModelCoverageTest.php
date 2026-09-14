<?php

declare(strict_types = 1);

namespace BristolianTest\Model\Generated;

use BristolianGenerated\Model\ApiToken;
use BristolianGenerated\Model\AvatarImageObjectInfo;
use BristolianGenerated\Model\BccTroInformation;
use BristolianGenerated\Model\EmailIncoming;
use BristolianGenerated\Model\EmailSendQueue;
use BristolianGenerated\Model\FoiRequests;
use BristolianGenerated\Model\MemeTag;
use BristolianGenerated\Model\MemeText;
use BristolianGenerated\Model\Migrations;
use BristolianGenerated\Model\PdoSimpleTest;
use BristolianGenerated\Model\Processor;
use BristolianGenerated\Model\RoomAnnotation;
use BristolianGenerated\Model\RoomAnnotationTag;
use BristolianGenerated\Model\RoomFile;
use BristolianGenerated\Model\RoomFileTag;
use BristolianGenerated\Model\RoomLinkTag;
use BristolianGenerated\Model\RoomTag;
use BristolianGenerated\Model\RoomVideo;
use BristolianGenerated\Model\RoomVideoTag;
use BristolianGenerated\Model\RoomVideoTranscript;
use BristolianGenerated\Model\RunTimeRecorder;
use BristolianGenerated\Model\Annotation;
use BristolianGenerated\Model\StoredMeme;
use BristolianGenerated\Model\TinnedFishProduct;
use BristolianGenerated\Model\UserAuthEmailPassword;
use BristolianGenerated\Model\UserDisplayName;
use BristolianGenerated\Model\UserOwnership;
use BristolianGenerated\Model\UserProfile;
use BristolianGenerated\Model\UserWebpushSubscription;
use BristolianGenerated\Model\Video;
use BristolianGenerated\Model\WhatdotheyknowRequestEvent;
use BristolianGenerated\Model\BristolStairInfo;
use BristolianGenerated\Model\Link;
use BristolianGenerated\Model\ProcessorRunRecord;
use BristolianGenerated\Model\Room;
use BristolianGenerated\Model\RoomFileObjectInfo;
use BristolianGenerated\Model\RoomLink;
use BristolianGenerated\Model\StairImageObjectInfo;
use BristolianGenerated\Model\User;
use Bristolian\Repo\UserProfileRepo\FakeUserProfileRepo;
use function createBlankUserProfileForUserId;
use BristolianTest\BaseTestCase;
use Safe\DateTimeImmutable;

/**
 * Minimal coverage tests for auto-generated Model classes.
 *
 * @coversNothing
 */
class GeneratedModelCoverageTest extends BaseTestCase
{
    private static function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    /** @covers \BristolianGenerated\Model\ApiToken */
    public function test_ApiToken(): void
    {
        $o = new ApiToken('id', 'token', 'name', self::now(), 0, null);
        $this->assertSame('id', $o->id);
    }

    /** @covers \BristolianGenerated\Model\AvatarImageObjectInfo */
    public function test_AvatarImageObjectInfo(): void
    {
        $o = new AvatarImageObjectInfo('id', 'norm', 'orig', 'active', 100, 'uid', self::now());
        $this->assertSame('id', $o->id);
    }

    /** @covers \BristolianGenerated\Model\BccTroInformation */
    public function test_BccTroInformation(): void
    {
        $o = new BccTroInformation(1, 'data', self::now());
        $this->assertSame(1, $o->id);
    }

    /** @covers \BristolianGenerated\Model\EmailIncoming */
    public function test_EmailIncoming(): void
    {
        $now = self::now();
        $o = new EmailIncoming(1, 'mid', 'body', 'r', 's', 'st', 'subj', '{}', 'raw', 'ok', 0, $now, $now);
        $this->assertSame(1, $o->id);
    }

    /** @covers \BristolianGenerated\Model\EmailSendQueue */
    public function test_EmailSendQueue(): void
    {
        $now = self::now();
        $o = new EmailSendQueue(1, 'r', 'subj', 'body', 'ok', 0, $now, $now);
        $this->assertSame(1, $o->id);
    }

    /** @covers \BristolianGenerated\Model\FoiRequests */
    public function test_FoiRequests(): void
    {
        $o = new FoiRequests('fid', 'text', 'url', 'desc', self::now());
        $this->assertSame('fid', $o->foi_request_id);
    }

    /** @covers \BristolianGenerated\Model\MemeTag */
    public function test_MemeTag(): void
    {
        $o = new MemeTag('id', 'uid', 'mid', 'type', 'text', self::now());
        $this->assertSame('id', $o->id);
    }

    /** @covers \BristolianGenerated\Model\MemeText */
    public function test_MemeText(): void
    {
        $o = new MemeText(1, 'text', 'mid', self::now());
        $this->assertSame(1, $o->id);
    }

    /** @covers \BristolianGenerated\Model\Migrations */
    public function test_Migrations(): void
    {
        $o = new Migrations(1, 'desc', '[]', self::now());
        $this->assertSame(1, $o->id);
    }

    /** @covers \BristolianGenerated\Model\PdoSimpleTest */
    public function test_PdoSimpleTest(): void
    {
        $o = new PdoSimpleTest(1, 's', 2, self::now());
        $this->assertSame(1, $o->id);
    }

    /** @covers \BristolianGenerated\Model\Processor */
    public function test_Processor(): void
    {
        $o = new Processor(1, 'type', 1, self::now());
        $this->assertSame(1, $o->id);
    }

    /** @covers \BristolianGenerated\Model\RoomFile */
    public function test_RoomFile(): void
    {
        $o = new RoomFile('rid', 'fid', null, null, null, null, self::now());
        $this->assertSame('rid', $o->room_id);
    }

    /** @covers \BristolianGenerated\Model\RoomAnnotation */
    public function test_RoomAnnotation(): void
    {
        $o = new RoomAnnotation('id', 'rid', 'aid', null, self::now());
        $this->assertSame('id', $o->id);
    }

    /** @covers \BristolianGenerated\Model\RunTimeRecorder */
    public function test_RunTimeRecorder(): void
    {
        $o = new RunTimeRecorder(1, 'task', 'ok', self::now(), null);
        $this->assertSame(1, $o->id);
    }

    /** @covers \BristolianGenerated\Model\Annotation */
    public function test_Annotation(): void
    {
        $o = new Annotation('id', 'uid', 'fid', '{}', 'text', self::now());
        $this->assertSame('id', $o->id);
    }

    /** @covers \BristolianGenerated\Model\StoredMeme */
    public function test_StoredMeme(): void
    {
        $o = new StoredMeme('id', 'norm', 'orig', 'active', 100, 'uid', self::now(), 0);
        $this->assertSame('id', $o->id);
    }

    /** @covers \BristolianGenerated\Model\RoomTag */
    public function test_RoomTag(): void
    {
        $roomTag = new RoomTag('tag_identifier_value', 'room_identifier_value', 'tag text content', 'tag description text', self::now());
        $this->assertSame('tag_identifier_value', $roomTag->tag_id);
        $this->assertSame('room_identifier_value', $roomTag->room_id);
    }

    /** @covers \BristolianGenerated\Model\TinnedFishProduct */
    public function test_TinnedFishProduct(): void
    {
        $now = self::now();
        $o = new TinnedFishProduct('id', 'barcode', 'name', 'brand', null, null, null, null, null, 'ok', $now, $now);
        $this->assertSame('id', $o->id);
    }

    /** @covers \BristolianGenerated\Model\UserAuthEmailPassword */
    public function test_UserAuthEmailPassword(): void
    {
        $o = new UserAuthEmailPassword('uid', 'a@b.com', 'hash', self::now());
        $this->assertSame('uid', $o->user_id);
    }

    /** @covers \BristolianGenerated\Model\UserDisplayName */
    public function test_UserDisplayName(): void
    {
        $o = new UserDisplayName(1, 'uid', 'name', 1, self::now());
        $this->assertSame(1, $o->id);
    }

    /**
     * @covers \BristolianGenerated\Model\UserOwnership::__construct
     */
    public function test_UserOwnership(): void
    {
        $o = new UserOwnership(1, 'user-id', 'ROOM_USER', 'room-id');
        $this->assertSame(1, $o->id);
        $this->assertSame('user-id', $o->user_id);
    }

    /** @covers \BristolianGenerated\Model\UserProfile */
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

    /** @covers \BristolianGenerated\Model\UserWebpushSubscription */
    public function test_UserWebpushSubscription(): void
    {
        $o = new UserWebpushSubscription(1, 'uid', 'ep', 'exp', 'raw', self::now());
        $this->assertSame(1, $o->user_webpush_subscription_id);
    }

    /** @covers \BristolianGenerated\Model\RoomAnnotationTag */
    public function test_RoomAnnotationTag(): void
    {
        $o = new RoomAnnotationTag('annotation_id', 'tag_id');
        $this->assertSame('annotation_id', $o->room_annotation_id);
        $this->assertSame('tag_id', $o->tag_id);
    }

    /** @covers \BristolianGenerated\Model\RoomFileTag */
    public function test_RoomFileTag(): void
    {
        $o = new RoomFileTag('room_id', 'file_id', 'tag_id');
        $this->assertSame('room_id', $o->room_id);
        $this->assertSame('tag_id', $o->tag_id);
    }

    /** @covers \BristolianGenerated\Model\RoomLinkTag */
    public function test_RoomLinkTag(): void
    {
        $o = new RoomLinkTag('link_id', 'tag_id');
        $this->assertSame('link_id', $o->room_link_id);
        $this->assertSame('tag_id', $o->tag_id);
    }

    /** @covers \BristolianGenerated\Model\RoomVideo */
    public function test_RoomVideo(): void
    {
        $o = new RoomVideo('id', 'room_id', 'video_id', 'title', 'desc', 10, 60, self::now(), null);
        $this->assertSame('id', $o->id);
        $this->assertSame('room_id', $o->room_id);
    }

    /** @covers \BristolianGenerated\Model\RoomVideoTag */
    public function test_RoomVideoTag(): void
    {
        $o = new RoomVideoTag('room_video_id', 'tag_id');
        $this->assertSame('room_video_id', $o->room_video_id);
        $this->assertSame('tag_id', $o->tag_id);
    }

    /** @covers \BristolianGenerated\Model\RoomVideoTranscript */
    public function test_RoomVideoTranscript(): void
    {
        $o = new RoomVideoTranscript('id', 'rv_id', 1, 'en', 'WEBVTT', self::now());
        $this->assertSame('id', $o->id);
        $this->assertSame('rv_id', $o->room_video_id);
    }

    /** @covers \BristolianGenerated\Model\Video */
    public function test_Video(): void
    {
        $o = new Video('id', 'uid', 'yt_id', self::now());
        $this->assertSame('id', $o->id);
        $this->assertSame('yt_id', $o->youtube_video_id);
    }

    /**
     * @covers \BristolianGenerated\Model\WhatdotheyknowRequestEvent::__construct
     */
    public function test_WhatdotheyknowRequestEvent(): void
    {
        $occurred = self::now();
        $created = self::now();
        $o = new WhatdotheyknowRequestEvent(
            1,
            100,
            '{}',
            200,
            'title',
            300,
            'url_name',
            'Display',
            400,
            $occurred,
            $created
        );
        $this->assertSame(1, $o->id);
        $this->assertSame(100, $o->wdt_event_id);
    }

    /** @covers \BristolianGenerated\Model\BristolStairInfo */
    public function test_BristolStairInfo(): void
    {
        $o = new BristolStairInfo(1, 'desc', 51.45, -2.58, 'file_id', 20, 0, self::now(), null);
        $this->assertSame(1, $o->id);
        $this->assertSame(20, $o->steps);
    }

    /** @covers \BristolianGenerated\Model\Link */
    public function test_Link(): void
    {
        $o = new Link('id', 'uid', 'https://example.com', self::now());
        $this->assertSame('id', $o->id);
        $this->assertSame('https://example.com', $o->url);
    }

    /** @covers \BristolianGenerated\Model\ProcessorRunRecord */
    public function test_ProcessorRunRecord(): void
    {
        $o = new ProcessorRunRecord(1, 'type', 'debug', self::now(), 'ok', null);
        $this->assertSame(1, $o->id);
        $this->assertSame('type', $o->processor_type);
    }

    /** @covers \BristolianGenerated\Model\Room */
    public function test_Room(): void
    {
        $o = new Room('id', 'uid', 'name', 'purpose', self::now());
        $this->assertSame('id', $o->id);
        $this->assertSame('name', $o->name);
    }

    /** @covers \BristolianGenerated\Model\RoomFileObjectInfo */
    public function test_RoomFileObjectInfo(): void
    {
        $o = new RoomFileObjectInfo('id', 'norm', 'orig', 'active', 100, 'uid', self::now());
        $this->assertSame('id', $o->id);
        $this->assertSame('norm', $o->normalized_name);
    }

    /** @covers \BristolianGenerated\Model\RoomLink */
    public function test_RoomLink(): void
    {
        $o = new RoomLink('id', 'room_id', 'link_id', 'title', 'desc', self::now(), null);
        $this->assertSame('id', $o->id);
        $this->assertSame('room_id', $o->room_id);
    }

    /** @covers \BristolianGenerated\Model\StairImageObjectInfo */
    public function test_StairImageObjectInfo(): void
    {
        $o = new StairImageObjectInfo('id', 'norm', 'orig', 'active', 100, 'uid', self::now());
        $this->assertSame('id', $o->id);
        $this->assertSame('norm', $o->normalized_name);
    }

    /** @covers \BristolianGenerated\Model\User */
    public function test_User(): void
    {
        $o = new User('id', self::now());
        $this->assertSame('id', $o->id);
    }
}
