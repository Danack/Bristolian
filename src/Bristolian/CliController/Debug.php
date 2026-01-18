<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use Bristolian\App;
use Bristolian\Database\stored_meme;
use Bristolian\Model\Generated\StoredMeme;
use Bristolian\Model\Types\WebPushNotification;
use Bristolian\Parameters\ChatMessageParam;
use Bristolian\Parameters\MemeTagParams;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\AdminRepo\AdminRepo;
use Bristolian\Repo\MemeTagRepo\MemeTagRepo;
use Bristolian\Repo\MemeTagRepo\MemeTagType;
use Bristolian\Repo\MemeTextRepo\MemeTextRepo;
use Bristolian\Repo\WebPushSubscriptionRepo\WebPushSubscriptionRepo;
use Bristolian\Service\MemeStorageProcessor\MemeStorageProcessor;
use Bristolian\Service\MemeStorageProcessor\UploadError;
use Bristolian\Service\ObjectStore\MemeObjectStore;
use Bristolian\Service\RoomMessageService\RoomMessageService;
use Bristolian\Service\WebPushService\WebPushService;
use Bristolian\UploadedFiles\UploadedFile;

function fn_level_1(): void
{
    fn_level_2();
}

function fn_level_2(): void
{
    fn_level_3();
}

function fn_level_3(): void
{
    throw new \Exception("This is on line ". __LINE__);
}

/**
 * Placeholder code for testing webpushes.
 * @codeCoverageIgnore
 */
class Debug
{
    public function hello(): void
    {
        echo "Hello.";
    }


    public function stack_trace(): void
    {
        fn_level_1();
    }

    public function send_webpush(
        string $email_address,
        string $message,
        AdminRepo $adminRepo,
        WebPushSubscriptionRepo $webPushSubscriptionRepo,
        WebPushService $webPushService
    ): void {
        $webPushNotification = WebPushNotification::create('Test message', $message);

        echo "Need to send to $email_address the message '$message'.\n";

        $user_id = $adminRepo->getAdminUserId($email_address);

        if ($user_id === null) {
            echo "User $email_address not found.";
            return;
        }

        $userWebPushSubscriptions = $webPushSubscriptionRepo->getUserSubscriptions($user_id);

        if (count($userWebPushSubscriptions) === 0) {
            echo "User has no Web Push Subscriptions.\n";
            return;
        }

        $webPushService->sendWebPushToSubscriptions(
            $webPushNotification,
            $userWebPushSubscriptions
        );
    }

    public function generate_system_info_email(): void
    {
        echo generateSystemInfoEmailContent();
    }


    public function send_message_to_room(
        RoomMessageService $roomMessageService,
        AdminRepo $adminRepo,
        string $message,
    ): void {
        $user_id = $adminRepo->getAdminUserId(getAdminEmailAddress());

        $chat_message_param = ChatMessageParam::createFromArray([
            $room_id = App::ROOM_ID_DEBUG,
            $text = $message,
            $message_reply_id = null,
        ]);

        $roomMessageService->sendMessage($user_id, $chat_message_param);
    }

    public function generate_room_messages(): void
    {
        $users = [
            'Alice',
            'Bob',
            'Carol',
            'Dave',
            'Eve',
            'Frank',
            'Grace',
            'Heidi',
            'Ivan',
            'Judy',
            'Mallory',
            'Oscar',
            'Peggy',
            'Trent',
            'Victor',
            'Walter',
        ];
    }

    /**
     * Add a meme file with optional tags and text.
     *
     * Usage examples:
     *   php cli.php debug:add_meme /path/to/image.jpg
     *   php cli.php debug:add_meme /path/to/image.jpg "tag1,tag2,tag3"
     *   php cli.php debug:add_meme /path/to/image.jpg "tag1,tag2" "OCR extracted text content"
     *
     * @param AdminRepo $adminRepo
     * @param MemeStorageProcessor $memeStorageProcessor
     * @param MemeObjectStore $memeObjectStore
     * @param MemeTagRepo $memeTagRepo
     * @param MemeTextRepo $memeTextRepo
     * @param PdoSimple $pdoSimple
     * @param string $file_path Path to the meme file to upload
     * @param string|null $tags Comma-separated list of tags to add (e.g., "funny,memes,cats")
     * @param string|null $text Text content for the meme (OCR text)
     */
    public function add_meme(
        AdminRepo $adminRepo,
        MemeStorageProcessor $memeStorageProcessor,
        MemeObjectStore $memeObjectStore,
        MemeTagRepo $memeTagRepo,
        MemeTextRepo $memeTextRepo,
        PdoSimple $pdoSimple,
        string $file_path,
        ?string $tags = null,
        ?string $text = null,
    ): void {
        $user_id = $adminRepo->getAdminUserId(getAdminEmailAddress());
        if ($user_id === null) {
            echo "Failed to find admin user\n";
            exit(-1);
        }

        if (!file_exists($file_path)) {
            echo "File not found: $file_path\n";
            exit(-1);
        }

        $uploadedFile = UploadedFile::fromFile($file_path);

        $storedFileOrError = $memeStorageProcessor->storeMemeForUser(
            $user_id,
            $uploadedFile,
            get_supported_meme_file_extensions(),
            $memeObjectStore
        );

        if ($storedFileOrError instanceof UploadError) {
            echo "Failed to store meme: " . $storedFileOrError->error_message . "\n";
            exit(-1);
        }

        $meme_id = $storedFileOrError->meme_id;
        echo "Meme stored with ID: $meme_id\n";

        // Get StoredMeme object for text storage
        $sql = stored_meme::SELECT . " where id = :id";
        $storedMeme = $pdoSimple->fetchOneAsObjectOrNullConstructor(
            $sql,
            [':id' => $meme_id],
            StoredMeme::class
        );

        if ($storedMeme === null) {
            echo "Warning: Could not retrieve stored meme for text storage\n";
        }

        // Add tags if provided
        if ($tags !== null && $tags !== '') {
            $tagList = array_map('trim', explode(',', $tags));
            foreach ($tagList as $tagText) {
                if ($tagText !== '') {
                    $memeTagParam = new MemeTagParams(
                        $meme_id,
                        MemeTagType::USER_TAG->value,
                        $tagText
                    );
                    $memeTagRepo->addTagForMeme($user_id, $memeTagParam);
                    echo "Added tag: $tagText\n";
                }
            }
        }

        // Save text if provided
        if ($text !== null && $text !== '' && $storedMeme !== null) {
            $memeTextRepo->saveMemeText($storedMeme, $text);
            echo "Saved text for meme\n";
        }

        echo "Meme added successfully\n";
    }
}
