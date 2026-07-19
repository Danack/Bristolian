<?php

declare(strict_types=1);

namespace Bristolian\CliController;

use Bristolian\Service\CliOutput\CliOutput;
use Bristolian\Service\ExplorerData\ExplorerDataBuilder;
use Bristolian\Service\ExplorerData\ApiEndpointsEntryTypeFinder;
use Bristolian\Service\ExplorerData\CodeMapEntryTypeFinder;
use Bristolian\Service\ExplorerData\CliCommandsEntryTypeFinder;
use Bristolian\Service\ExplorerData\ControllersEntryTypeFinder;
use Bristolian\Service\ExplorerData\DatasourcesEntryTypeFinder;
use Bristolian\Service\ExplorerData\DependenciesEntryTypeFinder;
use Bristolian\Service\ExplorerData\HttpEndpointsEntryTypeFinder;
use Bristolian\Service\ExplorerData\SupervisordTasksEntryTypeFinder;
use Bristolian\Service\ExplorerData\GeneratedArtifactsEntryTypeFinder;
use Bristolian\Service\ExplorerData\WidgetsEntryTypeFinder;

class GenerateExplorerData
{
    private const CODEVIEW_DATA_RELATIVE_PATH = '/../../../codeview-data.json';

    public function __construct(
        private readonly CliOutput $cliOutput
    ) {
    }

    public function generateExplorerData(): void
    {
        $outputPath = __DIR__ . self::CODEVIEW_DATA_RELATIVE_PATH;

        $builder = new ExplorerDataBuilder($outputPath);
        $builder->addFromEntryTypeFinder(new CliCommandsEntryTypeFinder());
        $builder->addFromEntryTypeFinder(new CodeMapEntryTypeFinder());
        $builder->addFromEntryTypeFinder(new ControllersEntryTypeFinder());
        $builder->addFromEntryTypeFinder(new DatasourcesEntryTypeFinder());
        $builder->addFromEntryTypeFinder(new DependenciesEntryTypeFinder());
        $builder->addFromEntryTypeFinder(new HttpEndpointsEntryTypeFinder());
        $builder->addFromEntryTypeFinder(new ApiEndpointsEntryTypeFinder());
        $builder->addFromEntryTypeFinder(new SupervisordTasksEntryTypeFinder());
        $builder->addFromEntryTypeFinder(new WidgetsEntryTypeFinder());
        $builder->addFromEntryTypeFinder(new GeneratedArtifactsEntryTypeFinder());

        try {
            $builder->execute();
        } catch (\RuntimeException $exception) {
            $this->cliOutput->write($exception->getMessage() . "\n");
            $this->cliOutput->exit(-1);
        }

        $this->cliOutput->write('Wrote codeview data to ' . $builder->getOutputPath() . ".\n");
    }
}
