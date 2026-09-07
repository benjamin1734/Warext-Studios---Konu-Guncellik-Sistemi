<?php

require __DIR__ . '/../upload/src/addons/WarextStudios/ThreadFreshness/Util/NotificationStatus.php';

use WarextStudios\ThreadFreshness\Util\NotificationStatus;

$cases = [
    ['current', 'current', false],
    ['current', 'questionable', true],
    ['questionable', 'not_working', true],
    ['not_working', 'current', true],
    ['revalidating', 'likely_current', true],
    ['unverified', 'current', false],
    ['current', 'mixed', false]
];

foreach ($cases as [$old, $new, $expected])
{
    if (NotificationStatus::shouldNotify($old, $new) !== $expected)
    {
        fwrite(STDERR, "{$old} -> {$new} failed\n");
        exit(1);
    }
}

echo "OK\n";
