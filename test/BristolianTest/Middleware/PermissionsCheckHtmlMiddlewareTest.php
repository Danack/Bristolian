<?php

namespace BristolianTest\Middleware;

use Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo;
use Bristolian\Session\FakeAppSessionManager;
use BristolianTest\BaseTestCase;
use Bristolian\Middleware\PermissionsCheckHtmlMiddleware;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Bristolian\Exception\InvalidPermissionsException;

/**
 * @covers \Bristolian\Middleware\PermissionsCheckHtmlMiddleware
 */
class PermissionsCheckHtmlMiddlewareTest extends BaseTestCase
{
    public function testWorks_standard_get()
    {
        $appSessionManager = new FakeAppSessionManager();
        $apiTokenRepo = new FakeApiTokenRepo();

        $middleware = new PermissionsCheckHtmlMiddleware($appSessionManager, $apiTokenRepo);

        $request = new ServerRequest();
        $request_handler = new FakeRequestHandler();

        $result = $middleware->process($request, $request_handler);
    }

    public function testWorks_standard_POST_not_logged_in()
    {
        $appSessionManager = new FakeAppSessionManager();
        $apiTokenRepo = new FakeApiTokenRepo();

        $middleware = new PermissionsCheckHtmlMiddleware($appSessionManager, $apiTokenRepo);

        $request = new ServerRequest(method: 'POST');
        $request_handler = new FakeRequestHandler();

        $this->expectException(InvalidPermissionsException::class);
        $middleware->process($request, $request_handler);
    }

    public function testWorks_standard_POST_logged_in()
    {
        $appSessionManager = FakeAppSessionManager::createLoggedIn();
        $apiTokenRepo = new FakeApiTokenRepo();

        $middleware = new PermissionsCheckHtmlMiddleware($appSessionManager, $apiTokenRepo);

        $request = new ServerRequest(method: 'POST');
        $request_handler = new FakeRequestHandler();

        $middleware->process($request, $request_handler);

        $result = $middleware->process($request, $request_handler);
        $this->assertInstanceOf(Response::class, $result);
    }

    public function provides_login_route()
    {
        yield ['/login'];
        yield ['/api/login-status'];
    }

    /**
     * @dataProvider provides_login_route
     */
    public function testWorks_standard_POST_to_optional_login_route(string $route)
    {
        $appSessionManager = new FakeAppSessionManager();
        $apiTokenRepo = new FakeApiTokenRepo();

        $middleware = new PermissionsCheckHtmlMiddleware($appSessionManager, $apiTokenRepo);

        $request = new ServerRequest(
            method: 'POST',
            uri: $route
        );
        $request_handler = new FakeRequestHandler();

        $result = $middleware->process($request, $request_handler);
        $this->assertInstanceOf(Response::class, $result);
    }
}
