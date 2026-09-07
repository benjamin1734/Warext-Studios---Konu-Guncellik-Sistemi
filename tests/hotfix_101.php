<?php

$root = dirname(__DIR__) . '/upload/src/addons/WarextStudios/ThreadFreshness';

$repo = (string)file_get_contents($root . '/Repository/ThreadFreshness.php');
if (str_contains($repo, '$this->app->service(') || !str_contains($repo, '\\XF::app()->service('))
{
    throw new RuntimeException('Repository notifier factory regression');
}

$forumController = (string)file_get_contents($root . '/XF/Admin/Controller/Forum.php');
if (!str_contains($forumController, 'basicEntitySave') || str_contains($forumController, '$form->setup(function()'))
{
    throw new RuntimeException('Forum settings save regression');
}

$dashboard = (string)file_get_contents($root . '/Admin/Controller/Dashboard.php');
if (!str_contains($dashboard, 'catch (\\Throwable $e)') || !str_contains($dashboard, '$db->rollback()'))
{
    throw new RuntimeException('ACP bulk save rollback regression');
}
if (str_contains($dashboard, "UPDATE xf_forum SET wrxt_freshness_enabled = 0');

            if ($forumIds)"))
{
    throw new RuntimeException('ACP bulk save still rewrites the entire forum table');
}

$mods = (string)file_get_contents($root . '/_data/template_modifications.xml');
if (str_contains($mods, 'xf:submitrow submit="{{ $thread.hasWrxtFreshnessOwnerClaim()'))
{
    throw new RuntimeException('Owner claim still uses oversized submitrow');
}
if (!str_contains($mods, 'Konu sahibi: hâlâ geçerli'))
{
    throw new RuntimeException('Compact owner claim action missing');
}

echo "OK\n";
