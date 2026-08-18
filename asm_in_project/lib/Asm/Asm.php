<?php

namespace Asm;

use Asm\AsmException;
use Asm\SessionManager;

class Asm
{
    /**
     *
     * @param string $caching
     * @return array
     * @throws AsmException
     */
    public static function getCacheControlPrivacyHeader(string $caching)
    {
        $cacheHeaderInfo = [
            SessionManager::CACHE_SKIP => null,
            SessionManager::CACHE_PUBLIC => "public",
            SessionManager::CACHE_PRIVATE => "private",
            SessionManager::CACHE_NO_CACHE => "no-store, no-cache, must-revalidate, post-check=0, pre-check=0"
        ];
        
        if (array_key_exists($caching, $cacheHeaderInfo) == false) {
            throw new AsmException(
                "Unknown cache setting '$caching'.",
                AsmException::BAD_ARGUMENT
            );
        }

        if ($cacheHeaderInfo[$caching] === null) {
            return [];
        }

        return ['Cache-Control', $cacheHeaderInfo[$caching]];
    }

    public static function generateCookieHeaderString(
        int $time,
        string $cookieName,
        string $cookieValue,
        int $lifetime,
        ?string $path = null,
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true
    ): string {

        $headerString = "";
        $headerString .= $cookieName.'='.$cookieValue;

        $expireTime = $time + $lifetime;
        $expireDate = date("D, d M Y H:i:s T", $expireTime);
        $headerString .= "; expires=";
        $headerString .= $expireDate;

        // eww - this should be single operation?
        $headerString .= "; Max-Age=";
        $headerString .= $lifetime;

        if ($path !== null) {
            $headerString .= "; path=";
            $headerString .= $path;
        }

        if ($domain !== null) {
            $headerString .= "; domain=";
            $headerString .= $domain;
        }

        if ($secure) {
            $headerString .= "; secure";
        }

        if ($httpOnly) {
            $headerString .= "; httpOnly";
        }

        $headerString .="; SameSite=Strict";

        return $headerString;
    }
}
