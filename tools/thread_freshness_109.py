from pathlib import Path
import json

root = Path(__file__).resolve().parents[1]
addon = root / 'upload/src/addons/WarextStudios/ThreadFreshness'

def replace_once(path, old, new):
    text = path.read_text(encoding='utf-8')
    if old not in text:
        raise SystemExit(f'Expected block not found: {path}')
    path.write_text(text.replace(old, new, 1), encoding='utf-8')

thread = addon / 'XF/Entity/Thread.php'
replace_once(thread, '''        $visitor = \\XF::visitor();
        $ownThread = (int)$visitor->user_id > 0 && (int)$visitor->user_id === (int)$this->user_id;
        $ownerClaimActive = $ownThread && $this->hasWrxtFreshnessOwnerClaim();
        $allowOwnThread = !$ownThread || !$ownerClaimActive;

        if (!Eligibility::canVisitorVote(
            (int)$visitor->user_id,
            (int)$this->user_id,
            (int)$visitor->register_date,
            (int)$visitor->message_count,
            $visitor->hasPermission('wrxtFreshness', 'vote'),
            $allowOwnThread,
            (int)(\\XF::options()->wrxtFreshnessMinAccountDays ?? 7),
            (int)(\\XF::options()->wrxtFreshnessMinMessages ?? 3),
            \\XF::$time
        ))
        {
            return false;
        }

        return true;
''', '''        $visitor = \\XF::visitor();
        if ((int)$visitor->user_id <= 0)
        {
            return false;
        }

        $ownThread = (int)$visitor->user_id === (int)$this->user_id;
        if ($ownThread)
        {
            return !$this->hasWrxtFreshnessOwnerClaim();
        }

        return Eligibility::canVisitorVote(
            (int)$visitor->user_id,
            (int)$this->user_id,
            (int)$visitor->register_date,
            (int)$visitor->message_count,
            $visitor->hasPermission('wrxtFreshness', 'vote'),
            true,
            (int)(\\XF::options()->wrxtFreshnessMinAccountDays ?? 7),
            (int)(\\XF::options()->wrxtFreshnessMinMessages ?? 3),
            \\XF::$time
        );
''')

vote = addon / 'Service/ThreadFreshness/Vote.php'
replace_once(vote, '''        if (!$this->user->hasPermission('wrxtFreshness', 'vote'))
        {
            throw new \\LogicException('Permission denied');
        }

        $ownThread = (int)$this->thread->user_id === (int)$this->user->user_id;
''', '''        $ownThread = (int)$this->thread->user_id === (int)$this->user->user_id;
''')

meta_path = addon / 'addon.json'
meta = json.loads(meta_path.read_text(encoding='utf-8'))
meta['version_id'] = 1010970
meta['version_string'] = '1.0.9'
meta_path.write_text(json.dumps(meta, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')

release_data = root / 'tests/release_data.php'
s = release_data.read_text(encoding='utf-8').replace('1010870', '1010970').replace("'1.0.8'", "'1.0.9'")
release_data.write_text(s, encoding='utf-8')

release_static = root / 'tests/release_static.php'
s = release_static.read_text(encoding='utf-8').replace('1010870', '1010970').replace("'1.0.8'", "'1.0.9'")
s = s.replace("if (!str_contains($threadEntity, '$ownerClaimActive') || !str_contains($threadEntity, '$allowOwnThread = !$ownThread || !$ownerClaimActive'))\n{\n    throw new RuntimeException('Owner vote unlock after removing owner verification is missing');\n}", "if (!str_contains($threadEntity, 'return !$this->hasWrxtFreshnessOwnerClaim();'))\n{\n    throw new RuntimeException('Owner vote controls do not unlock after removing owner verification');\n}\nif (str_contains($voteService, \"if (!\\$this->user->hasPermission('wrxtFreshness', 'vote'))\"))\n{\n    throw new RuntimeException('Vote service still blocks the topic owner after owner verification is removed');\n}")
release_static.write_text(s, encoding='utf-8')

changelog = root / 'CHANGELOG.md'
c = changelog.read_text(encoding='utf-8')
header = '# Değişiklik Günlüğü\n\n'
section = '''## 1.0.9 Stable - Oylama Kutularının Görünürlük Düzeltmesi\n\n- Konu sahibi doğrulamayı kaldırdıktan sonra Çalıştı / Çalışmadı seçim kutuları artık kesin olarak görünür.\n- Konu sahibinin normal topluluk oylamasına geçişi artık oy izni, minimum mesaj ve hesap yaşı koşullarına takılmaz; konu sahibi doğrulaması ile topluluk oyu aynı anda kullanılamaz.\n- Diğer kullanıcılar için oy izni, minimum mesaj ve minimum hesap yaşı korumaları aynen devam eder.\n- Oy değiştirme ve Oyunu güncelle akışı korunur.\n\n'''
if c.startswith(header) and '## 1.0.9 Stable' not in c:
    changelog.write_text(header + section + c[len(header):], encoding='utf-8')

readme = root / 'README.md'
r = readme.read_text(encoding='utf-8')
r = r.replace('1.0.8 Stable', '1.0.9 Stable')
r = r.replace('Konu-Guncellik-1.0.8.zip', 'Konu-Guncellik-1.0.9.zip')
r = r.replace('/releases/download/v1.0.8/', '/releases/download/v1.0.9/')
readme.write_text(r, encoding='utf-8')
