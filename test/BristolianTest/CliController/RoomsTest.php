<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\CliController\Rooms;
use Bristolian\Model\Generated\RoomFileObjectInfo;
use Bristolian\Model\Types\AdminUser;
use Bristolian\Repo\AdminRepo\FakeAdminRepo;
use Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo;
use Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo;
use Bristolian\Repo\RoomRepo\FakeRoomRepo;
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
}
