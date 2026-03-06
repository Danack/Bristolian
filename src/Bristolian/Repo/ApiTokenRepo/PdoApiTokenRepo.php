<?php

declare(strict_types = 1);

namespace Bristolian\Repo\ApiTokenRepo;

use Bristolian\Model\Types\ApiToken;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\PdoSimple\PdoSimpleWithPreviousException;
use Bristolian\Service\SecureTokenGenerator\SecureTokenGenerator;
use Ramsey\Uuid\Uuid;

/**
 * PDO-based implementation of ApiTokenRepo.
 */
class PdoApiTokenRepo implements ApiTokenRepo
{
    private const MAX_CREATE_RETRIES = 5;

    public function __construct(
        private PdoSimple $pdo_simple,
        private SecureTokenGenerator $secureTokenGenerator
    ) {
    }

    public function createToken(string $name): ApiToken
    {
        $lastException = null;

        $sql = <<< SQL
        INSERT INTO api_token (
            id,
            token,
            name,
            created_at,
            is_revoked,
            revoked_at
        ) VALUES (
            :id,
            :token,
            :name,
            NOW(),
            false,
            NULL
        )
        SQL;

        for ($attempt = 0; $attempt < self::MAX_CREATE_RETRIES; $attempt++) {
            $token = $this->secureTokenGenerator->generate();
            $uuid = Uuid::uuid7();
            $id = $uuid->toString();

            $params = [
                ':id' => $id,
                ':token' => $token,
                ':name' => $name,
            ];

            try {
                $this->pdo_simple->execute($sql, $params);

                return $this->getByToken($token);
            } catch (PdoSimpleWithPreviousException $e) {
                $lastException = $e;
                $pdo = $e->getPreviousPdoException();
                $code = $pdo->getCode();
                $errorInfo = $pdo->errorInfo ?? [];
                $driverCode = $errorInfo[1] ?? null;
                // MySQL duplicate key: SQLSTATE 23000 or driver code 1062
                $isDuplicate = $code === '23000' || $driverCode === 1062;
                if (!$isDuplicate) {
                    // TODO - inject ID generator
                    // @codeCoverageIgnoreStart
                    throw $e;
                    // @codeCoverageIgnoreEnd
                }
            }
        }

        throw ApiTokenCreateFailedException::afterMaxRetries(self::MAX_CREATE_RETRIES, $lastException);
    }

    public function getByToken(string $token): ?ApiToken
    {
        $sql = <<< SQL
SELECT 
    id,
    token,
    name,
    created_at,
    is_revoked,
    revoked_at
FROM api_token
WHERE token = :token
LIMIT 1
SQL;

        $apiToken = $this->pdo_simple->fetchOneAsObjectOrNullConstructor(
            $sql,
            [':token' => $token],
            ApiToken::class
        );

        if ($apiToken === null) {
            return null;
        }

        if ($apiToken->is_revoked) {
            return null;
        }

        return $apiToken;
    }

    public function revokeToken(string $tokenId): void
    {
        $sql = <<< SQL
UPDATE api_token
SET is_revoked = true,
    revoked_at = NOW()
WHERE id = :id
LIMIT 1
SQL;

        $this->pdo_simple->execute($sql, [':id' => $tokenId]);
    }
}
