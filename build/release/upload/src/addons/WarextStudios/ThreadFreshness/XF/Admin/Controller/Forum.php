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

        $input = $this->filter([
            'wrxt_freshness_enabled' => 'bool',
            'wrxt_freshness_days' => 'uint',
            'wrxt_freshness_versions' => 'str',
            'wrxt_freshness_age_mode' => 'str'
        ]);

        $input['wrxt_freshness_days'] = max(1, min(3650, (int)$input['wrxt_freshness_days']));
        $input['wrxt_freshness_versions'] = trim((string)$input['wrxt_freshness_versions']);
        $input['wrxt_freshness_age_mode'] = in_array(
            (string)$input['wrxt_freshness_age_mode'],
            ['meaningful', 'last_post'],
            true
        ) ? (string)$input['wrxt_freshness_age_mode'] : 'meaningful';

        $form->basicEntitySave($data, $input);
    }
}
