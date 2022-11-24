<?php

//namespace Bristolian\Route;

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

        // Static pages
        ['/', 'GET', 'Bristolian\AppController\Pages::index'],
        ['/about', 'GET', 'Bristolian\AppController\Pages::about'],
        ['/privacy_policy', 'GET', 'Bristolian\AppController\Pages::privacyPolicy'],

        ['/timeline', 'GET', '\Bristolian\AppController\Pages::timeline_page'],
        ['/notes', 'GET', '\Bristolian\AppController\Pages::notes_page'],

        ['/twitter_splitter', 'GET', '\Bristolian\AppController\Pages::twitter_splitter_page'],

        // System pages

        ['/system/csp/reports', 'GET', '\Bristolian\AppController\System::show_csp_reports'],
        ['/system/csp/test', 'GET', 'Bristolian\AppController\ContentSecurityPolicy::getTestPage'],
        ['/system/csp/clear', 'GET', 'Bristolian\AppController\ContentSecurityPolicy::clearReports'],
        ['/system', 'GET', 'Bristolian\AppController\System::index'],


//    ['/dramas', 'GET', 'Bristolian\AppController\Dramas::showDramas'],

        // Dynamic pages
        ['/topics', 'GET', 'Bristolian\AppController\Topics::index'],


        // System pages
        // ['/system', 'GET', 'Bristolian\AppController\System::indexPage'],
        ['/system/htmltest', 'GET', 'Bristolian\AppController\Pages::htmlTest'],
        // ['/system/csp_reports', 'GET', 'Bristolian\AppController\System::getReports'],


//    ['/css/{any:.*}', 'GET', 'Bristolian\AppController\Pages::get404Page'],

        // Testing
        ['/test/caught_exception', 'GET', 'Bristolian\AppController\Debug::testCaughtException'],
        ['/test/uncaught_exception', 'GET', 'Bristolian\AppController\Debug::testUncaughtException'],
        ['/test/compile_error', 'GET', 'Bristolian\AppController\CompileError::deliberateCompileError'],


        // TODO - actually make a 404 page
        ['/{any:.*}', 'GET', 'Bristolian\AppController\Pages::get404Page'],
    ];

}



