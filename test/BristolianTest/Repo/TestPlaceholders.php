<?php

declare(strict_types = 1);

namespace BristolianTest\Repo;

use Bristolian\Data\ContentPolicyViolationReport;
use Bristolian\DataType\CreateUserParams;
use Bristolian\JsonInput\FakeJsonInput;
use Bristolian\JsonInput\JsonInput;
use Bristolian\Model\AdminUser;
use Bristolian\Repo\AdminRepo\PdoAdminRepo;
use Bristolian\Model\Room;
use Bristolian\Repo\RoomRepo\PdoRoomRepo;
use Bristolian\Repo\FileStorageInfoRepo\PdoFileStorageInfoRepo;
use Bristolian\UploadedFiles\UploadedFile;
use DI\ConfigException;
use DI\InjectionException;
use VarMap\VarMap;
use Bristolian\Session\UserSession;

/**
 * Trait to make write tests easier.
 *
 * All of the createTest*() functions do create entries in the database,
 * so that constraints are tested.
 */
trait TestPlaceholders
{
    /**
     * @param Object[] $testDoubles
     * @return UserSession
     * @throws ConfigException
     * @throws InjectionException
     */
    protected function initLoggedInUser(array $testDoubles): UserSession
    {
        $placeholders = [
            \VarMap\VarMap::class,
            JsonInput::class
        ];

        foreach ($testDoubles as $testDouble) {
            foreach ($placeholders as $placeholder) {
                if ($testDouble instanceof $placeholder) {
                    $this->injector->alias($placeholder, get_class($testDouble));
                    $this->injector->share($testDouble);
                }
            }
        }

        $session = $this->injector->execute('getTestingUserSession');

        $this->injector->alias(
            \Bristolian\Session\UserSession::class,
            \Bristolian\Session\MockUserSession::class
        );
        $this->injector->share($session);

        return $session;
    }

    public function setup(): void
    {
        parent::setup();
        $this->injector = createTestInjector();
    }

    public function initInMemoryFakes()
    {
        $testDoubles = [
            \Bristolian\Repo\LinkRepo\LinkRepo::class =>
                \Bristolian\Repo\LinkRepo\FakeLinkRepo::class,

            \Bristolian\Repo\RoomLinkRepo\RoomLinkRepo::class =>
                \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::class,

        ];

        foreach ($testDoubles as $interface => $fake) {
            $this->injector->alias($interface, $fake);
            $this->injector->share($fake);
        }
    }

    public function initPdoTestObjects()
    {
        $testDoubles = [
            \Bristolian\Repo\LinkRepo\LinkRepo::class =>
                \Bristolian\Repo\LinkRepo\PdoLinkRepo::class,

            \Bristolian\Repo\RoomLinkRepo\RoomLinkRepo::class =>
                \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo::class,
        ];

        foreach ($testDoubles as $interface => $fake) {
            $this->injector->alias($interface, $fake);
            $this->injector->share($fake);
        }
    }




    public function createTestAdminUser(): AdminUser
    {
        $username = 'username' . time() . '_' . random_int(1000, 9999) . "@example.com";
        $password = 'password_' . time() . '_' . random_int(1000, 9999);

        $createAdminUserParams = CreateUserParams::createFromArray([
            'email_address' => $username,
            'password' => $password
        ]);

        $pdo_admin_repo = $this->injector->make(PdoAdminRepo::class);
        $adminUser = $pdo_admin_repo->addUser($createAdminUserParams);

        return $adminUser;
    }

    /**
     * @return array{0:Room, 1:AdminUser}
     * @throws \Bristolian\Exception\BristolianException
     * @throws \DI\InjectionException
     */
    public function createTestUserAndRoom():array
    {
        $roomRepo = $this->injector->make(PdoRoomRepo::class);
        $user = $this->createTestAdminUser();

        $room_name = $this->getTestRoomName();
        $room_description = $this->getTestRoomDescription();

        $room = $roomRepo->createRoom(
            $user->getUserId(),
            $room_name,
            $room_description
        );

        return [$room, $user];
    }

    public function createTestFile(AdminUser $user): string
    {
        $roomRepo = $this->injector->make(PdoFileStorageInfoRepo::class);
        $normalized_filename = $this->getTestFileName();

        $filepath = __DIR__ ."/../../sample.pdf";

        $original_filename =

        $uploadedFile = new UploadedFile(
            $filepath,
            \Safe\filesize($filepath),
            "sample.pdf",
            0
        );

        $file_id = $roomRepo->storeFileInfo(
            $user->getUserId(),
            $normalized_filename,
            $uploadedFile
        );

        return $file_id;
    }

    public function getTestFileName(): string
    {
        static $count = 0;
        $count += 1;
        return 'test_file' . time() . '_' . $count;
    }

    public function getTestLink(): string
    {
        static $count = 0;
        $count += 1;
        return 'http://www.example.com/' . time() . '/' . $count;
    }

    public function getTestObjectName(): string
    {
        static $count = 0;
        $count += 1;
        return 'test_object_' . time() . '_' . $count;
    }


    public function getTestRoomName(): string
    {
        static $count = 0;
        $count += 1;
        return 'test_room' . time() . '_' . $count;
    }

    public function getTestRoomDescription(): string
    {
        static $count = 0;
        $count += 1;
        return 'test_room_description' . time() . '_' . $count;
    }




    public function getTestString(): string
    {
        static $count = 0;
        $count += 1;
        return 'a test string: ' . time() . '_' . $count;
    }

    /**
     * @param array<string, string> $particulars
     * @return ContentPolicyViolationReport
     * @throws \Exception
     */
    public function createContentPolicyViolationReport(array $particulars)
    {
        $report =  [
            'document-uri' => $particulars['document-uri'] ?? 'http://www.example.com',
            'referrer' => $particulars[ 'referrer'] ?? 'http://www.google.com',
            'violated-directive' => $particulars['violated-directive'] ?? 'script-src-elem',
            'effective-directive' => $particulars['effective-directive'] ?? 'script-src-elem',
            'original-policy' => $particulars['original-policy'] ?? 'default-src \'self\'; connect-src \'self\' https://checkout.stripe.com; frame-src *; img-src *; script-src \'self\' \'nonce-b8b4cf51f675e0fc13a6b959\' https://checkout.stripe.com; object-src *; style-src \'self\'; report-uri /csp',
            'disposition' => $particulars['disposition'] ?? 'enforce',
            'blocked-uri' => $particulars['blocked-uri'] ?? 'inline',
            'line-number' => $particulars['line-number'] ?? 123,
            'source-file' => $particulars['source-file'] ?? 'http://www.example.com/status/csp_reports',
            'status-code' => $particulars['status-code'] ?? 200,
            'script-sample' => $particulars['script-sample'] ?? '',
        ];

        foreach ($report as $key => $value) {
            $input['csp-report'][$key] = $value;
        }

        return ContentPolicyViolationReport::fromCSPPayload($input);
    }
}
