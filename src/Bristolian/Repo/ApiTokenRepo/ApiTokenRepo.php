<?php

declare(strict_types = 1);

namespace Bristolian\Repo\ApiTokenRepo;

use Bristolian\Model\Types\ApiToken;

/**
 * Repository interface for API token management.
 */
interface ApiTokenRepo
{
    /**
     * Create a new API token with the given name.
     *
     * @param string $name Name/identifier for the token
     * @param string $token The token value to store
     * @return ApiToken The created token
     */
    public function createToken(string $name, string $token): ApiToken;

    /**
     * Find a token by its token value.
     *
     * @param string $token The token value
     * @return ApiToken|null The token if found and not revoked, null otherwise
     */
    public function getByToken(string $token): ?ApiToken;

    /**
     * Revoke a token by its ID.
     *
     * @param string $tokenId The token ID
     */
    public function revokeToken(string $tokenId): void;
}
