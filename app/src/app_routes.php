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
        // Homepage
        ['/', 'GET', 'Bristolian\AppController\Pages::index'],
        // About page
        ['/about', 'GET', 'Bristolian\AppController\Pages::about'],
        // Privacy policy
        ['/privacy_policy', 'GET', 'Bristolian\AppController\Pages::privacyPolicy'],
        // Show login form
        ['/login', 'GET', '\Bristolian\AppController\Login::showLoginPage'],
        // Process login submission
        ['/login', 'POST', '\Bristolian\AppController\Login::processLoginPage'],
        // Log out current user
        ['/logout', 'GET', '\Bristolian\AppController\Login::logout'],
        // Tools index
        ['/tools', 'GET', 'Bristolian\AppController\Tools::index'],

        // Current user's profile
        ['/user/profile', 'GET', 'Bristolian\AppController\Users::showOwnProfile'],

        // User doc by username and title
        ['/users/{username:.*}/docs/{title:.*}', 'GET', 'Bristolian\AppController\Users::showUserDocument'],

        // Current user identity (e.g. for API)
        ['/users/whoami', 'GET', 'Bristolian\AppController\Users::whoami'],
        // User avatar image
        ['/users/{user_id:.*}/avatar', 'GET', 'Bristolian\AppController\Users::getUserAvatar'],
        // User profile by id
        ['/users/{user_id:.*}/profile', 'GET', 'Bristolian\AppController\Users::showUserProfile'],
        // User profile page
        ['/users/{user_id:.*}/{username:.*}', 'GET', 'Bristolian\AppController\Users::showUser'],
        // Users list
        ['/users', 'GET', 'Bristolian\AppController\Users::index'],
        // Generate or display QR code
        ['/qr/code', 'GET', '\Bristolian\AppController\QRCode::get'],
        // Get QR auth token
        ['/qr/code/token', 'GET', '\Bristolian\AppController\QRCode::getToken'],
        // WECA active travel question
        ['/questions/1_weca_active_travel', 'GET', '\Bristolian\AppController\Pages::weca_question_active_travel'],
        // WECA tram question
        ['/questions/2_weca_cumberland_basin_tram', 'GET', '\Bristolian\AppController\Pages::weca_question_tram'],
        // Questions index
        ['/questions', 'GET', '\Bristolian\AppController\Pages::questions'],
        // BCC committee meetings
        ['/bcc/committee_meetings', 'GET', '\Bristolian\AppController\Pages::bcc_committee_meetings'],
        // Triangle Road complaint page
        ['/complaints/triangle_road', 'GET', '\Bristolian\AppController\Pages::triangle_road'],
        // Bristol Rovers explanation
        ['/explanations/bristol_rovers', 'GET', '\Bristolian\AppController\Pages::bristol_rovers'],
        // Avon Crescent explanation
        ['/explanations/avon_crescent', 'GET', 'Bristolian\AppController\Pages::avon_crescent'],
        // Advice for speaking at council
        ['/explanations/advice_for_speaking_at_council', 'GET', 'Bristolian\AppController\Pages::advice_for_speaking_at_council'],
        // Planning shenanigans explanation
        ['/explanations/shenanigans_planning', 'GET', 'Bristolian\AppController\Pages::shenanigans_planning'],
        // Monitoring officer notes
        ['/explanations/monitoring_officer_notes', 'GET', 'Bristolian\AppController\Pages::monitoring_officer_notes'],
        // Development committee rules
        ['/explanations/development_committee_rules', 'GET', 'Bristolian\AppController\Pages::development_committee_rules'],

        // Serve file in room (download/view)
        ['/rooms/{room_id}/file/{file_id}/{original_filename}', 'GET', 'Bristolian\AppController\Rooms::serveFileForRoom'],
        // File annotation page for room
        ['/rooms/{room_id:.*}/file_annotate/{file_id:.*}', 'GET', 'Bristolian\AppController\Rooms::annotate_file'],

        // Comms/chat test page
        ['/comms', 'GET', 'Bristolian\AppController\Chat::get_test_page'],

        // View single annotation
        ['/rooms/{room_id:.*}/file/{file_id:.*}/annotations/{annotation_id}/view', 'GET', '\Bristolian\AppController\Rooms::viewAnnotation'],
        // Iframe embedding for annotatable file
        ['/iframe/rooms/{room_id:.*}/file_annotate/{file_id:.*}', 'GET', 'Bristolian\AppController\Rooms::iframe_show_file'],

        // Rooms list
        ['/rooms', 'GET', 'Bristolian\AppController\Rooms::index'],
        // Single room page (TODO: limit allowed chars for files)
        ['/rooms/{room_id:.*}', 'GET', 'Bristolian\AppController\Rooms::showRoom'],
        // Floating point demo
        ['/tools/floating_point', 'GET', '\Bristolian\AppController\Pages::floating_point_page'],
        // 8-bit floating point demo
        ['/tools/floating_point_8_bit', 'GET', '\Bristolian\AppController\Pages::floating_point_page_8'],
        // Timeline tool
        ['/tools/timeline', 'GET', '\Bristolian\AppController\Pages::timeline_page'],

        // Bristol stairs – single stair
        ['/tools/bristol_stairs/{stair_id:.*}', 'GET', 'Bristolian\AppController\BristolStairs::stairs_page_stair_selected'],
        // Bristol stairs image asset
        ['/bristol_stairs/image/{stored_stair_image_file_id:.+}', 'GET', 'Bristolian\AppController\BristolStairs::getImage'],
        // Avatar image by id
        ['/avatar/image/{avatar_image_id:.+}', 'GET', 'Bristolian\AppController\Users::getAvatarImage'],
        // Bristol stairs index
        ['/tools/bristol_stairs', 'GET', 'Bristolian\AppController\BristolStairs::stairs_page'],

        // Notes tool
        ['/tools/notes', 'GET', '\Bristolian\AppController\Pages::notes_page'],
        // Twitter splitter tool
        ['/tools/twitter_splitter', 'GET', '\Bristolian\AppController\Pages::twitter_splitter_page'],
        // Teleprompter tool
        ['/tools/teleprompter', 'GET', '\Bristolian\AppController\Pages::teleprompter_page'],
        // Email link generator
        ['/tools/email_link_generator', 'GET', '\Bristolian\AppController\Pages::email_link_generator_page'],
        // QR code generator
        ['/tools/qr_code_generator', 'GET', '\Bristolian\AppController\Pages::qr_code_generator_page'],

        // System pages
        // CSP violation reports
        ['/system/csp/reports', 'GET', '\Bristolian\AppController\System::show_csp_reports'],
        // CSP test page
        ['/system/csp/test', 'GET', 'Bristolian\AppController\ContentSecurityPolicy::getTestPage'],
        // Clear CSP reports
        ['/system/csp/clear', 'GET', 'Bristolian\AppController\ContentSecurityPolicy::clearReports'],
        // Database tables info
        ['/system/database_tables', 'GET', 'Bristolian\AppController\System::showDbInfo'],
        // Deploy log viewer
        ['/system/deploy_log', 'GET', 'Bristolian\AppController\System::deploy_log'],
        // Debugging dashboard
        ['/system/debugging', 'GET', 'Bristolian\AppController\System::debugging'],
        // Swagger API docs UI
        ['/system/swagger', 'GET', 'Bristolian\AppController\System::display_swagger'],
        // Route explorer
        ['/system/route_explorer', 'GET', 'Bristolian\AppController\System::route_explorer'],
        // Tinned fish products (system)
        ['/system/tinned_fish_products', 'GET', 'Bristolian\AppController\System::tinned_fish_products'],
        // System index
        ['/system', 'GET', 'Bristolian\AppController\System::index'],

        // Files list
        ['/files', 'GET', '\Bristolian\AppController\Docs::files'],
        // Memes list
        ['/memes', 'GET', '\Bristolian\AppController\Docs::memes'],
        // Docs index
        ['/docs', 'GET', '\Bristolian\AppController\Docs::index'],
        // Add or update FOI request
        ['/foi_requests/edit', 'POST', 'Bristolian\AppController\FoiRequests::process_add'],
        // FOI request edit form
        ['/foi_requests/edit', 'GET', 'Bristolian\AppController\FoiRequests::edit'],
        // FOI requests list
        ['/foi_requests', 'GET', 'Bristolian\AppController\FoiRequests::view'],
        // Experimental features page
        ['/experimental', 'GET', 'Bristolian\AppController\Pages::experimental'],
        // Generate notification keys
        ['/notifications_keys', 'GET', 'Bristolian\AppController\Notifications::generate_keys'],
        // User meme management
        ['/user/memes', 'GET', 'Bristolian\AppController\User::manageMemes'],
        // Serve meme image
        ["/images/memes/{id:.+}.jpg", 'GET', 'Bristolian\AppController\Images::show_meme'],
        // Admin notification test
        ["/admin/notification_test", 'GET', '\Bristolian\AppController\Admin::showNotificationTestPage'],

        // Update processor config
        ['/admin/control_processors', 'POST', 'Bristolian\AppController\Admin::updateProcessors'],
        // Processors admin page
        ['/admin/control_processors', 'GET', 'Bristolian\AppController\Admin::showProcessorsPage'],
        // Admin email page
        ['/admin/email', 'GET', 'Bristolian\AppController\Admin::showEmailPage'],
        // API: list emails
        ['/api/admin/email', 'GET', 'Bristolian\AppController\Admin::getEmails'],

        // Admin: unknown cache queries
        ['/admin/unknown_cache_queries', 'GET', 'Bristolian\AppController\Admin::showUnknownCacheQueries'],
        // Admin index
        ['/admin', 'GET', 'Bristolian\AppController\Admin::showAdminPage'],
        // Topics index
        ['/topics', 'GET', 'Bristolian\AppController\Topics::index'],
        // HTML test page
        ['/system/htmltest', 'GET', 'Bristolian\AppController\Pages::htmlTest'],

        // Testing / debug
        // Trigger caught exception
        ['/test/caught_exception', 'GET', 'Bristolian\AppController\Debug::testCaughtException'],
        // Trigger uncaught exception
        ['/test/uncaught_exception', 'GET', 'Bristolian\AppController\Debug::testUncaughtException'],
        // Trigger compile error
        ['/test/compile_error', 'GET', 'Bristolian\AppController\CompileError::deliberateCompileError'],
        // Redis debug info
        ['/debug/redis', 'GET', 'Bristolian\AppController\Debug::debug_redis'],
        // Debug page
        ['/debug', 'GET', 'Bristolian\AppController\Debug::debug_page'],

        // 404 catch-all
        ['/{any:.*}', 'GET', 'Bristolian\AppController\Pages::get404Page'],
    ];

}



