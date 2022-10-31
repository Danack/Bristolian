<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use Bristolian\Jobs\RenderInvoiceToPdf\RenderInvoiceToPdfJob;
use Bristolian\Queue\PrintUrlToPdfQueue;
use Bristolian\Jobs\RenderInvoiceToPdf\RenderInvoiceToPdfProcessor;
use Bristolian\Jobs\RenderInvoiceToPdf\RenderInvoiceToPdfJobFetcher;

class RenderInvoiceToPdfQueueProcessor
{
    /** @var RenderInvoiceToPdfProcessor  */
    private $renderInvoiceToPdfProcessor;

    /** @var RenderInvoiceToPdfJobFetcher  */
    private $renderInvoiceJobFetcher;


    public function __construct(
        RenderInvoiceToPdfJobFetcher $renderInvoiceJobFetcher,
        RenderInvoiceToPdfProcessor $renderInvoiceToPDFProcessor
    ) {
        $this->renderInvoiceJobFetcher = $renderInvoiceJobFetcher;
        $this->renderInvoiceToPdfProcessor = $renderInvoiceToPDFProcessor;
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
            $secondsBetweenRuns = 0,
            $sleepTime = 0,
            $maxRunTime = 600
        );
    }

    public function runInternal()
    {
        $renderInvoiceToPDFJob = $this->renderInvoiceJobFetcher->fetch(5);

        if ($renderInvoiceToPDFJob === null) {
            return;
        }

        $this->renderInvoiceToPdfProcessor->process($renderInvoiceToPDFJob);
    }
}
