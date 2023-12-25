<?php

declare(strict_types = 1);

namespace IntegrationTest;

use BristolianTest\BaseTestCase;
use Bristolian\App;

/**
 * @coversNothing
 * @group needs_fixing
 */
class ErrorSetupTest extends BaseTestCase
{
    public function providesCaughtExceptionsAreActuallyCaughtForApp()
    {
        return [
            ['http://local.admin.opensourcefees.com/test/caught_exception'],
            ['http://local.app.opensourcefees.com/test/caught_exception'],
            ['http://local.super.opensourcefees.com/test/caught_exception'],
        ];
    }

    /**
     * @dataProvider providesCaughtExceptionsAreActuallyCaughtForApp
     * @group slow
     */
    public function testCaughtExceptionsAreActuallyCaughtForApp(string $url)
    {
        [$statusCode, $body, $headers] = fetchUri($url, 'GET');

        $this->assertSame($statusCode, 512);
        $this->assertStringContainsString('Osf\Exception\DebuggingCaughtException', $body);
        $this->assertStringContainsString(
            App::ERROR_CAUGHT_BY_MIDDLEWARE_MESSAGE,
            $body
        );
    }

    /**
     * @group slow
     */
    public function testCaughtExceptionsAreActuallyCaughtForApi()
    {
        $url = 'http://local.api.opensourcefees.com/test/caught_exception';

        [$statusCode, $body, $headers] = fetchUri($url, 'GET');

        $this->assertSame($statusCode, 512);
        $data = json_decode_safe($body);
        $this->assertSame(["status" => "Correctly caught DebuggingCaughtException"], $data);
    }

    public function providesUncaughtExceptionsAreActuallyCaughtBySlimForApp()
    {
        return [
            ['http://local.admin.opensourcefees.com/test/uncaught_exception'],
            ['http://local.app.opensourcefees.com/test/uncaught_exception'],
            ['http://local.super.opensourcefees.com/test/uncaught_exception'],
        ];
    }

    /**
     * @dataProvider providesUncaughtExceptionsAreActuallyCaughtBySlimForApp
     */
    public function testUncaughtExceptionsAreActuallyCaughtBySlimForApp(string $url)
    {
        [$statusCode, $body, $headers] = fetchUri($url, 'GET');

        $this->assertSame($statusCode, 500);
        $this->assertStringContainsString('Bristolian\Exception\DebuggingUncaughtException', $body);
        $this->assertStringContainsString(
            App::ERROR_CAUGHT_BY_ERROR_HANDLER_MESSAGE,
            $body
        );
    }

    /**
     * @group slow
     */
    public function testUncaughtExceptionsAreActuallyCaughtBySlimForApi()
    {
        $url = 'http://local.api.opensourcefees.com/test/uncaught_exception';

        [$statusCode, $body, $headers] = fetchUri($url, 'GET');

        $this->assertSame($statusCode, 500);
        $data = json_decode_safe($body);
        $this->assertSame(
            "Bristolian\\Exception\\DebuggingUncaughtException",
            $data['details'][0]['type']
        );
        $this->assertStringContainsString(
            App::ERROR_CAUGHT_BY_ERROR_HANDLER_API_MESSAGE,
            $body
        );
    }
}
