<?php

namespace WarextStudios\ThreadFreshness\Job;

use XF\Job\AbstractRebuildJob;

class Recalculate extends AbstractRebuildJob
{
    protected function getNextIds($start, $batch): array
    {
        $db = $this->app->db();

        return $db->fetchAllColumn(
            $db->limit(
                "SELECT s.thread_id
                FROM xf_wrxt_thread_freshness_state s
                INNER JOIN xf_thread t ON t.thread_id = s.thread_id
                INNER JOIN xf_forum f ON f.node_id = t.node_id
                WHERE s.thread_id > ?
                    AND t.discussion_state = 'visible'
                    AND f.wrxt_freshness_enabled = 1
                ORDER BY s.thread_id",
                max(1, (int)$batch)
            ),
            (int)$start
        );
    }

    protected function rebuildById($id): void
    {
        $this->app->repository('WarextStudios\ThreadFreshness:ThreadFreshness')
            ->recalculateThreadSafely((int)$id);
    }

    protected function getStatusType(): \XF\Phrase
    {
        return \XF::phrase('threads');
    }
}
