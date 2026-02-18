<?php

declare(strict_types = 1);

namespace Bristolian\Repo\ApiTokenRepo;

use Bristolian\Model\Types\ApiToken;

use function generateSecureToken;

/**
 * Fake implementation of ApiTokenRepo for testing.
 */
class FakeApiTokenRepo implements ApiTokenRepo
{
    /**
     * @var ApiToken[]
     */
    private array $tokens = [];

    /**
     * @param ApiToken[] $initialTokens
     */
    public function __construct(array $initialTokens = [])
    {
        $this->tokens = $initialTokens;
    }

    private const MAX_CREATE_RETRIES = 5;

    public function createToken(string $name): ApiToken
    {
        for ($attempt = 0; $attempt < self::MAX_CREATE_RETRIES; $attempt++) {
            $token = generateSecureToken();

            $alreadyExists = false;
            foreach ($this->tokens as $existing) {
                if ($existing->token === $token) {
                    $alreadyExists = true;
                    break;
                }
            }
            if ($alreadyExists) {
                continue;
            }

            $id = uniqid('token_', true);
            $now = new \DateTimeImmutable();

            $apiToken = new ApiToken(
                id: $id,
                token: $token,
                name: $name,
                created_at: $now,
                is_revoked: false,
                revoked_at: null
            );

            $this->tokens[] = $apiToken;

            return $apiToken;
        }

        throw ApiTokenCreateFailedException::afterMaxRetries(self::MAX_CREATE_RETRIES);
    }

    public function getByToken(string $token): ?ApiToken
    {
        foreach ($this->tokens as $apiToken) {
            if ($apiToken->token === $token && !$apiToken->is_revoked) {
                return $apiToken;
            }
        }

        return null;
    }

    public function revokeToken(string $tokenId): void
    {
        foreach ($this->tokens as $index => $apiToken) {
            if ($apiToken->id === $tokenId) {
                $this->tokens[$index] = new ApiToken(
                    id: $apiToken->id,
                    token: $apiToken->token,
                    name: $apiToken->name,
                    created_at: $apiToken->created_at,
                    is_revoked: true,
                    revoked_at: new \DateTimeImmutable()
                );
                return;
            }
        }
    }
}
