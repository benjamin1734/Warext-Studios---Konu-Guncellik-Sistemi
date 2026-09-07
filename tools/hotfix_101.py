from pathlib import Path
import hashlib
import json

ROOT = Path(__file__).resolve().parents[1]
ADDON = ROOT / "upload/src/addons/WarextStudios/ThreadFreshness"


def replace_once(path: Path, old: str, new: str) -> None:
    text = path.read_text(encoding="utf-8")
    if old not in text:
        raise SystemExit(f"Expected text not found in {path}: {old[:160]!r}")
    path.write_text(text.replace(old, new, 1), encoding="utf-8")


# Critical runtime error: XF repository objects do not expose an $app property.
repo = ADDON / "Repository/ThreadFreshness.php"
replace_once(
    repo,
    "$this->app->service(\n            'WarextStudios\\ThreadFreshness:ThreadFreshness\\Notifier'\n        )->notify($threadId, $oldStatus, $newStatus, $triggerType, $userId);",
    "\\XF::app()->service(\n            'WarextStudios\\ThreadFreshness:ThreadFreshness\\Notifier'\n        )->notify($threadId, $oldStatus, $newStatus, $triggerType, $userId);",
)

# Save forum-specific settings through FormAction's entity save path.
forum_controller = ADDON / "XF/Admin/Controller/Forum.php"
forum_controller.write_text(
    """<?php

namespace WarextStudios\\ThreadFreshness\\XF\\Admin\\Controller;

use XF\\Entity\\AbstractNode;
use XF\\Entity\\Node;
use XF\\Mvc\\FormAction;

class Forum extends XFCP_Forum
{
    protected function saveTypeData(FormAction $form, Node $node, AbstractNode $data)
    {
        parent::saveTypeData($form, $node, $data);

        $input = $this->filter([
            'wrxt_freshness_enabled' => 'bool',
            'wrxt_freshness_days' => 'uint',
            'wrxt_freshness_versions' => 'str',
            'wrxt_freshness_age_mode' => 'str'
        ]);

        $input['wrxt_freshness_days'] = max(1, min(3650, (int)$input['wrxt_freshness_days']));
        $input['wrxt_freshness_versions'] = trim((string)$input['wrxt_freshness_versions']);
        $input['wrxt_freshness_age_mode'] = in_array(
            (string)$input['wrxt_freshness_age_mode'],
            ['meaningful', 'last_post'],
            true
        ) ? (string)$input['wrxt_freshness_age_mode'] : 'meaningful';

        $form->basicEntitySave($data, $input);
    }
}
""",
    encoding="utf-8",
)

# Avoid rewriting every forum on each central settings save and always rollback on failure.
dashboard = ADDON / "Admin/Controller/Dashboard.php"
old_tx = """            $forumIds = array_values(array_unique(array_filter($forumIds)));
            $db->beginTransaction();
            $db->query('UPDATE xf_forum SET wrxt_freshness_enabled = 0');

            if ($forumIds)
            {
                $idList = implode(',', array_map('intval', $forumIds));
                $db->query(
                    \"UPDATE xf_forum
                     SET wrxt_freshness_enabled = 1, wrxt_freshness_days = ?, wrxt_freshness_age_mode = ?
                     WHERE node_id IN ($idList)\",
                    [$days, $ageMode]
                );
            }

            $db->commit();
"""
new_tx = """            $forumIds = array_values(array_unique(array_filter($forumIds)));
            $idList = $forumIds ? implode(',', array_map('intval', $forumIds)) : '';

            $db->beginTransaction();
            try
            {
                if ($forumIds)
                {
                    $db->query(
                        \"UPDATE xf_forum
                         SET wrxt_freshness_enabled = 0
                         WHERE wrxt_freshness_enabled = 1
                           AND node_id NOT IN ($idList)\"
                    );
                    $db->query(
                        \"UPDATE xf_forum
                         SET wrxt_freshness_enabled = 1,
                             wrxt_freshness_days = ?,
                             wrxt_freshness_age_mode = ?
                         WHERE node_id IN ($idList)
                           AND (
                               wrxt_freshness_enabled <> 1
                               OR wrxt_freshness_days <> ?
                               OR wrxt_freshness_age_mode <> ?
                           )\",
                        [$days, $ageMode, $days, $ageMode]
                    );
                }
                else
                {
                    $db->query(
                        'UPDATE xf_forum SET wrxt_freshness_enabled = 0 WHERE wrxt_freshness_enabled = 1'
                    );
                }

                $db->commit();
            }
            catch (\\Throwable $e)
            {
                $db->rollback();
                throw $e;
            }
"""
replace_once(dashboard, old_tx, new_tx)

# Compact owner confirmation action.
mods = ADDON / "_data/template_modifications.xml"
replace_once(
    mods,
    '&lt;xf:submitrow submit="{{ $thread.hasWrxtFreshnessOwnerClaim() ? \'Konu sahibi doğrulamasını kaldır\' : \'Konu sahibi olarak hâlâ geçerli bildir\' }}" /&gt;',
    '&lt;xf:button type="submit" class="button--primary"&gt;{{ $thread.hasWrxtFreshnessOwnerClaim() ? \'Doğrulamayı kaldır\' : \'Konu sahibi: hâlâ geçerli\' }}&lt;/xf:button&gt;',
)

# Version bump. Must be greater than the shipped 1010072 internal version id.
addon_json = ADDON / "addon.json"
meta = json.loads(addon_json.read_text(encoding="utf-8"))
meta["version_id"] = 1010170
meta["version_string"] = "1.0.1"
addon_json.write_text(json.dumps(meta, ensure_ascii=False, indent=4) + "\n", encoding="utf-8")

# Keep release assertions aligned with the new package version.
for test_path in [ROOT / "tests/release_data.php", ROOT / "tests/release_static.php"]:
    text = test_path.read_text(encoding="utf-8")
    text = text.replace("1010072", "1010170").replace("1.0.0", "1.0.1")
    test_path.write_text(text, encoding="utf-8")

# Add explicit regression checks for all issues reported in 1.0.0.
regression = ROOT / "tests/hotfix_101.php"
regression.write_text(
    """<?php

$root = dirname(__DIR__) . '/upload/src/addons/WarextStudios/ThreadFreshness';

$repo = (string)file_get_contents($root . '/Repository/ThreadFreshness.php');
if (str_contains($repo, '$this->app->service(') || !str_contains($repo, '\\\\XF::app()->service('))
{
    throw new RuntimeException('Repository notifier factory regression');
}

$forumController = (string)file_get_contents($root . '/XF/Admin/Controller/Forum.php');
if (!str_contains($forumController, 'basicEntitySave') || str_contains($forumController, '$form->setup(function()'))
{
    throw new RuntimeException('Forum settings save regression');
}

$dashboard = (string)file_get_contents($root . '/Admin/Controller/Dashboard.php');
if (!str_contains($dashboard, 'catch (\\\\Throwable $e)') || !str_contains($dashboard, '$db->rollback()'))
{
    throw new RuntimeException('ACP bulk save rollback regression');
}
if (str_contains($dashboard, \"UPDATE xf_forum SET wrxt_freshness_enabled = 0');\n\n            if ($forumIds)\"))
{
    throw new RuntimeException('ACP bulk save still rewrites the entire forum table');
}

$mods = (string)file_get_contents($root . '/_data/template_modifications.xml');
if (str_contains($mods, 'xf:submitrow submit=\"{{ $thread.hasWrxtFreshnessOwnerClaim()'))
{
    throw new RuntimeException('Owner claim still uses oversized submitrow');
}
if (!str_contains($mods, 'Konu sahibi: hâlâ geçerli'))
{
    throw new RuntimeException('Compact owner claim action missing');
}

echo \"OK\\n\";
""",
    encoding="utf-8",
)

# Make package naming dynamic instead of pinning every future release to 1.0.0.
prepare = ROOT / "tools/prepare_release.php"
replace_once(
    prepare,
    "$zipName = 'Warext-Studios-Konu-Guncellik-Sistemi-1.0.0.zip';",
    "$addonData = json_decode((string)file_get_contents($root . '/upload/src/addons/WarextStudios/ThreadFreshness/addon.json'), true);\n"
    "$version = trim((string)($addonData['version_string'] ?? ''));\n"
    "if ($version === '' || !preg_match('/^[0-9A-Za-z._-]+$/', $version))\n"
    "{\n"
    "    fwrite(STDERR, \"Geçersiz addon sürümü.\\n\");\n"
    "    exit(1);\n"
    "}\n"
    "$zipName = 'XenForo-ACP-Direct-Install-Warext-Konu-Guncellik-' . $version . '.zip';",
)

# Changelog / documentation.
changelog = ROOT / "CHANGELOG.md"
cl = changelog.read_text(encoding="utf-8")
header = "# Değişiklik Günlüğü\n\n"
section = """## 1.0.1 Stable - Hata Düzeltme

- Konu sahibi “hâlâ geçerli” işlemindeki `ThreadFreshness::$app` tanımsız property hatası giderildi.
- ACP forum düzenleme kaydı tek seferlik input filtreleme + `FormAction::basicEntitySave()` akışına taşındı.
- ACP kategori/forum toplu kaydında gereksiz tüm tablo güncellemesi kaldırıldı; yalnızca değişen forumlar yazılıyor ve hata halinde transaction rollback ediliyor.
- Konu sahibi doğrulama aksiyonundaki geniş submit alanı normal boyutlu ve daha anlaşılır bir butona dönüştürüldü.
- Eklenti sürümü `1.0.1` / `1010170` olarak yükseltildi.
- Paket üretimi sürüm bilgisini `addon.json` üzerinden dinamik okuyacak hale getirildi.
- Bu hatalar için kalıcı regresyon testi eklendi.

"""
if not cl.startswith(header):
    raise SystemExit("Unexpected CHANGELOG header")
changelog.write_text(header + section + cl[len(header):], encoding="utf-8")

readme = ROOT / "README.md"
r = readme.read_text(encoding="utf-8")
r = r.replace("**1.0.0 Stable - Nihai Sürüm**", "**1.0.1 Stable - Hata Düzeltme**")
r = r.replace("XenForo-ACP-Direct-Install-Warext-Konu-Guncellik-1.0.0.zip", "XenForo-ACP-Direct-Install-Warext-Konu-Guncellik-1.0.1.zip")
r = r.replace("Bu paket resmi **1.0.0 Stable** sürümüdür.", "Bu paket resmi **1.0.1 Stable** sürümüdür.")
readme.write_text(r, encoding="utf-8")

install = ROOT / "docs/INSTALL.md"
install.write_text(
    install.read_text(encoding="utf-8").replace(
        "XenForo-ACP-Direct-Install-Warext-Konu-Guncellik-1.0.0.zip",
        "XenForo-ACP-Direct-Install-Warext-Konu-Guncellik-1.0.1.zip",
    ),
    encoding="utf-8",
)

# Refresh source hashes after all add-on files are final.
hashes = {}
for path in sorted(ADDON.rglob("*")):
    if not path.is_file() or path.name == "hashes.json":
        continue
    hashes[path.relative_to(ADDON).as_posix()] = hashlib.sha256(path.read_bytes()).hexdigest()
(ADDON / "hashes.json").write_text(json.dumps(hashes, indent=4, sort_keys=True) + "\n", encoding="utf-8")

print("1.0.1 hotfix applied")
