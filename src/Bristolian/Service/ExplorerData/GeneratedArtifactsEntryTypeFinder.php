<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

/**
 * One CodeView entry per auto-generation process (not per emitted file).
 *
 * generator_callable is the click-through target (code-map / open-in-editor).
 * When detail is taken from PHP source, detail_source points at that span.
 */
class GeneratedArtifactsEntryTypeFinder implements EntryTypeFinder
{
    /**
     * @var list<array{
     *     generator: string,
     *     generator_callable: string,
     *     output_file: string,
     *     detail_variables?: list<string>
     * }>
     */
    private const PROCESSES = [
        [
            'generator' => 'generate:javascript_constants',
            'generator_callable' => 'Bristolian\\CliController\\GenerateFiles::generateJavaScriptConstants',
            'output_file' => 'app/public/tsx/generated/constants.tsx',
            'detail_variables' => ['constantDefinitions'],
        ],
        [
            'generator' => 'generate:javascript_constants',
            'generator_callable' => 'Bristolian\\CliController\\GenerateFiles::generateJavaScriptTypes',
            'output_file' => 'app/public/tsx/generated/types.tsx',
            'detail_variables' => ['types', 'enums'],
        ],
        [
            'generator' => 'generate:typescript_api_routes',
            'generator_callable' => 'Bristolian\\CliController\\GenerateFiles::generateTypeScriptApiRoutes',
            'output_file' => 'app/public/tsx/generated/api_routes.tsx',
        ],
        [
            'generator' => 'generate:widget_panels',
            'generator_callable' => 'Bristolian\\CliController\\GenerateFiles::generateWidgetPanels',
            'output_file' => 'app/public/tsx/generated/widget_panels.tsx',
        ],
        [
            'generator' => 'generate:php_response_types',
            'generator_callable' => 'Bristolian\\CliController\\GenerateFiles::generatePhpResponseTypes',
            'output_file' => 'src/Bristolian/Response/Typed/',
        ],
        [
            'generator' => 'generate:php_table_helper_classes',
            'generator_callable' => 'Bristolian\\CliController\\GenerateFiles::generateTableHelperClasses',
            'output_file' => 'src/Bristolian/Database/',
        ],
        [
            'generator' => 'generate:model_classes',
            'generator_callable' => 'Bristolian\\CliController\\GenerateFiles::generateModelClasses',
            'output_file' => 'src/Bristolian/Model/Generated/',
        ],
    ];

    public function getEntryTypeKey(): string
    {
        return 'generated_artifacts';
    }

    public function findEntries(): array
    {
        $entries = [];

        foreach (self::PROCESSES as $process) {
            $detail = null;
            $detailSource = null;

            if (
                array_key_exists('detail_variables', $process) === true
                && is_array($process['detail_variables']) === true
            ) {
                $extracted = CodegenProvenance::extractAssignments(
                    $process['generator_callable'],
                    $process['detail_variables']
                );
                $detail = $extracted['detail'];
                $detailSource = $extracted['detail_source'];
            }

            $payload = CodegenProvenance::buildPayload(
                $process['generator'],
                $process['generator_callable'],
                $process['output_file'],
                $detail,
                $detailSource
            );

            $entry = [
                'name' => $payload['output_file'],
                'generator' => $payload['generator'],
                'generator_callable' => $payload['generator_callable'],
                'output_file' => $payload['output_file'],
                'description' => $payload['description'],
            ];

            if (array_key_exists('detail', $payload) === true) {
                $entry['detail'] = $payload['detail'];
            }

            if (array_key_exists('detail_source', $payload) === true) {
                $entry['detail_source'] = $payload['detail_source'];
            }

            $entries[] = $entry;
        }

        return $entries;
    }
}
