<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use Bristolian\Repo\StripeEventRepo\StripeEventRepo;
use Bristolian\Event\Processor\StripeEventProcessor\StripeEventProcessor;

class ProcessStripeEventQueue
{
    /** @var StripeEventRepo */
    private $stripeEventRepo;

    /**
     * @var StripeEventProcessor
     */
    private $stripeEventProcessor;

    public function __construct(
        StripeEventRepo $stripeEventRepo,
        StripeEventProcessor $stripeEventProcessor
    ) {
        $this->stripeEventRepo = $stripeEventRepo;
        $this->stripeEventProcessor = $stripeEventProcessor;
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
        $stripeEvent = $this->stripeEventRepo->waitForStripeEvent();
        if ($stripeEvent === null) {
            return;
        }

        $this->stripeEventProcessor->processStripeEvent($stripeEvent);
    }
}
