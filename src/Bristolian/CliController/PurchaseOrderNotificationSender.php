<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use Bristolian\Repo\PurchaseOrderRepo\PurchaseOrderRepo;
use Bristolian\Repo\NotificationRepo\PurchaseOrderNotificationRepo;
use Bristolian\Service\NotificationSender\NotificationSender;
use Bristolian\Processor\PurchaseOrderNotificationProcessor;

class PurchaseOrderNotificationSender
{
    /** @var PurchaseOrderNotificationProcessor */
    private $purchaseOrderNotificationProcessor;

    /**
     *
     * @param PurchaseOrderNotificationProcessor $purchaseOrderNotificationProcessor
     */
    public function __construct(PurchaseOrderNotificationProcessor $purchaseOrderNotificationProcessor)
    {
        $this->purchaseOrderNotificationProcessor = $purchaseOrderNotificationProcessor;
    }


    public function watchPurchaseOrdersAndAlert()
    {
        $callable = function () {
            $this->purchaseOrderNotificationProcessor->run();
        };

        continuallyExecuteCallable(
            $callable,
            $secondsBetweenRuns = 20,
            $sleepTime = 1,
            $maxRunTime = 600
        );
    }
}
