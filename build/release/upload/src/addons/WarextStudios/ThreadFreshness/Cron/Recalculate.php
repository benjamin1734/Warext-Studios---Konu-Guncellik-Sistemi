<?php

namespace WarextStudios\ThreadFreshness\Cron;

class Recalculate
{
    public static function run(): void
    {
        \XF::app()->jobManager()->enqueueUnique(
            'wrxtThreadFreshnessRecalculate',
            'WarextStudios\ThreadFreshness:Recalculate',
            [],
            false
        );
    }
}
