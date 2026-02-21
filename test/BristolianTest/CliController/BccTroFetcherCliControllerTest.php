<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\CliController\BccTroFetcherCliController;
use Bristolian\Model\Types\BccTro;
use Bristolian\Model\Types\BccTroDocument;
use Bristolian\Repo\BccTroRepo\BccTroRepo;
use Bristolian\Service\BccTroFetcher\BccTroFetcher;
use BristolianTest\BaseTestCase;
use function Bristolian\CliController\output_tro_list_to_output;

/**
 * BccTroRepo that records the last array passed to saveData for assertions.
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
class BccTroFetcherCliControllerTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        class_exists(BccTroFetcherCliController::class); // load file containing output_tro_list_to_output
    }

    /**
     * @covers \Bristolian\CliController\BccTroFetcherCliController
     */
    public function test_output_tro_list_to_output_empty_echoes_no_tros_found(): void
    {
        ob_start();
        output_tro_list_to_output([]);
        $output = ob_get_clean();
        $this->assertStringContainsString('No TROs found.', $output);
    }

    /**
     * @covers \Bristolian\CliController\BccTroFetcherCliController
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
     * @covers \Bristolian\CliController\BccTroFetcherCliController
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
     * @covers \Bristolian\CliController\BccTroFetcherCliController::fetchTros
     */
    public function test_fetchTros_saves_fetched_tros_to_repo(): void
    {
        $doc = new BccTroDocument('', '', '');
        $tro = new BccTro('Fetched TRO', 'REF-42', $doc, $doc, $doc);
        $fetcher = new BccTroFetcherReturningFixedTros([$tro]);
        $repo = new BccTroFetcherTestBccTroRepo();
        $controller = new BccTroFetcherCliController();

        ob_start();
        $controller->fetchTros($fetcher, $repo, 'CLI');
        ob_get_clean();

        $this->assertNotNull($repo->lastSavedTros);
        $this->assertCount(1, $repo->lastSavedTros);
        $this->assertSame('Fetched TRO', $repo->lastSavedTros[0]->title);
        $this->assertSame('REF-42', $repo->lastSavedTros[0]->reference_code);
    }
}
