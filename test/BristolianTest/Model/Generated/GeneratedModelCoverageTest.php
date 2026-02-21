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
use Bristolian\Model\Generated\RoomFile;
use Bristolian\Model\Generated\RoomSourcelink;
use Bristolian\Model\Generated\RunTimeRecorder;
use Bristolian\Model\Generated\Sourcelink;
use Bristolian\Model\Generated\StoredMeme;
use Bristolian\Model\Generated\Tag;
use Bristolian\Model\Generated\TinnedFishProduct;
use Bristolian\Model\Generated\UserAuthEmailPassword;
use Bristolian\Model\Generated\UserDisplayName;
use Bristolian\Model\Generated\UserProfile;
use Bristolian\Model\Generated\UserWebpushSubscription;
use Bristolian\Repo\UserProfileRepo\FakeUserProfileRepo;
use function Bristolian\Repo\UserProfileRepo\createBlankUserProfileForUserId;
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

    /** @covers \Bristolian\Model\Generated\RoomSourcelink */
    public function test_RoomSourcelink(): void
    {
        $o = new RoomSourcelink('id', 'rid', 'sid', null, self::now());
        $this->assertSame('id', $o->id);
    }

    /** @covers \Bristolian\Model\Generated\RunTimeRecorder */
    public function test_RunTimeRecorder(): void
    {
        $o = new RunTimeRecorder(1, 'task', 'ok', self::now(), null);
        $this->assertSame(1, $o->id);
    }

    /** @covers \Bristolian\Model\Generated\Sourcelink */
    public function test_Sourcelink(): void
    {
        $o = new Sourcelink('id', 'uid', 'fid', '{}', 'text', self::now());
        $this->assertSame('id', $o->id);
    }

    /** @covers \Bristolian\Model\Generated\StoredMeme */
    public function test_StoredMeme(): void
    {
        $o = new StoredMeme('id', 'norm', 'orig', 'active', 100, 'uid', self::now(), 0);
        $this->assertSame('id', $o->id);
    }

    /** @covers \Bristolian\Model\Generated\Tag */
    public function test_Tag(): void
    {
        $o = new Tag('tid', 'text', 'desc', self::now());
        $this->assertSame('tid', $o->tag_id);
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
}
