<?php

//namespace Bristolian\Route;

use Bristolian\App;

function getAllRoutes()
{
// Each row of this array should return an array of:
// - The path to match
// - The method to match
// - The route info
// - (optional) A setup callable to add middleware/DI info specific to that route
//
// This allows use to configure data per endpoint e.g. the endpoints that should be secured by
// an api key, should call an appropriate callable.
    return [

        [App::CSP_REPORT_PATH, 'POST', 'Bristolian\AppController\ContentSecurityPolicy::postReport'],


//    ['/csp/test', 'GET', 'Osf\CommonController\ContentSecurityPolicy::getTestPage'],
//    ['/csp', 'POST', 'Osf\CommonController\ContentSecurityPolicy::postReport'],
//  ['/projects/{project_name:.+}', 'GET', '\Osf\AppController\Projects::getProject'],

        ['/test/caught_exception', 'GET', 'Bristolian\ApiController\Debug::testCaughtException'],
        ['/test/uncaught_exception', 'GET', 'Bristolian\ApiController\Debug::testUncaughtException'],
        ['/test/xdebug', 'GET', 'Bristolian\ApiController\Debug::testXdebugWorking'],
        ['/status', 'GET', 'Bristolian\ApiController\HealthCheck::get'],
        ['/{any:.+}', 'GET', 'Bristolian\ApiController\HealthCheck::get'],
        ['/', 'GET', 'Bristolian\ApiController\Index::getRouteList'],
    ];

}