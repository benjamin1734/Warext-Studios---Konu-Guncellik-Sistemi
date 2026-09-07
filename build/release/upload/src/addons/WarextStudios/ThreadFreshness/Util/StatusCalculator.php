<?php

namespace WarextStudios\ThreadFreshness\Util;

class StatusCalculator
{
    public static function defaults(): array
    {
        return [
            'likely_min_votes' => 3,
            'likely_min_percent' => 75,
            'current_min_votes' => 5,
            'current_min_percent' => 80,
            'questionable_min_votes' => 5,
            'questionable_no_percent' => 70,
            'not_working_min_votes' => 8,
            'not_working_no_percent' => 80,
            'mixed_min_votes' => 3,
            'mixed_low_percent' => 40,
            'mixed_high_percent' => 60
        ];
    }

    public static function normalizeRules(array $rules): array
    {
        $rules = array_replace(self::defaults(), $rules);

        foreach (['likely_min_votes', 'current_min_votes', 'questionable_min_votes', 'not_working_min_votes', 'mixed_min_votes'] as $key)
        {
            $rules[$key] = max(1, min(1000, (int)$rules[$key]));
        }

        foreach (['likely_min_percent', 'current_min_percent', 'questionable_no_percent', 'not_working_no_percent', 'mixed_low_percent', 'mixed_high_percent'] as $key)
        {
            $rules[$key] = max(0, min(100, (int)$rules[$key]));
        }

        if ($rules['mixed_low_percent'] > $rules['mixed_high_percent'])
        {
            [$rules['mixed_low_percent'], $rules['mixed_high_percent']] = [$rules['mixed_high_percent'], $rules['mixed_low_percent']];
        }

        $rules['current_min_votes'] = max($rules['current_min_votes'], $rules['likely_min_votes']);
        $rules['current_min_percent'] = max($rules['current_min_percent'], $rules['likely_min_percent']);
        $rules['not_working_min_votes'] = max($rules['not_working_min_votes'], $rules['questionable_min_votes']);
        $rules['not_working_no_percent'] = max($rules['not_working_no_percent'], $rules['questionable_no_percent']);

        return $rules;
    }

    public static function calculate(array $votes, int $now, array $rules = []): array
    {
        $rules = self::normalizeRules($rules);
        $positiveWeight = 0.0;
        $negativeWeight = 0.0;
        $positiveCount = 0;
        $negativeCount = 0;
        $lastVoteDate = 0;

        foreach ($votes as $vote)
        {
            $value = (int)$vote['vote'];
            if ($value !== 1 && $value !== -1)
            {
                continue;
            }

            $date = max(0, (int)$vote['vote_date']);
            $weight = VoteWeight::forAge($date, $now);
            $lastVoteDate = max($lastVoteDate, $date);

            if ($value === 1)
            {
                $positiveCount++;
                $positiveWeight += $weight;
            }
            else
            {
                $negativeCount++;
                $negativeWeight += $weight;
            }
        }

        $voteCount = $positiveCount + $negativeCount;
        $totalWeight = $positiveWeight + $negativeWeight;
        $score = $totalWeight > 0 ? $positiveWeight / $totalWeight : 0.0;
        $yesPercent = $score * 100;
        $noPercent = 100 - $yesPercent;
        $status = 'unverified';

        if ($voteCount >= $rules['not_working_min_votes'] && $noPercent >= $rules['not_working_no_percent'])
        {
            $status = 'not_working';
        }
        elseif ($voteCount >= $rules['questionable_min_votes'] && $noPercent >= $rules['questionable_no_percent'])
        {
            $status = 'questionable';
        }
        elseif ($voteCount >= $rules['current_min_votes'] && $yesPercent >= $rules['current_min_percent'])
        {
            $status = 'current';
        }
        elseif ($voteCount >= $rules['likely_min_votes'] && $yesPercent >= $rules['likely_min_percent'])
        {
            $status = 'likely_current';
        }
        elseif (
            $voteCount >= $rules['mixed_min_votes']
            && $yesPercent >= $rules['mixed_low_percent']
            && $yesPercent <= $rules['mixed_high_percent']
        )
        {
            $status = 'mixed';
        }

        return [
            'status' => $status,
            'score' => $score,
            'positive_weight' => $positiveWeight,
            'negative_weight' => $negativeWeight,
            'vote_count' => $voteCount,
            'positive_count' => $positiveCount,
            'negative_count' => $negativeCount,
            'last_vote_date' => $lastVoteDate
        ];
    }
}
