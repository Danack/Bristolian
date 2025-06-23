<?php

declare(strict_types=1);

namespace BristolianTest\Data;

use BristolianTest\BaseTestCase;
use Bristolian\Data\ContentPolicyViolationReport;

/**
 * @coversNothing
 */
class ContentPolicyViolationReportTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Data\ContentPolicyViolationReport
     * @return void
     */
    public function testWorks()
    {
        $data = [];
        $data['document-uri'] = $document_uri = 'http://www.example.com';
        $data['referrer'] = $referrer = 'http://www.google.com';
        $data['blocked-uri'] = $blocked_uri = 'www.foo.bar';
        $data['violated-directive'] = $violated_directive = 'some directive';
        $data['original-policy'] = $some_policy = 'some policy';

        $report = ContentPolicyViolationReport::fromArray($data);

        $this->assertSame($document_uri, $report->getDocumentUri());
        $this->assertSame($referrer, $report->getReferrer());
        $this->assertSame($blocked_uri, $report->getBlockedUri());
        $this->assertSame($violated_directive, $report->getViolatedDirective());
        $this->assertSame($some_policy, $report->getOriginalPolicy());

        $this->assertSame('disposition NOT SET', $report->getDisposition());
        $this->assertSame('effective_directive NOT SET', $report->getEffectiveDirective());
        $this->assertSame('line_number NOT SET', $report->getLineNumber());
        $this->assertSame('script_sample NOT SET', $report->getScriptSample());
        $this->assertSame('source_file NOT SET', $report->getSourceFile());
        $this->assertSame('status_code NOT SET', $report->getStatusCode());

        $resultData = $report->toArray();
        // TODO - write assertions
    }

    /**
     * @covers \Bristolian\Data\ContentPolicyViolationReport
     * @return void
     */
    public function testFromCSPPayloadWorks()
    {
        $data = [];
        $data['document-uri'] = $document_uri = 'http://www.example.com';
        $data['referrer'] = $referrer = 'http://www.google.com';
        $data['blocked-uri'] = $blocked_uri = 'www.foo.bar';
        $data['violated-directive'] = 'some directive';
        $data['original-policy'] = 'some policy';

        $report['csp-report'] = $data;


        $report = ContentPolicyViolationReport::fromCSPPayload($report);

        $this->expectException(\Exception::class);
        ContentPolicyViolationReport::fromCSPPayload([]);
    }
}
