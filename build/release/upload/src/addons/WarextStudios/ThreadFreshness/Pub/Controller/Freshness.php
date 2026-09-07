<?php

namespace WarextStudios\ThreadFreshness\Pub\Controller;

use XF\Pub\Controller\AbstractController;

class Freshness extends AbstractController
{
    public function actionIndex()
    {
        $query = mb_substr(trim($this->filter('q', 'str')), 0, 100);
        $status = trim($this->filter('status', 'str'));

        $allowed = [
            'current',
            'likely_current',
            'mixed',
            'questionable',
            'not_working',
            'revalidating',
            'unverified',
            'all'
        ];

        if (!in_array($status, $allowed, true))
        {
            $status = 'current';
        }

        $results = $this->repository(
            'WarextStudios\ThreadFreshness:ThreadFreshness'
        )->searchVerifiedThreads($query, $status, 100);

        return $this->view(
            'WarextStudios\ThreadFreshness:Search',
            'wrxt_thread_freshness_search',
            [
                'results' => $results,
                'query' => $query,
                'status' => $status
            ]
        );
    }
}
