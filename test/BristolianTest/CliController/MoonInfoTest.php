<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\CliController\MoonInfo;
use Bristolian\Repo\ProcessorRepo\FakeProcessorRepo;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Service\MoonAlertNotifier\MoonAlertNotifier;
use BristolianTest\BaseTestCase;
use function Bristolian\CliController\getMoonInfo;
use function Bristolian\CliController\isTimeToProcessMoonInfo;

/**
 * MoonAlertNotifier that records the last moon info string passed for assertions.
 */
final class MoonInfoTestNotifier implements MoonAlertNotifier
{
    public ?string $lastMoonInfo = null;

    public function notifyRegisteredUsers(string $mooninfo): void
    {
        $this->lastMoonInfo = $mooninfo;
    }
}

/**
 * @coversNothing
 */
class MoonInfoTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        class_exists(MoonInfo::class); // load file containing getMoonInfo, isTimeToProcessMoonInfo
    }

    /**
     * @covers \Bristolian\CliController\MoonInfo
     */
    public function test_getMoonInfo_returns_string_with_expected_sections(): void
    {
        $result = getMoonInfo();
        $this->assertIsString($result);
        $this->assertStringContainsString('visible fraction', $result);
        $this->assertStringContainsString('moon rise', $result);
        $this->assertStringContainsString('sunset', $result);
    }

    /**
     * @covers \Bristolian\CliController\MoonInfo
     */
    public function test_isTimeToProcessMoonInfo_returns_boolean(): void
    {
        $this->assertIsBool(isTimeToProcessMoonInfo());
    }

    /**
     * @covers \Bristolian\CliController\MoonInfo::__construct
     * @covers \Bristolian\CliController\MoonInfo::info
     */
    public function test_info_outputs_and_returns_early_when_processor_disabled_or_not_time(): void
    {
        $notifier = new MoonInfoTestNotifier();
        $processorRepo = new FakeProcessorRepo();
        $processorRepo->setProcessorEnabled(ProcessType::moon_alert, false);

        $moonInfo = new MoonInfo($notifier, $processorRepo);
        ob_start();
        $moonInfo->info();
        $output = ob_get_clean();

        $this->assertStringContainsString('Run internal.', $output);
        // Either "Not time" or "not enabled" depending on current hour
        $this->assertTrue(
            str_contains($output, 'Not time to process moon info') || str_contains($output, 'not enabled'),
            'Output should indicate skip reason: ' . $output
        );
        $this->assertNull($notifier->lastMoonInfo, 'Notifier should not be called when skipping');
    }
}
