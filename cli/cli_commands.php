<?php

use Bristolian\Cli\CliCommandRegistry;
use Danack\Console\Application;

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
    addWhatDoTheyKnowCommands($console);
}

function addEmailCommands(Application $console)
{
    CliCommandRegistry::registerCommands($console, CliCommandRegistry::getEmailCommandDefinitions());
}

/**
 * @param Application $console
 */
function addDebugCommands(Application $console)
{
    CliCommandRegistry::registerCommands($console, CliCommandRegistry::getDebugCommandDefinitions());
}

function addSeedCommands(Application $console)
{
    CliCommandRegistry::registerCommands($console, CliCommandRegistry::getSeedCommandDefinitions());
}

function addDatabaseCommands(Application $console)
{
    CliCommandRegistry::registerCommands($console, CliCommandRegistry::getDatabaseCommandDefinitions());
}

function addMiscCommands(Application $console)
{
    CliCommandRegistry::registerCommands($console, CliCommandRegistry::getMiscCommandDefinitions());
}

function addGenerateCommands(Application $console)
{
    CliCommandRegistry::registerCommands($console, CliCommandRegistry::getGenerateCommandDefinitions());
}

function addMoonCommands(Application $console)
{
    CliCommandRegistry::registerCommands($console, CliCommandRegistry::getMoonCommandDefinitions());
}

function addTestCommands(Application $console)
{
    CliCommandRegistry::registerCommands($console, CliCommandRegistry::getTestCommandDefinitions());
}

function addAdminAccountCommands(Application $console)
{
    CliCommandRegistry::registerCommands($console, CliCommandRegistry::getAdminAccountCommandDefinitions());
}

function addRoomCommands(Application $console)
{
    CliCommandRegistry::registerCommands($console, CliCommandRegistry::getRoomCommandDefinitions());
}

function addBristolStairsCommands(Application $console)
{
    CliCommandRegistry::registerCommands($console, CliCommandRegistry::getBristolStairsCommandDefinitions());
}

function addMemeCommands(Application $console)
{
    CliCommandRegistry::registerCommands($console, CliCommandRegistry::getMemeCommandDefinitions());
}

function addOpenApiCommands(Application $console)
{
    CliCommandRegistry::registerCommands($console, CliCommandRegistry::getOpenApiCommandDefinitions());
}

function addBccTroCommands(Application $console)
{
    CliCommandRegistry::registerCommands($console, CliCommandRegistry::getBccTroCommandDefinitions());
}

function addWhatDoTheyKnowCommands(Application $console): void
{
    CliCommandRegistry::registerCommands($console, CliCommandRegistry::getWhatDoTheyKnowCommandDefinitions());
}
