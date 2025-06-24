<?php

namespace Bristolian\Session;

use Asm\Session;
use \Bristolian\Session\AppSession;
use Bristolian\Exception\BristolianException;
use Bristolian\Session\AppSessionManagerInterface;
use Bristolian\Session\UserSession;
use Psr\Http\Message\ServerRequestInterface as Request;

use Bristolian\Session\FakeUserSession;

class FakeAppSessionManager implements AppSessionManagerInterface
{
    private UserSession|null $userSession = null;

    /**
     * @param list<array{0:string, 1:string}>|null $fake_headers
     */
    public function __construct(private array|null $fake_headers = null)
    {
    }

    public static function createLoggedIn(): self
    {
        $instance = new self();
        $instance->userSession = new FakeUserSession(
            $isLoggedIn = true,
            $userId = "abcde123345",
            $username = 'john'
        );
        return $instance;
    }

    public function initialize(Request $request): void
    {
//        throw new BristolianException('Not implemented');
    }

    public function deleteSession(): void
    {
        throw new BristolianException('Not implemented');
    }

    public function getCurrentAppSession(): UserSession|null
    {
        return $this->userSession;
    }

    public function createRawSession(): Session
    {
        throw new BristolianException('Not implemented');
    }

    /**
     * @return list<array{0:string, 1:string}>
     */
    public function renewSession(): array
    {
        if ($this->fake_headers) {
            return $this->fake_headers;
        }

        return [
            [
                'set-cookie',
                'john_is_my_name=123456; expires=Sun, 05 Jan 2025 23:04:05 UTC; Max-Age=3600; path=/; httpOnly'
            ],
            [
                'set-cookie',
                'john_is_my_name_key=123457890abcdefgh==; expires=Sun, 05 Jan 2025 23:04:05 UTC; Max-Age=3600; path=/; httpOnly']
        ];
    }

    /**
     * @return list<array{0:string, 1:string}>
     */
    public function saveIfOpenedAndGetHeaders(): array
    {
        if ($this->fake_headers) {
            return $this->fake_headers;
        }

        return [
            [
                'set-cookie',
                'john_is_my_name=123456; expires=Sun, 05 Jan 2025 23:04:05 UTC; Max-Age=3600; path=/; httpOnly'
            ],
            [
                'set-cookie',
                'john_is_my_name_key=123457890abcdefgh==; expires=Sun, 05 Jan 2025 23:04:05 UTC; Max-Age=3600; path=/; httpOnly']
        ];
    }
}
