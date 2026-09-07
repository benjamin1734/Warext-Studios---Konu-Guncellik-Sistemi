<?php

namespace WarextStudios\ThreadFreshness\Service\ThreadFreshness;

use WarextStudios\ThreadFreshness\Util\FailureReason;
use XF\Entity\Thread;
use XF\Entity\User;
use XF\Service\AbstractService;

class Vote extends AbstractService
{
    protected Thread $thread;
    protected User $user;
    protected int $vote = 0;
    protected string $reason = '';
    protected string $version = '';
    protected string $message = '';
    protected int $alternativeThreadId = 0;

    public function __construct(\XF\App $app, Thread $thread, User $user)
    {
        parent::__construct($app);
        $this->thread = $thread;
        $this->user = $user;
    }

    public function setVote(int $vote): void
    {
        if (!in_array($vote, [-1, 1], true))
        {
            throw new \InvalidArgumentException('Invalid vote');
        }
        $this->vote = $vote;
    }

    public function setReason(string $reason): void
    {
        $reason = trim($reason);
        if (!FailureReason::isValid($reason))
        {
            throw new \InvalidArgumentException('Invalid reason');
        }
        $this->reason = $reason;
    }

    public function setVersion(string $version): void
    {
        $this->version = mb_substr(trim($version), 0, 100);
    }

    public function setMessage(string $message): void
    {
        $this->message = mb_substr(trim($message), 0, 500);
    }

    public function setAlternativeThreadId(int $threadId): void
    {
        $this->alternativeThreadId = max(0, $threadId);
    }

    public function save()
    {
        if (!$this->thread->thread_id || !$this->user->user_id)
        {
            throw new \LogicException('Thread and user must exist');
        }
        if (!in_array($this->vote, [-1, 1], true))
        {
            throw new \LogicException('Vote is required');
        }
        if (!$this->thread->canWrxtFreshnessVote())
        {
            throw new \LogicException('Permission denied');
        }
        if (!$this->user->hasPermission('wrxtFreshness', 'vote'))
        {
            throw new \LogicException('Permission denied');
        }

        $ownThread = (int)$this->thread->user_id === (int)$this->user->user_id;
        if ($ownThread && !(
            (bool)(\XF::options()->wrxtFreshnessAllowOwnThread ?? false)
            && $this->user->hasPermission('wrxtFreshness', 'voteOwn')
        ))
        {
            throw new \LogicException('Permission denied');
        }

        if ($this->vote === 1)
        {
            $this->reason = '';
            $this->alternativeThreadId = 0;
        }
        else
        {
            $this->assertAlternativeThreadIsValid();
        }

        $repository = $this->repository();

        return $repository->withThreadLock((int)$this->thread->thread_id, function() use ($repository)
        {
            $em = $this->app->em();
            $entity = $this->app->finder('WarextStudios\ThreadFreshness:Vote')
                ->where('thread_id', $this->thread->thread_id)
                ->where('user_id', $this->user->user_id)
                ->fetchOne();

            if ($entity)
            {
                $voteDate = max((int)$entity->vote_date, (int)$entity->updated_date);
                $isStaleCycleVote = $voteDate < (int)$this->thread->getWrxtFreshnessReferenceDate();
                if (!$isStaleCycleVote && !$this->user->hasPermission('wrxtFreshness', 'changeVote'))
                {
                    throw new \LogicException('Permission denied');
                }
            }
            else
            {
                $entity = $em->create('WarextStudios\ThreadFreshness:Vote');
                $entity->thread_id = $this->thread->thread_id;
                $entity->user_id = $this->user->user_id;
            }

            $entity->vote = $this->vote;
            $entity->reason = $this->reason;
            $entity->version = $this->version;
            $entity->message = $this->message;
            $entity->alternative_thread_id = $this->alternativeThreadId;
            $entity->save();

            $repository->recalculateThread(
                (int)$this->thread->thread_id,
                'vote',
                (int)$this->user->user_id
            );

            return $entity;
        });
    }

    protected function assertAlternativeThreadIsValid(): void
    {
        if ($this->alternativeThreadId === 0)
        {
            return;
        }

        if ($this->alternativeThreadId === (int)$this->thread->thread_id)
        {
            throw new \InvalidArgumentException('Invalid alternative thread');
        }

        $alternative = $this->app->em()->find('XF:Thread', $this->alternativeThreadId);
        if (
            !$alternative
            || $alternative->discussion_state !== 'visible'
            || $alternative->discussion_type === 'redirect'
            || !$alternative->canView()
            || (int)$alternative->post_date < (int)$this->thread->post_date
        )
        {
            throw new \InvalidArgumentException('Invalid alternative thread');
        }
    }

    protected function repository(): \WarextStudios\ThreadFreshness\Repository\ThreadFreshness
    {
        return $this->app->repository('WarextStudios\ThreadFreshness:ThreadFreshness');
    }
}
