<?php

declare(strict_types = 1);

use Bristolian\Config\Config;
use Bristolian\Middleware\ExceptionToErrorPageResponseMiddleware;
use Bristolian\SiteHtml\PageResponseGenerator;
use DI\Injector;
use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use SlimDispatcher\DispatchingResolver;

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


/**
 * @param \Bristolian\Data\ApiDomain $apiDomain
 * @param \Bristolian\Service\RequestNonce $requestNonce
 * @return \Bristolian\Middleware\ContentSecurityPolicyMiddleware
 */
function createContentSecurityPolicyMiddleware(
    \Bristolian\Service\RequestNonce $requestNonce
) {
    return new \Bristolian\Middleware\ContentSecurityPolicyMiddleware(
        $requestNonce,
        [],
        [],
        []
    );
}


/**
 * @param Injector $injector
 * @return \Slim\App
 * @throws \Auryn\InjectionException
 */
function createSlimAppForApp(Injector $injector): \Slim\App {

    $dispatcher = new \Bristolian\Basic\Dispatcher($injector);

    $callableResolver = new DispatchingResolver(
        $dispatcher,
        $resultMappers = getAppResultMappers($injector)
    );

    $app = new \Slim\App(
    /* ResponseFactoryInterface */ $responseFactory = new ResponseFactory(),
        /* ?ContainerInterface */ $container = null,
        /* ?CallableResolverInterface */ $callableResolver,
        /* ?RouteCollectorInterface */ $routeCollector = null,
        /* ?RouteResolverInterface */ $routeResolver = null,
        /* ?MiddlewareDispatcherInterface */ $middlewareDispatcher = null
    );


    // Slim processes middleware in a Last In, First Out (LIFO) order.
    // This means the last middleware added is the first one to be executed.
    // If you add multiple middleware components, they will be executed
    // in the reverse order of their addition.
    $app->add($injector->make(\Bristolian\Middleware\PermissionsCheckHtmlMiddleware::class));
    $app->add($injector->make(\Bristolian\Middleware\MemoryCheckMiddleware::class));
    $app->add($injector->make(\Bristolian\Middleware\AppSessionMiddleware::class));
//    $app->add($injector->make(\Bristolian\Middleware\ContentSecurityPolicyMiddleware::class));
    $app->add($injector->make(\Bristolian\Middleware\ExceptionToErrorPageResponseMiddleware::class));

    return $app;
}



/**
 * Creates the objects that map StubResponse into PSR7 responses
 * @return mixed
 */
function getAppResultMappers(\DI\Injector $injector)
{
    return [
        // Convert a streaming Response to a PSR-7 response.
        \Bristolian\Response\StreamingResponse::class =>
            '\mapStreamingResponseToPSR7',

        // Convert a Stub Response to a PSR-7 response.
        \SlimDispatcher\Response\StubResponse::class =>
            'SlimDispatcher\mapStubResponseToPsr7',

        // Response is already a PSR-7 response, just pass it through.
        ResponseInterface::class =>
            '\SlimDispatcher\passThroughResponse',

        // Some controllers just want to return a chunk of HTML
        'string' =>
            'Bristolian\StringToHtmlPageConverter::convertStringToHtmlResponse',
    ];
}