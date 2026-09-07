<?php

namespace WarextStudios\ThreadFreshness\XF\Pub\Controller;

use XF\Entity\Forum as ForumEntity;
use XF\Finder\Thread as ThreadFinder;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;

class Forum extends XFCP_Forum
{
    public function actionForum(ParameterBag $params)
    {
        $reply = parent::actionForum($params);

        if ($reply instanceof View)
        {
            $forum = $reply->getParam('forum');
            if ($forum && $forum->wrxt_freshness_enabled)
            {
                $this->repository('WarextStudios\ThreadFreshness:ThreadFreshness')->preloadThreadData(
                    $reply->getParam('threads'),
                    $reply->getParam('stickyThreads')
                );
            }
        }

        return $reply;
    }

    protected function getForumFilterInput(ForumEntity $forum)
    {
        $filters = parent::getForumFilterInput($forum);

        if ($forum->wrxt_freshness_enabled)
        {
            $status = trim($this->filter('freshness_status', 'str'));
            if (in_array($status, [
                'current',
                'likely_current',
                'mixed',
                'questionable',
                'not_working',
                'revalidating',
                'unverified'
            ], true))
            {
                $filters['freshness_status'] = $status;
            }
        }

        return $filters;
    }

    protected function applyForumFilters(ForumEntity $forum, ThreadFinder $threadFinder, array $filters)
    {
        parent::applyForumFilters($forum, $threadFinder, $filters);

        if (!$forum->wrxt_freshness_enabled || empty($filters['freshness_status']))
        {
            return;
        }

        $status = $filters['freshness_status'];
        $threadFinder->with('WrxtFreshnessState', false);

        $stateThreadId = $threadFinder->columnSqlName('WrxtFreshnessState.thread_id');
        $stateReference = $threadFinder->columnSqlName('WrxtFreshnessState.reference_date');
        $stateModerator = $threadFinder->columnSqlName('WrxtFreshnessState.moderator_status');
        $stateCalculated = $threadFinder->columnSqlName('WrxtFreshnessState.last_calculated_date');
        $stateStatus = $threadFinder->columnSqlName('WrxtFreshnessState.status');
        $threadId = $threadFinder->columnSqlName('thread_id');
        $threadUserId = $threadFinder->columnSqlName('user_id');
        $firstPostId = $threadFinder->columnSqlName('first_post_id');
        $lastPostDate = $threadFinder->columnSqlName('last_post_date');

        if ($forum->getWrxtFreshnessAgeMode() === 'meaningful')
        {
            $referenceSql = "COALESCE((
                SELECT MAX(GREATEST(wp.post_date, wp.last_edit_date))
                FROM xf_post wp
                LEFT JOIN xf_thread_question wtq ON wtq.thread_id = wp.thread_id
                WHERE wp.thread_id = {$threadId}
                    AND wp.message_state = 'visible'
                    AND (
                        wp.post_id = {$firstPostId}
                        OR ({$threadUserId} > 0 AND wp.user_id = {$threadUserId})
                        OR (wtq.solution_post_id > 0 AND wp.post_id = wtq.solution_post_id)
                    )
            ), {$lastPostDate})";
        }
        else
        {
            $referenceSql = $lastPostDate;
        }

        $cutoff = \XF::$time - (max(1, (int)$forum->wrxt_freshness_days) * 86400);
        $threadFinder->whereSql("{$referenceSql} <= " . (int)$cutoff);

        if ($status === 'unverified')
        {
            $threadFinder->whereSql(
                "({$stateThreadId} IS NULL OR {$stateReference} <> {$referenceSql} OR {$stateCalculated} < {$referenceSql} OR ({$stateModerator} = '' AND {$stateStatus} = 'unverified'))"
            );
            return;
        }

        $threadFinder->whereSql("{$stateReference} = {$referenceSql} AND {$stateCalculated} >= {$referenceSql}");

        if ($status === 'current')
        {
            $threadFinder->whereOr(
                ['WrxtFreshnessState.moderator_status', '=', 'current'],
                [
                    ['WrxtFreshnessState.moderator_status', '=', ''],
                    ['WrxtFreshnessState.status', '=', 'current']
                ]
            );
            return;
        }

        if ($status === 'questionable')
        {
            $threadFinder->whereOr(
                ['WrxtFreshnessState.moderator_status', '=', 'review'],
                [
                    ['WrxtFreshnessState.moderator_status', '=', ''],
                    ['WrxtFreshnessState.status', '=', 'questionable']
                ]
            );
            return;
        }

        if ($status === 'not_working')
        {
            $threadFinder->whereOr(
                ['WrxtFreshnessState.moderator_status', '=', 'not_working'],
                [
                    ['WrxtFreshnessState.moderator_status', '=', ''],
                    ['WrxtFreshnessState.status', '=', 'not_working']
                ]
            );
            return;
        }

        $threadFinder
            ->where('WrxtFreshnessState.moderator_status', '')
            ->where('WrxtFreshnessState.status', $status);
    }
}
