<?php

namespace WarextStudios\ThreadFreshness\Cron;

class Cleanup
{
    public static function run(): void
    {
        \XF::app()->repository('WarextStudios\ThreadFreshness:ThreadFreshness')
            ->cleanupOrphans(1000);
    }
}
