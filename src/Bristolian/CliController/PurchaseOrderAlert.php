<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use Bristolian\Queue\PurchaseOrderNotify\PurchaseOrderNotifyQueue;
use Bristolian\PurchaseOrderNotifier\PurchaseOrderNotifier;

class PurchaseOrderAlert
{
    /** @var PurchaseOrderNotifyQueue  */
    private $purchaseOrderNotifyQueue;

    /** @var PurchaseOrderNotifier */
    private $purchaseOrderNotifier;


    public function __construct(
        PurchaseOrderNotifyQueue $purchaseOrderNotifyQueue,
        PurchaseOrderNotifier $purchaseOrderNotifier
    ) {
        $this->purchaseOrderNotifyQueue = $purchaseOrderNotifyQueue;
        $this->purchaseOrderNotifier = $purchaseOrderNotifier;
    }

    /**
     * This is a placeholder background task
     */
    public function run()
    {
        $callable = function () {
            $this->runInternal();
        };

        continuallyExecuteCallable(
            $callable,
            $secondsBetweenRuns = 5,
            $sleepTime = 1,
            $maxRunTime = 600
        );
    }

    public function runInternal()
    {
        $purchaseOrderNotifyJob = $this->purchaseOrderNotifyQueue->getPurchaseOrderNotifyJob(5);

        if ($purchaseOrderNotifyJob === null) {
            return;
        }

        $this->purchaseOrderNotifier->notifyOfPurchaseOrder($purchaseOrderNotifyJob);
    }
}
