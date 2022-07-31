<?php

declare(strict_types = 1);

error_reporting(E_ALL);

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../injectionParams/app.php";
require_once __DIR__ . '/../app/exception_mappers_app.php';
require_once __DIR__ . '/factories.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/error_functions.php';
require_once __DIR__ . '/site_html.php';
require_once __DIR__ . '/slim_functions.php';
require_once __DIR__ . '/../injectionParams/section.php';
require_once __DIR__ . "/../config.generated.php";

use Slim\Http\Request;

set_error_handler('saneErrorHandler');

function showPage(\Bristolian\Page $page)
{
    $injector = new Auryn\Injector();
    $injectionParams = injectionParams();
    $injectionParams->addToInjector($injector);
    $injector->share($injector);

    $callable = function () use ($page) {
        $html = createPageHtml(
            null,
            $page
        );

        return new \SlimAuryn\Response\HtmlResponse($html);
    };

    try {
        $container = new \Slim\Container();
        $container['request'] = function ($container) {
            $request = Request::createFromEnvironment($container->get('environment'));
            $uri = $request->getUri();
            $fakeUri = $uri->withPath('/fake_path');
            return $request->withUri($fakeUri);
        };

        $errorHandler = $injector->make(\Bristolian\AppErrorHandler\AppErrorHandler::class);
        $app = \createSlimAppForApp($injector, $container, $errorHandler);
        $app->map(['GET'], '/fake_path', $callable);

        $app->run();
    } catch (\Throwable $exception) {
        showTotalErrorPage($exception);
    }

    exit(0);
}

function showInternalsResponse($callable)
{
    $injector = new Auryn\Injector();
    $injectionParams = injectionParams();
    $injectionParams->addToInjector($injector);
    $injector->share($injector);
    $section = $injector->make(\Internals\InternalsSection::class);
    $breadcrumbsFactory = new \Bristolian\BreadcrumbsFactory($section);
    $injector->share($breadcrumbsFactory);

    showResponseInternal($callable, $injector);
}

function showLearningResponse($callable)
{
    $injector = new Auryn\Injector();
    $injectionParams = injectionParams();
    $injectionParams->addToInjector($injector);
    $injector->share($injector);
    $section = $injector->make(\Learning\LearningSection::class);
    $breadcrumbsFactory = new \Bristolian\BreadcrumbsFactory($section);
    $injector->share($breadcrumbsFactory);

    showResponseInternal($callable, $injector);
}

function showResponse($callable)
{
    $injector = new Auryn\Injector();
    $injectionParams = injectionParams();
    $injectionParams->addToInjector($injector);
    $injector->share($injector);

    showResponseInternal($callable, $injector);
}

function showResponseInternal($callable, Auryn\Injector $injector)
{
    try {
        $container = new \Slim\Container();
        $container['request'] = function ($container) {
            $request = Request::createFromEnvironment($container->get('environment'));
            $uri = $request->getUri();
            $fakeUri = $uri->withPath('/fake_path');
            return $request->withUri($fakeUri);
        };

        $errorHandler = $injector->make(\Bristolian\AppErrorHandler\AppErrorHandler::class);
        $app = \createSlimAppForApp($injector, $container, $errorHandler);
        $app->map(['GET'], '/fake_path', $callable);

        $app->run();
    } catch (\Throwable $exception) {
        showTotalErrorPage($exception);
    }

    exit(0);
}
