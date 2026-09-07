<?php

namespace WarextStudios\ThreadFreshness\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class ThreadState extends Entity
{
    public static function getStructure(Structure $structure): Structure
    {
        $structure->table = 'xf_wrxt_thread_freshness_state';
        $structure->shortName = 'WarextStudios\ThreadFreshness:ThreadState';
        $structure->primaryKey = 'thread_id';
        $structure->columns = [
            'thread_id' => ['type' => self::UINT, 'required' => true],
            'status' => ['type' => self::STR, 'maxLength' => 32, 'default' => 'unverified'],
            'score' => ['type' => self::FLOAT, 'default' => 0],
            'positive_weight' => ['type' => self::FLOAT, 'default' => 0],
            'negative_weight' => ['type' => self::FLOAT, 'default' => 0],
            'vote_count' => ['type' => self::UINT, 'default' => 0],
            'positive_count' => ['type' => self::UINT, 'default' => 0],
            'negative_count' => ['type' => self::UINT, 'default' => 0],
            'last_vote_date' => ['type' => self::UINT, 'default' => 0],
            'last_calculated_date' => ['type' => self::UINT, 'default' => 0],
            'last_verified_date' => ['type' => self::UINT, 'default' => 0],
            'moderator_status' => ['type' => self::STR, 'maxLength' => 32, 'default' => ''],
            'moderator_user_id' => ['type' => self::UINT, 'default' => 0],
            'moderator_date' => ['type' => self::UINT, 'default' => 0],
            'reference_date' => ['type' => self::UINT, 'default' => 0],
            'owner_claim_date' => ['type' => self::UINT, 'default' => 0],
            'replacement_thread_id' => ['type' => self::UINT, 'default' => 0],
            'replacement_user_id' => ['type' => self::UINT, 'default' => 0],
            'replacement_date' => ['type' => self::UINT, 'default' => 0]
        ];
        $structure->relations = [
            'Thread' => [
                'entity' => 'XF:Thread',
                'type' => self::TO_ONE,
                'conditions' => 'thread_id',
                'primary' => true
            ],
            'Moderator' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => [['user_id', '=', '$moderator_user_id']],
                'primary' => true
            ],
            'ReplacementThread' => [
                'entity' => 'XF:Thread',
                'type' => self::TO_ONE,
                'conditions' => [['thread_id', '=', '$replacement_thread_id']],
                'primary' => true
            ],
            'ReplacementUser' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => [['user_id', '=', '$replacement_user_id']],
                'primary' => true
            ]
        ];
        return $structure;
    }
}
