<?php

namespace Bristolian\Basic;

use DI\Injector;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SlimDispatcher\DispatcherInterface;

class Dispatcher implements DispatcherInterface
{
    public function __construct(private Injector $injector)
    {
    }

    /**
     * Dispatch an incoming request to a controller through an injector
     * @param Request $request
     * @param array $routeArguments
     * @param $resolvedCallable
     * @return mixed
     * @throws \DI\ConfigException
     * @throws \DI\InjectionException
     */
    public function dispatch_route(Request $request, array $routeArguments, $resolvedCallable)
    {
        $this->injector->alias(Request::class, get_class($request));
        $this->injector->share($request);
        foreach ($routeArguments as $key => $value) {
            $this->injector->defineParam($key, $value);
        }

        // $routeParams = new RouteParams($routeArguments);
        // $injector->share($routeParams);

        return $this->injector->execute($resolvedCallable);
    }

    /**
     * Convert a result from a controller, into an actual HTML response.
     *
     * @param $mapCallable
     * @param $result mixed Actual type will depend on the callable
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \DI\InjectionException
     */
    public function convert_response_to_html($mapCallable, $result, Request $request, Response $response)
    {
        $fn = $this->injector->buildExecutable($mapCallable);
        return $fn($result, $request, $response);
    }
}
