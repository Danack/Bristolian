<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\CliController\BccTroFetcherCliController;
use Bristolian\Model\Types\BccTro;
use Bristolian\Model\Types\BccTroDocument;
use Bristolian\Repo\BccTroRepo\FakeBccTroRepo;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRunRecordRepo\FakeProcessorRunRecordRepo;
use Bristolian\Service\BccTroFetcher\FakeBccTroFetcher;
use Bristolian\Service\CliOutput\CapturingCliOutput;
use Bristolian\Service\DailyProcessorSchedule\FakeBccTroExecutionCheck;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversFunction('output_tro_list_to_output')]
#[CoversMethod(\Bristolian\CliController\BccTroFetcherCliController::class, 'single_bcc_tro_process')]
class BccTroFetcherCliControllerTest extends BaseTestCase
{
    public function test_output_tro_list_to_output_empty_echoes_no_tros_found(): void
    {
        $output = output_tro_list_to_output([]);
        $this->assertStringContainsString('No TROs found.', $output);
    }

    public function test_output_tro_list_to_output_with_one_tro_echoes_title_and_reference(): void
    {
        $doc = new BccTroDocument('', '', '');
        $tro = new BccTro('Test TRO Title', 'REF-001', $doc, $doc, $doc);
        $output = output_tro_list_to_output([$tro]);
        $this->assertStringContainsString('Found 1 TRO(s):', $output);
        $this->assertStringContainsString('Title: Test TRO Title', $output);
        $this->assertStringContainsString('Reference: REF-001', $output);
        $this->assertStringContainsString('---', $output);
    }

    public function test_output_tro_list_to_output_echoes_statement_of_reasons_when_non_empty(): void
    {
        $statement = new BccTroDocument('Reasons doc', 'https://example.com/reasons', 'id1');
        $other = new BccTroDocument('', '', '');
        $tro = new BccTro('Title', 'REF', $statement, $other, $other);
        $output = output_tro_list_to_output([$tro]);
        $this->assertStringContainsString('Statement of Reasons: Reasons doc', $output);
        $this->assertStringContainsString('Link: https://example.com/reasons', $output);
    }

    public function test_output_tro_list_to_output_echoes_notice_of_proposal_when_non_empty(): void
    {
        $proposal = new BccTroDocument('Notice title', 'https://example.com/notice', 'id2');
        $empty = new BccTroDocument('', '', '');
        $tro = new BccTro('T', 'R', $empty, $proposal, $empty);
        $output = output_tro_list_to_output([$tro]);
        $this->assertStringContainsString('Notice of Proposal: Notice title', $output);
        $this->assertStringContainsString('Link: https://example.com/notice', $output);
    }

    public function test_output_tro_list_to_output_echoes_proposed_plan_when_non_empty(): void
    {
        $plan = new BccTroDocument('Plan title', 'https://example.com/plan', 'id3');
        $empty = new BccTroDocument('', '', '');
        $tro = new BccTro('T', 'R', $empty, $empty, $plan);
        $output = output_tro_list_to_output([$tro]);
        $this->assertStringContainsString('Proposed Plan: Plan title', $output);
        $this->assertStringContainsString('Link: https://example.com/plan', $output);
    }

    public function test_single_bcc_tro_process_skips_when_execution_check_fails(): void
    {
        $cliOutput = new CapturingCliOutput();
        $processorRunRecordRepo = new FakeProcessorRunRecordRepo();
        $bccTroRepo = new FakeBccTroRepo();
        $controller = new BccTroFetcherCliController();

        $controller->single_bcc_tro_process(
            new FakeBccTroExecutionCheck(false),
            $processorRunRecordRepo,
            new FakeBccTroFetcher('<html>should not be stored</html>'),
            $bccTroRepo,
            $cliOutput,
        );

        $this->assertStringContainsString('Skipping, BccTroExecutionCheck failed', $cliOutput->getCapturedOutput());
        $this->assertSame([], $processorRunRecordRepo->getRunRecords(ProcessType::daily_bcc_tro));
        $this->assertSame([], $bccTroRepo->getSavedPages());
    }

    public function test_single_bcc_tro_process_saves_page_when_new(): void
    {
        $html = '<html>fetched page ' . create_test_uniqid() . '</html>';
        $cliOutput = new CapturingCliOutput();
        $processorRunRecordRepo = new FakeProcessorRunRecordRepo();
        $bccTroRepo = new FakeBccTroRepo();
        $controller = new BccTroFetcherCliController();

        $controller->single_bcc_tro_process(
            new FakeBccTroExecutionCheck(true),
            $processorRunRecordRepo,
            new FakeBccTroFetcher($html),
            $bccTroRepo,
            $cliOutput,
        );

        $this->assertSame($html, $bccTroRepo->getMostRecentData());
        $this->assertStringContainsString('BccTro save_id_or_null is', $cliOutput->getCapturedOutput());
        $this->assertStringContainsString('Fin.', $cliOutput->getCapturedOutput());

        $records = $processorRunRecordRepo->getRunRecords(ProcessType::daily_bcc_tro);
        $this->assertCount(1, $records);
        $this->assertSame(FakeProcessorRunRecordRepo::STATE_FINISHED, $records[0]->status);
    }

    public function test_single_bcc_tro_process_records_error_when_fetch_throws(): void
    {
        $cliOutput = new CapturingCliOutput();
        $processorRunRecordRepo = new FakeProcessorRunRecordRepo();
        $bccTroRepo = new FakeBccTroRepo();
        $controller = new BccTroFetcherCliController();

        $controller->single_bcc_tro_process(
            new FakeBccTroExecutionCheck(true),
            $processorRunRecordRepo,
            new FakeBccTroFetcher('', new \RuntimeException('network down')),
            $bccTroRepo,
            $cliOutput,
        );

        $this->assertSame([], $bccTroRepo->getSavedPages());
        $this->assertStringContainsString(
            'Error running BccTroFetcherCliController: network down',
            $cliOutput->getCapturedOutput()
        );
        $this->assertStringContainsString('Fin.', $cliOutput->getCapturedOutput());

        $records = $processorRunRecordRepo->getRunRecords(ProcessType::daily_bcc_tro);
        $this->assertCount(1, $records);
        $this->assertSame(FakeProcessorRunRecordRepo::STATE_FINISHED, $records[0]->status);
        $this->assertSame(
            'Error running BccTroFetcherCliController: network down',
            $records[0]->debug_info
        );
    }
}
