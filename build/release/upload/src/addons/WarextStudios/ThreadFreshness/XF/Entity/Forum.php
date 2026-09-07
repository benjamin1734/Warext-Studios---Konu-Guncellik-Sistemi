<?php

namespace WarextStudios\ThreadFreshness\XF\Entity;

use XF\Mvc\Entity\Structure;

class Forum extends XFCP_Forum
{
    public static function getStructure(Structure $structure): Structure
    {
        $structure = parent::getStructure($structure);
        $structure->columns['wrxt_freshness_enabled'] = ['type' => self::BOOL, 'default' => false];
        $structure->columns['wrxt_freshness_days'] = ['type' => self::UINT, 'default' => 90, 'min' => 1, 'max' => 3650];
        $structure->columns['wrxt_freshness_versions'] = ['type' => self::STR, 'default' => '', 'nullable' => true];
        $structure->columns['wrxt_freshness_age_mode'] = ['type' => self::STR, 'default' => 'meaningful', 'maxLength' => 20];

        return $structure;
    }

    public function getWrxtFreshnessAgeMode(): string
    {
        return in_array($this->wrxt_freshness_age_mode, ['meaningful', 'last_post'], true)
            ? $this->wrxt_freshness_age_mode
            : 'meaningful';
    }

    public function getWrxtFreshnessVersions(): array
    {
        $lines = preg_split('/\R/u', (string)$this->wrxt_freshness_versions) ?: [];
        $versions = [];

        foreach ($lines as $line)
        {
            $version = trim($line);
            if ($version === '')
            {
                continue;
            }

            $key = mb_strtolower($version);
            if (!isset($versions[$key]))
            {
                $versions[$key] = mb_substr($version, 0, 100);
            }
        }

        return array_values($versions);
    }
}
