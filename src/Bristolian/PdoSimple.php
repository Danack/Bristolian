<?php

declare(strict_types = 1);

namespace Bristolian;

use PDO;
use Bristolian\Exception\RowNotFoundException;

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

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollback(): void
    {
        $this->pdo->rollBack();
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
        $statement = $this->pdo->prepare($query);

        if ($statement === false) {
            throw new \Exception("Preparing statement failed");
        }

        $result = $statement->execute($params);

        if ($result === false) {
            throw new \Exception("Executing statement failed");
        }

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
        $statement = $this->pdo->prepare($query);

        if ($statement === false) {
            throw new \Exception("Preparing statement failed");
        }

        $result = $statement->execute($params);

        if ($result === false) {
            throw new \Exception("Executing statement failed");
        }

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
        $statement = $this->pdo->prepare($query);

        $result = $statement->execute($params);

        if ($result === false) {
            throw new \Exception("Executing statement failed");
        }

        $statement->setFetchMode(PDO::FETCH_CLASS, $classname);

        $object = $statement->fetch();

        if ($object === false) {
            throw new RowNotFoundException("The query did not result in a row");
        }

        return $object;
    }

    /**
     * @template T of object
     * @param string $query
     * @param mixed[] $params
     * @param class-string<T> $classname
     * @return T|null
     * @throws \Exception
     */
    public function fetchOneAsObjectOrNull(string $query, array $params, string $classname)
    {
        $statement = $this->pdo->prepare($query);

        $result = $statement->execute($params);

        if ($result === false) {
            throw new \Exception("Executing statement failed");
        }

        $statement->setFetchMode(PDO::FETCH_CLASS, $classname);

        $object = $statement->fetch();

        if ($object === false) {
            return null;
        }

        return $object;
    }

    /**
     * @param string $query
     * @param mixed[] $params
     * @return mixed[]|null
     * @throws \Exception
     */
    public function fetchOneAsDataOrNull(string $query, array $params)
    {
        $statement = $this->pdo->prepare($query);

        $result = $statement->execute($params);

        if ($result === false) {
            throw new \Exception("Executing statement failed");
        }

        $result = $statement->fetch();

        if ($result === false) {
            return null;
        }

        return $result;
    }


    /**
     * @param string $query
     * @param mixed[] $params
     * @return mixed[]
     * @throws \Exception
     */
    public function fetchAllAsData(string $query, array $params)
    {
        $statement = $this->pdo->prepare($query);

        $result = $statement->execute($params);

        if ($result === false) {
            throw new \Exception("Executing statement failed");
        }

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
        $statement = $this->pdo->prepare($query);

        $result = $statement->execute($params);

        if ($result === false) {
            throw new \Exception("Executing statement failed");
        }

        $statement->setFetchMode(PDO::FETCH_CLASS, $classname);

        $objects = $statement->fetchAll();

        return $objects;
    }


    /**
     * @param string $query
     * @param mixed[] $params
     * @return int[]|string[]
     * @throws PdoSimpleException
     */
    public function fetchAllAsScalar(string $query, array $params): array
    {
        $statement = $this->pdo->prepare($query);

        $statement->setFetchMode(PDO::FETCH_NUM);

        $result = $statement->execute($params);

        if ($result === false) {
            throw new \Exception("Executing statement failed");
        }

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
