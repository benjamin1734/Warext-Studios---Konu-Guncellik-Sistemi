<?php

namespace WarextStudios\ThreadFreshness\Util;

class FailureReason
{
    public const ALLOWED = [
        '',
        'outdated_version',
        'dead_links',
        'method_invalid',
        'incomplete',
        'did_not_work',
        'other'
    ];

    public static function isValid(string $reason): bool
    {
        return in_array(trim($reason), self::ALLOWED, true);
    }

    public static function normalize(string $reason): string
    {
        $reason = trim($reason);
        return self::isValid($reason) ? $reason : '';
    }
}
