<?php

namespace WarextStudios\ThreadFreshness\Util;

class Eligibility
{
    public static function parseForumIds(string $value): array
    {
        $parts = preg_split('/[\s,;]+/', trim($value)) ?: [];
        $ids = [];

        foreach ($parts as $part)
        {
            $id = (int)$part;
            if ($id > 0)
            {
                $ids[$id] = $id;
            }
        }

        $ids = array_values($ids);
        sort($ids, SORT_NUMERIC);

        return $ids;
    }

    public static function isThreadEligible(
        bool $enabled,
        int $nodeId,
        int $referenceDate,
        array $forumIds,
        int $days,
        int $now
    ): bool
    {
        if (!$enabled || $nodeId <= 0 || $referenceDate <= 0)
        {
            return false;
        }

        if (!in_array($nodeId, $forumIds, true))
        {
            return false;
        }

        $days = max(1, $days);

        return $referenceDate <= $now - ($days * 86400);
    }

    public static function canVisitorVote(
        int $visitorId,
        int $threadUserId,
        int $registerDate,
        int $messageCount,
        bool $hasPermission,
        bool $allowOwnThread,
        int $minAccountDays,
        int $minMessages,
        int $now
    ): bool
    {
        if ($visitorId <= 0 || !$hasPermission)
        {
            return false;
        }

        if (!$allowOwnThread && $threadUserId > 0 && $visitorId === $threadUserId)
        {
            return false;
        }

        if ($messageCount < max(0, $minMessages))
        {
            return false;
        }

        $minAccountDays = max(0, $minAccountDays);
        if ($minAccountDays > 0)
        {
            if ($registerDate <= 0 || $registerDate > $now - ($minAccountDays * 86400))
            {
                return false;
            }
        }

        return true;
    }
}
