<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\ContentSecurityPolicy;
use Bristolian\CSPViolation\CSPViolationStorage;
use Bristolian\CSPViolation\FakeCSPViolationStorage;
use Bristolian\JsonInput\FakeJsonInput;
use Bristolian\JsonInput\JsonInput;
use BristolianTest\BaseTestCase;
use SlimDispatcher\Response\HtmlResponse;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\TextResponse;

/**
 * @coversNothing
 */
class ContentSecurityPolicyTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->injector->alias(CSPViolationStorage::class, FakeCSPViolationStorage::class);
        $this->injector->share(FakeCSPViolationStorage::class);
    }

    /**
     * @covers \Bristolian\AppController\ContentSecurityPolicy::postReport
     */
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

    /**
     * @covers \Bristolian\AppController\ContentSecurityPolicy::clearReports
     */
    public function test_clearReports(): void
    {
        $result = $this->injector->execute([ContentSecurityPolicy::class, 'clearReports']);
        $this->assertInstanceOf(TextResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\ContentSecurityPolicy::getReports
     */
    public function test_getReports(): void
    {
        $result = $this->injector->execute([ContentSecurityPolicy::class, 'getReports']);
        $this->assertInstanceOf(JsonNoCacheResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\ContentSecurityPolicy::getTestPage
     */
    public function test_getTestPage(): void
    {
        $result = $this->injector->execute([ContentSecurityPolicy::class, 'getTestPage']);
        $this->assertInstanceOf(HtmlResponse::class, $result);
    }
}
