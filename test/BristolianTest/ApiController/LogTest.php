<?php

declare(strict_types=1);

namespace BristolianTest\ApiController;

use Bristolian\ApiController\Log;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRunRecordRepo\FakeProcessorRunRecordRepo;
use Bristolian\Response\Typed\GetLogProcessorRunRecordsResponse;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\ApiController\Log::get_processor_run_records
 */
class LogTest extends BaseTestCase
{
    public function test_get_processor_run_records_returns_empty_list_when_no_records(): void
    {
        $processorRepo = new FakeProcessorRunRecordRepo();
        $pdoSimple = $this->make(PdoSimple::class);
        $varMap = new ArrayVarMap([]);

        $controller = new Log();
        $response = $controller->get_processor_run_records($processorRepo, $pdoSimple, $varMap);

        $this->assertInstanceOf(GetLogProcessorRunRecordsResponse::class, $response);
        $this->assertSame(200, $response->getStatus());

        $data = json_decode_safe($response->getBody());
        $this->assertArrayHasKey('result', $data);
        $this->assertSame('success', $data['result']);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('run_records', $data['data']);
        $this->assertIsArray($data['data']['run_records']);
        $this->assertCount(0, $data['data']['run_records']);
    }

    public function test_get_processor_run_records_returns_records_from_repo(): void
    {
        $processorRepo = new FakeProcessorRunRecordRepo();
        $runId = $processorRepo->startRun(ProcessType::meme_ocr);
        $processorRepo->setRunFinished($runId, 'debug info');

        $pdoSimple = $this->make(PdoSimple::class);
        $varMap = new ArrayVarMap([]);

        $controller = new Log();
        $response = $controller->get_processor_run_records($processorRepo, $pdoSimple, $varMap);

        $this->assertInstanceOf(GetLogProcessorRunRecordsResponse::class, $response);
        $this->assertSame(200, $response->getStatus());

        $data = json_decode_safe($response->getBody());
        $this->assertArrayHasKey('data', $data);
        $runRecords = $data['data']['run_records'];
        $this->assertCount(1, $runRecords);
        $this->assertSame('meme_ocr', $runRecords[0]['processor_type']);
        $this->assertArrayHasKey('start_time', $runRecords[0]);
    }

    public function test_get_processor_run_records_filters_by_task_type(): void
    {
        $processorRepo = new FakeProcessorRunRecordRepo();
        $processorRepo->startRun(ProcessType::meme_ocr);
        $processorRepo->startRun(ProcessType::email_send);

        $pdoSimple = $this->make(PdoSimple::class);
        $varMap = new ArrayVarMap(['task_type' => 'meme_ocr']);

        $controller = new Log();
        $response = $controller->get_processor_run_records($processorRepo, $pdoSimple, $varMap);

        $data = json_decode_safe($response->getBody());
        $runRecords = $data['data']['run_records'];
        $this->assertCount(1, $runRecords);
        $this->assertSame('meme_ocr', $runRecords[0]['processor_type']);
    }
}
