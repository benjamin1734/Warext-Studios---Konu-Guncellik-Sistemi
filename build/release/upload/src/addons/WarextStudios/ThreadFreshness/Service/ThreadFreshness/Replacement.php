<?php

namespace WarextStudios\ThreadFreshness\Service\ThreadFreshness;

use XF\Entity\Thread;
use XF\Entity\User;
use XF\Service\AbstractService;

class Replacement extends AbstractService
{
    protected Thread $thread;
    protected User $user;
    protected int $replacementThreadId = 0;

    public function __construct(\XF\App $app, Thread $thread, User $user)
    {
        parent::__construct($app);
        $this->thread = $thread;
        $this->user = $user;
    }

    public function setReplacementThreadId(int $threadId): void
    {
        $this->replacementThreadId = max(0, $threadId);
    }

    public function save()
    {
        if (!$this->thread->thread_id || !$this->user->user_id)
        {
            throw new \LogicException('Thread and user must exist');
        }
        if (!$this->thread->canWrxtFreshnessManageReplacement())
        {
            throw new \LogicException('Permission denied');
        }

        $repository = $this->repository();

        return $repository->withThreadLock((int)$this->thread->thread_id, function() use ($repository)
        {
            $state = $repository->getOrCreateState((int)$this->thread->thread_id);

            if ($this->replacementThreadId === 0)
            {
                $state->replacement_thread_id = 0;
                $state->replacement_user_id = 0;
                $state->replacement_date = 0;
                $state->save();
                return $state;
            }

            if ($this->replacementThreadId === (int)$this->thread->thread_id)
            {
                throw new \InvalidArgumentException('Replacement thread cannot be the same thread');
            }

            $replacement = $this->app->em()->find('XF:Thread', $this->replacementThreadId);
            if (
                !$replacement
                || $replacement->discussion_state !== 'visible'
                || $replacement->discussion_type === 'redirect'
                || !$replacement->canView()
                || (int)$replacement->post_date < (int)$this->thread->post_date
            )
            {
                throw new \InvalidArgumentException('Replacement thread is not available');
            }

            $state->replacement_thread_id = (int)$replacement->thread_id;
            $state->replacement_user_id = (int)$this->user->user_id;
            $state->replacement_date = \XF::$time;
            $state->save();

            return $state;
        });
    }

    protected function repository(): \WarextStudios\ThreadFreshness\Repository\ThreadFreshness
    {
        return $this->app->repository('WarextStudios\ThreadFreshness:ThreadFreshness');
    }
}
