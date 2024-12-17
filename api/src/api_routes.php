<?php

//namespace Bristolian\Route;

use Bristolian\App;

function getAllApiRoutes()
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

        ['/api/save-subscription/', 'POST', 'Bristolian\AppController\Notifications::save_subscription'],
        ['/api/save-subscription/', 'GET', 'Bristolian\AppController\Notifications::save_subscription_get'],

        ['/api/search_users', 'GET', 'Bristolian\AppController\Admin::search_users'],

        ['/api/ping_user', 'GET', 'Bristolian\AppController\Admin::ping_user'],

        ['/api/login-status', 'GET', 'Bristolian\AppController\User::get_login_status'],

        ['/api/meme-upload/', 'POST', 'Bristolian\AppController\MemeUpload::handleFileUpload'],
        ['/api/meme-upload/', 'GET', 'Bristolian\AppController\MemeUpload::handleFileUpload_get'],

        ['/api/meme-tag-add/', 'POST', 'Bristolian\AppController\User::handleMemeTagAdd'],
        ['/api/meme-tag-add/', 'GET', 'Bristolian\AppController\User::handleMemeTagAdd_get'],

        ['/api/meme-tag-delete/', 'DELETE', 'Bristolian\AppController\User::handleMemeTagDelete'],
        ['/api/meme-tag-delete/', 'GET', 'Bristolian\AppController\User::handleMemeTagDelete_get'],

        ['/api/memes', 'GET', 'Bristolian\AppController\User::listMemes'],
        ['/api/memes/{meme_id:.+}/tags', 'GET', 'Bristolian\AppController\User::getTagsForMeme'],

        ['/api/rooms/{room_id:.*}/files', 'GET', 'Bristolian\AppController\Rooms::getFiles'],

        [
            '/api/rooms/{room_id:.*}/file-upload',
            'POST',
            'Bristolian\AppController\Rooms::handleFileUpload'
        ],

        [
            '/api/rooms/{room_id:.*}/file-upload',
            'GET',
            '\Bristolian\AppController\Rooms::handleFileUpload_get'
        ],

        [
            '/api/rooms/{room_id:.*}/links',
            'POST',
            'Bristolian\AppController\Rooms::addLink'
        ],

        [
            '/api/rooms/{room_id:.*}/links',
            'GET',
            'Bristolian\AppController\Rooms::getLinks'
        ],

        ['/api/system/csp/reports_for_page', 'GET', 'Bristolian\ApiController\Csp::get_reports_for_page'],
        ['/api/test/caught_exception', 'GET', 'Bristolian\ApiController\Debug::testCaughtException'],
        ['/api/test/uncaught_exception', 'GET', 'Bristolian\ApiController\Debug::testUncaughtException'],
        ['/api/test/xdebug', 'GET', 'Bristolian\ApiController\Debug::testXdebugWorking'],

        ['/api/status', 'GET', 'Bristolian\ApiController\HealthCheck::get'],
//        ['/api/{any:.+}', 'GET', 'Bristolian\ApiController\HealthCheck::get'],
        ['/api', 'GET', 'Bristolian\ApiController\Index::getRouteList'],
    ];

}