<?php

namespace WarextStudios\ThreadFreshness\Service\ThreadFreshness;

use WarextStudios\ThreadFreshness\Util\NotificationStatus;
use XF\Service\AbstractService;

class Notifier extends AbstractService
{
    public function notify(
        int $threadId,
        string $oldStatus,
        string $newStatus,
        string $triggerType,
        int $actorUserId = 0
    ): void
    {
        if (!(bool)(\XF::options()->wrxtFreshnessNotifyOwner ?? true))
        {
            return;
        }

        if (!NotificationStatus::shouldNotify($oldStatus, $newStatus))
        {
            return;
        }

        $thread = $this->app->em()->find('XF:Thread', $threadId);
        if (!$thread || !$thread->user_id)
        {
            return;
        }

        if ($actorUserId > 0 && $actorUserId === (int)$thread->user_id)
        {
            return;
        }

        $owner = $this->app->em()->find('XF:User', (int)$thread->user_id);
        if (!$owner)
        {
            return;
        }

        $sender = $actorUserId > 0
            ? $this->app->em()->find('XF:User', $actorUserId)
            : null;

        try
        {
            $this->app->repository('XF:UserAlert')->alertFromUser(
                $owner,
                $sender,
                'thread',
                $threadId,
                'wrxt_freshness_status',
                [
                    'old_status' => $oldStatus,
                    'status' => $newStatus,
                    'trigger_type' => $triggerType,
                    'depends_on_addon_id' => 'WarextStudios/ThreadFreshness'
                ]
            );
        }
        catch (\Throwable $e)
        {
            \XF::logException($e, false);
        }
    }
}
