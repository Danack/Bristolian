<?php

declare(strict_types=1);

/**
 * Parse a Supervisord program config file. Explorer output uses
 * program_name and command via SupervisordProgramParams.
 *
 * @return array<string, string>
 */
function parseSupervisordProgramConfigFile(string $filePath): array
{
    $content = file_get_contents($filePath);

    if ($content === false) {
        throw new \InvalidArgumentException('Could not read Supervisord config file: ' . $filePath);
    }

    $programName = null;
    $values = [];

    foreach (explode("\n", $content) as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, ';')) {
            continue;
        }

        if (preg_match('/^\[program:(.+)\]$/', $line, $matches) === 1) {
            $programName = $matches[1];
            continue;
        }

        $semicolonPosition = strpos($line, ';');
        if ($semicolonPosition !== false) {
            $line = trim(substr($line, 0, $semicolonPosition));
        }

        if ($line === '') {
            continue;
        }

        $equalsPosition = strpos($line, '=');
        if ($equalsPosition === false) {
            continue;
        }

        $key = trim(substr($line, 0, $equalsPosition));
        $value = trim(substr($line, $equalsPosition + 1));
        $values[$key] = $value;
    }

    if ($programName === null) {
        throw new \InvalidArgumentException(
            'No [program:...] section in Supervisord config file: ' . $filePath
        );
    }

    $values['program_name'] = $programName;

    return $values;
}
