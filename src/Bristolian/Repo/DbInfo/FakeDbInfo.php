<?php

namespace Bristolian\Repo\DbInfo;

use Bristolian\Model\MigrationThatHasBeenRun;
use Bristolian\Parameters\Table;

class FakeDbInfo implements DbInfo
{
    public function getTableInfo(): array
    {
        $tables = [];

        $tables[] = new Table(0, "file_storage_info");
        $tables[] = new Table(2, "pdo_simple_test");

        return $tables;
    }

    public function getMigrations(): array
    {
        $migrations = [];
        $migrations[] = new MigrationThatHasBeenRun(
            1,
            "Migration 0: user",
            "9b72279e8a83ab214ec77c77967bb3d97796de6353baf6d58685edf0e4ac043c",
            new \DateTimeImmutable("2024-12-02 20:01:53.000000")
        );

        $migrations[] = new MigrationThatHasBeenRun(
            2,
            "Migration 1: user auth",
            "cf4c97fdbbfc804bad6e855b7182148768eb271919d885f2d50d0149263ef047",
            new \DateTimeImmutable("2024-12-02 20:01:53.000000")
        );

        return $migrations;
    }
}
