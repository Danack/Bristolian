<?php

declare(strict_types=1);

namespace BristolianTest\Service\ExplorerData;

use Bristolian\Service\ExplorerData\ApiEndpointsEntryTypeFinder;
use Bristolian\Service\ExplorerData\HttpEndpointsEntryTypeFinder;
use Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder;
use BristolianTest\BaseTestCase;
use SlimDispatcher\Response\RedirectResponse;

/**
 * @coversNothing
 */
class RouteEndpointsEntryTypeFinderTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\ExplorerData\HttpEndpointsEntryTypeFinder::getEntryTypeKey
     * @covers \Bristolian\Service\ExplorerData\HttpEndpointsEntryTypeFinder::findEntries
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::getAppResultMappers
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::buildEntriesFromRoutes
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::resolveReturnTypes
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::resolveResultMapper
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::typeNamesFromReflectionType
     * @covers \Bristolian\Service\ExplorerData\ControllerCallableCodeLocator::parseControllerCallable
     */
    public function test_http_endpoints_include_homepage_with_string_mapper(): void
    {
        $finder = new HttpEndpointsEntryTypeFinder();

        $this->assertSame('http_endpoints', $finder->getEntryTypeKey());

        $entries = $finder->findEntries();
        $this->assertGreaterThan(20, count($entries));

        $homepage = null;
        foreach ($entries as $entry) {
            if ($entry['path'] === '/' && $entry['method'] === 'GET') {
                $homepage = $entry;
                break;
            }
        }

        $this->assertIsArray($homepage);
        $this->assertSame('Bristolian\\AppController\\Pages::index', $homepage['controller']);
        $this->assertSame(['string'], $homepage['return_types']);
        $this->assertSame(
            [
                [
                    'return_type' => 'string',
                    'mapper' => 'Bristolian\\StringToHtmlPageConverter::convertStringToHtmlResponse',
                ],
            ],
            $homepage['response_mappers']
        );
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\HttpEndpointsEntryTypeFinder::findEntries
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::getAppResultMappers
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::buildEntriesFromRoutes
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::resolveReturnTypes
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::resolveResultMapper
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::typeNamesFromReflectionType
     * @covers \Bristolian\Service\ExplorerData\ControllerCallableCodeLocator::parseControllerCallable
     */
    public function test_http_endpoints_map_union_return_types(): void
    {
        $finder = new HttpEndpointsEntryTypeFinder();
        $entries = $finder->findEntries();

        $loginGet = null;
        foreach ($entries as $entry) {
            if ($entry['path'] === '/login' && $entry['method'] === 'GET') {
                $loginGet = $entry;
                break;
            }
        }

        $this->assertIsArray($loginGet);
        $this->assertContains('string', $loginGet['return_types']);
        $this->assertContains(RedirectResponse::class, $loginGet['return_types']);
        $this->assertGreaterThanOrEqual(2, count($loginGet['response_mappers']));
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\ApiEndpointsEntryTypeFinder::getEntryTypeKey
     * @covers \Bristolian\Service\ExplorerData\ApiEndpointsEntryTypeFinder::findEntries
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::getApiResultMappers
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::buildEntriesFromRoutes
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::resolveReturnTypes
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::resolveResultMapper
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::typeNamesFromReflectionType
     * @covers \Bristolian\Service\ExplorerData\ControllerCallableCodeLocator::parseControllerCallable
     */
    public function test_api_endpoints_include_bristol_stairs_get_data(): void
    {
        $finder = new ApiEndpointsEntryTypeFinder();

        $this->assertSame('api_endpoints', $finder->getEntryTypeKey());

        $entries = $finder->findEntries();
        $this->assertGreaterThan(20, count($entries));

        $stairsData = null;
        foreach ($entries as $entry) {
            if ($entry['path'] === '/api/bristol_stairs' && $entry['method'] === 'GET') {
                $stairsData = $entry;
                break;
            }
        }

        $this->assertIsArray($stairsData);
        $this->assertSame(
            'Bristolian\\AppController\\BristolStairs::getData',
            $stairsData['controller']
        );
        $this->assertNotEmpty($stairsData['return_types']);
        $this->assertNotEmpty($stairsData['response_mappers']);
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::getAppResultMappers
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::getApiResultMappers
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::resolveResultMapper
     */
    public function test_resolveResultMapper_matches_stub_response_subclasses(): void
    {
        $appMappers = RouteEndpointEntryBuilder::getAppResultMappers();
        $apiMappers = RouteEndpointEntryBuilder::getApiResultMappers();

        $this->assertSame(
            'SlimDispatcher\\mapStubResponseToPsr7',
            RouteEndpointEntryBuilder::resolveResultMapper(RedirectResponse::class, $appMappers)
        );
        $this->assertSame(
            'SlimDispatcher\\mapStubResponseToPsr7',
            RouteEndpointEntryBuilder::resolveResultMapper(RedirectResponse::class, $apiMappers)
        );
        $this->assertSame('convertStringToResponse', $apiMappers['string']);
        $this->assertSame(
            'SlimDispatcher\\passThroughResponse',
            $apiMappers[\Psr\Http\Message\ResponseInterface::class]
        );
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::buildEntriesFromRoutes
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::resolveReturnTypes
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::resolveResultMapper
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::getAppResultMappers
     * @covers \Bristolian\Service\ExplorerData\ControllerCallableCodeLocator::normalizeFqcn
     * @covers \Bristolian\Service\ExplorerData\ControllerCallableCodeLocator::parseControllerCallable
     */
    public function test_buildEntriesFromRoutes_skips_invalid_rows_and_unknown_return_types(): void
    {
        $entries = RouteEndpointEntryBuilder::buildEntriesFromRoutes(
            [
                ['/too-short'],
                [123, 'GET', 'Bristolian\\CliController\\Debug::hello'],
                ['/ok', 'GET', 'Bristolian\\CliController\\Debug::hello'],
                ['/missing', 'GET', 'Bristolian\\DoesNotExist\\Nowhere::missing'],
            ],
            RouteEndpointEntryBuilder::getAppResultMappers()
        );

        $this->assertCount(2, $entries);
        $this->assertSame('/ok', $entries[0]['path']);
        $this->assertSame('Bristolian\\CliController\\Debug::hello', $entries[0]['controller']);
        $this->assertSame('/missing', $entries[1]['path']);
        $this->assertSame([], $entries[1]['return_types']);
        $this->assertSame([], $entries[1]['response_mappers']);
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::resolveResultMapper
     * @covers \Bristolian\Service\ExplorerData\RouteEndpointEntryBuilder::getAppResultMappers
     */
    public function test_resolveResultMapper_returns_null_when_no_mapper_matches(): void
    {
        $this->assertNull(
            RouteEndpointEntryBuilder::resolveResultMapper(
                'Some\\Unknown\\Type',
                RouteEndpointEntryBuilder::getAppResultMappers()
            )
        );
    }
}
