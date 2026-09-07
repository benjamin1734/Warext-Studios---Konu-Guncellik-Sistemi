<?php

namespace WarextStudios\ThreadFreshness\Util;

class Revalidation
{
    public static function shouldRevalidate(
        string $status,
        int $lastVerifiedDate,
        int $lastVoteDate,
        int $days,
        int $now
    ): bool
    {
        if (!in_array($status, ['current', 'likely_current'], true))
        {
            return false;
        }

        if ($lastVerifiedDate <= 0)
        {
            return false;
        }

        $days = max(1, $days);
        if ($lastVerifiedDate > $now - ($days * 86400))
        {
            return false;
        }

        return $lastVoteDate <= $lastVerifiedDate;
    }
}
