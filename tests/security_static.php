<?php

$repoRoot = dirname(__DIR__);
$root = $repoRoot . '/upload/src/addons/WarextStudios/ThreadFreshness';
$forbiddenFunctions = ['eval', 'exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen'];
$phpRoots = [$root, $repoRoot . '/tests', $repoRoot . '/tools'];

foreach ($phpRoots as $phpRoot)
{
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($phpRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file)
    {
        if (!$file->isFile() || $file->getExtension() !== 'php')
        {
            continue;
        }
        $source = (string)file_get_contents($file->getPathname());
        foreach (token_get_all($source) as $token)
        {
            if (is_array($token) && in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true))
            {
                fwrite(STDERR, "Yorum bulundu: {$file->getPathname()}\n");
                exit(1);
            }
        }
        foreach ($forbiddenFunctions as $function)
        {
            if (preg_match('/\b' . preg_quote($function, '/') . '\s*\(/i', $source))
            {
                fwrite(STDERR, "Yasak fonksiyon bulundu: $function\n");
                exit(1);
            }
        }
    }
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file)
{
    if (!$file->isFile())
    {
        continue;
    }
    $extension = strtolower($file->getExtension());
    if (!in_array($extension, ['xml', 'html', 'htm'], true))
    {
        continue;
    }
    $source = (string)file_get_contents($file->getPathname());
    if (str_contains($source, '<!--') || str_contains($source, '-->'))
    {
        fwrite(STDERR, "Markup yorumu bulundu: {$file->getPathname()}\n");
        exit(1);
    }
}

$workflowRoot = $repoRoot . '/.github/workflows';
if (is_dir($workflowRoot))
{
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($workflowRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file)
    {
        if (!$file->isFile() || !in_array(strtolower($file->getExtension()), ['yml', 'yaml'], true))
        {
            continue;
        }
        $source = (string)file_get_contents($file->getPathname());
        if (preg_match('/(^|\R)\s*#/m', $source))
        {
            fwrite(STDERR, "Workflow yorumu bulundu: {$file->getPathname()}\n");
            exit(1);
        }
    }
}

$threadController = (string)file_get_contents($root . '/XF/Pub/Controller/Thread.php');
foreach (['actionFreshnessVote', 'actionFreshnessModerate', 'actionFreshnessReplacement', 'actionFreshnessOwnerClaim'] as $action)
{
    $position = strpos($threadController, 'function ' . $action);
    if ($position === false)
    {
        fwrite(STDERR, "Action bulunamadı: $action\n");
        exit(1);
    }
    $section = substr($threadController, $position, 900);
    if (strpos($section, 'assertPostOnly') === false)
    {
        fwrite(STDERR, "POST koruması bulunamadı: $action\n");
        exit(1);
    }
}

$setup = (string)file_get_contents($root . '/Setup.php');
foreach ([
    "addUniqueKey(['thread_id', 'user_id'], 'thread_user')",
    "addKey(['thread_id', 'version'], 'thread_version')",
    "addKey('replacement_thread_id')",
    "addKey('alternative_thread_id')",
    "reference_date",
    "owner_claim_date",
    "upgrade1010070Step5"
] as $needle)
{
    if (strpos($setup, $needle) === false)
    {
        fwrite(STDERR, "Şema güvenlik kontrolü başarısız: $needle\n");
        exit(1);
    }
}

$repository = (string)file_get_contents($root . '/Repository/ThreadFreshness.php');
foreach (['FOR UPDATE', 'beginTransaction', 'rollback', 'preloadThreadData', 'getMeaningfulDateForThread', 'getFailureReasonSummaryForThread', "['last_calculated_date' => 0]"] as $needle)
{
    if (strpos($repository, $needle) === false)
    {
        fwrite(STDERR, "Repository güvenlik kontrolü başarısız: $needle\n");
        exit(1);
    }
}

$vote = (string)file_get_contents($root . '/Service/ThreadFreshness/Vote.php');
foreach (['FailureReason::isValid', 'alternative_thread_id', 'getWrxtFreshnessReferenceDate'] as $needle)
{
    if (strpos($vote, $needle) === false)
    {
        fwrite(STDERR, "Oy servisi doğrulaması eksik: $needle\n");
        exit(1);
    }
}

echo "OK\n";
