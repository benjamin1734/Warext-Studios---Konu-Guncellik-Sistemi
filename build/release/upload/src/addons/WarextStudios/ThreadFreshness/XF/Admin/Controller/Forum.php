<?php

namespace WarextStudios\ThreadFreshness\XF\Admin\Controller;

use XF\Entity\AbstractNode;
use XF\Entity\Node;
use XF\Mvc\FormAction;

class Forum extends XFCP_Forum
{
    protected function saveTypeData(FormAction $form, Node $node, AbstractNode $data)
    {
        parent::saveTypeData($form, $node, $data);

        $form->setup(function() use ($data)
        {
            $data->wrxt_freshness_enabled = $this->filter('wrxt_freshness_enabled', 'bool');
            $data->wrxt_freshness_days = max(1, min(3650, $this->filter('wrxt_freshness_days', 'uint')));
            $data->wrxt_freshness_versions = trim($this->filter('wrxt_freshness_versions', 'str'));
            $ageMode = trim($this->filter('wrxt_freshness_age_mode', 'str'));
            $data->wrxt_freshness_age_mode = in_array($ageMode, ['meaningful', 'last_post'], true) ? $ageMode : 'meaningful';
        });
    }
}
