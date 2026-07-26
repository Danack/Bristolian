<?php

declare(strict_types=1);

namespace BristolianTest\Service\ExplorerData;

use Bristolian\Exception\BristolianException;
use Bristolian\Service\ExplorerData\WidgetsEntryTypeFinder;
use Bristolian\Widget\WidgetApiCall;
use Bristolian\Widget\WidgetApiCallValidator;
use Bristolian\Widget\WidgetDefinition;
use Bristolian\Widget\WidgetRegistry;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class WidgetsEntryTypeFinderTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\ExplorerData\WidgetsEntryTypeFinder::getEntryTypeKey
     * @covers \Bristolian\Service\ExplorerData\WidgetsEntryTypeFinder::findEntries
     * @covers \Bristolian\Service\ExplorerData\WidgetsEntryTypeFinder::resolveSourcePath
     */
    public function test_findEntries_lists_registry_widgets_with_api_calls(): void
    {
        $finder = new WidgetsEntryTypeFinder();

        $this->assertSame('widgets', $finder->getEntryTypeKey());

        $entries = $finder->findEntries();
        $this->assertCount(count(WidgetRegistry::getAllDefinitions()), $entries);

        $names = array_column($entries, 'name');
        $this->assertSame(count($names), count(array_unique($names)));
        $this->assertContains('bristol_stairs_panel', $names);
        $this->assertContains('notification_test_panel', $names);
        $this->assertNotContains('admin_email_panel', $names);

        $stairsEntry = null;
        foreach ($entries as $entry) {
            if ($entry['name'] === 'bristol_stairs_panel') {
                $stairsEntry = $entry;
                break;
            }
        }

        $this->assertIsArray($stairsEntry);
        $this->assertSame('BristolStairsPanel', $stairsEntry['component']);
        $this->assertSame('app/public/tsx/BristolStairsPanel.tsx', $stairsEntry['source']);
        $this->assertNotEmpty($stairsEntry['api_calls']);

        $openmapCall = null;
        foreach ($stairsEntry['api_calls'] as $apiCall) {
            if ($apiCall['path'] === '/api/bristol_stairs_openmap_nearby') {
                $openmapCall = $apiCall;
                break;
            }
        }

        $this->assertIsArray($openmapCall);
        $this->assertSame('GET', $openmapCall['method']);
    }

    /**
     * @covers \Bristolian\Widget\WidgetApiCallValidator::validateDefinitionsAgainstApiRoutes
     */
    public function test_validateDefinitionsAgainstApiRoutes_rejects_unknown_route(): void
    {
        $definitions = [
            new WidgetDefinition(
                'example_panel',
                'ExamplePanel',
                './ExamplePanel',
                [
                    new WidgetApiCall('GET', '/api/does-not-exist'),
                ],
            ),
        ];

        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage(
            'WidgetRegistry: api_call not found in api_routes for widget example_panel: GET /api/does-not-exist'
        );

        WidgetApiCallValidator::validateDefinitionsAgainstApiRoutes(
            $definitions,
            [
                ['/api/login-status', 'GET', 'Bristolian\AppController\User::get_login_status', null],
            ]
        );
    }

    /**
     * @covers \Bristolian\Widget\WidgetApiCallValidator::validateDefinitionsAgainstApiRoutes
     */
    public function test_validateDefinitionsAgainstApiRoutes_rejects_duplicate_within_widget(): void
    {
        $definitions = [
            new WidgetDefinition(
                'example_panel',
                'ExamplePanel',
                './ExamplePanel',
                [
                    new WidgetApiCall('GET', '/api/login-status'),
                    new WidgetApiCall('GET', '/api/login-status'),
                ],
            ),
        ];

        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage(
            'WidgetRegistry: duplicate api_call for widget example_panel: GET /api/login-status'
        );

        WidgetApiCallValidator::validateDefinitionsAgainstApiRoutes(
            $definitions,
            [
                ['/api/login-status', 'GET', 'Bristolian\AppController\User::get_login_status', null],
            ]
        );
    }
}
