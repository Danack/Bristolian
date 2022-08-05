<?php

declare(strict_types = 1);

namespace BristolianTest;

use Behat\Mink\Mink;
use Behat\Mink\Session;
use DMore\ChromeDriver\ChromeDriver;
use PHPUnit\Framework\TestCase;
use Bristolian\CSPViolation\RedisCSPViolationStorage;
use Bristolian\App;

/**
 * @group csp
 * @coversNothing
 */
class CspRulesTest extends TestCase
{
    const CSP_VIOLATION_PAGE = 'http://local.app.opensourcefees.com/csp/test';

    public function testCspReportEndPointIsWorking()
    {
        $expectedDocumentUri = "https://example.com/foo/bar";
        $expectedReferrer = "https://www.google.com/";
        $expectedViolatedDirective = "default-src self";
        $expectedOriginalPolicy = "default-src self; report-uri /csp-hotline.php";
        $expectedBlockedUri = "http://evilhackerscripts.com";

        $cspData = [
            "csp-report" => [
                "document-uri" => $expectedDocumentUri,
                "referrer" => $expectedReferrer,
                "violated-directive" => $expectedViolatedDirective,
                "original-policy" => $expectedOriginalPolicy,
                "blocked-uri"=> $expectedBlockedUri
            ]
        ];

        $json = \json_encode_safe($cspData);

        [$statusCode, $body, $headers] = \fetchUri(
            'http://local.api.bristolian.org' . App::CSP_REPORT_PATH,
            'POST',
            [],
            $json,
            ['Content-Type: application/json']
        );

        $this->assertSame(201, $statusCode);
        $this->assertSame('CSP report accepted', $body);
    }

    /**
     * @group slow
     */
    public function testTheCspViolationsAreRecorded()
    {
        $this->markTestSkipped("Do we have to use chrome? probably");
        // TODO - check contents of reports. However chrome headless is currently crashing
        // https://bugs.chromium.org/p/chromium/issues/detail?id=804262
        $chromeDriver = new ChromeDriver(
            'http://10.254.254.254:9222',
            null,
            self::CSP_VIOLATION_PAGE
        );

        $mink = new Mink(array(
            'browser' => new Session($chromeDriver)
        ));

        $mink->setDefaultSessionName('browser');
        $this->assertEquals(200, $mink->getSession()->getStatusCode());

        $injector = createInjector();
        $cspStorage = $injector->make(RedisCSPViolationStorage::class);

        $cspStorage->clearReports();
        sleep(10);

        $mink->getSession()->visit(self::CSP_VIOLATION_PAGE);
        sleep(10);
        $reports = $cspStorage->getReports();

        $chromeDriver->captureScreenshot(
            __DIR__ . '/../../tmp/screenshots/testTheCspViolationsAreRecorded.png'
        );

        $this->assertCount(1, $reports);
        // TODO - check contents of reports. However chrome headless is currently crashing
        // https://bugs.chromium.org/p/chromium/issues/detail?id=804262
    }

    /**
     * @group needs_fixing
     */
    public function providesNoOtherPagesGiveACspReport()
    {
        $this->markTestSkipped("grrr");
        $routes = require __DIR__ . '/../../routes/app_routes.php';
        $testData = [];

        foreach ($routes as $route) {
            $path = $route[0];
            $method = $route[1];

            if (strcasecmp($method, 'GET') !== 0) {
                // We only test Get methods
                continue;
            }
            if (strcasecmp($path, '/csp/test') === 0) {
                // This page always gives an error.
                continue;
            }

            if (strpos($path, '{') !== false) {
                // TODO - implement this to test dynamic pages.
//                $this->addWarning('Path ' . $path . 'needs a hard-coded example');
                continue;
            }

            $testData[] = [$path];
        }

        return $testData;
    }

    /**
     * @group needs_fixing
     * @group slow
     * @dataProvider providesNoOtherPagesGiveACspReport
     */
    public function testNoOtherPagesGiveACspReport($path)
    {
        $this->markTestSkipped("grrr");

        $chromeDriver = new ChromeDriver(
            'http://10.254.254.254:9222',
            null,
            self::CSP_VIOLATION_PAGE
        );

        $mink = new Mink(array(
            'browser' => new Session($chromeDriver)
        ));

        $mink->setDefaultSessionName('browser');
        $this->assertEquals(200, $mink->getSession()->getStatusCode());

        $injector = createInjector();
        $cspStorage = $injector->make(RedisCSPViolationStorage::class);

        $url = 'http://local.app.opensourcefees.com' . $path;

        $cspStorage->clearReports();
        $mink->getSession()->visit($url);
        $reports = $cspStorage->getReports();

        $this->assertCount(
            0,
            $reports,
            "The page at $path apparently issued a CSP report: " . \json_encode($reports)
        );
    }
}
