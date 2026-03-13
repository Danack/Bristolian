<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\CliController\BccTroFetcherCliController;
use Bristolian\Model\Types\BccTro;
use Bristolian\Model\Types\BccTroDocument;
use Bristolian\Repo\BccTroRepo\BccTroRepo;
use Bristolian\Repo\ProcessorRunRecordRepo\FakeProcessorRunRecordRepo;
use Bristolian\Service\BccTroFetcher\BccTroFetcher;
use Bristolian\Service\CliOutput\CapturingCliOutput;
use Bristolian\Service\CliOutput\CliExitRequestedException;
use Bristolian\Service\DailyProcessorSchedule\FakeDailyProcessorSchedule;
use BristolianTest\BaseTestCase;
use function Bristolian\CliController\output_tro_list_to_output;

/**
 * BccTroRepo that records the last array passed to saveData for assertions.
 *
 * @coversNothing
 */
final class BccTroFetcherTestBccTroRepo implements BccTroRepo
{
    /** @var BccTro[]|null */
    public ?array $lastSavedTros = null;

    public function saveData(array $tros): void
    {
        $this->lastSavedTros = $tros;
    }
}

/**
 * BccTroFetcher that returns a fixed list of TROs for testing.
 *
 * @coversNothing
 */
final class BccTroFetcherReturningFixedTros implements BccTroFetcher
{
    /** @param BccTro[] $tros */
    public function __construct(private array $tros)
    {
    }

    public function fetchTros(): array
    {
        return $this->tros;
    }
}

/**
 * @coversNothing
 */
final class BccTroFetcherThatThrows implements BccTroFetcher
{
    public function __construct(private \Throwable $throwable)
    {
    }

    public function fetchTros(): array
    {
        throw $this->throwable;
    }
}

/**
 * @coversNothing
 */
class BccTroFetcherCliControllerTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        class_exists(BccTroFetcherCliController::class);
    }

    /**
     * @covers \Bristolian\CliController\output_tro_list_to_output
     */
    public function test_output_tro_list_to_output_empty_echoes_no_tros_found(): void
    {
        ob_start();
        output_tro_list_to_output([]);
        $output = ob_get_clean();
        $this->assertStringContainsString('No TROs found.', $output);
    }

    /**
     * @covers \Bristolian\CliController\output_tro_list_to_output
     */
    public function test_output_tro_list_to_output_with_one_tro_echoes_title_and_reference(): void
    {
        $doc = new BccTroDocument('', '', '');
        $tro = new BccTro('Test TRO Title', 'REF-001', $doc, $doc, $doc);
        ob_start();
        output_tro_list_to_output([$tro]);
        $output = ob_get_clean();
        $this->assertStringContainsString('Found 1 TRO(s):', $output);
        $this->assertStringContainsString('Title: Test TRO Title', $output);
        $this->assertStringContainsString('Reference: REF-001', $output);
        $this->assertStringContainsString('---', $output);
    }

    /**
     * @covers \Bristolian\CliController\output_tro_list_to_output
     */
    public function test_output_tro_list_to_output_echoes_statement_of_reasons_when_non_empty(): void
    {
        $statement = new BccTroDocument('Reasons doc', 'https://example.com/reasons', 'id1');
        $other = new BccTroDocument('', '', '');
        $tro = new BccTro('Title', 'REF', $statement, $other, $other);
        ob_start();
        output_tro_list_to_output([$tro]);
        $output = ob_get_clean();
        $this->assertStringContainsString('Statement of Reasons: Reasons doc', $output);
        $this->assertStringContainsString('Link: https://example.com/reasons', $output);
    }

    /**
     * @covers \Bristolian\CliController\output_tro_list_to_output
     */
    public function test_output_tro_list_to_output_echoes_notice_of_proposal_when_non_empty(): void
    {
        $proposal = new BccTroDocument('Notice title', 'https://example.com/notice', 'id2');
        $empty = new BccTroDocument('', '', '');
        $tro = new BccTro('T', 'R', $empty, $proposal, $empty);
        ob_start();
        output_tro_list_to_output([$tro]);
        $output = ob_get_clean();
        $this->assertStringContainsString('Notice of Proposal: Notice title', $output);
        $this->assertStringContainsString('Link: https://example.com/notice', $output);
    }

    /**
     * @covers \Bristolian\CliController\output_tro_list_to_output
     */
    public function test_output_tro_list_to_output_echoes_proposed_plan_when_non_empty(): void
    {
        $plan = new BccTroDocument('Plan title', 'https://example.com/plan', 'id3');
        $empty = new BccTroDocument('', '', '');
        $tro = new BccTro('T', 'R', $empty, $empty, $plan);
        ob_start();
        output_tro_list_to_output([$tro]);
        $output = ob_get_clean();
        $this->assertStringContainsString('Proposed Plan: Plan title', $output);
        $this->assertStringContainsString('Link: https://example.com/plan', $output);
    }

    /**
     * @covers \Bristolian\CliController\BccTroFetcherCliController::fetchTros
     */
    public function test_fetchTros_writes_fetching_line_and_saves_fetched_tros_to_repo(): void
    {
        $doc = new BccTroDocument('', '', '');
        $tro = new BccTro('Fetched TRO', 'REF-42', $doc, $doc, $doc);
        $fetcher = new BccTroFetcherReturningFixedTros([$tro]);
        $repo = new BccTroFetcherTestBccTroRepo();
        $cliOutput = new CapturingCliOutput();
        $controller = new BccTroFetcherCliController();

        $controller->fetchTros($fetcher, $repo, $cliOutput, 'CLI');

        $this->assertStringContainsString(
            'Fetching TRO data from Bristol City Council',
            $cliOutput->getCapturedOutput()
        );
        $this->assertNotNull($repo->lastSavedTros);
        $this->assertCount(1, $repo->lastSavedTros);
        $this->assertSame('Fetched TRO', $repo->lastSavedTros[0]->title);
        $this->assertSame('REF-42', $repo->lastSavedTros[0]->reference_code);
    }

    /**
     * @covers \Bristolian\CliController\BccTroFetcherCliController::fetchTros
     */
    public function test_fetchTros_writes_error_and_requests_exit_when_fetch_throws(): void
    {
        $repo = new BccTroFetcherTestBccTroRepo();
        $cliOutput = new CapturingCliOutput();
        $fetcher = new BccTroFetcherThatThrows(new \RuntimeException('network down'));
        $controller = new BccTroFetcherCliController();

        try {
            $controller->fetchTros($fetcher, $repo, $cliOutput, 'CLI');
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $cliExitRequestedException) {
            $this->assertSame(1, $cliExitRequestedException->getExitCode());
        }

        $this->assertStringContainsString('Fetching TRO data from Bristol City Council', $cliOutput->getCapturedOutput());
        $this->assertStringContainsString('Error fetching TRO data: network down', $cliOutput->getCapturedOutput());
        $this->assertNull($repo->lastSavedTros);
    }

    /**
     * @covers \Bristolian\CliController\BccTroFetcherCliController::runInternal
     */
    public function test_runInternal_writes_skip_when_not_in_daily_window(): void
    {
        $schedule = new FakeDailyProcessorSchedule();
        $schedule->isWithinDailyWindow = false;
        $cliOutput = new CapturingCliOutput();
        $controller = new BccTroFetcherCliController();
        $controller->runInternal(
            new FakeProcessorRunRecordRepo(),
            new BccTroFetcherReturningFixedTros([]),
            $schedule,
            $cliOutput
        );
        $lines = $cliOutput->getCapturedLines();
        $this->assertStringContainsString('daily_bcc_tro processor', implode("\n", $lines));
        $this->assertStringContainsString('Skipping, not currently time', implode("\n", $lines));
    }

    /**
     * @covers \Bristolian\CliController\BccTroFetcherCliController::runInternal
     */
    public function test_runInternal_writes_skip_when_last_run_within_cooldown(): void
    {
        $schedule = new FakeDailyProcessorSchedule();
        $schedule->isWithinDailyWindow = true;
        $schedule->lastRunIsOverCooldownHoursAgo = false;
        $repo = new FakeProcessorRunRecordRepo();
        $repo->startRun(\Bristolian\Repo\ProcessorRepo\ProcessType::daily_bcc_tro);
        $cliOutput = new CapturingCliOutput();
        $controller = new BccTroFetcherCliController();
        $controller->runInternal(
            $repo,
            new BccTroFetcherReturningFixedTros([]),
            $schedule,
            $cliOutput
        );
        $text = $cliOutput->getCapturedOutput();
        $this->assertStringContainsString('within the last 21 hours', $text);
    }

    /**
     * @covers \Bristolian\CliController\BccTroFetcherCliController::runInternal
     */
    public function test_runInternal_fetches_and_finishes_when_allowed(): void
    {
        $schedule = new FakeDailyProcessorSchedule();
        $schedule->isWithinDailyWindow = true;
        $schedule->lastRunIsOverCooldownHoursAgo = true;
        $repo = new FakeProcessorRunRecordRepo();
        $doc = new BccTroDocument('', '', '');
        $fetcher = new BccTroFetcherReturningFixedTros([
            new BccTro('X', 'Y', $doc, $doc, $doc),
        ]);
        $cliOutput = new CapturingCliOutput();
        $controller = new BccTroFetcherCliController();
        $controller->runInternal($repo, $fetcher, $schedule, $cliOutput);
        $text = $cliOutput->getCapturedOutput();
        $this->assertStringContainsString('Fetching TROs.', $text);
        $this->assertStringContainsString('Fin.', $text);
        $records = $repo->getRunRecords(\Bristolian\Repo\ProcessorRepo\ProcessType::daily_bcc_tro);
        $this->assertNotEmpty($records);
        $this->assertSame(\Bristolian\Repo\ProcessorRunRecordRepo\FakeProcessorRunRecordRepo::STATE_FINISHED, $records[0]->status);
    }
}
