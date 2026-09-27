<?php

namespace WarextStudios\ThreadFreshness\XF\Pub\Controller;

use WarextStudios\ThreadFreshness\Util\ModeratorStatus;
use XF\Mvc\ParameterBag;

class Thread extends XFCP_Thread
{
    public function actionFreshnessVote(ParameterBag $params)
    {
        $this->assertPostOnly();
        $thread = $this->assertViewableThread($params->thread_id);

        if (!$thread->canWrxtFreshnessVote())
        {
            return $this->noPermission();
        }

        $input = $this->filter([
            'vote' => 'int',
            'reason' => 'str',
            'version' => 'str',
            'message' => 'str',
            'alternative_thread_id' => 'uint'
        ]);

        if (!in_array($input['vote'], [-1, 1], true))
        {
            return $this->error(\XF::phrase('wrxt_freshness_error_invalid_vote'));
        }

        if ($input['vote'] === 1)
        {
            $input['reason'] = '';
            $input['alternative_thread_id'] = 0;
        }

        $configuredVersions = $thread->getWrxtFreshnessConfiguredVersions();
        if ($configuredVersions && !in_array(trim($input['version']), $configuredVersions, true))
        {
            return $this->error(\XF::phrase('wrxt_freshness_error_version_required'));
        }

        $service = $this->service(
            'WarextStudios\ThreadFreshness:ThreadFreshness\Vote',
            $thread,
            \XF::visitor()
        );

        try
        {
            $service->setVote($input['vote']);
            $service->setReason($input['reason']);
            $service->setVersion($input['version']);
            $service->setMessage($input['message']);
            $service->setAlternativeThreadId($input['alternative_thread_id']);
            $service->save();
        }
        catch (\InvalidArgumentException $e)
        {
            return $this->error(\XF::phrase('wrxt_freshness_error_invalid_vote_payload'));
        }

        return $this->redirect($this->buildLink('threads', $thread) . '#wrxt-thread-freshness');
    }

    public function actionFreshnessModerate(ParameterBag $params)
    {
        $this->assertPostOnly();
        $thread = $this->assertViewableThread($params->thread_id);

        if (!$thread->canWrxtFreshnessModerate())
        {
            return $this->noPermission();
        }

        $status = trim($this->filter('moderator_status', 'str'));
        if (ModeratorStatus::normalize($status) !== $status)
        {
            return $this->error(\XF::phrase('wrxt_freshness_error_invalid_moderator_status'));
        }

        $service = $this->service(
            'WarextStudios\ThreadFreshness:ThreadFreshness\Moderate',
            $thread,
            \XF::visitor()
        );
        $service->setStatus($status);
        $service->save();

        return $this->redirect($this->buildLink('threads', $thread) . '#wrxt-thread-freshness');
    }

    public function actionFreshnessOwnerClaim(ParameterBag $params)
    {
        $this->assertPostOnly();
        $thread = $this->assertViewableThread($params->thread_id);

        if (!$thread->canWrxtFreshnessOwnerClaim())
        {
            return $this->noPermission();
        }

        $service = $this->service(
            'WarextStudios\ThreadFreshness:ThreadFreshness\OwnerClaim',
            $thread,
            \XF::visitor()
        );
        $service->setClaimed($this->filter('claimed', 'bool'));
        $service->save();

        return $this->redirect($this->buildLink('threads', $thread) . '#wrxt-thread-freshness');
    }

    public function actionFreshnessReplacement(ParameterBag $params)
    {
        $this->assertPostOnly();
        $thread = $this->assertViewableThread($params->thread_id);

        if (!$thread->canWrxtFreshnessManageReplacement())
        {
            return $this->noPermission();
        }

        $replacementThreadId = $this->filter('replacement_thread_id', 'uint');
        $service = $this->service(
            'WarextStudios\ThreadFreshness:ThreadFreshness\Replacement',
            $thread,
            \XF::visitor()
        );
        $service->setReplacementThreadId($replacementThreadId);

        try
        {
            $service->save();
        }
        catch (\InvalidArgumentException $e)
        {
            return $this->error(\XF::phrase('wrxt_freshness_error_invalid_replacement'));
        }

        return $this->redirect($this->buildLink('threads', $thread) . '#wrxt-thread-freshness-replacement');
    }
}
