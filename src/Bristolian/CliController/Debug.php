<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use SlimAuryn\Response\HtmlResponse;
//use Bristolian\Queue\PurchaseOrderNotify\RedisPurchaseOrderNotifyQueue;
//use Bristolian\Repo\StripeEventRepo\StripeEventRepo;
use DMore\ChromeDriver\ChromeDriver;

class Debug
{
    public function hello()
    {
        return new HtmlResponse("Hello");
    }

//    public function testPurchaseOrderAlert(
//        RedisPurchaseOrderNotifyQueue $redisPurchaseOrderNotifyQueue
//    ) {
//        $purchaseOrderJob = new \Bristolian\Queue\PurchaseOrderNotify\PurchaseOrderNotifyJob(5);
//        $redisPurchaseOrderNotifyQueue->pushPurchaseOrderNotifyJob($purchaseOrderJob);
//    }

    public function debug()
    {
//        $result = $stripeEventRepo->waitToAssignTask();
//
//        var_dump($result);
//
//        $redisData = $redis->blpop(['alishdoiashdoaisdoiahsdihasodaoshd'], 5);
//        var_dump($redisData);
//        exit(0);
    }

//    public function addStripeEvent(StripeEventRepo $stripeEventRepo)
//    {
//        $stripeEventRepo->checkoutSessionNeedsUpdating(
//            'e924a76cbbd119bcff69b105d98c43fe954f29faadaf2a3ad743c1df675dbf34'
//        );
//    }
//
//    public function invoicePdf()
//    {
//        $urlForInvoice = 'http://local.internal.opensourcefees.com/projects/Imagick/invoices/1/render';
//        $driver = new ChromeDriver(
//            "http://10.254.254.254:9222",
//            null,
//            $urlForInvoice
//        );
//        $driver->start();
//        $driver->visit($urlForInvoice);
//
//        $urlCurrent = $driver->getCurrentUrl();
//        if ($urlCurrent !== $urlForInvoice) {
//            echo "wrong url for invoice.\n";
//            $driver->captureScreenshot(__DIR__ . '/../../../dan_what_the_shit.png');
//            exit(-1);
//        }
//
//        $statusCode = $driver->getStatusCode();
//        if ($statusCode !== 200) {
//            echo "statusCode is not 200 but instead $statusCode \n";
//            $driver->captureScreenshot(__DIR__ . '/../../../dan_what_the_shit.png');
//            exit(-1);
//        }
//
//        $driver->printToPDF(
//            __DIR__ . '/../../../dan_test.pdf',
//            $landscape = false,
//            $displayHeaderFooter = false,
//            $printBackground = true,
//            $scale = 1,
//            // Colonial letter size
//            // $paperWidth = 8.5,
//            // $paperHeight = 11.0,
//            // A4 size in "inches"
//            $paperWidth = 8.27,
//            $paperHeight = 11.69
//            // $marginTop = 1.0,
//            // $marginBottom = 1.0,
//            // $marginLeft = 1.0,
//            // $marginRight = 1.0,
//            // $pageRanges = '',
//            // $ignoreInvalidPageRanges = false,
//            // $headerTemplate = '',
//            // $footerTemplate = ''
//        );
//    }
}
