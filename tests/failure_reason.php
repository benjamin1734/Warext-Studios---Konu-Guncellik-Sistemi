<?php

require __DIR__ . '/../upload/src/addons/WarextStudios/ThreadFreshness/Util/FailureReason.php';

use WarextStudios\ThreadFreshness\Util\FailureReason;

foreach (FailureReason::ALLOWED as $reason)
{
    if (FailureReason::normalize($reason) !== $reason)
    {
        throw new RuntimeException('Allowed reason rejected: ' . $reason);
    }
}

if (FailureReason::normalize('javascript:alert(1)') !== '')
{
    throw new RuntimeException('Unknown reason was accepted');
}

echo "OK\n";
