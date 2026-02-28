<?php

declare(strict_types=1);

namespace BristolianChatTest\Fixtures;

use Psr\Http\Message\UriInterface;

/**
 * Minimal PSR-7 Uri for building Amp Request in tests.
 * Supports a simple absolute URI (e.g. http://localhost/).
 *
 * @coversNothing
 */
final class Psr7UriForTests implements UriInterface
{
    public function __construct(
        private string $scheme = 'http',
        private string $host = 'localhost',
        private ?int $port = null,
        private string $path = '/',
        private string $query = '',
        private string $fragment = '',
        private string $userInfo = '',
    ) {
    }

    public static function fromString(string $uri): self
    {
        $parts = parse_url($uri);
        return new self(
            scheme: isset($parts['scheme']) ? strtolower($parts['scheme']) : 'http',
            host: $parts['host'] ?? '',
            port: isset($parts['port']) ? (int) $parts['port'] : null,
            path: $parts['path'] ?? '/',
            query: $parts['query'] ?? '',
            fragment: $parts['fragment'] ?? '',
            userInfo: isset($parts['user'], $parts['pass'])
                ? $parts['user'] . ':' . $parts['pass']
                : ($parts['user'] ?? ''),
        );
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        $authority = $this->host;
        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }
        if ($this->port !== null && !$this->isStandardPort()) {
            $authority .= ':' . $this->port;
        }
        return $authority;
    }

    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->isStandardPort() ? null : $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function withScheme(string $scheme): UriInterface
    {
        $new = clone $this;
        $new->scheme = strtolower($scheme);
        return $new;
    }

    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $new = clone $this;
        $new->userInfo = $password !== null && $password !== '' ? $user . ':' . $password : $user;
        return $new;
    }

    public function withHost(string $host): UriInterface
    {
        $new = clone $this;
        $new->host = $host;
        return $new;
    }

    public function withPort(?int $port): UriInterface
    {
        $new = clone $this;
        $new->port = $port;
        return $new;
    }

    public function withPath(string $path): UriInterface
    {
        $new = clone $this;
        $new->path = $path;
        return $new;
    }

    public function withQuery(string $query): UriInterface
    {
        $new = clone $this;
        $new->query = $query;
        return $new;
    }

    public function withFragment(string $fragment): UriInterface
    {
        $new = clone $this;
        $new->fragment = $fragment;
        return $new;
    }

    public function __toString(): string
    {
        $uri = $this->scheme !== '' ? $this->scheme . ':' : '';
        $authority = $this->getAuthority();
        if ($authority !== '') {
            $uri .= '//' . $authority;
        }
        $uri .= $this->path !== '' ? $this->path : '/';
        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }
        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }
        return $uri;
    }

    private function isStandardPort(): bool
    {
        if ($this->port === null) {
            return true;
        }
        return ($this->scheme === 'http' && $this->port === 80)
            || ($this->scheme === 'https' && $this->port === 443);
    }
}
