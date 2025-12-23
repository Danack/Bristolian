<?php

//namespace Bristolian\Route;

use Bristolian\App;

function getAllApiRoutes()
{
// Each row of this array should return an array of:
// - The path to match
// - The method to match
// - The route info
// - (optional) Return type information for TypeScript generation
// - (optional) A setup callable to add middleware/DI info specific to that route
//
// This allows use to configure data per endpoint e.g. the endpoints that should be secured by
// an api key, should call an appropriate callable.
    return [

        [App::CSP_REPORT_PATH, 'POST', 'Bristolian\AppController\ContentSecurityPolicy::postReport', null], // TextResponse

        ['/api/save-subscription/', 'POST', 'Bristolian\AppController\Notifications::save_subscription', null],

        ['/api/save-subscription/', 'GET', 'Bristolian\AppController\Notifications::save_subscription_get', null],

        ['/api/search_users', 'GET', 'Bristolian\AppController\Admin::search_users', null],

        ['/api/ping_user', 'GET', 'Bristolian\AppController\Admin::ping_user', null],

        [
            '/api/bristol_stairs_update/{bristol_stair_info_id:.*}',
            'GET',
            'Bristolian\AppController\BristolStairs::update_stairs_info_get',
            null
        ],

        ['/api/bristol_stairs_update_position/{bristol_stair_info_id:.*}', 'POST', 'Bristolian\AppController\BristolStairs::update_stairs_position', null],

        ['/api/bristol_stairs_update/{bristol_stair_info_id:.*}', 'POST', 'Bristolian\AppController\BristolStairs::update_stairs_info', null],

        ['/api/bristol_stairs_image', 'POST', 'Bristolian\AppController\BristolStairs::handleFileUpload', null],



        ['/api/bristol_stairs/{bristol_stairs_image_id:.+}', 'GET', 'Bristolian\AppController\BristolStairs::getDetails', null],
        [
            '/api/bristol_stairs',
            'GET',
            'Bristolian\AppController\BristolStairs::getData',
            [
                ['stair_infos', \Bristolian\Model\BristolStairInfo::class, true]
            ],
        ],


        ['/api/services/email/mailgun', 'POST', 'Bristolian\ApiController\MailgunEmailHandler::handleIncomingEmail', null],
        [
            '/api/log/processor_run_records',
            'GET',
            'Bristolian\ApiController\Log::get_processor_run_records',
            [
                ['run_records', \Bristolian\Model\ProcessorRunRecord::class, true]
            ],
        ],
        ['/api/login-status', 'GET', 'Bristolian\AppController\User::get_login_status', null],
        ['/api/meme-upload/', 'POST', 'Bristolian\AppController\MemeUpload::handleMemeUpload', null],
        ['/api/meme-upload/', 'GET', 'Bristolian\AppController\MemeUpload::handleMemeUpload_get', null],
        ['/api/meme-tag-add/', 'POST', 'Bristolian\AppController\User::handleMemeTagAdd', null],
        ['/api/meme-tag-add/', 'GET', 'Bristolian\AppController\User::handleMemeTagAdd_get', null],
        ['/api/meme-tag-delete/', 'DELETE', 'Bristolian\AppController\User::handleMemeTagDelete', null],
        ['/api/meme-tag-delete/', 'GET', 'Bristolian\AppController\User::handleMemeTagDelete_get', null],
        [
            '/api/memes',
            'GET',
            'Bristolian\AppController\User::listMemes',
            [
                ['memes', \Bristolian\Model\Meme::class, true]
            ],
        ],
        ['/api/memes/{meme_id:.+}/tags', 'GET', 'Bristolian\AppController\User::getTagsForMeme', null],
        ['/api/user/profile', 'POST', 'Bristolian\AppController\Users::updateProfile', null],
        ['/api/user/avatar', 'POST', 'Bristolian\AppController\Users::uploadAvatar', null],
        ['/api/users/{user_id:.*}', 'GET', 'Bristolian\AppController\Users::getUserInfo', null],

        [
            '/api/rooms/{room_id:.*}/files',
            'GET',
            'Bristolian\AppController\Rooms::getFiles',
            [
                ['files', \Bristolian\Model\StoredFile::class, true]
            ],
        ],


        [
            '/api/chat/message',
            'GET',
            'Bristolian\AppController\Chat::send_message_get',
            null,
        ],

        [
            '/api/chat/message',
            'POST',
            'Bristolian\AppController\Chat::send_message',
            null,
        ],

        [
            '/api/chat/room_messages/{room_id:.*}/',
            'GET',
            'Bristolian\AppController\Chat::get_room_messages',
            null,
        ],

        [
            '/api/extension_test',
            'POST',
            'Bristolian\AppController\BristolStairs::handleFileUpload',
            null,
        ],

        [
            '/api/bristol_stairs_create',
            'POST',
            'Bristolian\AppController\BristolStairs::handleFileUpload',
            null,
        ],

        [
            '/api/rooms/{room_id:.*}/file-upload',
            'POST',
            'Bristolian\AppController\Rooms::handleFileUpload',
            null,
        ],
        [
            '/api/rooms/{room_id:.*}/file-upload',
            'GET',
            '\Bristolian\AppController\Rooms::handleFileUpload_get',
            null,
        ],
        [
            '/api/rooms/{room_id:.*}/links',
            'POST',
            'Bristolian\AppController\Rooms::addLink',
            null,
        ],
        [
            '/api/rooms/{room_id:.*}/links',
            'GET',
            'Bristolian\AppController\Rooms::getLinks',
            [
                ['links', \Bristolian\Model\RoomLink::class, true]
            ],
        ],
        [
            '/api/rooms/{room_id:.*}/source_link/{file_id:.*}',
            'POST',
            '\Bristolian\AppController\Rooms::handleAddSourceLink',
            null,
        ],
        [
            '/api/rooms/{room_id}/file/{file_id}/sourcelinks',
            'GET',
            '\Bristolian\AppController\Rooms::getSourcelinksForFile',
            [
                ['sourcelinks', \Bristolian\Model\RoomSourceLink::class, true]
            ],
        ],
        [
            '/api/rooms/{room_id:.*}/sourcelinks',
            'GET',
            '\Bristolian\AppController\Rooms::getSourcelinks',
            [
                ['sourcelinks', \Bristolian\Model\RoomSourceLink::class, true]
            ],
        ],
        ['/api/system/csp/reports_for_page', 'GET', 'Bristolian\ApiController\Csp::get_reports_for_page', null],
        ['/api/test/caught_exception', 'GET', 'Bristolian\ApiController\Debug::testCaughtException', null],
        ['/api/test/uncaught_exception', 'GET', 'Bristolian\ApiController\Debug::testUncaughtException', null],
        ['/api/test/xdebug', 'GET', 'Bristolian\ApiController\Debug::testXdebugWorking', null],
        ['/api/status', 'GET', 'Bristolian\ApiController\HealthCheck::get', null],
        ['/api', 'GET', 'Bristolian\ApiController\Index::getRouteList', null],
    ];

}