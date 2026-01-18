<?php

declare(strict_types = 1);

namespace Bristolian\Repo\ApiTokenRepo;

use Bristolian\Model\Types\ApiToken;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;

/**
 * PDO-based implementation of ApiTokenRepo.
 */
class PdoApiTokenRepo implements ApiTokenRepo
{
    public function __construct(
        private PdoSimple $pdo_simple
    ) {
    }

    public function createToken(string $name, string $token): ApiToken
    {
        $uuid = Uuid::uuid7();
        $id = $uuid->toString();

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

        $params = [
            ':id' => $id,
            ':token' => $token,
            ':name' => $name,
        ];

        $this->pdo_simple->execute($sql, $params);

        return $this->getByToken($token);
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

        $row = $this->pdo_simple->fetchOneAsDataOrNull(
            $sql,
            [':token' => $token]
        );

        if ($row === null) {
            return null;
        }

        // Check if token is revoked
        if ($row['is_revoked'] === true || $row['is_revoked'] === 1) {
            return null;
        }

        return $this->rowToApiToken($row);
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

    /**
     * @param array<string, mixed> $row
     */
    private function rowToApiToken(array $row): ApiToken
    {
        return new ApiToken(
            id: $row['id'],
            token: $row['token'],
            name: $row['name'],
            created_at: new \DateTimeImmutable($row['created_at']),
            is_revoked: (bool)$row['is_revoked'],
            revoked_at: $row['revoked_at'] !== null ? new \DateTimeImmutable($row['revoked_at']) : null
        );
    }
}
