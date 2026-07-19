<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

class ExplorerDataBuilder
{
    /**
     * Category buttons. Order = UI order. Paths without a leading slash map to top-level JSON keys.
     *
     * @var list<array{name: string, path: string}>
     */
    private const ROOT_ENTRIES = [
        [
            'name' => 'CLI commands',
            'path' => '/cli_commands',
        ],
        [
            'name' => 'Supervisor commands',
            'path' => '/supervisord_tasks',
        ],
        [
            'name' => 'Features',
            'path' => '/features',
        ],
        [
            'name' => 'API endpoints',
            'path' => '/api_endpoints',
        ],
        [
            'name' => 'HTTP endpoints',
            'path' => '/http_endpoints',
        ],
        [
            'name' => 'Widgets',
            'path' => '/widgets',
        ],
        [
            'name' => 'Dependencies',
            'path' => '/dependencies',
        ],
        [
            'name' => 'Data Sources',
            'path' => '/datasources',
        ],
        [
            'name' => 'Generated artifacts',
            'path' => '/generated_artifacts',
        ],
    ];

    /**
     * Documentation for CodeView (extension / agent). Not a navigable category list.
     * Explains each `root` path and important non-root index keys so new layers can be reasoned about.
     *
     * @var list<array{path: string, role: string, description: string, item_shape?: string, drill_down?: string}>
     */
    private const ROOT_EXPLANATIONS = [
        [
            'path' => '/cli_commands',
            'role' => 'entry',
            'description' => 'Runnable PHP CLI commands registered with cli.php. Primary entry layer for batch/admin tools invoked by hand or scripts.',
            'item_shape' => 'command, controller (Class::method), description',
            'drill_down' => 'item.controller → controllers / code-map → dependencies → datasources',
        ],
        [
            'path' => '/supervisord_tasks',
            'role' => 'entry',
            'description' => 'Long-running / scheduled supervisor programs (containers/supervisord). Daemon or cron-like process entry points, usually wrapping a CLI command.',
            'item_shape' => 'program_name, command, controller, src_file',
            'drill_down' => 'Same as CLI: controller → deps → datasources',
        ],
        [
            'path' => '/features',
            'role' => 'grouping',
            'description' => 'Optional product-feature groupings for display. May be empty; UI does not drill into features until the extension adds support.',
            'item_shape' => 'Product-oriented labels linking related entry points (when present)',
            'drill_down' => 'Not navigable yet',
        ],
        [
            'path' => '/api_endpoints',
            'role' => 'entry',
            'description' => 'Machine/JSON HTTP routes (api/src/api_routes.php). Distinct from browser HTML pages.',
            'item_shape' => 'method, path, controller, optional return_types / response_mappers',
            'drill_down' => 'controller → deps → datasources; widgets.api_calls join here by method+path',
        ],
        [
            'path' => '/http_endpoints',
            'role' => 'entry',
            'description' => 'Browser/HTML page routes (app/src/app_routes.php). User-facing pages, not JSON APIs.',
            'item_shape' => 'method, path, controller, optional return_types / response_mappers',
            'drill_down' => 'controller → deps → datasources; future web_pages may compose widgets on these routes',
        ],
        [
            'path' => '/widgets',
            'role' => 'ui',
            'description' => 'Frontend widgety panels registered in WidgetRegistry (generated into app/public/tsx/generated/widget_panels.tsx). CSS class mounts React/Preact components.',
            'item_shape' => 'name (css class), component, source (tsx path), api_calls[{method,path}]',
            'drill_down' => 'api_calls → api_endpoints by method+path; then into controllers / deps as usual',
        ],
        [
            'path' => '/dependencies',
            'role' => 'dependency',
            'description' => 'Repo interfaces (with table read/write edges) and Service contracts with their implementations. Start here to ask which controllers/entries use a dependency and which stores a repo touches.',
            'item_shape' => 'name, implementations[], methods[{name, datasources[{path,reads,writes}]}] (methods/datasources often empty for services)',
            'drill_down' => 'Forward: methods[].datasources[].path → /datasources/{name} when present. Reverse: controllers that list this dep → entry items (CLI / supervisord / API / HTTP)',
        ],
        [
            'path' => '/datasources',
            'role' => 'store',
            'description' => 'Catalog of stores (DB tables today; may later include redis/s3). Reverse exploration start: who reads/writes this store?',
            'item_shape' => 'name, type (e.g. database)',
            'drill_down' => 'datasource ← dependencies (repos) ← controllers ← entry items (CLI / supervisord / API / HTTP)',
        ],
        [
            'path' => '/generated_artifacts',
            'role' => 'codegen',
            'description' => 'One entry per auto-generation process (not per emitted file). Each entry names the output file or directory, the CLI command to regenerate, and generator_callable (Class::method) for open-in-editor / code-map. description comes from that method\'s docblock. Optional detail holds opaque raw PHP source (do not mine it for FQCNs). When detail is taken from code, detail_source is the clickable open-in-editor target (file + line-start/line-end). Human headers in generated files still say how to regenerate (bounce docker boxes or php cli.php generate:…).',
            'item_shape' => 'name/output_file, generator (CLI), generator_callable, description, optional detail, optional detail_source{file,line-start,line-end}',
            'drill_down' => 'generator_callable → code-map / open GenerateFiles method; detail_source → open the exact PHP span that produced detail (when present); detail is display-only text',
        ],
        [
            'path' => 'controllers',
            'role' => 'index',
            'description' => 'Not a root category button. Class-level index of handler classes and their injected dependency type names. Used when drilling from an entry item.',
            'item_shape' => 'name (FQCN), dependencies[]',
            'drill_down' => 'dependencies[] → /dependencies entries',
        ],
        [
            'path' => 'code-map',
            'role' => 'index',
            'description' => 'Not a root category button. Jump-to-source index for Class::method (file, line-start, line-end). Entries and controllers should resolve here for open-in-editor.',
            'item_shape' => 'name, file, line-start, line-end, optional dependencies',
            'drill_down' => 'Navigation aid only; deps primarily come from controllers',
        ],
    ];

    /**
     * @var array<string, mixed>
     */
    private array $explorerData = [];

    public function __construct(
        private readonly string $outputPath,
    ) {
    }

    public function getOutputPath(): string
    {
        return $this->outputPath;
    }

    public function addFromEntryTypeFinder(EntryTypeFinder $entryTypeFinder): void
    {
        $this->explorerData[$entryTypeFinder->getEntryTypeKey()] = $entryTypeFinder->findEntries();
    }

    public function execute(): void
    {
        $dataDirectory = dirname($this->outputPath);

        if (is_dir($dataDirectory) === false) {
            mkdir($dataDirectory, 0755, true);
        }

        $jsonContent = $this->buildJsonContent();
        $bytesWritten = file_put_contents($this->outputPath, $jsonContent);

        if ($bytesWritten === false) {
            throw new \RuntimeException(
                'Failed to write explorer data to ' . $this->outputPath
            );
        }
    }

    private function buildJsonContent(): string
    {
        $output = [
            'root' => self::ROOT_ENTRIES,
            // Metadata for the CodeView extension / agents — not a navigable category.
            'root_explanations' => self::ROOT_EXPLANATIONS,
        ];

        foreach ($this->explorerData as $key => $value) {
            $output[$key] = $value;
        }

        $encoded = json_encode(
            $output,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        if ($encoded === false) {
            throw new \RuntimeException('Failed to encode explorer data as JSON.');
        }

        return $encoded . "\n";
    }
}
