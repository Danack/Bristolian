<?php

namespace Bristolian\AppController;

use Asm\SessionManager;
use Bristolian\Repo\AdminRepo\AdminRepo;
use SlimDispatcher\Response\RedirectResponse;
use Psr\Http\Message\ServerRequestInterface as Request;

class Login
{
    public function showLoginPage(): string
    {
        $html = <<< HTML

<form method="post">
<table>
  <tr>
    <td>Username</td>
    <td><input type="text" name="username" ></input></td>
  </tr>
  <tr>
    <td>Password</td>
    <td><input type="password" name="password"></input></td>
  </tr>
</table>

<input type="submit" value="Login"></input>

</form>
HTML;

        return $html;
    }

    public function processLoginPage(
        AdminRepo $adminRepo,
        SessionManager $sessionManager,
        Request $request,
        \Asm\RequestSessionStorage $rqs
    ): RedirectResponse {
        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;

        if ($username === null) {
            echo "Username is null.";
            exit(0);
        }
        if ($password === null) {
            echo "password is null.";
            exit(0);
        }

        $adminUser = $adminRepo->getAdminUser($username, $password);

        if (!$adminUser) {
            return new RedirectResponse('/login?message=login failed');
        }

        $session = $sessionManager->createSession($request);
        $session->set('username', $username);
        $rqs->store($session);

        return new RedirectResponse('/tools?message=login worked');
    }
}