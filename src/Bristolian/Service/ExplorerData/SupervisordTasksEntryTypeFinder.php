<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

use Bristolian\Cli\CliCommandRegistry;
use Bristolian\Parameters\SupervisordProgramParams;

class SupervisordTasksEntryTypeFinder implements EntryTypeFinder
{
    private const TASKS_DIRECTORY = 'containers/supervisord/tasks';

    /**
     * Supervisord program configs shipped with the project (under {@see TASKS_DIRECTORY}).
     *
     * @var list<string>
     */
    private const TASK_CONFIG_FILES = [
        'php_daily_bcc_tro.conf',
        'php_daily_system_info.conf',
        'php_meme_ocr.conf',
        'php_queue_email_send.conf',
        'php_whatdotheyknow_requested.conf',
    ];

    public function getEntryTypeKey(): string
    {
        return 'supervisord_tasks';
    }

    public function findEntries(): array
    {
        $tasksDirectoryPath = dirname(__DIR__, 4) . '/' . self::TASKS_DIRECTORY;

        if (is_dir($tasksDirectoryPath) === false) {
            throw new \RuntimeException(
                'Supervisord tasks directory not found: ' . $tasksDirectoryPath
            );
        }

        $entries = [];

        foreach (self::TASK_CONFIG_FILES as $configFileName) {
            $configPath = $tasksDirectoryPath . '/' . $configFileName;
            $parsedValues = \parseSupervisordProgramConfigFile($configPath);
            $programParams = SupervisordProgramParams::createFromArray($parsedValues);

            $cliCommandName = self::extractCliCommandNameFromSupervisordCommand($programParams->command);
            $controller = $cliCommandName === null
                ? null
                : CliCommandRegistry::getControllerCallableForCommandName($cliCommandName);

            if ($controller !== null) {
                $controller = ControllerCallableCodeLocator::normalizeFqcn($controller);
            }

            $entries[] = [
                'command' => $programParams->command,
                'controller' => $controller,
                'program_name' => $programParams->program_name,
                'src_file' => self::TASKS_DIRECTORY . '/' . $configFileName,
            ];
        }

        return $entries;
    }

    private static function extractCliCommandNameFromSupervisordCommand(string $supervisordCommand): ?string
    {
        if (preg_match('/php cli\.php\s+(\S+)/', $supervisordCommand, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }
}
