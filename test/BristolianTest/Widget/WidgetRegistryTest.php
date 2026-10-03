<?php

declare(strict_types=1);

namespace BristolianTest\Widget;

use Bristolian\Widget\WidgetApiCall;
use Bristolian\Widget\WidgetDefinition;
use Bristolian\Widget\WidgetRegistry;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WidgetApiCall::class)]
#[CoversClass(WidgetDefinition::class)]
#[CoversClass(WidgetRegistry::class)]
class WidgetRegistryTest extends BaseTestCase
{
    public function test_getAllDefinitions_returns_non_empty_list_of_definitions(): void
    {
        $definitions = WidgetRegistry::getAllDefinitions();

        $this->assertNotEmpty($definitions);
        $this->assertContainsOnlyInstancesOf(WidgetDefinition::class, $definitions);

        $first = $definitions[0];
        $this->assertNotSame('', $first->cssClass);
        $this->assertNotSame('', $first->exportName);
        $this->assertNotSame('', $first->modulePath);
        $this->assertContainsOnlyInstancesOf(WidgetApiCall::class, $first->apiCalls);

        if (count($first->apiCalls) > 0) {
            $apiCall = $first->apiCalls[0];
            $this->assertSame($apiCall->method . ' ' . $apiCall->path, $apiCall->routeKey());
        }
    }
}
