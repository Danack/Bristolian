<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\CliController\GenerateFiles;
use BristolianTest\BaseTestCase;
use function Safe\file_get_contents;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class GenerateWidgetPanelsTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\CliController\GenerateFiles::generateWidgetPanels
     * @covers \Bristolian\CliController\GenerateFiles::generateWidgetApiCallsBreadcrumbBlock
     */
    public function test_generateWidgetPanels_writes_panels_and_api_calls(): void
    {
        $generator = new GenerateFiles();
        $generator->generateWidgetPanels();

        $outputPath = __DIR__ . '/../../../app/public/tsx/generated/widget_panels.tsx';
        $this->assertFileExists($outputPath);

        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('export const panels: WidgetClassBinding[] = [', $content);
        $this->assertStringContainsString('class: "bristol_stairs_panel"', $content);
        $this->assertStringContainsString('import { BristolStairsPanel } from "../BristolStairsPanel";', $content);
        $this->assertStringContainsString('export const WIDGET_API_CALLS:', $content);
        $this->assertStringContainsString('"bristol_stairs_panel": [', $content);
        $this->assertStringContainsString(
            '{ method: "GET", path: "/api/bristol_stairs_openmap_nearby" }',
            $content
        );
    }
}
