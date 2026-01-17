<?php

declare(strict_types = 1);

namespace Bristolian\AppController;

use Bristolian\Repo\AdminRepo\AdminRepo;
use Bristolian\Session\AppSession;
use Bristolian\Session\AppSessionManager;
use SlimDispatcher\Response\RedirectResponse;

class Login
{
    public function logout(AppSessionManager $appSessionManager): RedirectResponse
    {
        $appSessionManager->deleteSession();

        return new RedirectResponse('/?message=You should be logged out.');
    }


    public function showLoginPage(AppSessionManager $appSessionManager): string|RedirectResponse
    {
        $appSession = $appSessionManager->getCurrentAppSession();

        if ($appSession) {// && $appSession->isLoggedIn()) {
            return new RedirectResponse('/?message=You are logged in');
        }

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
        AppSessionManager $appSessionManager
    ): RedirectResponse {

        // TODO - replace with DataType
        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;

        if ($username === null) {
            return new RedirectResponse('/login?message=login failed');
        }
        if ($password === null) {
            return new RedirectResponse('/login?message=login failed');
        }

        $adminUser = $adminRepo->getAdminUser($username, $password);

        if (!$adminUser) {
            return new RedirectResponse('/login?message=login failed');
        }

        $rawSession = $appSessionManager->createRawSession();

        AppSession::createSessionForUser(
            $rawSession,
            $adminUser
        );


        return new RedirectResponse('/tools?message=login worked');
    }
}
