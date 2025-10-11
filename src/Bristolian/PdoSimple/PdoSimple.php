<?php

declare(strict_types = 1);

namespace Bristolian\PdoSimple;

use Bristolian\App;
use PDO;
use PDOException;

/**
 * @param  array<string, string|int|null> $row
 * @return array<string, string|\DateTimeInterface|null>
 * @throws \Exception
 *
 * TODO - when upgrading to PHP 8.3, change to \DateMalformedStringException
 */
function convertRowToDatetime(array $row): array
{
    $time_columns = [
        'created_at',
        'updated_at',
        'start_time',
        'end_time',
    ];

    $data = [];
    foreach ($row as $key => $value) {
        if ($value === null) {
            $data[$key] = null;
        }
        else if (in_array($key, $time_columns)) {
            $data[$key] = new \DateTimeImmutable($value);
        }
        else {
            $data[$key] = $value;
        }
    }
    return $data;
}

/**
 * @param array<string, string|int|\DateTimeInterface> $row
 * @return array<string, string|int>
 */
function convertRowFromDatetime(array $row)
{
    $data = [];
    foreach ($row as $key => $value) {
        if ($value instanceof \DateTimeInterface) {
            $data[$key] = $value->format(App::MYSQL_DATE_TIME_FORMAT);
        }
        else {
            $data[$key] = $value;
        }
    }
    return $data;
}



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

    public function get_pdo(): \PDO
    {
        return $this->pdo;
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
     * @param array<string, string|int> $params
     * @return array{0:bool, 1:\PdoStatement}
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
            // Convert datetime to string here?
            $params = convertRowFromDatetime($params);
            $result = $statement->execute($params);
        }
        catch (PDOException $pdoe) {
            throw PdoSimpleWithPreviousException::errorExecutingSql($pdoe);
        }

        // TODO - can result ever be false?
        return [$result, $statement];
    }

    /**
     * Executes some SQL.
     * Returns the number of rows affected
     *
     * @param string $query
     * @param array<string, string|int|float|null> $params
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
     * @param array<string, string|int|null|float> $params
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
     * @param array<string, string|int> $params
     * @param class-string<T> $classname
     * @return T
     * @throws RowNotFoundException
     */
    public function fetchOneAsObject(string $query, array $params, string $classname)
    {
        [$result, $statement] = $this->prepareAndExecute($query, $params);

//        $statement->setFetchMode(PDO::FETCH_CLASS, $classname);
//        $object = $statement->fetch();
//
//        if ($object === false) {
//            throw new RowNotFoundException("The query did not result in a row");
//        }
//
//        return $object;


        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $row = $statement->fetch();

        if ($row === false) {
            throw new RowNotFoundException("The query did not result in a row");
        }

        $reflection = new \ReflectionClass($classname);
        $converted_row = convertRowToDatetime($row);
        $instance = $reflection->newInstanceArgs($converted_row);

        return $instance;
    }


    /**
     * @template T of object
     * @param string $query
     * @param array<string, string|int> $params
     * @param class-string<T> $classname
     * @return T
     * @throws RowNotFoundException
     */
    public function fetchOneAsObjectConstructor(string $query, array $params, string $classname)
    {
        [$result, $statement] = $this->prepareAndExecute($query, $params);

        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $row = $statement->fetch();

        if ($row === false) {
            throw new RowNotFoundException("The query did not result in a row");
        }

        $reflection = new \ReflectionClass($classname);
        $converted_row = convertRowToDatetime($row);
        $instance = $reflection->newInstanceArgs($converted_row);

        return $instance;
    }


    /**
     * This version assigns the properties magically, before calling the constructor.
     *
     *
     * @template T of object
     * @param string $query
     * @param array<string, string|int> $params
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
     * @template T of object
     * @param string $query
     * @param array<string, string|int> $params
     * @param class-string<T> $classname
     * @return T|null
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
        $converted_row = convertRowToDatetime($row);
        $instance = $reflection->newInstanceArgs($converted_row);

        return $instance;
    }

    /**
     *
     * This version calls the class constructor properly.
     *
     * @template T of object
     * @param string $query
     * @param array<string, string|int> $params
     * @param class-string<T> $classname
     * @return T[]
     * @throws \ReflectionException
     */
    public function fetchAllAsObjectConstructor(string $query, array $params, string $classname): array
    {
        [$result, $statement] = $this->prepareAndExecute($query, $params);

        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $rows = $statement->fetchAll();

        $objects = [];


        // if is a time column, convert it to a date time.
        foreach ($rows as $row) {
            $converted_row = convertRowToDatetime($row);
            $reflection = new \ReflectionClass($classname);
            $objects[] = $reflection->newInstanceArgs($converted_row);
        }

        return $objects;
    }

    /**
     * @param string $query
     * @param array<string, string|int> $params
     * @return mixed[]|null
     * @throws \Exception
     */
    public function fetchOneAsDataOrNull(string $query, array $params): array|null
    {
        [$result, $statement] = $this->prepareAndExecute($query, $params);
        $result = $statement->fetch();

        if ($result === false) {
            return null;
        }

        return $result;
    }

    /**
     * @param string $query
     * @param array<string, string|int> $params
     * @return array<array<string, int|string|float|bool>>
     * @throws PdoSimpleException
     */
    public function fetchAllAsData(string $query, array $params): array
    {
        [$result, $statement] = $this->prepareAndExecute($query, $params);

        $rows = $statement->fetchAll();

        return $rows;
    }


    /**
     * @template T of object
     * @param string $query
     * @param array<string, string|int> $params
     * @param class-string<T> $classname
     * @return T[]
     * @throws \Exception
     */
    public function fetchAllAsObject(string $query, array $params, string $classname)
    {
        // TODO - this should be fetchAllAsType

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
