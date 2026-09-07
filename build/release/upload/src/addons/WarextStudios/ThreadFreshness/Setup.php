<?php

namespace WarextStudios\ThreadFreshness;

use WarextStudios\ThreadFreshness\Util\Eligibility;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1(): void
    {
        $this->schemaManager()->createTable('xf_wrxt_thread_freshness_state', function(Create $table)
        {
            $table->addColumn('thread_id', 'int')->unsigned();
            $table->addColumn('status', 'varchar', 32)->setDefault('unverified');
            $table->addColumn('score', 'decimal', '8,4')->setDefault(0);
            $table->addColumn('positive_weight', 'decimal', '10,4')->setDefault(0);
            $table->addColumn('negative_weight', 'decimal', '10,4')->setDefault(0);
            $table->addColumn('vote_count', 'int')->unsigned()->setDefault(0);
            $table->addColumn('positive_count', 'int')->unsigned()->setDefault(0);
            $table->addColumn('negative_count', 'int')->unsigned()->setDefault(0);
            $table->addColumn('last_vote_date', 'int')->unsigned()->setDefault(0);
            $table->addColumn('last_calculated_date', 'int')->unsigned()->setDefault(0);
            $table->addColumn('last_verified_date', 'int')->unsigned()->setDefault(0);
            $table->addColumn('moderator_status', 'varchar', 32)->setDefault('');
            $table->addColumn('moderator_user_id', 'int')->unsigned()->setDefault(0);
            $table->addColumn('moderator_date', 'int')->unsigned()->setDefault(0);
            $table->addColumn('reference_date', 'int')->unsigned()->setDefault(0);
            $table->addColumn('owner_claim_date', 'int')->unsigned()->setDefault(0);
            $table->addColumn('replacement_thread_id', 'int')->unsigned()->setDefault(0);
            $table->addColumn('replacement_user_id', 'int')->unsigned()->setDefault(0);
            $table->addColumn('replacement_date', 'int')->unsigned()->setDefault(0);
            $table->addPrimaryKey('thread_id');
            $table->addKey('status');
            $table->addKey('last_calculated_date');
            $table->addKey('reference_date');
            $table->addKey('replacement_thread_id');
            $table->addKey('moderator_user_id');
            $table->addKey('replacement_user_id');
        });
    }

    public function installStep2(): void
    {
        $this->schemaManager()->createTable('xf_wrxt_thread_freshness_vote', function(Create $table)
        {
            $table->addColumn('vote_id', 'int')->unsigned()->autoIncrement();
            $table->addColumn('thread_id', 'int')->unsigned();
            $table->addColumn('user_id', 'int')->unsigned();
            $table->addColumn('vote', 'tinyint');
            $table->addColumn('reason', 'varchar', 64)->setDefault('');
            $table->addColumn('version', 'varchar', 100)->setDefault('');
            $table->addColumn('message', 'varchar', 500)->setDefault('');
            $table->addColumn('vote_date', 'int')->unsigned();
            $table->addColumn('updated_date', 'int')->unsigned()->setDefault(0);
            $table->addColumn('alternative_thread_id', 'int')->unsigned()->setDefault(0);
            $table->addPrimaryKey('vote_id');
            $table->addUniqueKey(['thread_id', 'user_id'], 'thread_user');
            $table->addKey(['thread_id', 'vote_date'], 'thread_date');
            $table->addKey(['user_id', 'vote_date'], 'user_date');
            $table->addKey(['thread_id', 'version'], 'thread_version');
            $table->addKey('alternative_thread_id');
        });
    }

    public function installStep3(): void
    {
        $this->schemaManager()->createTable('xf_wrxt_thread_freshness_log', function(Create $table)
        {
            $table->addColumn('log_id', 'int')->unsigned()->autoIncrement();
            $table->addColumn('thread_id', 'int')->unsigned();
            $table->addColumn('old_status', 'varchar', 32)->setDefault('');
            $table->addColumn('new_status', 'varchar', 32)->setDefault('');
            $table->addColumn('trigger_type', 'varchar', 32)->setDefault('system');
            $table->addColumn('user_id', 'int')->unsigned()->setDefault(0);
            $table->addColumn('log_date', 'int')->unsigned();
            $table->addPrimaryKey('log_id');
            $table->addKey(['thread_id', 'log_date'], 'thread_date');
        });
    }

    public function installStep4(): void
    {
        $this->schemaManager()->alterTable('xf_forum', function(Alter $table)
        {
            $table->addColumn('wrxt_freshness_enabled', 'tinyint')->setDefault(0);
            $table->addColumn('wrxt_freshness_days', 'smallint')->unsigned()->setDefault(90);
            $table->addColumn('wrxt_freshness_versions', 'text')->nullable();
            $table->addColumn('wrxt_freshness_age_mode', 'varchar', 20)->setDefault('meaningful');
        });
    }

    public function upgrade1000014Step1(): void
    {
        $this->schemaManager()->alterTable('xf_forum', function(Alter $table)
        {
            $table->addColumn('wrxt_freshness_enabled', 'tinyint')->setDefault(0);
            $table->addColumn('wrxt_freshness_days', 'smallint')->unsigned()->setDefault(90);
            $table->addColumn('wrxt_freshness_versions', 'text')->nullable();
        });
    }

    public function upgrade1000014Step2(): void
    {
        $forumIds = Eligibility::parseForumIds((string)(\XF::options()->wrxtFreshnessForumIds ?? ''));
        if (!$forumIds)
        {
            return;
        }

        $general = (array)(\XF::options()->wrxtFreshnessGeneral ?? []);
        $days = max(1, min(3650, (int)($general['days'] ?? 90)));
        $idList = implode(',', array_map('intval', $forumIds));

        $this->db()->query(
            "UPDATE xf_forum
            SET wrxt_freshness_enabled = 1, wrxt_freshness_days = ?
            WHERE node_id IN ($idList)",
            $days
        );
    }

    public function upgrade1000014Step3(): void
    {
        $this->schemaManager()->alterTable('xf_wrxt_thread_freshness_vote', function(Alter $table)
        {
            $table->addKey(['thread_id', 'version'], 'thread_version');
        });
    }

    public function upgrade1000070Step1(): void
    {
        $this->schemaManager()->alterTable('xf_wrxt_thread_freshness_state', function(Alter $table)
        {
            $table->addColumn('replacement_thread_id', 'int')->unsigned()->setDefault(0);
            $table->addColumn('replacement_user_id', 'int')->unsigned()->setDefault(0);
            $table->addColumn('replacement_date', 'int')->unsigned()->setDefault(0);
            $table->addKey('replacement_thread_id');
        });
    }

    public function upgrade1010070Step1(): void
    {
        $this->schemaManager()->alterTable('xf_forum', function(Alter $table)
        {
            $table->addColumn('wrxt_freshness_age_mode', 'varchar', 20)->setDefault('meaningful');
        });
    }

    public function upgrade1010070Step2(): void
    {
        $this->schemaManager()->alterTable('xf_wrxt_thread_freshness_state', function(Alter $table)
        {
            $table->addColumn('reference_date', 'int')->unsigned()->setDefault(0);
            $table->addColumn('owner_claim_date', 'int')->unsigned()->setDefault(0);
            $table->addKey('reference_date');
        });
    }

    public function upgrade1010070Step3(): void
    {
        $this->schemaManager()->alterTable('xf_wrxt_thread_freshness_vote', function(Alter $table)
        {
            $table->addColumn('alternative_thread_id', 'int')->unsigned()->setDefault(0);
            $table->addKey('alternative_thread_id');
        });
    }

    public function upgrade1010070Step4(): void
    {
        $this->schemaManager()->alterTable('xf_wrxt_thread_freshness_state', function(Alter $table)
        {
            $table->addKey('moderator_user_id');
            $table->addKey('replacement_user_id');
        });
    }

    public function upgrade1010070Step5(): void
    {
        \XF::app()->jobManager()->enqueue(
            'WarextStudios\ThreadFreshness:Recalculate',
            []
        );
    }

    public function uninstallStep1(): void
    {
        $this->schemaManager()->dropTable('xf_wrxt_thread_freshness_log');
        $this->schemaManager()->dropTable('xf_wrxt_thread_freshness_vote');
        $this->schemaManager()->dropTable('xf_wrxt_thread_freshness_state');
    }

    public function uninstallStep2(): void
    {
        $this->schemaManager()->alterTable('xf_forum', function(Alter $table)
        {
            $table->dropColumns([
                'wrxt_freshness_enabled',
                'wrxt_freshness_days',
                'wrxt_freshness_versions',
                'wrxt_freshness_age_mode'
            ]);
        });
    }
}
