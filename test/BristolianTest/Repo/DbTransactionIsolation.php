<?php

declare(strict_types = 1);

namespace BristolianTest\Repo;

use Bristolian\PdoSimple\PdoSimple;

/**
 * Trait for @group db tests that wrap each test in a transaction and roll back
 * in tearDown so tests don't see each other's data.
 *
 * Requires $this->injector (e.g. from TestPlaceholders). Override setUp and
 * tearDown to call parent, then dbTransactionSetUp() / dbTransactionTearDown().
 *
 * Override dbTransactionClearTables() to delete from test-specific tables after
 * beginTransaction(), so tests don't see committed data from other runs.
 */
trait DbTransactionIsolation
{
    private bool $dbTransactionIsolationActive = false;

    protected function dbTransactionSetUp(): void
    {
        $pdoSimple = $this->injector->make(PdoSimple::class);
        $pdoSimple->beginTransaction();
        $this->dbTransactionIsolationActive = true;
        $this->dbTransactionClearTables();
    }

    /**
     * Override to delete from tables your tests need empty (inside the transaction).
     * Default no-op.
     */
    protected function dbTransactionClearTables(): void
    {
    }

    protected function dbTransactionTearDown(): void
    {
        if (! $this->dbTransactionIsolationActive) {
            return;
        }
        $this->dbTransactionIsolationActive = false;
        $pdoSimple = $this->injector->make(PdoSimple::class);
        $pdoSimple->rollback();
    }
}
