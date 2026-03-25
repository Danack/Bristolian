<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\CliController\Rooms;
use Bristolian\Model\Generated\RoomFileObjectInfo;
use Bristolian\Parameters\RoomContentSearchParams;
use Bristolian\Model\Types\AdminUser;
use Bristolian\Repo\AdminRepo\FakeAdminRepo;
use Bristolian\Repo\LinkRepo\FakeLinkRepo;
use Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo;
use Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo;
use Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo;
use Bristolian\Repo\RoomRepo\FakeRoomRepo;
use Bristolian\Repo\RoomTagRepo\FakeRoomTagRepo;
use Bristolian\Repo\RoomVideoRepo\InMemoryRoomVideoRepo;
use Bristolian\Repo\RoomVideoTagRepo\InMemoryRoomVideoTagRepo;
use Bristolian\Repo\VideoRepo\InMemoryVideoRepo;
use Bristolian\Service\CliOutput\CapturingCliOutput;
use Bristolian\Service\CliOutput\CliExitRequestedException;
use Bristolian\Service\RoomFileStorage\FakeRoomFileStorage;
use Bristolian\Service\RoomFileStorage\UploadError;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class RoomsTest extends BaseTestCase
{
    private function adminUserForCli(): AdminUser
    {
        return AdminUser::new(
            'user-1',
            'testing@example.com',
            generate_password_hash('testing')
        );
    }

    /**
     * @covers \Bristolian\CliController\Rooms::__construct
     * @covers \Bristolian\CliController\Rooms::addFileFromCli
     */
    public function test_addFileFromCli_when_admin_not_found_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $adminRepo = new FakeAdminRepo([]);

        try {
            $rooms->addFileFromCli(
                $adminRepo,
                new FakeRoomRepo(),
                new FakeRoomFileStorage('ignored'),
                'Housing',
                __DIR__ . '/../../fixtures/pdfs/sample.pdf'
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('Failed to find admin user', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addFileFromCli
     */
    public function test_addFileFromCli_when_no_room_match_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $adminRepo = new FakeAdminRepo([$this->adminUserForCli()]);
        $roomRepo = new FakeRoomRepo();

        try {
            $rooms->addFileFromCli(
                $adminRepo,
                $roomRepo,
                new FakeRoomFileStorage('ignored'),
                'Housing',
                __DIR__ . '/../../fixtures/pdfs/sample.pdf'
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('No room found with name: Housing', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addFileFromCli
     */
    public function test_addFileFromCli_when_multiple_rooms_share_name_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $adminRepo = new FakeAdminRepo([$this->adminUserForCli()]);
        $roomRepo = new FakeRoomRepo();
        $roomRepo->createRoom('user-1', 'Housing', 'first');
        $roomRepo->createRoom('user-1', 'Housing', 'second');

        try {
            $rooms->addFileFromCli(
                $adminRepo,
                $roomRepo,
                new FakeRoomFileStorage('ignored'),
                'Housing',
                __DIR__ . '/../../fixtures/pdfs/sample.pdf'
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('Multiple rooms have the name "Housing"', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addFileFromCli
     */
    public function test_addFileFromCli_when_file_missing_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $adminRepo = new FakeAdminRepo([$this->adminUserForCli()]);
        $roomRepo = new FakeRoomRepo();
        $roomRepo->createRoom('user-1', 'Housing', 'purpose');

        $missingPath = __DIR__ . '/../../fixtures/pdfs/does-not-exist-' . bin2hex(random_bytes(4)) . '.pdf';

        try {
            $rooms->addFileFromCli(
                $adminRepo,
                $roomRepo,
                new FakeRoomFileStorage('ignored'),
                'Housing',
                $missingPath
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('File not found: ' . $missingPath, $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addFileFromCli
     */
    public function test_addFileFromCli_when_storage_returns_error_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $adminRepo = new FakeAdminRepo([$this->adminUserForCli()]);
        $roomRepo = new FakeRoomRepo();
        $roomRepo->createRoom('user-1', 'Housing', 'purpose');
        $storage = new FakeRoomFileStorage(UploadError::unsupportedFileType());

        try {
            $rooms->addFileFromCli(
                $adminRepo,
                $roomRepo,
                $storage,
                'Housing',
                __DIR__ . '/../../fixtures/pdfs/sample.pdf'
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('Failed to upload file:', $cliOutput->getCapturedOutput());
            $this->assertStringContainsString(UploadError::UNSUPPORTED_FILE_TYPE, $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addFileFromCli
     */
    public function test_addFileFromCli_success_writes_stored_file_id(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $adminRepo = new FakeAdminRepo([$this->adminUserForCli()]);
        $roomRepo = new FakeRoomRepo();
        $roomRepo->createRoom('user-1', 'Housing', 'purpose');
        $storedFileId = 'stored-file-id-abc';
        $storage = new FakeRoomFileStorage($storedFileId);

        $rooms->addFileFromCli(
            $adminRepo,
            $roomRepo,
            $storage,
            'Housing',
            __DIR__ . '/../../fixtures/pdfs/sample.pdf'
        );

        $this->assertStringContainsString(
            'File added to room with stored file id: ' . $storedFileId,
            $cliOutput->getCapturedOutput()
        );
    }

    /**
     * @covers \Bristolian\CliController\Rooms::createFromCli
     */
    public function test_createFromCli_when_admin_not_found_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $adminRepo = new FakeAdminRepo([]);
        $roomRepo = new FakeRoomRepo();

        try {
            $rooms->createFromCli($adminRepo, $roomRepo, 'My room', 'A purpose');
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('Failed to find admin user', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::createFromCli
     */
    public function test_createFromCli_success_creates_room(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $adminRepo = new FakeAdminRepo([$this->adminUserForCli()]);
        $roomRepo = new FakeRoomRepo();

        $rooms->createFromCli($adminRepo, $roomRepo, 'CLI room', 'CLI purpose');

        $all = $roomRepo->getAllRooms();
        $this->assertCount(1, $all);
        $this->assertSame('CLI room', $all[0]->name);
        $this->assertSame('CLI purpose', $all[0]->purpose);
        $this->assertSame('', $cliOutput->getCapturedOutput());
    }

    private function validAnnotationJsonForCli(): string
    {
        return json_encode([
            'title' => 'Nulla consequat quam ut nisl - annotation.',
            'highlights_json' => '[{"page":0,"left":101,"top":392,"right":264,"bottom":407}]',
            'text' => '',
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addFileAnnotationFromCli
     */
    public function test_addFileAnnotationFromCli_when_admin_not_found_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);

        try {
            $rooms->addFileAnnotationFromCli(
                new FakeAdminRepo([]),
                new FakeRoomRepo(),
                new FakeRoomFileRepo(),
                new FakeRoomAnnotationRepo(),
                'Housing',
                'sample.pdf',
                $this->validAnnotationJsonForCli()
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('Failed to find admin user', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addFileAnnotationFromCli
     */
    public function test_addFileAnnotationFromCli_when_no_room_match_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);

        try {
            $rooms->addFileAnnotationFromCli(
                new FakeAdminRepo([$this->adminUserForCli()]),
                new FakeRoomRepo(),
                new FakeRoomFileRepo(),
                new FakeRoomAnnotationRepo(),
                'Housing',
                'sample.pdf',
                $this->validAnnotationJsonForCli()
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('No room found with name: Housing', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addFileAnnotationFromCli
     */
    public function test_addFileAnnotationFromCli_when_multiple_rooms_share_name_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $roomRepo = new FakeRoomRepo();
        $roomRepo->createRoom('user-1', 'Housing', 'first');
        $roomRepo->createRoom('user-1', 'Housing', 'second');

        try {
            $rooms->addFileAnnotationFromCli(
                new FakeAdminRepo([$this->adminUserForCli()]),
                $roomRepo,
                new FakeRoomFileRepo(),
                new FakeRoomAnnotationRepo(),
                'Housing',
                'sample.pdf',
                $this->validAnnotationJsonForCli()
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('Multiple rooms have the name "Housing"', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addFileAnnotationFromCli
     */
    public function test_addFileAnnotationFromCli_when_no_file_in_room_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $roomRepo = new FakeRoomRepo();
        $roomRepo->createRoom('user-1', 'Housing', 'purpose');

        try {
            $rooms->addFileAnnotationFromCli(
                new FakeAdminRepo([$this->adminUserForCli()]),
                $roomRepo,
                new FakeRoomFileRepo(),
                new FakeRoomAnnotationRepo(),
                'Housing',
                'sample.pdf',
                $this->validAnnotationJsonForCli()
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString(
                'No file with original filename "sample.pdf" in that room.',
                $cliOutput->getCapturedOutput()
            );
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addFileAnnotationFromCli
     */
    public function test_addFileAnnotationFromCli_when_multiple_files_same_original_name_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $roomRepo = new FakeRoomRepo();
        $room = $roomRepo->createRoom('user-1', 'Housing', 'purpose');

        $roomFileRepo = new FakeRoomFileRepo();
        $roomFileRepo->registerFileObjectInfo(new RoomFileObjectInfo(
            'file-a-id',
            'norm-a.pdf',
            'dup.pdf',
            'uploaded',
            100,
            'user-1',
            new \DateTimeImmutable()
        ));
        $roomFileRepo->registerFileObjectInfo(new RoomFileObjectInfo(
            'file-b-id',
            'norm-b.pdf',
            'dup.pdf',
            'uploaded',
            200,
            'user-1',
            new \DateTimeImmutable()
        ));
        $roomFileRepo->addFileToRoom('file-a-id', $room->id);
        $roomFileRepo->addFileToRoom('file-b-id', $room->id);

        try {
            $rooms->addFileAnnotationFromCli(
                new FakeAdminRepo([$this->adminUserForCli()]),
                $roomRepo,
                $roomFileRepo,
                new FakeRoomAnnotationRepo(),
                'Housing',
                'dup.pdf',
                $this->validAnnotationJsonForCli()
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString(
                'Multiple files named "dup.pdf" in that room; resolve duplicates first.',
                $cliOutput->getCapturedOutput()
            );
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addFileAnnotationFromCli
     */
    public function test_addFileAnnotationFromCli_when_annotation_json_invalid_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $roomRepo = new FakeRoomRepo();
        $room = $roomRepo->createRoom('user-1', 'Housing', 'purpose');

        $roomFileRepo = new FakeRoomFileRepo();
        $file_id = 'file-for-json-test';
        $roomFileRepo->registerFileObjectInfo(new RoomFileObjectInfo(
            $file_id,
            'norm.pdf',
            'sample.pdf',
            'uploaded',
            100,
            'user-1',
            new \DateTimeImmutable()
        ));
        $roomFileRepo->addFileToRoom($file_id, $room->id);

        try {
            $rooms->addFileAnnotationFromCli(
                new FakeAdminRepo([$this->adminUserForCli()]),
                $roomRepo,
                $roomFileRepo,
                new FakeRoomAnnotationRepo(),
                'Housing',
                'sample.pdf',
                '{not valid json'
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('Invalid JSON:', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addFileAnnotationFromCli
     */
    public function test_addFileAnnotationFromCli_when_annotation_validation_fails_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $roomRepo = new FakeRoomRepo();
        $room = $roomRepo->createRoom('user-1', 'Housing', 'purpose');

        $roomFileRepo = new FakeRoomFileRepo();
        $file_id = 'file-for-validation-test';
        $roomFileRepo->registerFileObjectInfo(new RoomFileObjectInfo(
            $file_id,
            'norm.pdf',
            'sample.pdf',
            'uploaded',
            100,
            'user-1',
            new \DateTimeImmutable()
        ));
        $roomFileRepo->addFileToRoom($file_id, $room->id);

        $bad_json = json_encode([
            'title' => 'short',
            'highlights_json' => '[{"page":0,"left":1,"top":2,"right":3,"bottom":4}]',
            'text' => '',
        ], JSON_THROW_ON_ERROR);

        try {
            $rooms->addFileAnnotationFromCli(
                new FakeAdminRepo([$this->adminUserForCli()]),
                $roomRepo,
                $roomFileRepo,
                new FakeRoomAnnotationRepo(),
                'Housing',
                'sample.pdf',
                $bad_json
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('Invalid annotation parameters:', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addFileAnnotationFromCli
     */
    public function test_addFileAnnotationFromCli_success_writes_room_annotation_id(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $adminRepo = new FakeAdminRepo([$this->adminUserForCli()]);
        $roomRepo = new FakeRoomRepo();
        $room = $roomRepo->createRoom('user-1', 'Housing', 'purpose');

        $roomFileRepo = new FakeRoomFileRepo();
        $file_id = '019d0f8a-07e5-735e-b2c0-a2fd80ce24d7';
        $roomFileRepo->registerFileObjectInfo(new RoomFileObjectInfo(
            $file_id,
            'norm-sample.pdf',
            'sample.pdf',
            'uploaded',
            100,
            'user-1',
            new \DateTimeImmutable()
        ));
        $roomFileRepo->addFileToRoom($file_id, $room->id);

        $annotationRepo = new FakeRoomAnnotationRepo();

        $rooms->addFileAnnotationFromCli(
            $adminRepo,
            $roomRepo,
            $roomFileRepo,
            $annotationRepo,
            'Housing',
            'sample.pdf',
            $this->validAnnotationJsonForCli()
        );

        $this->assertStringContainsString('room_annotation id:', $cliOutput->getCapturedOutput());
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addLinkFromCli
     */
    public function test_addLinkFromCli_when_admin_not_found_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $linkRepo = new FakeLinkRepo();
        $roomLinkRepo = new FakeRoomLinkRepo($linkRepo);

        try {
            $rooms->addLinkFromCli(
                new FakeAdminRepo([]),
                new FakeRoomRepo(),
                $roomLinkRepo,
                'Housing',
                'https://example.com/',
                null,
                null
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('Failed to find admin user', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addLinkFromCli
     */
    public function test_addLinkFromCli_when_no_room_match_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $linkRepo = new FakeLinkRepo();
        $roomLinkRepo = new FakeRoomLinkRepo($linkRepo);

        try {
            $rooms->addLinkFromCli(
                new FakeAdminRepo([$this->adminUserForCli()]),
                new FakeRoomRepo(),
                $roomLinkRepo,
                'Housing',
                'https://example.com/',
                null,
                null
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('No room found with name: Housing', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addLinkFromCli
     */
    public function test_addLinkFromCli_when_multiple_rooms_share_name_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $roomRepo = new FakeRoomRepo();
        $roomRepo->createRoom('user-1', 'Housing', 'first');
        $roomRepo->createRoom('user-1', 'Housing', 'second');
        $linkRepo = new FakeLinkRepo();
        $roomLinkRepo = new FakeRoomLinkRepo($linkRepo);

        try {
            $rooms->addLinkFromCli(
                new FakeAdminRepo([$this->adminUserForCli()]),
                $roomRepo,
                $roomLinkRepo,
                'Housing',
                'https://example.com/',
                null,
                null
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('Multiple rooms have the name "Housing"', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addLinkFromCli
     */
    public function test_addLinkFromCli_when_validation_fails_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $roomRepo = new FakeRoomRepo();
        $roomRepo->createRoom('user-1', 'Housing', 'purpose');
        $linkRepo = new FakeLinkRepo();
        $roomLinkRepo = new FakeRoomLinkRepo($linkRepo);

        try {
            $rooms->addLinkFromCli(
                new FakeAdminRepo([$this->adminUserForCli()]),
                $roomRepo,
                $roomLinkRepo,
                'Housing',
                'https://example.com/',
                'short',
                null
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('Invalid link parameters:', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addLinkFromCli
     */
    public function test_addLinkFromCli_success_url_only_writes_room_link_id(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $roomRepo = new FakeRoomRepo();
        $roomRepo->createRoom('user-1', 'Housing', 'purpose');
        $linkRepo = new FakeLinkRepo();
        $roomLinkRepo = new FakeRoomLinkRepo($linkRepo);

        $rooms->addLinkFromCli(
            new FakeAdminRepo([$this->adminUserForCli()]),
            $roomRepo,
            $roomLinkRepo,
            'Housing',
            'https://example.com/',
            null,
            null
        );

        $this->assertStringContainsString('room_link id:', $cliOutput->getCapturedOutput());
        $last = $roomLinkRepo->getLastAddedLink();
        $this->assertNotNull($last);
        $this->assertNull($last->title);
        $this->assertNull($last->description);
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addLinkFromCli
     */
    public function test_addLinkFromCli_success_with_title_and_description_writes_room_link_id(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $roomRepo = new FakeRoomRepo();
        $roomRepo->createRoom('user-1', 'Housing', 'purpose');
        $linkRepo = new FakeLinkRepo();
        $roomLinkRepo = new FakeRoomLinkRepo($linkRepo);

        $rooms->addLinkFromCli(
            new FakeAdminRepo([$this->adminUserForCli()]),
            $roomRepo,
            $roomLinkRepo,
            'Housing',
            'https://example.com/',
            'Link title',
            'Link description'
        );

        $this->assertStringContainsString('room_link id:', $cliOutput->getCapturedOutput());
        $last = $roomLinkRepo->getLastAddedLink();
        $this->assertNotNull($last);
        $this->assertSame('Link title', $last->title);
        $this->assertSame('Link description', $last->description);
    }

    /**
     * @return array{InMemoryVideoRepo, InMemoryRoomVideoRepo}
     */
    private function inMemoryVideoRepositories(): array
    {
        $videoRepo = new InMemoryVideoRepo();
        $roomVideoRepo = new InMemoryRoomVideoRepo(
            new InMemoryRoomVideoTagRepo(),
            $videoRepo,
            new FakeRoomTagRepo(),
        );

        return [$videoRepo, $roomVideoRepo];
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addVideoFromCli
     */
    public function test_addVideoFromCli_when_admin_not_found_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        [$videoRepo, $roomVideoRepo] = $this->inMemoryVideoRepositories();

        try {
            $rooms->addVideoFromCli(
                new FakeAdminRepo([]),
                new FakeRoomRepo(),
                $videoRepo,
                $roomVideoRepo,
                'Housing',
                'https://www.youtube.com/watch?v=q84psZX6MbA',
                null,
                null
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('Failed to find admin user', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addVideoFromCli
     */
    public function test_addVideoFromCli_when_no_room_match_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        [$videoRepo, $roomVideoRepo] = $this->inMemoryVideoRepositories();

        try {
            $rooms->addVideoFromCli(
                new FakeAdminRepo([$this->adminUserForCli()]),
                new FakeRoomRepo(),
                $videoRepo,
                $roomVideoRepo,
                'Housing',
                'https://www.youtube.com/watch?v=q84psZX6MbA',
                null,
                null
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('No room found with name: Housing', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addVideoFromCli
     */
    public function test_addVideoFromCli_when_multiple_rooms_share_name_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $roomRepo = new FakeRoomRepo();
        $roomRepo->createRoom('user-1', 'Housing', 'first');
        $roomRepo->createRoom('user-1', 'Housing', 'second');
        [$videoRepo, $roomVideoRepo] = $this->inMemoryVideoRepositories();

        try {
            $rooms->addVideoFromCli(
                new FakeAdminRepo([$this->adminUserForCli()]),
                $roomRepo,
                $videoRepo,
                $roomVideoRepo,
                'Housing',
                'https://www.youtube.com/watch?v=q84psZX6MbA',
                null,
                null
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('Multiple rooms have the name "Housing"', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addVideoFromCli
     */
    public function test_addVideoFromCli_when_validation_fails_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $roomRepo = new FakeRoomRepo();
        $roomRepo->createRoom('user-1', 'Housing', 'purpose');
        [$videoRepo, $roomVideoRepo] = $this->inMemoryVideoRepositories();

        try {
            $rooms->addVideoFromCli(
                new FakeAdminRepo([$this->adminUserForCli()]),
                $roomRepo,
                $videoRepo,
                $roomVideoRepo,
                'Housing',
                'https://www.youtube.com/watch?v=q84psZX6MbA',
                'short',
                null
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('Invalid video parameters:', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addVideoFromCli
     */
    public function test_addVideoFromCli_when_url_invalid_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $roomRepo = new FakeRoomRepo();
        $roomRepo->createRoom('user-1', 'Housing', 'purpose');
        [$videoRepo, $roomVideoRepo] = $this->inMemoryVideoRepositories();

        try {
            $rooms->addVideoFromCli(
                new FakeAdminRepo([$this->adminUserForCli()]),
                $roomRepo,
                $videoRepo,
                $roomVideoRepo,
                'Housing',
                'not a youtube url',
                null,
                null
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('Invalid video parameters:', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addVideoFromCli
     */
    public function test_addVideoFromCli_success_url_only_writes_room_video_id(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $roomRepo = new FakeRoomRepo();
        $room = $roomRepo->createRoom('user-1', 'Housing', 'purpose');
        [$videoRepo, $roomVideoRepo] = $this->inMemoryVideoRepositories();

        $rooms->addVideoFromCli(
            new FakeAdminRepo([$this->adminUserForCli()]),
            $roomRepo,
            $videoRepo,
            $roomVideoRepo,
            'Housing',
            'https://www.youtube.com/watch?v=q84psZX6MbA',
            null,
            null
        );

        $this->assertStringContainsString('room_video id:', $cliOutput->getCapturedOutput());
        $videos = $roomVideoRepo->getVideosForRoom($room->id, RoomContentSearchParams::default());
        $this->assertCount(1, $videos);
        $this->assertNull($videos[0]->title);
        $this->assertNull($videos[0]->description);
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addVideoFromCli
     */
    public function test_addVideoFromCli_success_with_title_and_description_writes_room_video_id(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $roomRepo = new FakeRoomRepo();
        $room = $roomRepo->createRoom('user-1', 'Housing', 'purpose');
        [$videoRepo, $roomVideoRepo] = $this->inMemoryVideoRepositories();

        $rooms->addVideoFromCli(
            new FakeAdminRepo([$this->adminUserForCli()]),
            $roomRepo,
            $videoRepo,
            $roomVideoRepo,
            'Housing',
            'https://www.youtube.com/watch?v=q84psZX6MbA',
            'Video title',
            'Video description'
        );

        $this->assertStringContainsString('room_video id:', $cliOutput->getCapturedOutput());
        $videos = $roomVideoRepo->getVideosForRoom($room->id, RoomContentSearchParams::default());
        $this->assertCount(1, $videos);
        $this->assertSame('Video title', $videos[0]->title);
        $this->assertSame('Video description', $videos[0]->description);
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addVideoClipFromCli
     */
    public function test_addVideoClipFromCli_when_end_before_start_writes_and_exits(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $roomRepo = new FakeRoomRepo();
        $roomRepo->createRoom('user-1', 'Housing', 'purpose');
        [$videoRepo, $roomVideoRepo] = $this->inMemoryVideoRepositories();

        try {
            $rooms->addVideoClipFromCli(
                new FakeAdminRepo([$this->adminUserForCli()]),
                $roomRepo,
                $videoRepo,
                $roomVideoRepo,
                'Housing',
                'https://www.youtube.com/watch?v=q84psZX6MbA',
                '4:15',
                '1:15',
                null,
                null
            );
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $exception) {
            $this->assertSame(-1, $exception->getExitCode());
            $this->assertStringContainsString('Invalid video clip parameters:', $cliOutput->getCapturedOutput());
            $this->assertStringContainsString('End time must be after start time.', $cliOutput->getCapturedOutput());
        }
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addVideoClipFromCli
     */
    public function test_addVideoClipFromCli_success_times_only_writes_room_video_id(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $roomRepo = new FakeRoomRepo();
        $room = $roomRepo->createRoom('user-1', 'Housing', 'purpose');
        [$videoRepo, $roomVideoRepo] = $this->inMemoryVideoRepositories();

        $rooms->addVideoClipFromCli(
            new FakeAdminRepo([$this->adminUserForCli()]),
            $roomRepo,
            $videoRepo,
            $roomVideoRepo,
            'Housing',
            'https://www.youtube.com/watch?v=q84psZX6MbA',
            '1:15',
            '4:15',
            null,
            null
        );

        $this->assertStringContainsString('Video clip added to room with room_video id:', $cliOutput->getCapturedOutput());
        $videos = $roomVideoRepo->getVideosForRoom($room->id, RoomContentSearchParams::default());
        $this->assertCount(1, $videos);
        $this->assertSame(75, $videos[0]->start_seconds);
        $this->assertSame(255, $videos[0]->end_seconds);
        $this->assertNull($videos[0]->title);
        $this->assertNull($videos[0]->description);
    }

    /**
     * @covers \Bristolian\CliController\Rooms::addVideoClipFromCli
     */
    public function test_addVideoClipFromCli_success_with_title_and_description(): void
    {
        $cliOutput = new CapturingCliOutput();
        $rooms = new Rooms($cliOutput);
        $roomRepo = new FakeRoomRepo();
        $room = $roomRepo->createRoom('user-1', 'Housing', 'purpose');
        [$videoRepo, $roomVideoRepo] = $this->inMemoryVideoRepositories();

        $rooms->addVideoClipFromCli(
            new FakeAdminRepo([$this->adminUserForCli()]),
            $roomRepo,
            $videoRepo,
            $roomVideoRepo,
            'Housing',
            'https://www.youtube.com/watch?v=q84psZX6MbA',
            '1:15',
            '4:15',
            'Clip title',
            'Clip description'
        );

        $this->assertStringContainsString('Video clip added to room with room_video id:', $cliOutput->getCapturedOutput());
        $videos = $roomVideoRepo->getVideosForRoom($room->id, RoomContentSearchParams::default());
        $this->assertCount(1, $videos);
        $this->assertSame('Clip title', $videos[0]->title);
        $this->assertSame('Clip description', $videos[0]->description);
    }
}
