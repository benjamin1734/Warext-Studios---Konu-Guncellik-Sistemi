<?php

namespace WarextStudios\ThreadFreshness\Util;

class NotificationStatus
{
    public static function shouldNotify(string $oldStatus, string $newStatus): bool
    {
        if ($oldStatus === $newStatus)
        {
            return false;
        }

        $critical = ['questionable', 'not_working', 'revalidating'];

        if (in_array($newStatus, $critical, true))
        {
            return true;
        }

        return in_array($oldStatus, $critical, true)
            && in_array($newStatus, ['current', 'likely_current'], true);
    }
}
