<?php

declare(strict_types = 1);

use DI\Injector;
use Bristolian\Config\Config;
use Psr\Http\Message\ResponseInterface;
use SlimDispatcher\DispatchingResolver;
use Laminas\Diactoros\ResponseFactory;
use Bristolian\Middleware\ExceptionToErrorPageResponseMiddleware;
use Bristolian\SiteHtml\PageResponseGenerator;
/**
 * Creates the ExceptionMiddleware that converts all known app exceptions
 * to nicely formatted pages for the app/user facing sites
 */
function createExceptionToErrorPageResponseMiddleware(Injector $injector): ExceptionToErrorPageResponseMiddleware
{
    // TODO - the key is un-needed. Matching the exception handler to the
    // type of exception could be done via reflection.
    $exceptionHandlers = [
        \Bristolian\Exception\DebuggingCaughtException::class => 'renderDebuggingCaughtExceptionToHtml',
        \Auryn\InjectionException::class => 'renderAurynInjectionExceptionToHtml',
        \Bristolian\MarkdownRenderer\MarkdownRendererException::class => 'renderMarkdownRendererException',
        \ParseError::class => 'renderParseErrorToHtml',

        DI\InjectionException::class => 'renderInjectionExceptionToHtml',

        \Throwable::class => 'genericExceptionHandler' // should be last
    ];

    return new ExceptionToErrorPageResponseMiddleware(
        $injector->make(PageResponseGenerator::class),
        $exceptionHandlers
    );
}

function createHtmlAppErrorHandler(
    Config $config,
    \DI\Injector $injector
) : \Bristolian\AppErrorHandler\AppErrorHandler {
    if ($config->isProductionEnv() === true) {
        return $injector->make(\Bristolian\AppErrorHandler\HtmlErrorHandlerForProd::class);
    }

    return $injector->make(\Bristolian\AppErrorHandler\HtmlErrorHandlerForLocalDev::class);
}


/**
 * @param Injector $injector
 * @param \Bristolian\AppErrorHandler\AppErrorHandler $appErrorHandler
 * @return \Slim\App
 * @throws \Auryn\InjectionException
 */
function createSlimAppForApp(
    Injector $injector,
    \Bristolian\AppErrorHandler\AppErrorHandler $appErrorHandler
): \Slim\App {

    $dispatcher = new \Bristolian\Basic\Dispatcher($injector);

    $callableResolver = new DispatchingResolver(
        $dispatcher,
        $resultMappers = getResultMappers($injector)
    );

    $app = new \Slim\App(
    /* ResponseFactoryInterface */ $responseFactory = new ResponseFactory(),
        /* ?ContainerInterface */ $container = null,
        /* ?CallableResolverInterface */ $callableResolver,
        /* ?RouteCollectorInterface */ $routeCollector = null,
        /* ?RouteResolverInterface */ $routeResolver = null,
        /* ?MiddlewareDispatcherInterface */ $middlewareDispatcher = null
    );

    $app->add($injector->make(\Bristolian\Middleware\ExceptionToErrorPageResponseMiddleware::class));
    $app->add($injector->make(\Bristolian\Middleware\ExceptionToErrorPageResponseMiddleware::class));
    $app->add($injector->make(\Bristolian\Middleware\AppSessionMiddleware::class));
    $app->add($injector->make(\Bristolian\Middleware\ContentSecurityPolicyMiddleware::class));
    $app->add($injector->make(\Bristolian\Middleware\MemoryCheckMiddleware::class));

    return $app;
}




/**
 * Creates the objects that map StubResponse into PSR7 responses
 * @return mixed
 */
function getResultMappers(\DI\Injector $injector)
{
    return [
        \SlimDispatcher\Response\StubResponse::class =>
            'SlimDispatcher\mapStubResponseToPsr7',

        // TODO - add markdown return type

//        \Bristolian\Page::class => 'mapBristolianPageToPsr7',
        ResponseInterface::class =>
            'SlimAuryn\passThroughResponse',
        'string' =>
            'Bristolian\StringToHtmlPageConverter::convertStringToHtmlResponse',
    ];
}