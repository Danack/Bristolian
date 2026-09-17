<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\ContentSecurityPolicy;
use Bristolian\CSPViolation\CSPViolationStorage;
use Bristolian\CSPViolation\FakeCSPViolationStorage;
use Bristolian\Data\ContentPolicyViolationReport;
use Bristolian\JsonInput\FakeJsonInput;
use Bristolian\JsonInput\JsonInput;
use BristolianTest\BaseTestCase;
use SlimDispatcher\Response\HtmlResponse;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\TextResponse;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\AppController\ContentSecurityPolicy::class, 'clearReports')]
#[CoversMethod(\Bristolian\AppController\ContentSecurityPolicy::class, 'getReports')]
#[CoversMethod(\Bristolian\AppController\ContentSecurityPolicy::class, 'getTestPage')]
#[CoversMethod(\Bristolian\AppController\ContentSecurityPolicy::class, 'postReport')]

class ContentSecurityPolicyTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->injector->alias(CSPViolationStorage::class, FakeCSPViolationStorage::class);
        $this->injector->share(FakeCSPViolationStorage::class);
    }

    public function test_postReport(): void
    {
        $cspPayload = [
            'csp-report' => [
                'document-uri' => 'http://www.example.com',
                'referrer' => '',
                'violated-directive' => 'script-src-elem',
                'effective-directive' => 'script-src-elem',
                'original-policy' => 'default-src \'self\'',
                'disposition' => 'enforce',
                'blocked-uri' => 'inline',
                'line-number' => 1,
                'source-file' => 'http://www.example.com',
                'status-code' => 200,
                'script-sample' => '',
            ]
        ];

        $jsonInput = new FakeJsonInput($cspPayload);
        $this->injector->alias(JsonInput::class, FakeJsonInput::class);
        $this->injector->share($jsonInput);

        $result = $this->injector->execute([ContentSecurityPolicy::class, 'postReport']);
        $this->assertInstanceOf(TextResponse::class, $result);
    }

    public function test_clearReports(): void
    {
        $result = $this->injector->execute([ContentSecurityPolicy::class, 'clearReports']);
        $this->assertInstanceOf(TextResponse::class, $result);
    }

    public function test_getReports(): void
    {
        $result = $this->injector->execute([ContentSecurityPolicy::class, 'getReports']);
        $this->assertInstanceOf(JsonNoCacheResponse::class, $result);
    }

    public function test_getReports_with_stored_reports_returns_array_of_toArray(): void
    {
        $report = ContentPolicyViolationReport::fromArray([
            'document-uri' => 'https://example.com/page',
            'referrer' => '',
            'blocked-uri' => 'https://evil.com/script.js',
            'violated-directive' => 'script-src',
            'original-policy' => "default-src 'self'",
        ]);
        $storage = $this->injector->make(FakeCSPViolationStorage::class);
        $storage->report($report);

        $result = $this->injector->execute([ContentSecurityPolicy::class, 'getReports']);

        $this->assertInstanceOf(JsonNoCacheResponse::class, $result);
        $body = $result->getBody();
        $this->assertStringContainsString('example.com/page', $body);
        $this->assertStringContainsString('script-src', $body);
    }

    public function test_getTestPage(): void
    {
        $result = $this->injector->execute([ContentSecurityPolicy::class, 'getTestPage']);
        $this->assertInstanceOf(HtmlResponse::class, $result);
    }
}
