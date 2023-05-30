<?php

declare(strict_types = 1);

namespace Bristolian\AppController;

use Bristolian\Data\ContentPolicyViolationReport;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\HtmlResponse;
use Bristolian\CSPViolation\CSPViolationReporter;
use Bristolian\CSPViolation\CSPViolationStorage;
use SlimDispatcher\Response\TextResponse;
use Bristolian\JsonInput\JsonInput;

class ContentSecurityPolicy
{
    public function postReport(
        CSPViolationStorage $violationReporter,
        JsonInput $jsonInput
    ): TextResponse {
        $payload = $jsonInput->getData();

        $cspReport = ContentPolicyViolationReport::fromCSPPayload($payload);
        $violationReporter->report($cspReport);

        return new TextResponse("CSP report accepted.\n", [], 201);
    }

    public function clearReports(CSPViolationStorage $csppvReporter): TextResponse
    {
        $csppvReporter->clearReports();
//        return new JsonNoCacheResponse(['ok']);

        return new TextResponse("Reports cleared.\n", [], 201);
    }

    public function getReports(CSPViolationStorage $csppvStorage): JsonNoCacheResponse
    {
        $reports = $csppvStorage->getReports();

        $data = [];
        foreach ($reports as $report) {
            $data[] = $report->toArray();
        }

        return new JsonNoCacheResponse($data);
    }

    public function getTestPage(): HtmlResponse
    {
        $html = <<< HTML
<html>
<body>
  Hello, I am a test page, that tries to load some naughty javascript, which should trigger a CSP report.
</body>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</html>
HTML;

        return new HtmlResponse($html);
    }
}
