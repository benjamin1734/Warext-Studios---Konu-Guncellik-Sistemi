<?php

namespace WarextStudios\ThreadFreshness\Service\ThreadFreshness;

use XF\Entity\Thread;
use XF\Entity\User;
use XF\Service\AbstractService;

class OwnerClaim extends AbstractService
{
    protected Thread $thread;
    protected User $user;
    protected bool $claimed = true;

    public function __construct(\XF\App $app, Thread $thread, User $user)
    {
        parent::__construct($app);
        $this->thread = $thread;
        $this->user = $user;
    }

    public function setClaimed(bool $claimed): void
    {
        $this->claimed = $claimed;
    }

    public function save()
    {
        if (!$this->thread->canWrxtFreshnessOwnerClaim())
        {
            throw new \LogicException('Permission denied');
        }

        $repository = $this->app->repository('WarextStudios\ThreadFreshness:ThreadFreshness');

        return $repository->withThreadLock((int)$this->thread->thread_id, function() use ($repository)
        {
            $referenceDate = (int)$this->thread->getWrxtFreshnessReferenceDate();
            $state = $repository->getOrCreateState((int)$this->thread->thread_id);
            if ((int)$state->reference_date !== $referenceDate || (int)$state->last_calculated_date < $referenceDate)
            {
                $state = $repository->recalculateThread(
                    (int)$this->thread->thread_id,
                    'owner_claim',
                    (int)$this->user->user_id
                );
            }

            $state->owner_claim_date = $this->claimed ? \XF::$time : 0;
            $state->save();

            return $state;
        });
    }
}
