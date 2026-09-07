<?php

require __DIR__ . '/../upload/src/addons/WarextStudios/ThreadFreshness/Util/ModeratorStatus.php';

use WarextStudios\ThreadFreshness\Util\ModeratorStatus;

$cases = [
    ['current', '', 'current'],
    ['mixed', 'current', 'current'],
    ['current', 'not_working', 'not_working'],
    ['not_working', 'review', 'questionable'],
    ['', '', 'unverified']
];

foreach ($cases as [$community, $moderator, $expected])
{
    $actual = ModeratorStatus::effective($community, $moderator);
    if ($actual !== $expected)
    {
        fwrite(STDERR, "$community/$moderator => $actual, expected $expected\n");
        exit(1);
    }
}

if (ModeratorStatus::normalize('bad') !== '')
{
    fwrite(STDERR, "invalid moderator status accepted\n");
    exit(1);
}

echo "OK\n";
