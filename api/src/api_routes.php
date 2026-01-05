<?php

//namespace Bristolian\Route;

use Bristolian\App;
use Bristolian\Response\SuccessResponse;
use Bristolian\Response\ValidationErrorResponse;
use SlimDispatcher\Response\TextResponse;

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

        [
            App::CSP_REPORT_PATH,
            'POST',
            'Bristolian\AppController\ContentSecurityPolicy::postReport',
            null // TextResponse, which is fine
        ],

        [
            '/api/save-subscription/',
            'POST', 'Bristolian\AppController\Notifications::save_subscription',
            null
        ], // SuccessResponse|ValidationErrorResponse

        [
            '/api/save-subscription/',
            'GET',
            'Bristolian\AppController\Notifications::save_subscription_get',
            null
        ], // PostEndpointAccessedViaGetResponse

        [
            '/api/search_users',
            'GET',
            'Bristolian\AppController\Admin::search_users',
            null
        ], // TODO - needs converting to reasonable response.
           // details are buried in code.

        [
            '/api/ping_user',
            'GET',
            'Bristolian\AppController\Admin::ping_user',
            null
        ], // TODO - needs converting to reasonable response.
        // details are buried in code.

        [
            '/api/bristol_stairs_update/{bristol_stair_info_id:.*}',
            'GET',
            'Bristolian\AppController\BristolStairs::update_stairs_info_get',
            null
        ], // PostEndpointAccessedViaGetResponse

        [
            '/api/bristol_stairs_update_position/{bristol_stair_info_id:.*}',
            'POST',
            'Bristolian\AppController\BristolStairs::update_stairs_position',
            null
        ], // SuccessResponse

        [
            '/api/bristol_stairs_update/{bristol_stair_info_id:.*}',
            'POST',
            'Bristolian\AppController\BristolStairs::update_stairs_info',
            null
        ], // SuccessResponse

        [
            '/api/bristol_stairs',
            'GET',
            'Bristolian\AppController\BristolStairs::getData',
            [
                ['stair_infos', \Bristolian\Model\BristolStairInfo::class, true]
            ],
        ],

        [
            '/api/bristol_stairs_image',
            'POST',
            'Bristolian\AppController\BristolStairs::handleFileUpload',
            null
        ], // Needs to be generated response. Seems to be currently returning a
          // custom response.

        [
            '/api/services/email/mailgun',
            'POST',
            'Bristolian\ApiController\MailgunEmailHandler::handleIncomingEmail',
            null
        ], // Should be changed to // SuccessResponse?

        [
            '/api/log/processor_run_records',
            'GET',
            'Bristolian\ApiController\Log::get_processor_run_records',
            [
                ['run_records', \Bristolian\Model\ProcessorRunRecord::class, true]
            ],
        ],
        [
            '/api/login-status',
            'GET',
            'Bristolian\AppController\User::get_login_status',
            null
        ], // Needs to be changed to a custom response?

        [
            '/api/meme-upload/',
            'POST',
            'Bristolian\AppController\MemeUpload::handleMemeUpload',
            null
        ], // Should be changed to custom response with 'meme_id'

        [
            '/api/meme-upload/',
            'GET',
            'Bristolian\AppController\MemeUpload::handleMemeUpload_get',
            null
        ], // PostEndpointAccessedViaGetResponse

        [
            '/api/meme-tag-add/',
            'POST',
            'Bristolian\AppController\User::handleMemeTagAdd',
            null
        ], // Probably change to just SuccessResponse, and remove early optimisation of
        // returning current tags.

        [
            '/api/meme-tag-add/',
            'GET',
            'Bristolian\AppController\User::handleMemeTagAdd_get',
            null
        ], // EndpointAccessedViaGetResponse

        [
            '/api/meme-tag-update/',
            'PUT',
            'Bristolian\AppController\User::handleMemeTagUpdate',
            null
        ], // SuccessResponse

        [
            '/api/meme-tag-update/',
            'GET',
            'Bristolian\AppController\User::handleMemeTagUpdate_get',
            null
        ], // EndpointAccessedViaGetResponse

        [
            '/api/meme-tag-delete/',
            'POST',
            'Bristolian\AppController\User::handleMemeTagDelete',
            null
        ], // Change to Success Response, and remove early optimisation of
           // returning current tags.

        [
            '/api/meme-tag-delete/',
            'GET',
            'Bristolian\AppController\User::handleMemeTagDelete_get',
            null
        ], // EndpointAccessedViaGetResponse

        [
            '/api/memes',
            'GET',
            'Bristolian\AppController\User::listMemes',
            [
                ['memes', \Bristolian\Model\Meme::class, true]
            ],
        ],

        [
            '/api/memes/search',
            'GET',
            'Bristolian\AppController\User::searchMemes',
            [
                ['memes', \Bristolian\Model\Meme::class, true]
            ],
        ], // Search memes by tag text and/or tag type

        [
            '/api/memes/{meme_id:.+}/tags',
            'GET',
            'Bristolian\AppController\User::getTagsForMeme',
            [
                ['meme_tags', \Bristolian\Model\MemeTag::class, true]
            ]
        ], // GetMemeTagsResponse

        [
            '/api/user/profile',
            'POST',
            'Bristolian\AppController\Users::updateProfile',
            [
                ['profile', \Bristolian\Model\UserProfileWithDisplayName::class, false]
            ]
        ],  // UpdateUserProfileResponse

        [
            '/api/user/avatar',
            'POST',
            'Bristolian\AppController\Users::uploadAvatar',
            null
        ], // UploadAvatarResponse

        [
            '/api/users/{user_id:.*}',
            'GET',
            'Bristolian\AppController\Users::getUserInfo',
            [
                ['user_info', \Bristolian\Model\UserProfileWithDisplayName::class, false]
            ]
        ], // GetUserInfoResponse

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
        ], // PostEndpointAccessedViaGetResponse

        [
            '/api/chat/message',
            'POST',
            'Bristolian\AppController\Chat::send_message',
            null,
        ], // Extract shape - JsonNoCacheResponse(['data' => $chat_message]);

        [
            '/api/chat/room_messages/{room_id:.*}/',
            'GET',
            'Bristolian\AppController\Chat::get_room_messages',
            [
                ['messages', \Bristolian\Model\Chat\UserChatMessage::class, true]
            ],
        ], // GetChatRoomMessagesResponse

        [
            '/api/bristol_stairs_create',
            'POST',
            'Bristolian\AppController\BristolStairs::handleFileUpload',
            [
                ['stair_info', \Bristolian\Model\BristolStairInfo::class, false]
            ],
        ], // UploadBristolStairsImageResponse

        [
            '/api/rooms/{room_id:.*}/file-upload',
            'POST',
            'Bristolian\AppController\Rooms::handleFileUpload',
            null,
        ], // SuccessResponse|ValidationErrorResponse

        [
            '/api/rooms/{room_id:.*}/file-upload',
            'GET',
            '\Bristolian\AppController\Rooms::handleFileUpload_get',
            null,
        ], // PostEndpointAccessedViaGetResponse

        [
            '/api/rooms/{room_id:.*}/links',
            'POST',
            'Bristolian\AppController\Rooms::addLink',
            null,
        ], // SuccessResponse

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
        ], // Change to a SuccessResponse.

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

        [
            '/api/system/csp/reports_for_page',
            'GET',
            'Bristolian\ApiController\Csp::get_reports_for_page',
            null
        ], // GetCspReportsResponse

        [
            '/api/test/caught_exception',
            'GET',
            'Bristolian\ApiController\Debug::testCaughtException',
            null
        ],  // Never returns.

        [
            '/api/test/uncaught_exception',
            'GET',
            'Bristolian\ApiController\Debug::testUncaughtException',
            null
        ], // Never returns.

        [
            '/api/test/xdebug',
            'GET',
            'Bristolian\ApiController\Debug::testXdebugWorking',
            null
        ], // Change to SuccessResponse and ErrorResponse

        [
            '/api/status',
            'GET',
            'Bristolian\ApiController\HealthCheck::get',
            null
        ], // maybe extract a shape? Not sure I care.

        [
            '/api',
            'GET',
            'Bristolian\ApiController\Index::getRouteList',
            null
        ], // maybe extract a shape? Not sure I care.
    ];

}