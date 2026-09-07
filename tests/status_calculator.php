<?php

require __DIR__ . '/../upload/src/addons/WarextStudios/ThreadFreshness/Util/VoteWeight.php';
require __DIR__ . '/../upload/src/addons/WarextStudios/ThreadFreshness/Util/StatusCalculator.php';

use WarextStudios\ThreadFreshness\Util\StatusCalculator;

$now = 2000000000;
$old = $now - 500 * 86400;
$new = $now - 86400;

$votes = array_fill(0, 3, ['vote' => 1, 'vote_date' => $old]);
$result = StatusCalculator::calculate($votes, $now);
if ($result['status'] !== 'likely_current' || $result['vote_count'] !== 3)
{
    throw new RuntimeException('Raw vote threshold failed');
}

$votes = [];
for ($i = 0; $i < 4; $i++)
{
    $votes[] = ['vote' => 1, 'vote_date' => $new];
}
$votes[] = ['vote' => -1, 'vote_date' => $new];
$result = StatusCalculator::calculate($votes, $now);
if ($result['status'] !== 'current')
{
    throw new RuntimeException('Current threshold failed');
}

$votes = [['vote' => 1, 'vote_date' => $new]];
for ($i = 0; $i < 7; $i++)
{
    $votes[] = ['vote' => -1, 'vote_date' => $new];
}
$result = StatusCalculator::calculate($votes, $now);
if ($result['status'] !== 'not_working')
{
    throw new RuntimeException('Not-working threshold failed');
}

$custom = StatusCalculator::calculate(
    array_fill(0, 2, ['vote' => 1, 'vote_date' => $new]),
    $now,
    ['likely_min_votes' => 2]
);
if ($custom['status'] !== 'likely_current')
{
    throw new RuntimeException('Custom rules were not applied');
}

$normalized = StatusCalculator::normalizeRules([
    'likely_min_votes' => 10,
    'current_min_votes' => 2,
    'likely_min_percent' => 85,
    'current_min_percent' => 70,
    'questionable_min_votes' => 9,
    'not_working_min_votes' => 3,
    'questionable_no_percent' => 88,
    'not_working_no_percent' => 60
]);
if (
    $normalized['current_min_votes'] !== 10
    || $normalized['current_min_percent'] !== 85
    || $normalized['not_working_min_votes'] !== 9
    || $normalized['not_working_no_percent'] !== 88
)
{
    throw new RuntimeException('Rule ordering normalization failed');
}

$weighted = StatusCalculator::calculate([
    ['vote' => 1, 'vote_date' => $new],
    ['vote' => 1, 'vote_date' => $new],
    ['vote' => 1, 'vote_date' => $new],
    ['vote' => -1, 'vote_date' => $old],
    ['vote' => -1, 'vote_date' => $old]
], $now);
if ($weighted['vote_count'] !== 5 || $weighted['score'] <= 0.8)
{
    throw new RuntimeException('Raw count / weighted percentage behavior is invalid');
}

echo "OK\n";
