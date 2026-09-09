<?php

namespace WarextStudios\ThreadFreshness\XF\Entity;

use WarextStudios\ThreadFreshness\Entity\ThreadState;
use WarextStudios\ThreadFreshness\Entity\Vote;
use WarextStudios\ThreadFreshness\Util\Eligibility;
use WarextStudios\ThreadFreshness\Util\ModeratorStatus;
use XF\Mvc\Entity\Structure;

class Thread extends XFCP_Thread
{
    protected ?array $wrxtFreshnessVersionSummary = null;
    protected ?array $wrxtFreshnessFailureReasonSummary = null;
    protected ?array $wrxtFreshnessAlternativeSuggestions = null;
    protected ?int $wrxtFreshnessReferenceDate = null;
    protected bool $wrxtFreshnessVisitorVoteLoaded = false;
    protected bool $wrxtFreshnessListDataPreloaded = false;
    protected ?Vote $wrxtFreshnessVisitorVoteEntity = null;

    public static function getStructure(Structure $structure): Structure
    {
        $structure = parent::getStructure($structure);

        $structure->relations['WrxtFreshnessState'] = [
            'entity' => 'WarextStudios\ThreadFreshness:ThreadState',
            'type' => self::TO_ONE,
            'conditions' => 'thread_id',
            'primary' => true
        ];

        return $structure;
    }

    public function isWrxtFreshnessEnabled(): bool
    {
        $general = (array)(\XF::options()->wrxtFreshnessGeneral ?? []);
        if (empty($general['enabled']) || !$this->Forum)
        {
            return false;
        }

        return (bool)$this->Forum->wrxt_freshness_enabled;
    }

    public function setWrxtFreshnessReferenceDate(int $date): void
    {
        $this->wrxtFreshnessReferenceDate = max(0, $date);
    }

    public function setWrxtFreshnessListDataPreloaded(bool $preloaded = true): void
    {
        $this->wrxtFreshnessListDataPreloaded = $preloaded;
    }

    public function hasWrxtFreshnessListData(): bool
    {
        return $this->wrxtFreshnessListDataPreloaded;
    }

    public function getWrxtFreshnessReferenceDate(): int
    {
        if ($this->wrxtFreshnessReferenceDate !== null)
        {
            return $this->wrxtFreshnessReferenceDate;
        }

        if (!$this->Forum || $this->Forum->getWrxtFreshnessAgeMode() === 'last_post')
        {
            return $this->wrxtFreshnessReferenceDate = (int)$this->last_post_date;
        }

        return $this->wrxtFreshnessReferenceDate = (int)$this->repository(
            'WarextStudios\ThreadFreshness:ThreadFreshness'
        )->getMeaningfulDateForThread($this);
    }

    public function getWrxtFreshnessEligibleDate(): int
    {
        if (!$this->isWrxtFreshnessEnabled() || !$this->Forum)
        {
            return 0;
        }

        return $this->getWrxtFreshnessReferenceDate() + max(1, (int)$this->Forum->wrxt_freshness_days) * 86400;
    }

    public function getWrxtFreshnessDaysUntilEligible(): int
    {
        $eligibleDate = $this->getWrxtFreshnessEligibleDate();
        if ($eligibleDate <= 0 || $eligibleDate <= \XF::$time)
        {
            return 0;
        }

        return (int)ceil(($eligibleDate - \XF::$time) / 86400);
    }

    public function isWrxtFreshnessEligible(): bool
    {
        if (!$this->isWrxtFreshnessEnabled())
        {
            return false;
        }

        return Eligibility::isThreadEligible(
            true,
            (int)$this->node_id,
            $this->getWrxtFreshnessReferenceDate(),
            [(int)$this->node_id],
            (int)$this->Forum->wrxt_freshness_days,
            \XF::$time
        );
    }

    public function canWrxtFreshnessVote(): bool
    {
        if (!$this->isWrxtFreshnessEligible())
        {
            return false;
        }

        $visitor = \XF::visitor();
        $ownThread = (int)$visitor->user_id > 0 && (int)$visitor->user_id === (int)$this->user_id;
        $allowOwnThread = !$ownThread || (
            (bool)(\XF::options()->wrxtFreshnessAllowOwnThread ?? false)
            && $visitor->hasPermission('wrxtFreshness', 'voteOwn')
        );

        if (!Eligibility::canVisitorVote(
            (int)$visitor->user_id,
            (int)$this->user_id,
            (int)$visitor->register_date,
            (int)$visitor->message_count,
            $visitor->hasPermission('wrxtFreshness', 'vote'),
            $allowOwnThread,
            (int)(\XF::options()->wrxtFreshnessMinAccountDays ?? 7),
            (int)(\XF::options()->wrxtFreshnessMinMessages ?? 3),
            \XF::$time
        ))
        {
            return false;
        }

        return true;
    }

    public function canWrxtFreshnessModerate(): bool
    {
        return $this->isWrxtFreshnessEligible()
            && (int)\XF::visitor()->user_id > 0
            && \XF::visitor()->hasPermission('wrxtFreshness', 'moderate');
    }

    public function canWrxtFreshnessOwnerClaim(): bool
    {
        $visitor = \XF::visitor();

        return $this->isWrxtFreshnessEligible()
            && (int)$visitor->user_id > 0
            && (int)$visitor->user_id === (int)$this->user_id;
    }

    public function canWrxtFreshnessManageReplacement(): bool
    {
        $visitor = \XF::visitor();
        return $this->isWrxtFreshnessEligible()
            && (int)$visitor->user_id > 0
            && (
                (int)$visitor->user_id === (int)$this->user_id
                || $visitor->hasPermission('wrxtFreshness', 'moderate')
            );
    }

    public function getWrxtFreshnessState(): ?ThreadState
    {
        return $this->WrxtFreshnessState;
    }

    public function isWrxtFreshnessStateCurrent(): bool
    {
        $state = $this->getWrxtFreshnessState();
        if (!$state)
        {
            return false;
        }

        $referenceDate = $this->getWrxtFreshnessReferenceDate();
        if ($referenceDate <= 0)
        {
            return false;
        }

        return (int)$state->reference_date === $referenceDate
            && (int)$state->last_calculated_date >= $referenceDate;
    }

    public function getWrxtFreshnessDisplayStatus(): string
    {
        if (!$this->isWrxtFreshnessStateCurrent())
        {
            return 'unverified';
        }

        $state = $this->getWrxtFreshnessState();
        return ModeratorStatus::effective((string)$state->status, (string)$state->moderator_status);
    }

    public function getWrxtFreshnessConfiguredVersions(): array
    {
        return $this->Forum ? $this->Forum->getWrxtFreshnessVersions() : [];
    }

    public function getWrxtFreshnessVersionSummary(): array
    {
        if ($this->wrxtFreshnessVersionSummary === null)
        {
            $this->wrxtFreshnessVersionSummary = $this->repository(
                'WarextStudios\ThreadFreshness:ThreadFreshness'
            )->getVersionSummaryForThread(
                (int)$this->thread_id,
                8,
                $this->getWrxtFreshnessReferenceDate()
            );
        }

        return $this->wrxtFreshnessVersionSummary;
    }

    public function getWrxtFreshnessFailureReasonSummary(): array
    {
        if ($this->wrxtFreshnessFailureReasonSummary === null)
        {
            $this->wrxtFreshnessFailureReasonSummary = $this->repository(
                'WarextStudios\ThreadFreshness:ThreadFreshness'
            )->getFailureReasonSummaryForThread(
                (int)$this->thread_id,
                $this->getWrxtFreshnessReferenceDate()
            );
        }

        return $this->wrxtFreshnessFailureReasonSummary;
    }

    public function getWrxtFreshnessAlternativeSuggestions(): array
    {
        if (!$this->canWrxtFreshnessManageReplacement())
        {
            return [];
        }

        if ($this->wrxtFreshnessAlternativeSuggestions === null)
        {
            $this->wrxtFreshnessAlternativeSuggestions = $this->repository(
                'WarextStudios\ThreadFreshness:ThreadFreshness'
            )->getAlternativeSuggestionsForThread(
                (int)$this->thread_id,
                $this->getWrxtFreshnessReferenceDate()
            );
        }

        return $this->wrxtFreshnessAlternativeSuggestions;
    }

    public function getWrxtFreshnessVisitorVoteEntity(): ?Vote
    {
        if ($this->wrxtFreshnessVisitorVoteLoaded)
        {
            return $this->wrxtFreshnessVisitorVoteEntity;
        }

        $this->wrxtFreshnessVisitorVoteLoaded = true;
        $visitorId = (int)\XF::visitor()->user_id;
        if ($visitorId <= 0)
        {
            return null;
        }

        $vote = \XF::finder('WarextStudios\ThreadFreshness:Vote')
            ->where('thread_id', $this->thread_id)
            ->where('user_id', $visitorId)
            ->fetchOne();

        if ($vote)
        {
            $voteDate = max((int)$vote->vote_date, (int)$vote->updated_date);
            if ($voteDate < $this->getWrxtFreshnessReferenceDate())
            {
                $vote = null;
            }
        }

        return $this->wrxtFreshnessVisitorVoteEntity = $vote ?: null;
    }

    public function getWrxtFreshnessVisitorVote(): int
    {
        $vote = $this->getWrxtFreshnessVisitorVoteEntity();
        if (!$vote)
        {
            return 0;
        }

        $voteDate = max((int)$vote->vote_date, (int)$vote->updated_date);
        return $voteDate >= $this->getWrxtFreshnessReferenceDate() ? (int)$vote->vote : 0;
    }

    public function hasWrxtFreshnessOwnerClaim(): bool
    {
        $state = $this->getWrxtFreshnessState();
        return $state
            && (int)$state->owner_claim_date >= $this->getWrxtFreshnessReferenceDate()
            && (int)$state->owner_claim_date > 0;
    }

    public function getWrxtFreshnessReplacementThread(): ?\XF\Entity\Thread
    {
        if (!$this->isWrxtFreshnessEligible())
        {
            return null;
        }

        $state = $this->getWrxtFreshnessState();
        if (!$state || !$state->replacement_thread_id || !$state->ReplacementThread)
        {
            return null;
        }

        $replacement = $state->ReplacementThread;
        if (
            !$replacement->canView()
            || $replacement->discussion_state !== 'visible'
            || $replacement->discussion_type === 'redirect'
            || (int)$replacement->thread_id === (int)$this->thread_id
            || (int)$replacement->post_date < (int)$this->post_date
        )
        {
            return null;
        }

        return $replacement;
    }
}
