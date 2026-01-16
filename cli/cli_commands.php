<?php

use Danack\Console\Application;
use Danack\Console\Command\Command;
use Danack\Console\Input\InputArgument;

/**
 * @param Application $console
 */
function add_console_commands(Application $console)
{
    addDebugCommands($console);
//    addProcessCommands($console);
    addSeedCommands($console);
    addDatabaseCommands($console);
    addAdminAccountCommands($console);
    addMiscCommands($console);
    addTestCommands($console);
    addRoomCommands($console);
    addEmailCommands($console);
    addGenerateCommands($console);

    addBristolStairsCommands($console);
    addMemeCommands($console);
    addOpenApiCommands($console);

    addMoonCommands($console);
    addBccTroCommands($console);
}

function addEmailCommands(Application $console)
{
    $command = new Command('email:test', 'Bristolian\CliController\Email::testEmail');
    $command->setDescription("Send a test email.");
    $console->add($command);

    $command = new Command(
        'process:queue:email_send',
        'Bristolian\CliController\Email::processEmailSendQueue'
    );
    $command->setDescription("Process the email send queue.");
    $console->add($command);


    $command = new Command(
        'process:meme_ocr',
        'Bristolian\CliController\MemeOcr::process'
    );
    $command->setDescription("Run image ocr for the next meme");
    $console->add($command);



    $command = new Command('queue:email_clear', 'Bristolian\CliController\Email::clearEmailQueue');
    $command->setDescription("Clear any pending emails.");
    $console->add($command);
}

/**
 * @param Application $console
 */
function addDebugCommands(Application $console)
{
    $command = new Command('debug:hello', 'Bristolian\CliController\Debug::hello');
    $command->setDescription("Test cli commands are working.");
    $console->add($command);

    $command = new Command('debug:send_webpush', 'Bristolian\CliController\Debug::send_webpush');
    $command->setDescription(
        "Send a webpush to a user, if they are registered for webpushes"
    );
    $command->addArgument('email_address', InputArgument::REQUIRED, "The username for the account.");
    $command->addArgument('message', InputArgument::REQUIRED, "The message to send");
    $console->add($command);

    $command = new Command('debug:files', '\Bristolian\CliController\Debug::upload_file');
    $command->setDescription("Test file stuff is work.");
    $console->add($command);


    $command = new Command('debug:system_info', '\Bristolian\CliController\Debug::generate_system_info_email');
    $command->setDescription("Generate the system info email.");
    $console->add($command);


    $command = new Command('debug:stack_trace', 'Bristolian\CliController\Debug::stack_trace');
    $command->setDescription("Test exception stack trace is correct.");
    $console->add($command);


    $command = new Command('debug:add_room_file', 'Bristolian\CliController\Debug::test_add_room_file');
    $command->setDescription("Test adding a file to a room.");
    $console->add($command);


    $command = new Command(
        'debug:send_message_to_room',
        'Bristolian\CliController\Debug::send_message_to_room'
    );
    $command->setDescription("Test sending message to a room.");
    $command->addArgument('message', InputArgument::REQUIRED, "The message to send");
    $console->add($command);

    // Add a meme file with optional tags and text
    // Usage examples:
    //   php cli.php debug:add_meme /path/to/image.jpg
    //   php cli.php debug:add_meme /path/to/image.jpg "tag1,tag2,tag3"
    //   php cli.php debug:add_meme /path/to/image.jpg "tag1,tag2" "OCR extracted text content"
    $command = new Command(
        'debug:add_meme',
        'Bristolian\CliController\Debug::add_meme'
    );
    $command->setDescription("Add a meme file with optional tags and text.");
    $command->addArgument('file_path', InputArgument::REQUIRED, "Path to the meme file to upload");
    $command->addArgument('tags', InputArgument::OPTIONAL, "Comma-separated list of tags to add");
    $command->addArgument('text', InputArgument::OPTIONAL, "Text content for the meme (OCR text)");
    $console->add($command);
}

function addSeedCommands(Application $console)
{
    $command = new Command('seed:initial', 'Bristolian\CliController\DataSeed::seedDatabase');
    $command->setDescription("Seed the database");
    $console->add($command);
}


function addDatabaseCommands(Application $console)
{
    $command = new Command(
        'db:wait_for_db',
        'Bristolian\CliController\Database::waitForDBToBeWorking'
    );
    $command->setDescription("Wait for the database to be online");
    $console->add($command);

    $command = new Command(
        'db:migrate_to_latest',
        'Bristolian\CliController\Database::performMigrations'
    );

    $command->setDescription("Make the database have the latest structure");
    $console->add($command);
}


function addMiscCommands(Application $console)
{
    $command = new Command(
        'misc:check_config_complete',
        'Bristolian\Config::testValuesArePresent'
    );
    $command->setDescription("Check the config has values for all known config.");
    $console->add($command);

    $command = new Command(
        'process:generate:daily_system_info',
        'Bristolian\CliController\SystemInfo::process_daily_system_info'
    );

    $command->setDescription("Generate an email just past noon each day.");
    $console->add($command);
}

function addGenerateCommands(Application $console)
{
    $command = new Command(
        'generate:javascript_constants',
        'Bristolian\CliController\GenerateFiles::generateAllJavaScriptFiles'
    );
    $command->setDescription("Generate JavaScript constants from PHP source values.");
    $console->add($command);

    $command = new Command(
        'generate:php_table_helper_classes',
        'Bristolian\CliController\GenerateFiles::generateTableHelperClasses'
    );
    $command->setDescription("Generate Helper classes, to avoid having to type column names out.");
    $console->add($command);

    $command = new Command(
        'generate:model_classes',
        'Bristolian\CliController\GenerateFiles::generateModelClasses'
    );
    $command->setDescription("Generate model classes from database schema.");
    $console->add($command);


    $command = new Command(
        'generate:datatype_docs',
        'Bristolian\CliController\CodeGen::analyze_datatypes'
    );
    $command->setDescription("Generate documentation for the datatypes.");
    $console->add($command);

    $command = new Command(
        'generate:php_response_types',
        'Bristolian\CliController\GenerateFiles::generatePhpResponseTypes'
    );
    $command->setDescription("Generate PHP response type classes from API routes.");
    $console->add($command);

    $command = new Command(
        'generate:typescript_api_routes',
        'Bristolian\CliController\GenerateFiles::generateTypeScriptApiRoutes'
    );
    $command->setDescription("Generate TypeScript API routes file with endpoints and response types.");
    $console->add($command);
}


function addMoonCommands(Application $console)
{
    $command = new Command(
        'moon:info',
        'Bristolian\CliController\MoonInfo::info'
    );
    $command->setDescription("Show info about the moon.");
    $console->add($command);


    $command = new Command(
        'process:generate:moon_alert',
        'Bristolian\CliController\MoonInfo::run'
    );
    $command->setDescription("Run the task to generate alerts about the moon.");
    $console->add($command);
}





function addTestCommands(Application $console)
{
    $command = new Command(
        'test:push_notification',
        '\Bristolian\AppController\Notifications::test_push'
    );
    $command->setDescription("Send a test notification.");
    $console->add($command);
}


function addAdminAccountCommands(Application $console)
{
    $command = new Command('admin:create_user', 'Bristolian\CliController\Admin::createAdminLogin');
    $command->setDescription("Create an user");
    $command->addArgument('email_address', InputArgument::REQUIRED, "The username for the account.");
    $command->addArgument('password', InputArgument::OPTIONAL, "The password for the account. If not set, a random one will be generated.", null);

    $console->add($command);

//    $command = new Command('admin:reset_password', '\Osf\CliController\Admin::resetPassword');
//    $command->setDescription("Reset password an admin user");
//    $command->addArgument('username', InputArgument::REQUIRED, "The username for the account.");
//    $console->add($command);
//
//    $command = new Command('admin:reset_google_2fa', '\Osf\CliController\Admin::resetGoogle2FA');
//    $command->setDescription("Remove google 2fa from admin account");
//    $command->addArgument('username', InputArgument::REQUIRED, "The username for the account.");
//    $console->add($command);
}

function addRoomCommands(Application $console)
{
    $command = new Command('room:create', 'Bristolian\CliController\Rooms::createFromCli');
    $command->setDescription("Create a room");

    $command->addArgument('name', InputArgument::REQUIRED, "The name of the room.");
    $command->addArgument('purpose', InputArgument::REQUIRED, "The purpose/description of the room.");

    $console->add($command);
}


function addBristolStairsCommands(Application $console)
{
    $command = new Command('stairs:create', 'Bristolian\CliController\BristolStairs::create');
    $command->setDescription("Create Bristol stairs entry from an image");
    $command->addArgument('image_filename', InputArgument::REQUIRED, "The image filename.");
//    $command->addArgument('purpose', InputArgument::OPTIONAL, "The purpose/description of the room.");

    $console->add($command);

    $command = new Command('stairs:total', 'Bristolian\CliController\BristolStairs::total');
    $command->setDescription("Find the total number of steps known.");
    $console->add($command);

    $command = new Command('stairs:check', 'Bristolian\CliController\BristolStairs::check_contents');
    $command->setDescription("Check for files in stair image storage that don't have corresponding database entries (orphaned files).");
    $console->add($command);

}

function addMemeCommands(Application $console)
{
    $command = new Command('meme:check', 'Bristolian\CliController\Meme::check_contents');
    $command->setDescription("Check for files in meme image storage that don't have corresponding database entries (orphaned files).");
    $console->add($command);
}

function addOpenApiCommands(Application $console)
{
    $command = new Command('openapi:generate', 'Bristolian\CliController\OpenApi::generate');
    $command->setDescription("Generate OpenAPI specification from PHP generator");
    $console->add($command);

    $command = new Command('openapi:validate', 'Bristolian\CliController\OpenApi::validate');
    $command->setDescription("Validate an OpenAPI JSON file");
    $command->addArgument('file_path', InputArgument::REQUIRED, "Path to the OpenAPI JSON file to validate");
    $console->add($command);

    $command = new Command('openapi:generate-and-validate', 'Bristolian\CliController\OpenApi::generateAndValidate');
    $command->setDescription("Generate OpenAPI specification and validate it");
    $console->add($command);
}

function addBccTroCommands(Application $console)
{
    $command = new Command(
        'service:bcc_tro_fetch',
        'Bristolian\CliController\BccTroFetcherCliController::fetchTros'
    );

    $command->addArgument(
        'output',
        InputArgument::OPTIONAL,
        "One of 'CLI' or 'room'", 'CLI'
    );

    $command->setDescription("Fetch and display Bristol City Council Traffic Regulation Orders");
    $console->add($command);

//    $command = new Command(
//        'process:generate:daily_bcc_tro',
//        'Bristolian\CliController\BccTroFetcherCliController::daily_bcc_tro'
//    );
//    $command->addArgument(
//        'output',
//        InputArgument::OPTIONAL,
//        "One of 'CLI_TEST' or 'NOTIFY'",
//        'CLI_TEST'
//    );
//
//    $command->setDescription("Fetch and send Bristol City Council Traffic Regulation Orders to rooms.");
//    $console->add($command);
}