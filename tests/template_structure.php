<?php

$root = dirname(__DIR__) . '/upload/src/addons/WarextStudios/ThreadFreshness';
$templates = simplexml_load_file($root . '/_data/templates.xml', SimpleXMLElement::class, LIBXML_NOCDATA);
if (!$templates)
{
    throw new RuntimeException('templates.xml okunamadı');
}

$dashboard = null;
foreach ($templates->template as $template)
{
    if ((string)$template['title'] === 'wrxt_thread_freshness_dashboard')
    {
        $dashboard = (string)$template;
        if ((string)$template['version_string'] !== '1.0.0')
        {
            throw new RuntimeException('Dashboard template sürümü hatalı');
        }
        break;
    }
}

if ($dashboard === null)
{
    throw new RuntimeException('Dashboard template bulunamadı');
}
if (str_contains($dashboard, '<xf:main>'))
{
    throw new RuntimeException('Dashboard içinde xf:main kullanımı yasak');
}
if (str_contains($dashboard, 'responsive-datalist'))
{
    throw new RuntimeException('Eski responsive datalist adı bulundu');
}
if (!str_contains($dashboard, 'responsive-data-list'))
{
    throw new RuntimeException('Responsive data list başlatıcısı bulunamadı');
}
if (substr_count($dashboard, '<xf:datarow>') !== 2 || substr_count($dashboard, '<xf:cell class="dataList-cell--main">') !== 2)
{
    throw new RuntimeException('Dashboard datalist yapısı beklenen biçimde değil');
}

$phrases = simplexml_load_file($root . '/_data/phrases.xml');
if (!$phrases)
{
    throw new RuntimeException('phrases.xml okunamadı');
}
foreach ($phrases->phrase as $phrase)
{
    if ((string)$phrase['version_string'] !== '1.0.0')
    {
        throw new RuntimeException('Phrase sürüm metadata değeri hatalı');
    }
}

echo "OK\n";
