<?php

require __DIR__ . '/../upload/src/addons/WarextStudios/ThreadFreshness/Util/Revalidation.php';

use WarextStudios\ThreadFreshness\Util\Revalidation;

$now = 2000000000;

if (!Revalidation::shouldRevalidate('current', $now - 181 * 86400, $now - 181 * 86400, 180, $now))
{
    fwrite(STDERR, "due revalidation failed\n");
    exit(1);
}

if (Revalidation::shouldRevalidate('current', $now - 181 * 86400, $now - 86400, 180, $now))
{
    fwrite(STDERR, "recent vote revalidation failed\n");
    exit(1);
}

if (Revalidation::shouldRevalidate('unverified', $now - 181 * 86400, $now - 181 * 86400, 180, $now))
{
    fwrite(STDERR, "invalid status revalidation failed\n");
    exit(1);
}

echo "OK\n";
