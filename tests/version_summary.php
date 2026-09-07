<?php

if (!function_exists('mb_substr'))
{
    function mb_substr(string $string, int $start, ?int $length = null): string
    {
        return $length === null ? substr($string, $start) : substr($string, $start, $length);
    }
}

if (!function_exists('mb_strtolower'))
{
    function mb_strtolower(string $string): string
    {
        return strtolower($string);
    }
}

require __DIR__ . '/../upload/src/addons/WarextStudios/ThreadFreshness/Util/VoteWeight.php';
require __DIR__ . '/../upload/src/addons/WarextStudios/ThreadFreshness/Util/StatusCalculator.php';
require __DIR__ . '/../upload/src/addons/WarextStudios/ThreadFreshness/Util/VersionSummary.php';

use WarextStudios\ThreadFreshness\Util\VersionSummary;

$now = 2000000000;
$votes = [];

for ($i = 0; $i < 5; $i++)
{
    $votes[] = ['vote' => 1, 'vote_date' => $now - 86400, 'version' => '2.3.0'];
}

for ($i = 0; $i < 4; $i++)
{
    $votes[] = ['vote' => -1, 'vote_date' => $now - 86400, 'version' => '2.2.0'];
}

$summary = VersionSummary::summarize($votes, $now);

if (count($summary) !== 2)
{
    fwrite(STDERR, "version count failed\n");
    exit(1);
}

$map = [];
foreach ($summary as $row)
{
    $map[$row['version']] = $row;
}

if (($map['2.3.0']['status'] ?? '') !== 'current')
{
    fwrite(STDERR, "positive version status failed\n");
    exit(1);
}

if (($map['2.2.0']['negative_count'] ?? 0) !== 4)
{
    fwrite(STDERR, "negative version count failed\n");
    exit(1);
}

echo "OK\n";
