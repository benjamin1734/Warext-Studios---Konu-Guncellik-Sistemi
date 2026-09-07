<?php

namespace WarextStudios\ThreadFreshness\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Vote extends Entity
{
    protected function _preSave(): void
    {
        parent::_preSave();

        if ($this->isInsert())
        {
            $this->vote_date = \XF::$time;
        }
        else
        {
            $this->updated_date = \XF::$time;
        }
    }

    public static function getStructure(Structure $structure): Structure
    {
        $structure->table = 'xf_wrxt_thread_freshness_vote';
        $structure->shortName = 'WarextStudios\ThreadFreshness:Vote';
        $structure->primaryKey = 'vote_id';
        $structure->columns = [
            'vote_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'thread_id' => ['type' => self::UINT, 'required' => true],
            'user_id' => ['type' => self::UINT, 'required' => true],
            'vote' => ['type' => self::INT, 'required' => true, 'min' => -1, 'max' => 1],
            'reason' => ['type' => self::STR, 'maxLength' => 64, 'default' => ''],
            'version' => ['type' => self::STR, 'maxLength' => 100, 'default' => ''],
            'message' => ['type' => self::STR, 'maxLength' => 500, 'default' => ''],
            'vote_date' => ['type' => self::UINT, 'default' => 0],
            'updated_date' => ['type' => self::UINT, 'default' => 0],
            'alternative_thread_id' => ['type' => self::UINT, 'default' => 0]
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
            ],
            'AlternativeThread' => [
                'entity' => 'XF:Thread',
                'type' => self::TO_ONE,
                'conditions' => [['thread_id', '=', '$alternative_thread_id']],
                'primary' => true
            ]
        ];
        return $structure;
    }
}
