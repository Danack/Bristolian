<?php

declare(strict_types = 1);

namespace Osf\Data;

use BristolianTest\BaseTestCase;
use Osf\Data\ContentPolicyViolationReport;

///**
// * @group csp
// */
//class ContentPolicyViolationReportTest extends BaseTestCase
//{
//    public function testBasic()
//    {
//        $expectedDocumentUri = "https://example.com/foo/bar";
//        $expectedReferrer = "https://www.google.com/";
//        $expectedViolatedDirective = "default-src self";
//        $expectedOriginalPolicy = "default-src self; report-uri /csp-hotline.php";
//        $expectedBlockedUri = "http://evilhackerscripts.com";
//
//$json = <<< JSON
//{
//    "csp-report": {
//        "document-uri": "$expectedDocumentUri",
//        "referrer": "$expectedReferrer",
//        "violated-directive": "$expectedViolatedDirective",
//        "original-policy": "$expectedOriginalPolicy",
//        "blocked-uri": "$expectedBlockedUri"
//    }
//}
//JSON;
//
//        $cspReport = ContentPolicyViolationReport::fromCSPPayload($json);
//
//        $this->assertEquals($expectedDocumentUri, $cspReport->getDocumentUri());
//        $this->assertEquals($expectedReferrer   , $cspReport->getReferrer());
//        $this->assertEquals($expectedViolatedDirective, $cspReport->getViolatedDirective());
//        $this->assertEquals($expectedOriginalPolicy, $cspReport->getOriginalPolicy());
//        $this->assertEquals($expectedBlockedUri, $cspReport->getBlockedUri());
//    }
//}
