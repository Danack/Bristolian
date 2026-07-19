<?php

declare(strict_types=1);

namespace Bristolian\Cli;

use Danack\Console\Application;
use Danack\Console\Command\Command;
use Danack\Console\Input\InputArgument;

/**
 * Canonical list of CLI commands registered with cli.php.
 */
final class CliCommandRegistry
{
    /**
     * @return list<CliCommandDefinition>
     */
    public static function getAllDefinitions(): array
    {
        return array_merge(
            self::getDebugCommandDefinitions(),
            self::getSeedCommandDefinitions(),
            self::getDatabaseCommandDefinitions(),
            self::getAdminAccountCommandDefinitions(),
            self::getMiscCommandDefinitions(),
            self::getTestCommandDefinitions(),
            self::getRoomCommandDefinitions(),
            self::getEmailCommandDefinitions(),
            self::getGenerateCommandDefinitions(),
            self::getBristolStairsCommandDefinitions(),
            self::getMemeCommandDefinitions(),
            self::getOpenApiCommandDefinitions(),
            self::getMoonCommandDefinitions(),
            self::getBccTroCommandDefinitions(),
            self::getWhatDoTheyKnowCommandDefinitions(),
        );
    }

    public static function registerCommand(Application $console, CliCommandDefinition $definition): void
    {
        $command = new Command($definition->commandName, $definition->controllerCallable);

        if ($definition->description !== '') {
            $command->setDescription($definition->description);
        }

        if ($definition->configure !== null) {
            ($definition->configure)($command);
        }

        $console->add($command);
    }

    /**
     * @param list<CliCommandDefinition> $definitions
     */
    public static function registerCommands(Application $console, array $definitions): void
    {
        foreach ($definitions as $definition) {
            self::registerCommand($console, $definition);
        }
    }

    public static function getControllerCallableForCommandName(string $commandName): ?string
    {
        foreach (self::getAllDefinitions() as $definition) {
            if ($definition->commandName === $commandName) {
                return $definition->controllerCallable;
            }
        }

        return null;
    }

    /**
     * @return list<CliCommandDefinition>
     */
    public static function getDebugCommandDefinitions(): array
    {
        return [
            new CliCommandDefinition(
                'debug:hello',
                'Bristolian\CliController\Debug::hello',
                'Test cli commands are working.'
            ),
            new CliCommandDefinition(
                'debug:send_webpush',
                'Bristolian\CliController\Debug::send_webpush',
                'Send a webpush to a user, if they are registered for webpushes',
                static function (Command $command): void {
                    $command->addArgument('email_address', InputArgument::REQUIRED, 'The username for the account.');
                    $command->addArgument('message', InputArgument::REQUIRED, 'The message to send');
                }
            ),
            new CliCommandDefinition(
                'debug:files',
                'Bristolian\CliController\Debug::upload_file',
                'Test file stuff is work.'
            ),
            new CliCommandDefinition(
                'debug:system_info',
                'Bristolian\CliController\Debug::generate_system_info_email',
                'Generate the system info email.'
            ),
            new CliCommandDefinition(
                'debug:stack_trace',
                'Bristolian\CliController\Debug::stack_trace',
                'Test exception stack trace is correct.'
            ),
            new CliCommandDefinition(
                'debug:send_message_to_room',
                'Bristolian\CliController\Debug::send_message_to_room',
                'Test sending message to a room.',
                static function (Command $command): void {
                    $command->addArgument('message', InputArgument::REQUIRED, 'The message to send');
                }
            ),
            new CliCommandDefinition(
                'debug:add_meme',
                'Bristolian\CliController\Debug::add_meme',
                'Add a meme file with optional tags and text.',
                static function (Command $command): void {
                    $command->addArgument('file_path', InputArgument::REQUIRED, 'Path to the meme file to upload');
                    $command->addArgument('tags', InputArgument::OPTIONAL, 'Comma-separated list of tags to add');
                    $command->addArgument('text', InputArgument::OPTIONAL, 'Text content for the meme (OCR text)');
                }
            ),
        ];
    }

    /**
     * @return list<CliCommandDefinition>
     */
    public static function getEmailCommandDefinitions(): array
    {
        return [
            new CliCommandDefinition(
                'email:test',
                'Bristolian\CliController\Email::testEmail',
                'Send a test email.'
            ),
            new CliCommandDefinition(
                'process:queue:email_send',
                'Bristolian\CliController\Email::processEmailSendQueue',
                'Process the email send queue.'
            ),
            new CliCommandDefinition(
                'process:meme_ocr',
                'Bristolian\CliController\MemeOcr::process',
                'Run image ocr for the next meme'
            ),
            new CliCommandDefinition(
                'queue:email_clear',
                'Bristolian\CliController\Email::clearEmailQueue',
                'Clear any pending emails.'
            ),
        ];
    }

    /**
     * @return list<CliCommandDefinition>
     */
    public static function getSeedCommandDefinitions(): array
    {
        return [
            new CliCommandDefinition(
                'seed:initial',
                'Bristolian\CliController\DataSeed::seedDatabase',
                'Seed the database'
            ),
        ];
    }

    /**
     * @return list<CliCommandDefinition>
     */
    public static function getDatabaseCommandDefinitions(): array
    {
        return [
            new CliCommandDefinition(
                'db:wait_for_db',
                'Bristolian\CliController\Database::waitForDBToBeWorking',
                'Wait for the database to be online'
            ),
            new CliCommandDefinition(
                'db:migrate_to_latest',
                'Bristolian\CliController\Database::performMigrations',
                'Make the database have the latest structure'
            ),
            new CliCommandDefinition(
                'db:set_max_connections',
                'Bristolian\CliController\Database::setMaxConnections',
                'Set MySQL max_connections to 2000 (until server restart).'
            ),
        ];
    }

    /**
     * @return list<CliCommandDefinition>
     */
    public static function getMiscCommandDefinitions(): array
    {
        return [
            new CliCommandDefinition(
                'misc:check_config_complete',
                'Bristolian\Config::testValuesArePresent',
                'Check the config has values for all known config.'
            ),
            new CliCommandDefinition(
                'process:generate:daily_system_info',
                'Bristolian\CliController\SystemInfo::process_daily_system_info',
                'Generate an email just past noon each day.'
            ),
        ];
    }

    /**
     * @return list<CliCommandDefinition>
     */
    public static function getGenerateCommandDefinitions(): array
    {
        return [
            new CliCommandDefinition(
                'generate:javascript_constants',
                'Bristolian\CliController\GenerateFiles::generateAllJavaScriptFiles',
                'Generate JavaScript constants from PHP source values.'
            ),
            new CliCommandDefinition(
                'generate:php_table_helper_classes',
                'Bristolian\CliController\GenerateFiles::generateTableHelperClasses',
                'Generate Helper classes, to avoid having to type column names out.'
            ),
            new CliCommandDefinition(
                'generate:model_classes',
                'Bristolian\CliController\GenerateFiles::generateModelClasses',
                'Generate model classes from database schema.'
            ),
            new CliCommandDefinition(
                'generate:datatype_docs',
                'Bristolian\CliController\CodeGen::analyze_datatypes',
                'Generate documentation for the datatypes.'
            ),
            new CliCommandDefinition(
                'generate:php_response_types',
                'Bristolian\CliController\GenerateFiles::generatePhpResponseTypes',
                'Generate PHP response type classes from API routes.'
            ),
            new CliCommandDefinition(
                'generate:typescript_api_routes',
                'Bristolian\CliController\GenerateFiles::generateTypeScriptApiRoutes',
                'Generate TypeScript API routes file with endpoints and response types.'
            ),
            new CliCommandDefinition(
                'generate:widget_panels',
                'Bristolian\CliController\GenerateFiles::generateWidgetPanels',
                'Generate TypeScript widget panels registration from WidgetRegistry.'
            ),
            new CliCommandDefinition(
                'generate:codeview-data',
                'Bristolian\CliController\GenerateExplorerData::generateExplorerData',
                'Generate project codeview data JSON for bounded-context navigation.'
            ),
        ];
    }

    /**
     * @return list<CliCommandDefinition>
     */
    public static function getMoonCommandDefinitions(): array
    {
        return [
            new CliCommandDefinition(
                'moon:info',
                'Bristolian\CliController\MoonInfo::info',
                'Show info about the moon.'
            ),
            new CliCommandDefinition(
                'process:generate:moon_alert',
                'Bristolian\CliController\MoonInfo::run',
                'Run the task to generate alerts about the moon.'
            ),
        ];
    }

    /**
     * @return list<CliCommandDefinition>
     */
    public static function getTestCommandDefinitions(): array
    {
        return [
            new CliCommandDefinition(
                'test:push_notification',
                'Bristolian\AppController\Notifications::test_push',
                'Send a test notification.'
            ),
        ];
    }

    /**
     * @return list<CliCommandDefinition>
     */
    public static function getAdminAccountCommandDefinitions(): array
    {
        return [
            new CliCommandDefinition(
                'admin:create_user',
                'Bristolian\CliController\Admin::createAdminLogin',
                'Create an user',
                static function (Command $command): void {
                    $command->addArgument('email_address', InputArgument::REQUIRED, 'The username for the account.');
                    $command->addArgument(
                        'password',
                        InputArgument::OPTIONAL,
                        'The password for the account. If not set, a random one will be generated.',
                        null
                    );
                }
            ),
            new CliCommandDefinition(
                'admin:create_system_user',
                'Bristolian\CliController\Admin::createSystemUser',
                "Create a system user, if one doesn't already exist"
            ),
            new CliCommandDefinition(
                'admin:create_room_users',
                'Bristolian\CliController\Admin::createRoomUsers',
                'For each room, ensure user_ownership has type ROOM_USER with that room_id (creates a user per room if missing).'
            ),
        ];
    }

    /**
     * @return list<CliCommandDefinition>
     */
    public static function getRoomCommandDefinitions(): array
    {
        return [
            new CliCommandDefinition(
                'room:create',
                'Bristolian\CliController\Rooms::createFromCli',
                'Create a room',
                static function (Command $command): void {
                    $command->addArgument('name', InputArgument::REQUIRED, 'The name of the room.');
                    $command->addArgument('purpose', InputArgument::REQUIRED, 'The purpose/description of the room.');
                }
            ),
            new CliCommandDefinition(
                'room:add_file',
                'Bristolian\CliController\Rooms::addFileFromCli',
                'Upload a file and attach it to a room by room name.',
                static function (Command $command): void {
                    $command->addArgument('room_name', InputArgument::REQUIRED, 'The name of the room.');
                    $command->addArgument('file_path', InputArgument::REQUIRED, 'Path to the file to upload.');
                }
            ),
            new CliCommandDefinition(
                'room:add_file_annotation',
                'Bristolian\CliController\Rooms::addFileAnnotationFromCli',
                'Add a file annotation for seeding/tests. annotation_json: JSON object with title, highlights_json (string or highlight array), text — same shape as the web UI / AnnotationParam.',
                static function (Command $command): void {
                    $command->addArgument('room_name', InputArgument::REQUIRED, 'Room name (must be unique among rooms).');
                    $command->addArgument('original_filename', InputArgument::REQUIRED, "The file's stored original filename (e.g. sample.pdf).");
                    $command->addArgument('annotation_json', InputArgument::REQUIRED, 'JSON string, e.g. {"title":"...","highlights_json":"[{\"page\":0,...}]","text":""}');
                }
            ),
            new CliCommandDefinition(
                'room:add_link',
                'Bristolian\CliController\Rooms::addLinkFromCli',
                'Add a link to a room by room name.',
                static function (Command $command): void {
                    $command->addArgument('room_name', InputArgument::REQUIRED, 'The name of the room (must be unique among rooms).');
                    $command->addArgument('url', InputArgument::REQUIRED, 'The link URL.');
                    $command->addArgument('title', InputArgument::OPTIONAL, 'Link title (optional). Omit or use empty string when skipping.', null);
                    $command->addArgument('description', InputArgument::OPTIONAL, 'Link description (optional). Omit or use empty string when skipping.', null);
                }
            ),
            new CliCommandDefinition(
                'room:add_video',
                'Bristolian\CliController\Rooms::addVideoFromCli',
                'Add a YouTube video to a room by room name (URL or video ID).',
                static function (Command $command): void {
                    $command->addArgument('room_name', InputArgument::REQUIRED, 'The name of the room (must be unique among rooms).');
                    $command->addArgument('url', InputArgument::REQUIRED, 'YouTube watch URL, youtu.be link, or 11-character video ID.');
                    $command->addArgument('title', InputArgument::OPTIONAL, 'Video title (optional). When set, at least 8 characters (same as room links).', null);
                    $command->addArgument('description', InputArgument::OPTIONAL, 'Video description (optional). Omit or use empty string when skipping.', null);
                }
            ),
            new CliCommandDefinition(
                'room:add_tag',
                'Bristolian\CliController\Rooms::addRoomTagFromCli',
                'Create a room tag for a room identified by unique name.',
                static function (Command $command): void {
                    $command->addArgument('room_name', InputArgument::REQUIRED, 'The name of the room (must be unique among rooms).');
                    $command->addArgument('tag_text', InputArgument::REQUIRED, 'Tag label text.');
                    $command->addArgument('description', InputArgument::OPTIONAL, 'Tag description (optional).', null);
                }
            ),
            new CliCommandDefinition(
                'room:add_annotation_tag',
                'Bristolian\CliController\Rooms::addAnnotationTagFromCli',
                'Attach a room tag to an annotation. Matches an existing tag by exact text, or creates the tag if missing.',
                static function (Command $command): void {
                    $command->addArgument('room_name', InputArgument::REQUIRED, 'The name of the room (must be unique among rooms).');
                    $command->addArgument('annotation_title', InputArgument::REQUIRED, 'Annotation title (must be unique within the room).');
                    $command->addArgument('tag_text', InputArgument::REQUIRED, 'Tag label text (must match an existing room tag or will create one).');
                    $command->addArgument('description', InputArgument::OPTIONAL, 'When creating a new tag, optional description.', null);
                }
            ),
            new CliCommandDefinition(
                'room:add_video_clip',
                'Bristolian\CliController\Rooms::addVideoClipFromCli',
                'Add a YouTube clip (start/end) to a room. Times: seconds, M:SS, or H:MM:SS. Use room:add_video for a full video without trim.',
                static function (Command $command): void {
                    $command->addArgument('room_name', InputArgument::REQUIRED, 'The name of the room (must be unique among rooms).');
                    $command->addArgument('url', InputArgument::REQUIRED, 'YouTube watch URL, youtu.be link, or 11-character video ID.');
                    $command->addArgument('start_time', InputArgument::REQUIRED, 'Clip start (e.g. 75, 1:15, 1:00:00).');
                    $command->addArgument('end_time', InputArgument::REQUIRED, 'Clip end (same formats as start; must be after start).');
                    $command->addArgument('title', InputArgument::OPTIONAL, 'Clip title (optional). When set, at least 8 characters.', null);
                    $command->addArgument('description', InputArgument::OPTIONAL, 'Clip description (optional).', null);
                }
            ),
        ];
    }

    /**
     * @return list<CliCommandDefinition>
     */
    public static function getBristolStairsCommandDefinitions(): array
    {
        return [
            new CliCommandDefinition(
                'stairs:create',
                'Bristolian\CliController\BristolStairs::create',
                'Create Bristol stairs entry from an image',
                static function (Command $command): void {
                    $command->addArgument('image_filename', InputArgument::REQUIRED, 'The image filename.');
                }
            ),
            new CliCommandDefinition(
                'stairs:total',
                'Bristolian\CliController\BristolStairs::total',
                'Find the total number of steps known.'
            ),
            new CliCommandDefinition(
                'stairs:check',
                'Bristolian\CliController\BristolStairs::check_contents',
                "Check for files in stair image storage that don't have corresponding database entries (orphaned files)."
            ),
        ];
    }

    /**
     * @return list<CliCommandDefinition>
     */
    public static function getMemeCommandDefinitions(): array
    {
        return [
            new CliCommandDefinition(
                'meme:check_storage',
                'Bristolian\CliController\Meme::check_contents_of_storage',
                "Check for files in meme image storage that don't have corresponding database entries (orphaned files)."
            ),
            new CliCommandDefinition(
                'meme:check_database',
                'Bristolian\CliController\Meme::check_contents_of_database',
                "Check for database meme records that don't have a corresponding file in storage (missing files)."
            ),
        ];
    }

    /**
     * @return list<CliCommandDefinition>
     */
    public static function getOpenApiCommandDefinitions(): array
    {
        return [
            new CliCommandDefinition(
                'openapi:generate',
                'Bristolian\CliController\OpenApi::generate',
                'Generate OpenAPI specification from PHP generator'
            ),
            new CliCommandDefinition(
                'openapi:validate',
                'Bristolian\CliController\OpenApi::validate',
                'Validate an OpenAPI JSON file',
                static function (Command $command): void {
                    $command->addArgument('file_path', InputArgument::REQUIRED, 'Path to the OpenAPI JSON file to validate');
                }
            ),
            new CliCommandDefinition(
                'openapi:generate-and-validate',
                'Bristolian\CliController\OpenApi::generateAndValidate',
                'Generate OpenAPI specification and validate it'
            ),
        ];
    }

    /**
     * @return list<CliCommandDefinition>
     */
    public static function getBccTroCommandDefinitions(): array
    {
        return [
            new CliCommandDefinition(
                'service:bcc_tro_fetch:continual',
                'Bristolian\CliController\BccTroFetcherCliController::daily_bcc_tro',
                'Fetch and display Bristol City Council Traffic Regulation Orders',
                static function (Command $command): void {
                    $command->addArgument('output', InputArgument::OPTIONAL, "One of 'CLI' or 'room'", 'CLI');
                }
            ),
            new CliCommandDefinition(
                'service:bcc_tro_fetch:once',
                'Bristolian\CliController\BccTroFetcherCliController::fetchTros',
                'Fetch and display Bristol City Council Traffic Regulation Orders',
                static function (Command $command): void {
                    $command->addArgument('output', InputArgument::OPTIONAL, "One of 'CLI' or 'room'", 'CLI');
                }
            ),
        ];
    }

    /**
     * @return list<CliCommandDefinition>
     */
    public static function getWhatDoTheyKnowCommandDefinitions(): array
    {
        return [
            new CliCommandDefinition(
                'service:whatdotheyknow_requested:once',
                'Bristolian\CliController\WhatDoTheyKnowFeedCliController::syncRequestedFromBristolOnce',
                'Fetch WhatDoTheyKnow Bristol feed, store new events, notify FOI advice room'
            ),
            new CliCommandDefinition(
                'service:whatdotheyknow_requested:continual',
                'Bristolian\CliController\WhatDoTheyKnowFeedCliController::syncRequestedFromBristolContinual',
                'Poll WhatDoTheyKnow Bristol feed on an interval (supervisord)'
            ),
        ];
    }
}
