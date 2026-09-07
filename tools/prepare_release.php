<?php

$root = dirname(__DIR__);
$source = $root . '/upload';
$build = $root . '/build';
$stage = $build . '/release';
$zipName = 'Warext-Studios-Konu-Guncellik-Sistemi-1.0.0.zip';

function wrxtRemove(string $path): void
{
    if (!file_exists($path))
    {
        return;
    }
    if (is_file($path) || is_link($path))
    {
        unlink($path);
        return;
    }
    foreach (scandir($path) ?: [] as $item)
    {
        if ($item === '.' || $item === '..')
        {
            continue;
        }
        wrxtRemove($path . '/' . $item);
    }
    rmdir($path);
}

function wrxtCopy(string $source, string $target): void
{
    if (is_dir($source))
    {
        if (!is_dir($target))
        {
            mkdir($target, 0777, true);
        }
        foreach (scandir($source) ?: [] as $item)
        {
            if ($item === '.' || $item === '..' || $item === '_output' || $item === '_releases')
            {
                continue;
            }
            wrxtCopy($source . '/' . $item, $target . '/' . $item);
        }
        return;
    }

    $parent = dirname($target);
    if (!is_dir($parent))
    {
        mkdir($parent, 0777, true);
    }
    copy($source, $target);
}

wrxtRemove($stage);
if (!is_dir($build))
{
    mkdir($build, 0777, true);
}
mkdir($stage, 0777, true);
wrxtCopy($source, $stage . '/upload');

foreach (['README.md', 'CHANGELOG.md'] as $file)
{
    if (is_file($root . '/' . $file))
    {
        copy($root . '/' . $file, $stage . '/' . $file);
    }
}

$hashes = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($stage . '/upload', FilesystemIterator::SKIP_DOTS)
);
foreach ($iterator as $file)
{
    if (!$file->isFile())
    {
        continue;
    }
    $relative = substr($file->getPathname(), strlen($stage . '/upload/'));
    $relative = str_replace(DIRECTORY_SEPARATOR, '/', $relative);
    if (str_ends_with($relative, '/hashes.json'))
    {
        continue;
    }
    $hashes[$relative] = hash_file('sha256', $file->getPathname());
}
ksort($hashes);
$hashPath = $stage . '/upload/src/addons/WarextStudios/ThreadFreshness/hashes.json';
file_put_contents($hashPath, json_encode($hashes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

if (is_dir($stage . '/upload/src/addons/WarextStudios/ThreadFreshness/_output'))
{
    fwrite(STDERR, "Release paketinde _output bulundu.\n");
    exit(1);
}
if (!is_dir($stage . '/upload/src/addons/WarextStudios/ThreadFreshness/_data'))
{
    fwrite(STDERR, "Release paketinde _data bulunamadı.\n");
    exit(1);
}

echo $build . '/' . $zipName . "\n";
