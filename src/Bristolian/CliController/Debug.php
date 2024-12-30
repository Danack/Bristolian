<?php

namespace Bristolian\CliController;

use Bristolian\Model\WebPushNotification;
use Bristolian\Repo\AdminRepo\AdminRepo;
use Bristolian\Repo\WebPushSubscriptionRepo\WebPushSubscriptionRepo;
use Bristolian\Service\WebPushService\WebPushService;

class Debug
{
    public function hello(): void
    {
        echo "Hello.";
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


//    public function upload_file(
//        \Bristolian\Filesystem\LocalFilesystem $localFilesystem,
//        \Bristolian\Filesystem\MemeFilesystem $memeFilesystem
//    ): void {
//
//        $filesystem = $memeFilesystem;
//
//        try {
////            $filesystem->write($path, $contents, $config);
//            // USAGE
////            $contents = "This is my first file.";
////            $filesystem->write('test.txt', $contents);
//
//            $listing = $filesystem->listContents("/", true);
//
//            /** @var \League\Flysystem\StorageAttributes $item */
//            foreach ($listing as $item) {
//                $path = $item->path();
//
//                if ($item instanceof \League\Flysystem\FileAttributes) {
//                    echo "Found file: " . $path . "\n";
//                } elseif ($item instanceof \League\Flysystem\DirectoryAttributes) {
//                    echo "Found directory: " . $path . "\n";
//                }
//            }
//
//            echo "fin.";
//        } catch (\Exception $exception) {
//            // handle the error
//            echo $exception->getMessage();
//        }
//    }
}
