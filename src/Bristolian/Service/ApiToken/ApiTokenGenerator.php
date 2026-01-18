<?php

declare(strict_types = 1);

namespace Bristolian\Service\ApiToken;

/**
 * Service for generating secure API tokens.
 */
class ApiTokenGenerator
{
    /**
     * Generate a cryptographically secure random token.
     *
     * Generates 32 random bytes and base64 encodes them, resulting in
     * approximately 44 characters (base64 encoding adds ~33% overhead).
     *
     * @return string The generated token
     */
    public function generateSecureToken(): string
    {
        // Generate 32 random bytes
        $randomBytes = random_bytes(32);
        
        // Base64 encode and remove padding (= characters) for cleaner tokens
        $token = rtrim(base64_encode($randomBytes), '=');
        
        // Replace URL-unsafe characters with URL-safe alternatives
        $token = strtr($token, '+/', '-_');
        
        return $token;
    }
}
