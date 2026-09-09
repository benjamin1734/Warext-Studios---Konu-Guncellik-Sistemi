from pathlib import Path
import json
import re
import xml.etree.ElementTree as ET

root = Path(__file__).resolve().parents[1]
addon = root / 'upload/src/addons/WarextStudios/ThreadFreshness'

# Compact the vote form while preserving the two-step select -> save flow.
mods_path = addon / '_data/template_modifications.xml'
tree = ET.parse(mods_path)
mods_root = tree.getroot()
mod = next((m for m in mods_root.findall('modification') if m.get('modification_key') == 'wrxt_thread_freshness_thread_view'), None)
if mod is None:
    raise SystemExit('Thread view template modification not found')
replace = mod.find('replace')
if replace is None or replace.text is None:
    raise SystemExit('Thread view replacement is empty')
text = replace.text

prompt = '''                  <div class="wrxtFreshness-votePrompt">
                      <strong>Bu çözüm sende çalıştı mı?</strong>
                      <span>Seçimini yap, ardından oyu kaydet.</span>
                  </div>
'''
if prompt not in text:
    raise SystemExit('Vote prompt block not found')
text = text.replace(prompt, '', 1)

text = text.replace(
    '<span class="wrxtFreshness-choiceText"><b>Çalıştı</b><small>Çözüm hâlâ geçerli</small></span>',
    '<span class="wrxtFreshness-choiceText"><b>Çalıştı</b></span>',
    1
)
text = text.replace(
    '<span class="wrxtFreshness-choiceText"><b>Çalışmadı</b><small>Çözüm artık geçersiz</small></span>',
    '<span class="wrxtFreshness-choiceText"><b>Çalışmadı</b></span>',
    1
)
text = text.replace(
    '<summary class="button button--link wrxtFreshness-moreButton">＋ Oy ayrıntısı</summary>',
    '<summary class="button button--link wrxtFreshness-moreButton">＋ Ayrıntı</summary>',
    1
)

save_pattern = re.compile(
    r'<div class="wrxtFreshness-saveBar">\s*'
    r'<span class="wrxtFreshness-saveHint">.*?</span>\s*'
    r'<xf:button type="submit" class="button--primary wrxtFreshness-saveButton">.*?</xf:button>\s*'
    r'</div>',
    re.S
)
text, count = save_pattern.subn(
    '<div class="wrxtFreshness-saveBar">\n'
    '                      <xf:button type="submit" class="button--primary wrxtFreshness-saveButton">{{ $wrxtVisitorVote ? \'Güncelle\' : \'Kaydet\' }}</xf:button>\n'
    '                  </div>',
    text,
    count=1
)
if count != 1:
    raise SystemExit('Vote save bar not found')
replace.text = text
ET.indent(tree, space='  ')
tree.write(mods_path, encoding='utf-8', xml_declaration=True)

# Replace the widget LESS with a compact XenForo-native layout.
templates_path = addon / '_data/templates.xml'
ttree = ET.parse(templates_path)
troot = ttree.getroot()
css_tpl = next((t for t in troot.findall('template') if t.get('title') == 'wrxt_thread_freshness.less'), None)
if css_tpl is None:
    raise SystemExit('Freshness LESS template not found')
css_tpl.set('version_id', '1011070')
css_tpl.set('version_string', '1.0.10')
css_tpl.text = '''.wrxtFreshness{width:100%;max-width:340px;margin:6px 0 10px auto;position:relative;z-index:3;font-size:12px}.wrxtFreshness>.block-container{border:1px solid @xf-borderColor;border-radius:8px;background:@xf-contentBg;box-shadow:0 2px 7px rgba(0,0,0,.1);overflow:visible}.wrxtFreshness-main{padding:8px}.wrxtFreshness-top{display:flex;align-items:center;justify-content:space-between;gap:6px}.wrxtFreshness-heading{display:flex;align-items:center;gap:5px;min-width:0;flex-wrap:wrap}.wrxtFreshness-title{font-size:12px;line-height:1.2;white-space:nowrap}.wrxtFreshness-heading .label{font-size:9px;line-height:1.2;padding:2px 5px}.wrxtFreshness-icons{display:flex;align-items:center;gap:4px;flex:0 0 auto}.wrxtFreshness-pop{position:relative}.wrxtFreshness-pop>summary,.wrxtFreshness-more>summary{list-style:none;cursor:pointer}.wrxtFreshness-pop>summary::-webkit-details-marker,.wrxtFreshness-more>summary::-webkit-details-marker{display:none}.wrxtFreshness-toolButton.button,.wrxtFreshness-moreButton.button{min-width:0;padding:4px 7px;font-size:10px;line-height:1.15;border-radius:6px}.wrxtFreshness-pop[open]>.wrxtFreshness-toolButton{border-color:@xf-linkColor;box-shadow:inset 0 0 0 1px @xf-linkColor}.wrxtFreshness-popBody{position:absolute;right:0;top:calc(100% + 5px);width:255px;padding:9px;border:1px solid @xf-borderColor;border-radius:7px;background:@xf-contentBg;box-shadow:0 7px 22px rgba(0,0,0,.22);z-index:30;font-size:11px}.wrxtFreshness-popBody p{margin:4px 0 0;color:@xf-textColorMuted;line-height:1.35}.wrxtFreshness-popBody--actions form+form{margin-top:6px}.wrxtFreshness-actionForm{display:flex;gap:5px;align-items:center}.wrxtFreshness-actionForm .input,.wrxtFreshness-actionForm select{min-width:0;flex:1 1 auto}.wrxtFreshness-meta{display:flex;gap:4px;align-items:center;flex-wrap:wrap;margin-top:6px}.wrxtFreshness-meta>span{display:inline-flex;align-items:center;min-height:20px;padding:1px 6px;border:1px solid @xf-borderColor;border-radius:999px;background:@xf-contentAltBg;color:@xf-textColorMuted;font-size:10px}.wrxtFreshness-meta b{color:@xf-textColor}.wrxtFreshness-ownerBadge{color:@xf-linkColor!important}.wrxtFreshness-replacement{margin-top:6px;font-size:11px}.wrxtFreshness-voteForm{margin-top:7px;padding-top:7px;border-top:1px solid @xf-borderColor;display:grid;grid-template-columns:minmax(0,1fr) auto;gap:6px;align-items:center}.wrxtFreshness-choiceGroup{display:flex;align-items:center;gap:5px;min-width:0}.wrxtFreshness-choice{position:relative;display:block;min-width:0;cursor:pointer}.wrxtFreshness-choiceInput{position:absolute;opacity:0;width:1px;height:1px;pointer-events:none}.wrxtFreshness-choiceSurface.button{width:auto;min-width:0;min-height:32px;display:inline-flex;align-items:center;justify-content:center;gap:5px;padding:4px 8px;text-align:center;white-space:nowrap;border-radius:6px;border:1px solid @xf-borderColor;background:@xf-contentAltBg;color:@xf-textColor;box-shadow:none;transition:border-color .12s ease,background .12s ease,box-shadow .12s ease}.wrxtFreshness-choice:hover .wrxtFreshness-choiceSurface{border-color:@xf-linkColor}.wrxtFreshness-choiceInput:focus+.wrxtFreshness-choiceSurface{box-shadow:0 0 0 2px fade(@xf-linkColor,22%)}.wrxtFreshness-choiceMark{width:18px;height:18px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;flex:0 0 18px;border:1px solid @xf-borderColor;background:@xf-contentBg;font-size:10px;font-weight:700}.wrxtFreshness-choiceText{display:inline-flex;align-items:center;line-height:1}.wrxtFreshness-choiceText b{font-size:11px}.wrxtFreshness-choice--yes .wrxtFreshness-choiceInput:checked+.wrxtFreshness-choiceSurface{border-color:#43a047;background:fade(#43a047,16%);box-shadow:inset 0 0 0 1px fade(#43a047,40%)}.wrxtFreshness-choice--yes .wrxtFreshness-choiceInput:checked+.wrxtFreshness-choiceSurface .wrxtFreshness-choiceMark{border-color:#43a047;background:#43a047;color:#fff}.wrxtFreshness-choice--no .wrxtFreshness-choiceInput:checked+.wrxtFreshness-choiceSurface{border-color:#d9534f;background:fade(#d9534f,15%);box-shadow:inset 0 0 0 1px fade(#d9534f,40%)}.wrxtFreshness-choice--no .wrxtFreshness-choiceInput:checked+.wrxtFreshness-choiceSurface .wrxtFreshness-choiceMark{border-color:#d9534f;background:#d9534f;color:#fff}.wrxtFreshness-saveBar{grid-column:2;grid-row:1;margin:0;padding:0;border:0}.wrxtFreshness-saveButton.button{min-width:0;min-height:32px;padding:4px 9px;font-size:11px}.wrxtFreshness-voteDetails{grid-column:1/-1;margin-top:0}.wrxtFreshness-moreButton.button{display:inline-flex}.wrxtFreshness-fields,.wrxtFreshness-resultsBody{margin-top:6px;padding:7px;border:1px solid @xf-borderColor;border-radius:7px;background:@xf-contentAltBg}.wrxtFreshness-fields{display:grid;gap:6px}.wrxtFreshness-fields label{display:grid;gap:3px}.wrxtFreshness-fields label>span{color:@xf-textColorMuted;font-size:10px}.wrxtFreshness-more{margin-top:6px}.wrxtFreshness-resultsBody strong{display:block;margin:5px 0 3px;font-size:10px}.wrxtFreshness-main--waiting{padding:8px}@media(max-width:@xf-responsiveMedium){.wrxtFreshness{width:100%;max-width:none;margin:6px 0 9px}.wrxtFreshness-popBody{position:fixed;left:12px;right:12px;top:auto;width:auto}}@media(max-width:420px){.wrxtFreshness-main{padding:7px}.wrxtFreshness-voteForm{grid-template-columns:1fr auto;gap:5px}.wrxtFreshness-choiceGroup{gap:4px}.wrxtFreshness-choiceSurface.button{min-height:30px;padding:3px 6px}.wrxtFreshness-choiceMark{width:16px;height:16px;flex-basis:16px}.wrxtFreshness-choiceText b{font-size:10px}.wrxtFreshness-saveButton.button{min-height:30px;padding:3px 7px}}@media(min-width:651px){.p-title>.wrxtFreshness{flex:0 0 340px;align-self:flex-start;min-width:285px}.p-title>.wrxtFreshness>.block-container{margin:0}}'''
ET.indent(ttree, space='  ')
ttree.write(templates_path, encoding='utf-8', xml_declaration=True)

# Version bump.
meta_path = addon / 'addon.json'
meta = json.loads(meta_path.read_text(encoding='utf-8'))
meta['version_id'] = 1011070
meta['version_string'] = '1.0.10'
meta_path.write_text(json.dumps(meta, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')

# Release guards.
for rel in [root / 'tests/release_data.php', root / 'tests/release_static.php']:
    s = rel.read_text(encoding='utf-8').replace('1010970', '1011070').replace("'1.0.9'", "'1.0.10'")
    rel.write_text(s, encoding='utf-8')

release_static = root / 'tests/release_static.php'
s = release_static.read_text(encoding='utf-8')
s = s.replace("!str_contains($mods, 'Oyu kaydet')", "!str_contains($mods, \"'Güncelle' : 'Kaydet'\")")
marker = "$widgetJsSource = (string)file_get_contents($widgetJs);"
extra = '''if (str_contains($mods, 'Bu çözüm sende çalıştı mı?') || str_contains($mods, 'Seçimini yap, ardından oyu kaydet.') || str_contains($mods, 'Çözüm hâlâ geçerli') || str_contains($mods, 'Çözüm artık geçersiz'))
{
    throw new RuntimeException('Verbose vote UI text still exists');
}
if (!str_contains($mods, "'Güncelle' : 'Kaydet'") || !str_contains($templatesData, 'grid-template-columns:minmax(0,1fr) auto') || !str_contains($templatesData, 'min-height:32px'))
{
    throw new RuntimeException('Compact vote UI contract is missing');
}

'''
if extra not in s:
    if marker not in s:
        raise SystemExit('release_static marker missing')
    s = s.replace(marker, extra + marker, 1)
release_static.write_text(s, encoding='utf-8')

# Changelog / README.
changelog = root / 'CHANGELOG.md'
c = changelog.read_text(encoding='utf-8')
header = '# Değişiklik Günlüğü\n\n'
section = '''## 1.0.10 Stable - Kompakt Oylama Alanı\n\n- Oylama alanındaki açıklama başlığı ve yardımcı metinler kaldırıldı.\n- Çalıştı / Çalışmadı seçimleri geniş kartlar yerine küçük XenForo uyumlu seçim butonlarına dönüştürüldü.\n- Seçili butonun dolu renk durumu korunur; oy yalnızca Kaydet/Güncelle butonuyla işlenir.\n- Kaydet/Güncelle butonu seçimlerin yanına alınarak panel yüksekliği belirgin şekilde azaltıldı.\n- Oy ayrıntısı bağlantısı kısaltıldı ve isteğe bağlı alanlar kapalı kalmaya devam eder.\n- Masaüstü kart genişliği 340px'e düşürüldü; mobil görünüm kompakt şekilde korunur.\n\n'''
if c.startswith(header) and '## 1.0.10 Stable' not in c:
    changelog.write_text(header + section + c[len(header):], encoding='utf-8')

readme = root / 'README.md'
r = readme.read_text(encoding='utf-8')
r = r.replace('1.0.9 Stable', '1.0.10 Stable')
r = r.replace('Konu-Guncellik-1.0.9.zip', 'Konu-Guncellik-1.0.10.zip')
r = r.replace('/releases/download/v1.0.9/', '/releases/download/v1.0.10/')
readme.write_text(r, encoding='utf-8')
