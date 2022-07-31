<?php

declare(strict_types = 1);


//function createRoutesForApp()//: \SlimAuryn\Routes
//{
//    $routes = new \Bristolian\SlimRoutesExtended();
//
////    $injector = new Injector();
//
//    $standardRoutes = require __DIR__ . '/../app/src/app_routes.php';
//    foreach ($standardRoutes as $standardRoute) {
//        list($path, $method, $callable) = $standardRoute;
//        $routes->addRoute($path, $method, $callable);
//    }
//
//    return $routes;
//}



//function mapBristolianPageToPsr7(
//    \Bristolian\Page $page,
//    \Psr\Http\Message\ResponseInterface $response
//) {
//    $html = createPageHtml(
//        $page->getSection(),
//        $page
//    );
//
//    $htmlResponse = new \SlimAuryn\Response\HtmlResponse($html);
//
//    return SlimAuryn\ResponseMapper\ResponseMapper::mapStubResponseToPsr7(
//        $htmlResponse,
//        $response
//    );
//}