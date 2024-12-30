<?php

declare(strict_types = 1);

namespace Bristolian\PdoSimple;

use PDO;
use PDOException;

class PdoSimple
{
    /** @var \PDO */
    private $pdo;

    /**
     * PdoSimple constructor.
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @codeCoverageIgnore
     * @return void
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * @codeCoverageIgnore
     * @return void
     */
    public function commit(): void
    {
        $this->pdo->commit();
    }

    /**
     * @codeCoverageIgnore
     * @return void
     */
    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * @param string $query
     * @param array $params
     * @return {0:bool, 1:false|PdoStatement}
     * @throws \Bristolian\PdoSimple\PdoSimpleException
     */
    private function prepareAndExecute(string $query, array $params): array
    {
        try {
            $statement = $this->pdo->prepare($query);
        }
        catch (PDOException $pdoe) {
            throw PdoSimpleWithPreviousException::invalidSql($pdoe);
        }

        try {
            $result = $statement->execute($params);
        }
        catch (PDOException $pdoe) {
            throw PdoSimpleWithPreviousException::errorExecutingSql($pdoe);
        }

        return [$result, $statement];
    }

    /**
     * Executes some SQL.
     * Returns the number of rows affected
     *
     * @param string $query
     * @param mixed[] $params
     * @return int
     * @throws \Exception
     */
    public function execute(string $query, array $params): int
    {
        [$result, $statement] = $this->prepareAndExecute($query, $params);

        return $statement->rowCount();
    }

    /**
     * @param string $query
     * @param mixed[] $params
     * @return int
     * @throws \Exception
     */
    public function insert(string $query, array $params): int
    {
        [$result, $statement] = $this->prepareAndExecute($query, $params);

        return intval($this->pdo->lastInsertId());
    }


    /**
     * @template T of object
     * @param string $query
     * @param mixed[] $params
     * @param class-string<T> $classname
     * @return T
     * @throws RowNotFoundException
     */
    public function fetchOneAsObject(string $query, array $params, string $classname)
    {
        [$result, $statement] = $this->prepareAndExecute($query, $params);

        $statement->setFetchMode(PDO::FETCH_CLASS, $classname);
        $object = $statement->fetch();

        if ($object === false) {
            throw new RowNotFoundException("The query did not result in a row");
        }

        return $object;
    }

    /**
     * This version assigns the properties magically, before calling the constructor.
     *
     *
     * @template T of object
     * @param string $query
     * @param mixed[] $params
     * @param class-string<T> $classname
     * @return T|null
     * @throws \Exception
     */
    public function fetchOneAsObjectOrNull(string $query, array $params, string $classname)
    {
        [$result, $statement] = $this->prepareAndExecute($query, $params);

        $statement->setFetchMode(PDO::FETCH_CLASS, $classname);

        $object = $statement->fetch();

        if ($object === false) {
            return null;
        }

        return $object;
    }


    /**
     *
     * This version calls the class constructor properly.
     *
     * @param string $query
     * @param array $params
     * @param string $classname
     * @return mixed|object|string|null
     * @throws \ReflectionException
     */
    public function fetchOneAsObjectOrNullConstructor(string $query, array $params, string $classname)
    {
        [$result, $statement] = $this->prepareAndExecute($query, $params);

        $statement->setFetchMode(PDO::FETCH_ASSOC);

        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        $reflection = new \ReflectionClass($classname);
        $instance = $reflection->newInstanceArgs($row);

        return $instance;
    }

    /**
     *
     * This version calls the class constructor properly.
     *
     * @param string $query
     * @param array $params
     * @param string $classname
     * @return mixed|object|string|null
     * @throws \ReflectionException
     */
    public function fetchAllAsObjectConstructor(string $query, array $params, string $classname)
    {
        [$result, $statement] = $this->prepareAndExecute($query, $params);

        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $rows = $statement->fetchAll();

        $objects = [];

        foreach ($rows as $row) {
            $reflection = new \ReflectionClass($classname);
            $objects[] = $reflection->newInstanceArgs($row);
        }

        return $objects;
    }

    /**
     * @param string $query
     * @param mixed[] $params
     * @return mixed[]|null
     * @throws \Exception
     */
    public function fetchOneAsDataOrNull(string $query, array $params)
    {
        [$result, $statement] = $this->prepareAndExecute($query, $params);
        $result = $statement->fetch();

        if ($result === false) {
            return null;
        }

        return $result;
    }

    public function fetchAllAsData(string $query, array $params)
    {
        [$result, $statement] = $this->prepareAndExecute($query, $params);

        $objects = $statement->fetchAll();

        return $objects;
    }


    /**
     * @template T of object
     * @param string $query
     * @param mixed[] $params
     * @param class-string<T> $classname
     * @return T[]
     * @throws \Exception
     */
    public function fetchAllAsObject(string $query, array $params, string $classname)
    {
        [$result, $statement] = $this->prepareAndExecute($query, $params);

        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        return convertToArrayOfObjects($classname, $data);
    }


    /**
     * @param string $query
     * @param mixed[] $params
     * @return int[]|string[]
     * @throws PdoSimpleException
     */
    public function fetchAllRowsAsScalar(string $query, array $params): array
    {
        [$result, $statement] = $this->prepareAndExecute($query, $params);
        $statement->setFetchMode(PDO::FETCH_NUM);

        $rows = $statement->fetchAll();
        $data = [];

        foreach ($rows as $row) {
            $number_of_rows = count($row);
            if ($number_of_rows > 1) {
                throw PdoSimpleException::tooManyColumns($number_of_rows);
            }

            $data[] = $row[0];
        }

        return $data;
    }
}
