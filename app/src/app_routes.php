<?php

//namespace Bristolian\Route;

function getAllAppRoutes()
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
        ['/login', 'GET', '\Bristolian\AppController\Login::showLoginPage'],
        ['/login', 'POST', '\Bristolian\AppController\Login::processLoginPage'],
        ['/logout', 'GET', '\Bristolian\AppController\Login::logout'],
        ['/tools', 'GET', 'Bristolian\AppController\Tools::index'],



        [
            '/user/profile',
            'GET',
            'Bristolian\AppController\Users::showOwnProfile'
        ],



        [
            '/users/{username:.*}/docs/{title:.*}',
            'GET',
            'Bristolian\AppController\Users::showUserDocument'
        ],




        ['/users/whoami', 'GET', 'Bristolian\AppController\Users::whoami'],

        ['/users/{user_id:.*}/avatar', 'GET', 'Bristolian\AppController\Users::getUserAvatar'],

        ['/users/{user_id:.*}/profile', 'GET', 'Bristolian\AppController\Users::showUserProfile'],

        ['/users/{user_id:.*}/{username:.*}', 'GET', 'Bristolian\AppController\Users::showUser'],
        ['/users', 'GET', 'Bristolian\AppController\Users::index'],
        ['/qr/code', 'GET', '\Bristolian\AppController\QRCode::get'],
        ['/qr/code/token', 'GET', '\Bristolian\AppController\QRCode::getToken'],
        [
            '/questions/1_weca_active_travel',
            'GET',
            '\Bristolian\AppController\Pages::weca_question_active_travel'
        ],
        [
            '/questions/2_weca_cumberland_basin_tram',
            'GET',
            '\Bristolian\AppController\Pages::weca_question_tram'
        ],
        ['/questions', 'GET', '\Bristolian\AppController\Pages::questions'],
        ['/bcc/committee_meetings', 'GET', '\Bristolian\AppController\Pages::bcc_committee_meetings'],
        ['/complaints/triangle_road', 'GET', '\Bristolian\AppController\Pages::triangle_road'],
        ['/explanations/bristol_rovers', 'GET', '\Bristolian\AppController\Pages::bristol_rovers'],
        ['/explanations/avon_crescent', 'GET', 'Bristolian\AppController\Pages::avon_crescent'],
        [
            '/explanations/advice_for_speaking_at_council',
            'GET',
            'Bristolian\AppController\Pages::advice_for_speaking_at_council'
        ],


        ['/explanations/shenanigans_planning', 'GET', 'Bristolian\AppController\Pages::shenanigans_planning'],
        [
            '/explanations/monitoring_officer_notes',
            'GET',
            'Bristolian\AppController\Pages::monitoring_officer_notes'
        ],

        [
            '/explanations/development_committee_rules',
            'GET',
            'Bristolian\AppController\Pages::development_committee_rules'
        ],
        [
            '/rooms/{room_id}/file/{file_id}/{original_filename}',
            'GET',
            'Bristolian\AppController\Rooms::serveFileForRoom'
        ],

        [
            '/rooms/{room_id:.*}/file_annotate/{file_id:.*}',
            'GET',
            'Bristolian\AppController\Rooms::annotate_file'
        ],


        [
            '/comms',
            'GET',
            'Bristolian\AppController\Chat::get_test_page'
        ],

        [
            '/rooms/{room_id:.*}/file/{file_id:.*}/sourcelinks/{sourcelink_id}/view',
            'GET',
            '\Bristolian\AppController\Rooms::viewSourcelink'
        ],

        [
            '/iframe/rooms/{room_id:.*}/file_annotate/{file_id:.*}',
            'GET',
            'Bristolian\AppController\Rooms::iframe_show_file'
        ],



        ['/rooms', 'GET', 'Bristolian\AppController\Rooms::index'],

        // TODO - limit the allowed characters for files
        ['/rooms/{room_id:.*}', 'GET', 'Bristolian\AppController\Rooms::showRoom'],
        ['/tools/floating_point', 'GET', '\Bristolian\AppController\Pages::floating_point_page'],
        ['/tools/floating_point_8_bit', 'GET', '\Bristolian\AppController\Pages::floating_point_page_8'],
        ['/tools/timeline', 'GET', '\Bristolian\AppController\Pages::timeline_page'],


        ['/tools/bristol_stairs/{stair_id:.*}', 'GET', 'Bristolian\AppController\BristolStairs::stairs_page_stair_selected'],
        ['/bristol_stairs/image/{stored_stair_image_file_id:.+}', 'GET', 'Bristolian\AppController\BristolStairs::getImage'],
        ['/avatar/image/{avatar_image_id:.+}', 'GET', 'Bristolian\AppController\Users::getAvatarImage'],
        ['/tools/bristol_stairs', 'GET', 'Bristolian\AppController\BristolStairs::stairs_page'],

        ['/tools/notes', 'GET', '\Bristolian\AppController\Pages::notes_page'],
        ['/tools/twitter_splitter', 'GET', '\Bristolian\AppController\Pages::twitter_splitter_page'],
        ['/tools/teleprompter', 'GET', '\Bristolian\AppController\Pages::teleprompter_page'],
        ['/tools/email_link_generator', 'GET', '\Bristolian\AppController\Pages::email_link_generator_page'],
        ['/tools/qr_code_generator', 'GET', '\Bristolian\AppController\Pages::qr_code_generator_page'],
        // System pages
        ['/system/csp/reports', 'GET', '\Bristolian\AppController\System::show_csp_reports'],
        ['/system/csp/test', 'GET', 'Bristolian\AppController\ContentSecurityPolicy::getTestPage'],
        ['/system/csp/clear', 'GET', 'Bristolian\AppController\ContentSecurityPolicy::clearReports'],
        ['/system/database_tables', 'GET', 'Bristolian\AppController\System::showDbInfo'],
        ['/system/deploy_log', 'GET', 'Bristolian\AppController\System::deploy_log'],
        ['/system/debugging', 'GET', 'Bristolian\AppController\System::debugging'],
        ['/system/swagger', 'GET', 'Bristolian\AppController\System::display_swagger'],
        ['/system/route_explorer', 'GET', 'Bristolian\AppController\System::route_explorer'],
        ['/system/tinned_fish_products', 'GET', 'Bristolian\AppController\System::tinned_fish_products'],

        ['/system', 'GET', 'Bristolian\AppController\System::index'],
        ['/files', 'GET', '\Bristolian\AppController\Docs::files'],
        ['/memes', 'GET', '\Bristolian\AppController\Docs::memes'],
        ['/docs', 'GET', '\Bristolian\AppController\Docs::index'],
        ['/tags/edit', 'POST', 'Bristolian\AppController\Tags::process_add'],
        ['/tags/edit', 'GET', 'Bristolian\AppController\Tags::edit'],
        ['/tags', 'GET', 'Bristolian\AppController\Tags::view'],
        ['/foi_requests/edit', 'POST', 'Bristolian\AppController\FoiRequests::process_add'],
        ['/foi_requests/edit', 'GET', 'Bristolian\AppController\FoiRequests::edit'],
        ['/foi_requests', 'GET', 'Bristolian\AppController\FoiRequests::view'],
        ['/experimental', 'GET', 'Bristolian\AppController\Pages::experimental'],
        ['/notifications_keys', 'GET', 'Bristolian\AppController\Notifications::generate_keys'],
        ['/user/memes', 'GET', 'Bristolian\AppController\User::manageMemes'],
        ["/images/memes/{id:.+}.jpg", 'GET', 'Bristolian\AppController\Images::show_meme'],
        ["/admin/notification_test", 'GET', '\Bristolian\AppController\Admin::showNotificationTestPage'],

        ['/admin/control_processors', 'POST', 'Bristolian\AppController\Admin::updateProcessors'],
        ['/admin/control_processors', 'GET', 'Bristolian\AppController\Admin::showProcessorsPage'],

        ['/admin/email', 'GET', 'Bristolian\AppController\Admin::showEmailPage'],

        ['/api/admin/email', 'GET', 'Bristolian\AppController\Admin::getEmails'],


        ['/admin', 'GET', 'Bristolian\AppController\Admin::showAdminPage'],
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

        ['/debug/redis', 'GET', 'Bristolian\AppController\Debug::debug_redis'],
        ['/debug', 'GET', 'Bristolian\AppController\Debug::debug_page'],

        // TODO - actually make a 404 page
        ['/{any:.*}', 'GET', 'Bristolian\AppController\Pages::get404Page'],
    ];

}



