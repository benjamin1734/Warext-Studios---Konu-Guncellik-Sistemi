from pathlib import Path
import json

root = Path(__file__).resolve().parents[1]
addon = root / 'upload/src/addons/WarextStudios/ThreadFreshness'


def replace_once(path: Path, old: str, new: str):
    text = path.read_text(encoding='utf-8')
    if old not in text:
        raise SystemExit(f'Expected block not found in {path}: {old[:100]!r}')
    path.write_text(text.replace(old, new, 1), encoding='utf-8')

# Thread owner may use community voting after removing the owner-verification flag.
thread_path = addon / 'XF/Entity/Thread.php'
old = '''        $visitor = \\XF::visitor();
        $ownThread = (int)$visitor->user_id > 0 && (int)$visitor->user_id === (int)$this->user_id;
        $allowOwnThread = !$ownThread || (
            (bool)(\\XF::options()->wrxtFreshnessAllowOwnThread ?? false)
            && $visitor->hasPermission('wrxtFreshness', 'voteOwn')
        );
'''
new = '''        $visitor = \\XF::visitor();
        $ownThread = (int)$visitor->user_id > 0 && (int)$visitor->user_id === (int)$this->user_id;
        $ownerClaimActive = $ownThread && $this->hasWrxtFreshnessOwnerClaim();
        $allowOwnThread = !$ownThread || !$ownerClaimActive;
'''
replace_once(thread_path, old, new)

# Vote changing is already unrestricted in 1.0.7. Make the service use the same
# owner-verification rule as the entity permission check.
vote_path = addon / 'Service/ThreadFreshness/Vote.php'
old = '''        $ownThread = (int)$this->thread->user_id === (int)$this->user->user_id;
        if ($ownThread && !(
            (bool)(\\XF::options()->wrxtFreshnessAllowOwnThread ?? false)
            && $this->user->hasPermission('wrxtFreshness', 'voteOwn')
        ))
        {
            throw new \\LogicException('Permission denied');
        }
'''
new = '''        $ownThread = (int)$this->thread->user_id === (int)$this->user->user_id;
        if ($ownThread && $this->thread->hasWrxtFreshnessOwnerClaim())
        {
            throw new \\LogicException('Permission denied');
        }
'''
replace_once(vote_path, old, new)

# Owner verification removal should visibly refresh the widget immediately.
mods_path = addon / '_data/template_modifications.xml'
replace_once(
    mods_path,
    '&lt;xf:form action="{{ link(\'threads/freshness-owner-claim\', $thread) }}" ajax="true"&gt;',
    '&lt;xf:form action="{{ link(\'threads/freshness-owner-claim\', $thread) }}"&gt;'
)
replace_once(
    mods_path,
    '&lt;span class="wrxtFreshness-saveHint"&gt;Oy, Kaydet butonuna bastığınızda işlenir.&lt;/span&gt;\n                      &lt;xf:button type="submit" class="button--primary wrxtFreshness-saveButton"&gt;Oyu kaydet&lt;/xf:button&gt;',
    '&lt;span class="wrxtFreshness-saveHint"&gt;{{ $wrxtVisitorVote ? \'Seçimini değiştirip yeniden kaydedebilirsin.\' : \'Seçimini yaptıktan sonra kaydet.\' }}&lt;/span&gt;\n                      &lt;xf:button type="submit" class="button--primary wrxtFreshness-saveButton"&gt;{{ $wrxtVisitorVote ? \'Oyunu güncelle\' : \'Oyu kaydet\' }}&lt;/xf:button&gt;'
)

# ACP navigation: use conservative XenForo/Font Awesome icon names.
nav_path = addon / '_data/admin_navigation.xml'
nav = nav_path.read_text(encoding='utf-8')
nav = nav.replace('icon="fa-chart-line"', 'icon="fa-chart-bar"')
nav_path.write_text(nav, encoding='utf-8')

# Version bump.
meta_path = addon / 'addon.json'
meta = json.loads(meta_path.read_text(encoding='utf-8'))
meta['version_id'] = 1010870
meta['version_string'] = '1.0.8'
meta_path.write_text(json.dumps(meta, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')

templates_path = addon / '_data/templates.xml'
t = templates_path.read_text(encoding='utf-8')
t = t.replace('title="wrxt_thread_freshness.less" version_id="1010770" version_string="1.0.7"',
              'title="wrxt_thread_freshness.less" version_id="1010870" version_string="1.0.8"')
templates_path.write_text(t, encoding='utf-8')

# Release tests/version metadata.
for rel in [root / 'tests/release_data.php', root / 'tests/release_static.php']:
    s = rel.read_text(encoding='utf-8')
    s = s.replace('1010770', '1010870').replace("'1.0.7'", "'1.0.8'")
    s = s.replace("['fa-history', 'fa-chart-line', 'fa-comments', 'fa-cog']",
                  "['fa-history', 'fa-chart-bar', 'fa-comments', 'fa-cog']")
    rel.write_text(s, encoding='utf-8')

release_static = root / 'tests/release_static.php'
s = release_static.read_text(encoding='utf-8')
marker = '''$widgetJsSource = (string)file_get_contents($widgetJs);'''
extra = '''if (!str_contains($threadEntity, '$ownerClaimActive') || !str_contains($threadEntity, '$allowOwnThread = !$ownThread || !$ownerClaimActive'))
{
    throw new RuntimeException('Owner vote unlock after removing owner verification is missing');
}
if (!str_contains($mods, 'Oyunu güncelle') || !str_contains($mods, 'Seçimini değiştirip yeniden kaydedebilirsin.'))
{
    throw new RuntimeException('Vote update UX is missing');
}

'''
if extra not in s:
    if marker not in s:
        raise SystemExit('release_static insertion marker missing')
    s = s.replace(marker, extra + marker, 1)
release_static.write_text(s, encoding='utf-8')

changelog = root / 'CHANGELOG.md'
c = changelog.read_text(encoding='utf-8')
header = '# Değişiklik Günlüğü\n\n'
section = '''## 1.0.8 Stable - Oy Güncelleme ve Sahip Akışı\n\n- Konu sahibi doğrulamasını kaldırdıktan sonra konu sahibi normal topluluk oylamasına katılabilir.\n- Mevcut oylar sonradan değiştirilebilir; Çalıştı seçimi ileride Çalışmadı olarak veya tersi yönde güncellenebilir.\n- Mevcut oy olduğunda ana buton “Oyunu güncelle” olarak görünür.\n- Konu sahibi doğrulamasını kaldırma işlemi tam sayfa yenilemeyle sonuçlanır; rozet ve oy kutuları anında doğru duruma geçer.\n- Masaüstünde güncellik kartının konu başlığıyla aynı satırdaki yerleşimi korunur; mobilde başlığın altına iner.\n- ACP navigasyon ikonları XenForo ile daha uyumlu Font Awesome adlarıyla güncellendi.\n\n'''
if c.startswith(header) and '## 1.0.8 Stable' not in c:
    changelog.write_text(header + section + c[len(header):], encoding='utf-8')

readme = root / 'README.md'
r = readme.read_text(encoding='utf-8')
r = r.replace('**1.0.7 Stable', '**1.0.8 Stable')
r = r.replace('Konu-Guncellik-1.0.7.zip', 'Konu-Guncellik-1.0.8.zip')
r = r.replace('/releases/download/v1.0.7/', '/releases/download/v1.0.8/')
r = r.replace('**1.0.7 Stable**', '**1.0.8 Stable**')
readme.write_text(r, encoding='utf-8')
