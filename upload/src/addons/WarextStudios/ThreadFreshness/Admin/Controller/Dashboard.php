<?php

namespace WarextStudios\ThreadFreshness\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\ParameterBag;

class Dashboard extends AbstractController
{
    protected function preDispatchController($action, ParameterBag $params)
    {
        $this->setSectionContext('wrxtThreadFreshness');
    }

    public function actionIndex()
    {
        $repository = $this->repository('WarextStudios\ThreadFreshness:ThreadFreshness');

        return $this->view(
            'WarextStudios\ThreadFreshness:Dashboard',
            'wrxt_thread_freshness_dashboard',
            [
                'stats' => $repository->getDashboardStats(),
                'criticalThreads' => $repository->getCriticalThreads(50),
                'negativeFeedback' => $repository->getRecentNegativeFeedback(30)
            ]
        );
    }

    public function actionForums()
    {
        $this->assertAdminPermission('node');
        $db = $this->app()->db();
        $nodes = $db->fetchAll(
            "SELECT n.node_id, n.title, n.node_type_id, n.parent_node_id, n.depth, n.lft, n.rgt,
                    f.wrxt_freshness_enabled, f.wrxt_freshness_days, f.wrxt_freshness_age_mode
             FROM xf_node AS n
             LEFT JOIN xf_forum AS f ON (f.node_id = n.node_id)
             WHERE n.node_type_id IN ('Category', 'Forum')
             ORDER BY n.lft"
        );

        if ($this->isPost())
        {
            $forumIds = array_values(array_unique(array_map('intval', $this->filter('forum_ids', 'array-uint'))));
            $categoryIds = array_values(array_unique(array_map('intval', $this->filter('category_ids', 'array-uint'))));
            $days = max(1, min(3650, $this->filter('days', 'uint')));
            $ageMode = $this->filter('age_mode', 'str');
            $ageMode = in_array($ageMode, ['meaningful', 'last_post'], true) ? $ageMode : 'meaningful';
            $categoryMap = array_fill_keys($categoryIds, true);
            $categories = [];

            foreach ($nodes as $node)
            {
                if ($node['node_type_id'] === 'Category' && isset($categoryMap[(int)$node['node_id']]))
                {
                    $categories[] = $node;
                }
            }

            foreach ($nodes as $node)
            {
                if ($node['node_type_id'] !== 'Forum')
                {
                    continue;
                }

                foreach ($categories as $category)
                {
                    if ((int)$node['lft'] > (int)$category['lft'] && (int)$node['rgt'] < (int)$category['rgt'])
                    {
                        $forumIds[] = (int)$node['node_id'];
                        break;
                    }
                }
            }

            $forumIds = array_values(array_unique(array_filter($forumIds)));
            $db->beginTransaction();
            $db->query('UPDATE xf_forum SET wrxt_freshness_enabled = 0');

            if ($forumIds)
            {
                $idList = implode(',', array_map('intval', $forumIds));
                $db->query(
                    "UPDATE xf_forum
                     SET wrxt_freshness_enabled = 1, wrxt_freshness_days = ?, wrxt_freshness_age_mode = ?
                     WHERE node_id IN ($idList)",
                    [$days, $ageMode]
                );
            }

            $db->commit();

            return $this->redirect($this->buildLink('thread-freshness/forums'), \XF::phrase('changes_saved'));
        }

        $enabledForumIds = [];
        foreach ($nodes as $node)
        {
            if ($node['node_type_id'] === 'Forum' && !empty($node['wrxt_freshness_enabled']))
            {
                $enabledForumIds[(int)$node['node_id']] = true;
            }
        }

        foreach ($nodes as &$node)
        {
            $node['category_selected'] = false;
            $node['enabled_children'] = 0;
            $node['forum_children'] = 0;

            if ($node['node_type_id'] !== 'Category')
            {
                continue;
            }

            foreach ($nodes as $child)
            {
                if (
                    $child['node_type_id'] === 'Forum'
                    && (int)$child['lft'] > (int)$node['lft']
                    && (int)$child['rgt'] < (int)$node['rgt']
                )
                {
                    $node['forum_children']++;
                    if (isset($enabledForumIds[(int)$child['node_id']]))
                    {
                        $node['enabled_children']++;
                    }
                }
            }

            $node['category_selected'] = $node['forum_children'] > 0
                && $node['forum_children'] === $node['enabled_children'];
        }
        unset($node);

        $general = (array)(\XF::options()->wrxtFreshnessGeneral ?? []);

        return $this->view(
            'WarextStudios\ThreadFreshness:Forums',
            'wrxt_thread_freshness_forums',
            [
                'nodes' => $nodes,
                'days' => max(1, (int)($general['days'] ?? 90)),
                'ageMode' => 'meaningful'
            ]
        );
    }

    public function actionSettings()
    {
        $this->assertAdminPermission('option');

        return $this->redirect($this->buildLink('options/groups/wrxtThreadFreshness'));
    }
}
