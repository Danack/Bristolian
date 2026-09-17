<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\BccTroRepo;

use Bristolian\Model\Types\BccTro;
use Bristolian\Repo\BccTroRepo\BccTroRepo;
use BristolianTest\BaseTestCase;

/**
 * @internal
 * @coversNothing
 */
abstract class BccTroRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the BccTroRepo implementation.
     *
     * @return BccTroRepo
     */
    abstract public function getTestInstance(): BccTroRepo;


    /**
     * @covers \Bristolian\Repo\BccTroRepo\BccTroRepo::saveData
     * @covers \Bristolian\Repo\BccTroRepo\FakeBccTroRepo::saveData
     * @covers \Bristolian\Repo\BccTroRepo\PdoBccTroRepo::__construct
     * @covers \Bristolian\Repo\BccTroRepo\PdoBccTroRepo::saveData
     */
    public function test_saveData_stores_data(): void
    {
        $repo = $this->getTestInstance();

        $statement1 = new \Bristolian\Model\Types\BccTroDocument('Statement 1', '/files/1', 'doc1');
        $notice1 = new \Bristolian\Model\Types\BccTroDocument('Notice 1', '/files/2', 'doc2');
        $plan1 = new \Bristolian\Model\Types\BccTroDocument('Plan 1', '/files/3', 'doc3');

        $tro1 = new BccTro(
            title: 'TRO 1',
            reference_code: 'REF-001',
            statement_of_reasons: $statement1,
            notice_of_proposal: $notice1,
            proposed_plan: $plan1
        );

        // Should not throw exception
        $repo->saveData([$tro1]);
    }


    /**
     * @covers \Bristolian\Repo\BccTroRepo\BccTroRepo::saveData
     * @covers \Bristolian\Repo\BccTroRepo\FakeBccTroRepo::saveData
     * @covers \Bristolian\Repo\BccTroRepo\PdoBccTroRepo::saveData
     */
    public function test_saveData_accepts_empty_array(): void
    {
        $repo = $this->getTestInstance();

        // Should not throw exception
        $repo->saveData([]);
    }

    /**
     * @covers \Bristolian\Repo\BccTroRepo\BccTroRepo::saveDataIfNew
     * @covers \Bristolian\Repo\BccTroRepo\FakeBccTroRepo::saveDataIfNew
     * @covers \Bristolian\Repo\BccTroRepo\FakeBccTroRepo::saveData
     * @covers \Bristolian\Repo\BccTroRepo\PdoBccTroRepo::saveDataIfNew
     * @covers \Bristolian\Repo\BccTroRepo\PdoBccTroRepo::saveData
     */
    public function test_saveDataIfNew_saves_when_no_previous_data(): void
    {
        $repo = $this->getTestInstance();
        $tro = $this->createUniqueTro('first-save');

        $saveId = $repo->saveDataIfNew([$tro]);

        $this->assertNotNull($saveId);
    }

    /**
     * @covers \Bristolian\Repo\BccTroRepo\BccTroRepo::saveDataIfNew
     * @covers \Bristolian\Repo\BccTroRepo\FakeBccTroRepo::saveDataIfNew
     * @covers \Bristolian\Repo\BccTroRepo\FakeBccTroRepo::saveData
     * @covers \Bristolian\Repo\BccTroRepo\PdoBccTroRepo::saveDataIfNew
     * @covers \Bristolian\Repo\BccTroRepo\PdoBccTroRepo::saveData
     */
    public function test_saveDataIfNew_returns_null_when_data_unchanged(): void
    {
        $repo = $this->getTestInstance();
        $tro = $this->createUniqueTro('unchanged');

        $firstSaveId = $repo->saveDataIfNew([$tro]);
        $this->assertNotNull($firstSaveId);

        $secondSaveId = $repo->saveDataIfNew([$tro]);
        $this->assertNull($secondSaveId);
    }

    /**
     * @covers \Bristolian\Repo\BccTroRepo\BccTroRepo::saveDataIfNew
     * @covers \Bristolian\Repo\BccTroRepo\FakeBccTroRepo::saveDataIfNew
     * @covers \Bristolian\Repo\BccTroRepo\FakeBccTroRepo::saveData
     * @covers \Bristolian\Repo\BccTroRepo\PdoBccTroRepo::saveDataIfNew
     * @covers \Bristolian\Repo\BccTroRepo\PdoBccTroRepo::saveData
     */
    public function test_saveDataIfNew_saves_when_data_differs(): void
    {
        $repo = $this->getTestInstance();
        $firstTro = $this->createUniqueTro('before-change');
        $secondTro = $this->createUniqueTro('after-change');

        $firstSaveId = $repo->saveDataIfNew([$firstTro]);
        $this->assertNotNull($firstSaveId);

        $secondSaveId = $repo->saveDataIfNew([$secondTro]);
        $this->assertNotNull($secondSaveId);
        $this->assertNotSame($firstSaveId, $secondSaveId);
    }

    private function createUniqueTro(string $label): BccTro
    {
        $unique = create_test_uniqid();
        $statement = new \Bristolian\Model\Types\BccTroDocument(
            'Statement ' . $label . ' ' . $unique,
            'https://www.bristol.gov.uk/files/' . $unique . '-statement',
            'doc-' . $unique . '-1'
        );
        $notice = new \Bristolian\Model\Types\BccTroDocument(
            'Notice ' . $label . ' ' . $unique,
            'https://www.bristol.gov.uk/files/' . $unique . '-notice',
            'doc-' . $unique . '-2'
        );
        $plan = new \Bristolian\Model\Types\BccTroDocument(
            'Plan ' . $label . ' ' . $unique,
            'https://www.bristol.gov.uk/files/' . $unique . '-plan',
            'doc-' . $unique . '-3'
        );

        return new BccTro(
            title: 'TRO ' . $label . ' ' . $unique,
            reference_code: 'REF-' . $unique,
            statement_of_reasons: $statement,
            notice_of_proposal: $notice,
            proposed_plan: $plan
        );
    }
}
