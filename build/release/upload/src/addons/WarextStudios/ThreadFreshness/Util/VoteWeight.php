<?php

namespace WarextStudios\ThreadFreshness\Util;

class VoteWeight
{
    public static function forAge(int $voteDate, int $now): float
    {
        $age = max(0, $now - $voteDate);
        if ($age <= 90 * 86400)
        {
            return 1.0;
        }
        if ($age <= 180 * 86400)
        {
            return 0.75;
        }
        if ($age <= 365 * 86400)
        {
            return 0.5;
        }
        return 0.25;
    }
}
