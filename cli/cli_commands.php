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

    addMoonCommands($console);
}

function addEmailCommands(Application $console)
{
    $command = new Command('email:test', 'Bristolian\CliController\Email::testEmail');
    $command->setDescription("Send a test email.");
    $console->add($command);

    $command = new Command('process:email_send', 'Bristolian\CliController\Email::processEmailSendQueue');
    $command->setDescription("Process the email send queue.");
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

//    $command = new Command('debug:debug', 'Osf\CliController\Debug::debug');
//    $command->setDescription("Debugging, customise this.");
//    $console->add($command);
//
//    $command = new Command('debug:add_stripe_event', 'Osf\CliController\Debug::addStripeEvent');
//    $command->setDescription("Debugging, stripe events.");
//    $console->add($command);
//
//    $command = new Command('debug:invoice_pdf', 'Osf\CliController\Debug::invoicePdf');
//    $command->setDescription("Debugging invoice rendering.");
//    $console->add($command);

    $command = new Command('debug:files', '\Bristolian\CliController\Debug::upload_file');
    $command->setDescription("Test file stuff is work.");
    $console->add($command);
}


///**
// * @param Application $console
// */
//function addProcessCommands(Application $console)
//{
//    $command = new Command('process:alive_check', 'Osf\CliController\AliveCheck::run');
//    $command->setDescription("Place holder command to make sure commands are running .");
//    $console->add($command);
//
////    $command = new Command('process:invoice_pdf_generate', 'Osf\CliController\PrintUrlToPdfQueueProcessor::run');
////    $command->setDescription("Listens for InvoicePDF jobs and runs them .");
////    $console->add($command);
//
////    $command = new Command('process:purchase_order_alert', 'Osf\CliController\PurchaseOrderAlert::run');
////    $command->setDescription("Listens new purchase orders and generates alerts from them");
////    $console->add($command);
//
//    $command = new Command('process:stripe_events', 'Osf\CliController\ProcessStripeEventQueue::run');
//    $command->setDescription("Processes stripe events");
//    $console->add($command);
//
//    $command = new Command(
//        'process:purchase_order_alert',
//        '\Osf\CliController\PurchaseOrderNotificationSender::watchPurchaseOrdersAndAlert'
//    );
//    $command->setDescription("Sends notifications when there are new purchase orders");
//    $console->add($command);
//}


//function addTestCommands(Application $console)
//{
//    $command = new Command('test:twilio', 'Osf\CliController\Twilio::test');
//    $command->setDescription("Send a test SMS message.");
//
//    $command->addArgument('number', InputArgument::REQUIRED, 'The number to send the message to.');
//    $command->addArgument('message', InputArgument::REQUIRED, 'The message to send');
//    $console->add($command);
//
//    $command = new Command('test:purchase_queue', 'Osf\CliController\Debug::testPurchaseOrderAlert');
//    $command->setDescription("Send the purchase order queue works.");
//
////    $command->addArgument('number', InputArgument::REQUIRED, 'The number to send the message to.');
////    $command->addArgument('message', InputArgument::REQUIRED, 'The message to send');
//    $console->add($command);
//}


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
        'generate:datatype_docs',
        'Bristolian\CliController\CodeGen::analyze_datatypes'
    );
    $command->setDescription("Generate documentation for the datatypes.");
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
        'process:moon_alert',
        'Bristolian\CliController\MoonInfo::run'
    );
    $command->setDescription("Run the task to send alerts about the moon.");
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