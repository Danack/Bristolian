<?php

declare(strict_types=1);

namespace BristolianTest\Service\ExplorerData;

use Bristolian\Service\ExplorerData\SupervisordTasksEntryTypeFinder;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class SupervisordTasksEntryTypeFinderTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\ExplorerData\SupervisordTasksEntryTypeFinder::getEntryTypeKey
     * @covers \Bristolian\Service\ExplorerData\SupervisordTasksEntryTypeFinder::findEntries
     * @covers \Bristolian\Service\ExplorerData\SupervisordTasksEntryTypeFinder::extractCliCommandNameFromSupervisordCommand
     */
    public function test_findEntries_includes_controller_for_each_task(): void
    {
        $finder = new SupervisordTasksEntryTypeFinder();

        $this->assertSame('supervisord_tasks', $finder->getEntryTypeKey());

        $entries = $finder->findEntries();

        $this->assertCount(5, $entries);

        $programNames = array_column($entries, 'program_name');
        $this->assertContains('meme_ocr', $programNames);
        $this->assertContains('whatdotheyknow_requested', $programNames);

        $dailyBccTro = null;
        foreach ($entries as $entry) {
            if ($entry['program_name'] === 'daily_bcc_tro') {
                $dailyBccTro = $entry;
                break;
            }
        }

        $this->assertIsArray($dailyBccTro);
        $this->assertSame(['command', 'controller', 'program_name', 'src_file'], array_keys($dailyBccTro));
        $this->assertSame('daily_bcc_tro', $dailyBccTro['program_name']);
        $this->assertSame(
            'php cli.php service:bcc_tro_fetch:continual',
            $dailyBccTro['command']
        );
        $this->assertSame(
            'Bristolian\\CliController\\BccTroFetcherCliController::daily_bcc_tro',
            $dailyBccTro['controller']
        );
        $this->assertSame(
            'containers/supervisord/tasks/php_daily_bcc_tro.conf',
            $dailyBccTro['src_file']
        );

        $whatDoTheyKnow = null;
        foreach ($entries as $entry) {
            if ($entry['program_name'] === 'whatdotheyknow_requested') {
                $whatDoTheyKnow = $entry;
                break;
            }
        }

        $this->assertIsArray($whatDoTheyKnow);
        $this->assertSame(
            'Bristolian\\CliController\\WhatDoTheyKnowFeedCliController::syncRequestedFromBristolContinual',
            $whatDoTheyKnow['controller']
        );
    }
}
