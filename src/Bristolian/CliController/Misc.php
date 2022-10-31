<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use Bristolian\Config;

class Misc
{
    public function waitForDBToBeWorking(
        Config $config,
        int $maxTimeToWait = null)
    {
        if ($maxTimeToWait === null) {
            $maxTimeToWait = 60;
        }

        $startTime = microtime(true);

        do {
            echo "Attempting to connect to DB.\n";
            try {
                $pdo = createPDOForUser($config);
                $pdo->query('SELECT 1');
                echo "DB appears to be available.\n";
                return;
            } catch (\Exception $e) {
                echo "DB not available yet.\n";
            }

            sleep(1);
        } while ((microtime(true) - $startTime) < $maxTimeToWait);
    }

//    public function checkConfigHasAllValuesRequired()
//    {
//        \Bristolian\Config::testValuesArePresent();
//
//    }

//    public function clearPurchaseOrderLimit(PurchaseOrderRateLimitRepo $purchaseOrderRateLimitRepo)
//    {
//        $purchaseOrderRateLimitRepo->resetPurchaseOrderRateLimit();
//    }
//
//    public function triggerPurchaseOrderLimit(PurchaseOrderRateLimitRepo $purchaseOrderRateLimitRepo)
//    {
//        while ($purchaseOrderRateLimitRepo->canAPurchaseOrderBeRaised() === true) {
//            $purchaseOrderRateLimitRepo->purchaseOrderWasRaised();
//        }
//    }
}
