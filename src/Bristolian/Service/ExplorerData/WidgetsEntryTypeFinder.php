<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

use Bristolian\Widget\WidgetApiCall;
use Bristolian\Widget\WidgetApiCallValidator;
use Bristolian\Widget\WidgetRegistry;

class WidgetsEntryTypeFinder implements EntryTypeFinder
{
    private const TSX_BASE_DIRECTORY = 'app/public/tsx';

    public function getEntryTypeKey(): string
    {
        return 'widgets';
    }

    public function findEntries(): array
    {
        $definitions = WidgetRegistry::getAllDefinitions();

        require_once dirname(__DIR__, 4) . '/api/src/api_routes.php';
        WidgetApiCallValidator::validateDefinitionsAgainstApiRoutes(
            $definitions,
            getAllApiRoutes()
        );

        $entries = [];

        foreach ($definitions as $definition) {
            $entries[] = [
                'name' => $definition->cssClass,
                'component' => $definition->exportName,
                'source' => self::resolveSourcePath($definition->modulePath),
                'api_calls' => array_map(
                    static fn (WidgetApiCall $call): array => [
                        'method' => $call->method,
                        'path' => $call->path,
                    ],
                    $definition->apiCalls,
                ),
            ];
        }

        return $entries;
    }

    private static function resolveSourcePath(string $modulePath): string
    {
        $projectRoot = dirname(__DIR__, 4);
        $normalizedModulePath = preg_replace('#^\./#', '', $modulePath);
        if (is_string($normalizedModulePath) === false) {
            $normalizedModulePath = $modulePath;
        }

        $relativeWithoutExtension = self::TSX_BASE_DIRECTORY . '/' . $normalizedModulePath;

        foreach (['.tsx', '.ts'] as $extension) {
            $candidateRelativePath = $relativeWithoutExtension . $extension;
            if (is_file($projectRoot . '/' . $candidateRelativePath) === true) {
                return $candidateRelativePath;
            }
        }

        return $relativeWithoutExtension . '.tsx';
    }
}
