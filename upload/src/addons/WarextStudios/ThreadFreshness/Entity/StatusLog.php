<?php

namespace WarextStudios\ThreadFreshness\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class StatusLog extends Entity
{
    protected function _preSave(): void
    {
        parent::_preSave();

        if ($this->isInsert() && !$this->log_date)
        {
            $this->log_date = \XF::$time;
        }
    }

    public static function getStructure(Structure $structure): Structure
    {
        $structure->table = 'xf_wrxt_thread_freshness_log';
        $structure->shortName = 'WarextStudios\ThreadFreshness:StatusLog';
        $structure->primaryKey = 'log_id';
        $structure->columns = [
            'log_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'thread_id' => ['type' => self::UINT, 'required' => true],
            'old_status' => ['type' => self::STR, 'maxLength' => 32, 'default' => ''],
            'new_status' => ['type' => self::STR, 'maxLength' => 32, 'default' => ''],
            'trigger_type' => ['type' => self::STR, 'maxLength' => 32, 'default' => 'system'],
            'user_id' => ['type' => self::UINT, 'default' => 0],
            'log_date' => ['type' => self::UINT, 'default' => 0]
        ];
        $structure->relations = [
            'Thread' => [
                'entity' => 'XF:Thread',
                'type' => self::TO_ONE,
                'conditions' => 'thread_id',
                'primary' => true
            ],
            'User' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => 'user_id',
                'primary' => true
            ]
        ];
        return $structure;
    }
}
