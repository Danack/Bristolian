<?php

declare(strict_types = 1);

namespace BristolianTest\Repo;

use Bristolian\DataType\CreateUserParams;
use Bristolian\JsonInput\FakeJsonInput;
use Bristolian\JsonInput\JsonInput;
use Bristolian\Model\AdminUser;
use Bristolian\Repo\AdminRepo\PdoAdminRepo;
use Bristolian\Model\Room;
use Bristolian\Repo\RoomRepo\PdoRoomRepo;
use Bristolian\Repo\FileStorageInfoRepo\PdoFileStorageInfoRepo;
use Bristolian\UploadedFiles\UploadedFile;

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
     * @return void
     * @throws \DI\ConfigException
     * @throws \DI\InjectionException
     */
    protected function initLoggedInUser(array $testDoubles): void
    {
        $placeholders = [
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
            \Bristolian\UserSession::class,
            \Bristolian\MockUserSession::class
        );
        $this->injector->share($session);
    }

    public function setup(): void
    {
        parent::setup();
        $this->injector = createTestInjector();
    }

    private function createInjector()
    {
        if ($this->injector === null) {
            $this->injector = createTestInjector();
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
     * @throws \Bristolian\BristolianException
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

//    public function createContentPolicyViolationReport(array $particulars)
//    {
//        $input = [
//            'csp-report' =>
//                [
//                    'document-uri' => 'http://local.admin.opensourcefees.com:8000/status/csp_reports',
//                    'referrer' => '',
//                    'violated-directive' => 'script-src-elem',
//                    'effective-directive' => 'script-src-elem',
//                    'original-policy' => 'default-src \'self\'; connect-src \'self\' https://checkout.stripe.com; frame-src *; img-src *; script-src \'self\' \'nonce-b8b4cf51f675e0fc13a6b959\' https://checkout.stripe.com; object-src *; style-src \'self\'; report-uri /csp',
//                    'disposition' => 'enforce',
//                    'blocked-uri' => 'inline',
//                    'line-number' => 13,
//                    'source-file' => 'http://local.admin.opensourcefees.com:8000/status/csp_reports',
//                    'status-code' => 200,
//                    'script-sample' => '',
//                ],
//        ];
//
//        foreach ($particulars as $key => $value) {
//            $input['csp-report'][$key] = $value;
//        }
//
//        return ContentPolicyViolationReport::fromCSPPayload($input);
//    }
}
