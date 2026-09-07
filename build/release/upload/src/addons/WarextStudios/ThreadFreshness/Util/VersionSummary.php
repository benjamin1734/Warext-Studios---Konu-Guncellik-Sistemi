<?php

namespace WarextStudios\ThreadFreshness\Util;

class VersionSummary
{
    public static function summarize(array $votes, int $now, int $limit = 8, array $rules = []): array
    {
        $groups = [];

        foreach ($votes as $vote)
        {
            $display = self::clean((string)($vote['version'] ?? ''));
            if ($display === '')
            {
                continue;
            }

            $key = mb_strtolower($display);
            if (!isset($groups[$key]))
            {
                $groups[$key] = [
                    'version' => $display,
                    'votes' => []
                ];
            }

            $groups[$key]['votes'][] = [
                'vote' => (int)($vote['vote'] ?? 0),
                'vote_date' => (int)($vote['vote_date'] ?? 0)
            ];
        }

        $summary = [];

        foreach ($groups as $group)
        {
            $result = StatusCalculator::calculate($group['votes'], $now, $rules);
            $result['version'] = $group['version'];
            $result['total_weight'] = (float)$result['positive_weight'] + (float)$result['negative_weight'];
            $summary[] = $result;
        }

        usort($summary, function(array $a, array $b): int
        {
            $weightCompare = $b['total_weight'] <=> $a['total_weight'];
            if ($weightCompare !== 0)
            {
                return $weightCompare;
            }

            $countCompare = $b['vote_count'] <=> $a['vote_count'];
            if ($countCompare !== 0)
            {
                return $countCompare;
            }

            return strnatcasecmp($a['version'], $b['version']);
        });

        return array_slice($summary, 0, max(1, $limit));
    }

    public static function clean(string $version): string
    {
        $version = preg_replace('/\s+/u', ' ', trim($version)) ?? '';
        return mb_substr($version, 0, 100);
    }
}
