<?php

namespace WarextStudios\ThreadFreshness\Util;

class ModeratorStatus
{
    public static function normalize(string $status): string
    {
        $status = trim($status);

        return in_array($status, ['', 'current', 'not_working', 'review'], true) ? $status : '';
    }

    public static function effective(string $communityStatus, string $moderatorStatus): string
    {
        return match (self::normalize($moderatorStatus))
        {
            'current' => 'current',
            'not_working' => 'not_working',
            'review' => 'questionable',
            default => $communityStatus !== '' ? $communityStatus : 'unverified'
        };
    }
}
