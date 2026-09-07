<?php

namespace WarextStudios\ThreadFreshness\Service\ThreadFreshness;

use WarextStudios\ThreadFreshness\Util\ModeratorStatus;
use XF\Entity\Thread;
use XF\Entity\User;
use XF\Service\AbstractService;

class Moderate extends AbstractService
{
    protected Thread $thread;
    protected User $user;
    protected string $status = '';

    public function __construct(\XF\App $app, Thread $thread, User $user)
    {
        parent::__construct($app);
        $this->thread = $thread;
        $this->user = $user;
    }

    public function setStatus(string $status): void
    {
        $normalized = ModeratorStatus::normalize($status);
        if ($normalized !== trim($status))
        {
            throw new \InvalidArgumentException('Invalid moderator status');
        }

        $this->status = $normalized;
    }

    public function save()
    {
        if (!$this->thread->thread_id || !$this->user->user_id)
        {
            throw new \LogicException('Thread and user must exist');
        }
        if (!$this->thread->canWrxtFreshnessModerate())
        {
            throw new \LogicException('Permission denied');
        }
        if (!$this->user->hasPermission('wrxtFreshness', 'moderate'))
        {
            throw new \LogicException('Permission denied');
        }

        $repository = $this->repository();

        return $repository->withThreadLock((int)$this->thread->thread_id, function() use ($repository)
        {
            $state = $repository->getOrCreateState((int)$this->thread->thread_id);
            $referenceDate = (int)$this->thread->getWrxtFreshnessReferenceDate();
            if ((int)$state->reference_date !== $referenceDate || (int)$state->last_calculated_date < $referenceDate)
            {
                $state = $repository->recalculateThread(
                    (int)$this->thread->thread_id,
                    'moderator_prepare',
                    (int)$this->user->user_id
                );
            }

            $oldStatus = $repository->getEffectiveStatus($state);
            $state->moderator_status = $this->status;
            $state->moderator_user_id = $this->status === '' ? 0 : (int)$this->user->user_id;
            $state->moderator_date = $this->status === '' ? 0 : \XF::$time;
            $state->save();

            $newStatus = $repository->getEffectiveStatus($state);
            $repository->logStatusChange(
                (int)$this->thread->thread_id,
                $oldStatus,
                $newStatus,
                'moderator',
                (int)$this->user->user_id
            );
            $repository->notifyStatusChange(
                (int)$this->thread->thread_id,
                $oldStatus,
                $newStatus,
                'moderator',
                (int)$this->user->user_id
            );

            return $state;
        });
    }

    protected function repository(): \WarextStudios\ThreadFreshness\Repository\ThreadFreshness
    {
        return $this->app->repository('WarextStudios\ThreadFreshness:ThreadFreshness');
    }
}
