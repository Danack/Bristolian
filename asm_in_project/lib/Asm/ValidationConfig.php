<?php

namespace Asm;

/**
 * Class ValidationConfig
 *
 *
 */
class ValidationConfig
{
    /**
     * @var callable|null Which callable to call when the profile has changed. The callable
     * is passed both the profile as it was when the session was generated, and the new profile
     *
     * $fn(SessionManager $sessionManager, $newProfile, $existingProfiles);
     *
     */
    private $profileChanged;

    /**
     * @var callable|null Which callable to call when a zombie session has been accessed.
     */
    private $zombieKeyAccessed;

    /**
     * @var callable|null Which callable to call when a totally invalid session has been accessed.
     */
    private $invalidSessionAccessed;

    /**
     * @var callable|null Which callable to call when a lock has been lost. If this callable
     * is not set
     */
    private $lostLockCallable;

    public function __construct(
        callable $profileChanged = null,
        callable $zombieKeyAccessed = null,
        callable $invalidSessionAccessed = null,
        callable $lostLockCallable = null
    ) {
        $this->profileChanged = $profileChanged;
        $this->zombieKeyAccessed = $zombieKeyAccessed;
        $this->invalidSessionAccessed = $invalidSessionAccessed;
        $this->lostLockCallable = $lostLockCallable;
    }

    /**
     * @return ?callable
     */
    public function getInvalidSessionAccessedCallable()
    {
        return $this->invalidSessionAccessed;
    }

    /**
     * @return ?callable
     */
    public function getProfileChangedCallable()
    {
        return $this->profileChanged;
    }

    /**
     * @return ?callable
     */
    public function getZombieKeyAccessedCallable()
    {
        return $this->zombieKeyAccessed;
    }

    /**
     * @return ?callable
     */
    public function getLockWasForceReleasedCallable()
    {
        return $this->lostLockCallable;
    }
}
