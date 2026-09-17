<?php

declare(strict_types=1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\SupervisordProgramParams;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Parameters\SupervisordProgramParams::class)]

class SupervisordProgramParamsTest extends BaseTestCase
{
    public function test_createFromArray_parses_whatdotheyknow_requested_fixture(): void
    {
        $configPath = dirname(__DIR__, 3)
            . '/containers/supervisord/tasks/php_whatdotheyknow_requested.conf';

        $parsedValues = \parseSupervisordProgramConfigFile($configPath);
        $params = SupervisordProgramParams::createFromArray($parsedValues);

        $this->assertSame('whatdotheyknow_requested', $params->program_name);
        $this->assertSame(
            'php cli.php service:whatdotheyknow_requested:continual',
            $params->command
        );
    }

    public function test_toExplorerArray_returns_only_program_name_and_command(): void
    {
        $configPath = dirname(__DIR__, 3)
            . '/containers/supervisord/tasks/php_meme_ocr.conf';

        $params = SupervisordProgramParams::createFromArray(
            \parseSupervisordProgramConfigFile($configPath)
        );

        $array = $params->toExplorerArray();

        $this->assertSame(['program_name', 'command'], array_keys($array));
        $this->assertSame('meme_ocr', $array['program_name']);
        $this->assertSame('php cli.php process:meme_ocr', $array['command']);
    }
}
