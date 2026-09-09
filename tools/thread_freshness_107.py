from pathlib import Path
import json
import re
import xml.etree.ElementTree as ET

root = Path('.')
addon = root / 'upload/src/addons/WarextStudios/ThreadFreshness'

thread_path = addon / 'XF/Entity/Thread.php'
t = thread_path.read_text(encoding='utf-8')
pattern = re.compile(r"\n\s*\$vote = \$this->getWrxtFreshnessVisitorVoteEntity\(\);.*?return \$visitor->hasPermission\('wrxtFreshness', 'changeVote'\);", re.S)
t2, n = pattern.subn("\n\n        return true;", t, count=1)
if n != 1:
    raise SystemExit(f'Thread changeVote replacement failed: {n}')
thread_path.write_text(t2, encoding='utf-8')

vote_path = addon / 'Service/ThreadFreshness/Vote.php'
v = vote_path.read_text(encoding='utf-8')
guard = re.compile(r"\n\s*if \(!\$isStaleCycleVote && !\$this->user->hasPermission\('wrxtFreshness', 'changeVote'\)\)\s*\{\s*throw new \\LogicException\('Permission denied'\);\s*\}", re.S)
v2, n = guard.subn('', v, count=1)
if n != 1:
    raise SystemExit(f'Vote service changeVote guard removal failed: {n}')
vote_path.write_text(v2, encoding='utf-8')

js = """(function () {
    'use strict';
    function placeFreshnessWidget() {
        var widget = document.getElementById('wrxt-thread-freshness');
        var title = document.querySelector('.p-title');
        if (!widget || !title || !title.parentNode) return;
        var mobile = window.matchMedia && window.matchMedia('(max-width: 650px)').matches;
        if (mobile) {
            if (widget.parentNode !== title.parentNode || widget.previousElementSibling !== title) title.insertAdjacentElement('afterend', widget);
            widget.style.width = '100%';
            widget.style.maxWidth = 'none';
            widget.style.margin = '7px 0 10px';
        } else {
            if (widget.parentNode !== title) title.appendChild(widget);
            widget.style.width = '100%';
            widget.style.maxWidth = '360px';
            widget.style.margin = '0 0 0 auto';
        }
        widget.dataset.wrxtPlaced = '1';
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', placeFreshnessWidget, {once:true}); else placeFreshnessWidget();
    window.addEventListener('load', placeFreshnessWidget, {once:true});
    window.addEventListener('resize', placeFreshnessWidget);
}());
"""
(root / 'upload/js/warext/thread_freshness.js').write_text(js, encoding='utf-8')

nav_path = addon / '_data/admin_navigation.xml'
nav_tree = ET.parse(nav_path)
icons = {
    'wrxtThreadFreshness': 'fa-history',
    'wrxtThreadFreshnessDashboard': 'fa-chart-line',
    'wrxtThreadFreshnessForums': 'fa-comments',
    'wrxtThreadFreshnessSettings': 'fa-cog'
}
for entry in nav_tree.getroot().findall('admin_navigation_entry'):
    nav_id = entry.attrib.get('navigation_id')
    if nav_id in icons:
        entry.attrib['icon'] = icons[nav_id]
ET.indent(nav_tree, space='  ')
nav_tree.write(nav_path, encoding='utf-8', xml_declaration=True)

templates_path = addon / '_data/templates.xml'
tree = ET.parse(templates_path)
less = next((x for x in tree.getroot().findall('template') if x.attrib.get('type') == 'public' and x.attrib.get('title') == 'wrxt_thread_freshness.less'), None)
if less is None:
    raise SystemExit('LESS template missing')
css = less.text or ''
extra = '@media(min-width:651px){.p-title>.wrxtFreshness{flex:0 0 360px;align-self:flex-start;min-width:300px}.p-title>.wrxtFreshness>.block-container{margin:0}}'
if extra not in css:
    css += extra
less.text = css
less.attrib['version_id'] = '1010770'
less.attrib['version_string'] = '1.0.7'
ET.indent(tree, space='  ')
tree.write(templates_path, encoding='utf-8', xml_declaration=True)

addon_json = addon / 'addon.json'
data = json.loads(addon_json.read_text(encoding='utf-8'))
data['version_id'] = 1010770
data['version_string'] = '1.0.7'
addon_json.write_text(json.dumps(data, ensure_ascii=False, indent=4) + '\n', encoding='utf-8')

for path in [root / 'tests/release_data.php', root / 'tests/release_static.php']:
    s = path.read_text(encoding='utf-8').replace('1010670', '1010770').replace("'1.0.6'", "'1.0.7'")
    path.write_text(s, encoding='utf-8')

static = root / 'tests/release_static.php'
s = static.read_text(encoding='utf-8')
s = s.replace("foreach (['fa-chart-line', 'fa-folder-tree', 'fa-sliders'] as $icon)", "foreach (['fa-history', 'fa-chart-line', 'fa-comments', 'fa-cog'] as $icon)")
marker = 'echo "OK\\n";'
checks = '''$voteService = (string)file_get_contents($addon . '/Service/ThreadFreshness/Vote.php');
if (str_contains($threadEntity, "hasPermission('wrxtFreshness', 'changeVote')") || str_contains($voteService, "hasPermission('wrxtFreshness', 'changeVote')"))
{
    throw new RuntimeException('Vote changing is still restricted by legacy changeVote permission');
}
$widgetJsSource = (string)file_get_contents($widgetJs);
if (!str_contains($widgetJsSource, 'title.appendChild(widget)') || !str_contains($widgetJsSource, "title.insertAdjacentElement('afterend', widget)"))
{
    throw new RuntimeException('Desktop title-row / mobile below-title placement is missing');
}

'''
if checks not in s:
    s = s.replace(marker, checks + marker)
static.write_text(s, encoding='utf-8')

readme = root / 'README.md'
r = readme.read_text(encoding='utf-8')
r = r.replace('**1.0.6 Stable - Belirgin Kontroller**', '**1.0.7 Stable - Başlık Hizası ve Oy Değiştirme**')
r = r.replace('XenForo-ACP-Direct-Install-Warext-Konu-Guncellik-1.0.6.zip', 'XenForo-ACP-Direct-Install-Warext-Konu-Guncellik-1.0.7.zip')
r = r.replace('/releases/download/v1.0.6/', '/releases/download/v1.0.7/')
r = r.replace('Bu paket resmi **1.0.6 Stable** sürümüdür.', 'Bu paket resmi **1.0.7 Stable** sürümüdür.')
readme.write_text(r, encoding='utf-8')

changelog = root / 'CHANGELOG.md'
c = changelog.read_text(encoding='utf-8')
head = '# Değişiklik Günlüğü\n\n'
section = '''## 1.0.7 Stable - Başlık Hizası ve Oy Değiştirme\n\n- Masaüstünde konu güncellik paneli konu başlığıyla aynı satırda sağ tarafa hizalandı.\n- Mobilde panel başlığın hemen altında tam genişlikte kalacak şekilde responsive yerleşim güncellendi.\n- Kullanıcıların aynı doğrulama döngüsünde kendi oylarını daha sonra değiştirebilmesi sağlandı.\n- ACP ana başlık ve alt menü ikonları XenForo çekirdeğinde bulunan uyumlu Font Awesome ikonlarıyla değiştirildi.\n\n'''
if c.startswith(head) and section not in c:
    c = head + section + c[len(head):]
changelog.write_text(c, encoding='utf-8')
