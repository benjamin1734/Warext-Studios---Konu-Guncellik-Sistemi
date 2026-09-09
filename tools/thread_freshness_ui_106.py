from pathlib import Path
import json
import re
import xml.etree.ElementTree as ET

root = Path(__file__).resolve().parents[1]
addon = root / 'upload/src/addons/WarextStudios/ThreadFreshness'
mods_path = addon / '_data/template_modifications.xml'
templates_path = addon / '_data/templates.xml'
nav_path = addon / '_data/admin_navigation.xml'
js_path = root / 'upload/js/warext/thread_freshness.js'

# Thread view widget markup
mods_tree = ET.parse(mods_path)
mods_root = mods_tree.getroot()
thread_mod = None
for mod in mods_root.findall('modification'):
    if mod.attrib.get('modification_key') == 'wrxt_thread_freshness_thread_view':
        thread_mod = mod
        break
if thread_mod is None:
    raise SystemExit('thread_view modification not found')
replace = thread_mod.find('replace')
text = replace.text or ''
text = text.replace(
    '<div class="wrxtFreshness" id="wrxt-thread-freshness">',
    '<div class="wrxtFreshness" id="wrxt-thread-freshness" style="width:100%;max-width:360px;margin:8px 0 12px auto;">'
)
text = text.replace(
    '<summary title="Bu sistem ne işe yarar?" aria-label="Bu sistem ne işe yarar?">?</summary>',
    '<summary class="button button--link wrxtFreshness-toolButton" title="Bu sistem ne işe yarar?" aria-label="Bu sistem ne işe yarar?"><span aria-hidden="true">ⓘ</span> Bilgi</summary>'
)
text = text.replace(
    '<summary title="İşlemler" aria-label="İşlemler">•••</summary>',
    '<summary class="button button--link wrxtFreshness-toolButton" title="İşlemler" aria-label="İşlemler"><span aria-hidden="true">⚙</span> İşlemler</summary>'
)
text = text.replace(
    '<summary title="Bilgi">?</summary>',
    '<summary class="button button--link wrxtFreshness-toolButton" title="Bilgi"><span aria-hidden="true">ⓘ</span> Bilgi</summary>'
)

vote_block = '''<xf:if is="$thread.canWrxtFreshnessVote()">
              <xf:form action="{{ link('threads/freshness-vote', $thread) }}" ajax="true" class="wrxtFreshness-voteForm">
                  <div class="wrxtFreshness-votePrompt">
                      <strong>Bu çözüm sende çalıştı mı?</strong>
                      <span>Seçimini yap, ardından oyu kaydet.</span>
                  </div>
                  <div class="wrxtFreshness-choiceGroup" role="radiogroup" aria-label="Çözüm sonucu">
                      <label class="wrxtFreshness-choice wrxtFreshness-choice--yes">
                          <input class="wrxtFreshness-choiceInput" type="radio" name="vote" value="1" required="required" checked="{{ $wrxtVisitorVote == 1 ? 'checked' : '' }}" />
                          <span class="button button--link wrxtFreshness-choiceSurface">
                              <span class="wrxtFreshness-choiceMark" aria-hidden="true">✓</span>
                              <span class="wrxtFreshness-choiceText"><b>Çalıştı</b><small>Çözüm hâlâ geçerli</small></span>
                          </span>
                      </label>
                      <label class="wrxtFreshness-choice wrxtFreshness-choice--no">
                          <input class="wrxtFreshness-choiceInput" type="radio" name="vote" value="-1" required="required" checked="{{ $wrxtVisitorVote == -1 ? 'checked' : '' }}" />
                          <span class="button button--link wrxtFreshness-choiceSurface">
                              <span class="wrxtFreshness-choiceMark" aria-hidden="true">✕</span>
                              <span class="wrxtFreshness-choiceText"><b>Çalışmadı</b><small>Çözüm artık geçersiz</small></span>
                          </span>
                      </label>
                  </div>
                  <details class="wrxtFreshness-more wrxtFreshness-voteDetails">
                      <summary class="button button--link wrxtFreshness-moreButton">＋ Oy ayrıntısı</summary>
                      <div class="wrxtFreshness-fields">
                          <label><span>Sürüm</span>
                              <xf:if is="$wrxtVersions">
                                  <xf:select name="version" value="{{ $wrxtVoteEntity ? $wrxtVoteEntity.version : '' }}">
                                      <xf:option value="">Seçin</xf:option>
                                      <xf:foreach loop="$wrxtVersions" value="$wrxtVersion"><xf:option value="{$wrxtVersion}">{$wrxtVersion}</xf:option></xf:foreach>
                                  </xf:select>
                              <xf:else />
                                  <xf:textbox name="version" value="{{ $wrxtVoteEntity ? $wrxtVoteEntity.version : '' }}" maxlength="100" />
                              </xf:if>
                          </label>
                          <label><span>Neden</span>
                              <xf:select name="reason" value="{{ $wrxtVoteEntity ? $wrxtVoteEntity.reason : '' }}">
                                  <xf:option value="">Seçmek istemiyorum</xf:option>
                                  <xf:option value="outdated_version">Yeni sürümde çalışmıyor</xf:option>
                                  <xf:option value="dead_links">Dosya/bağlantı çalışmıyor</xf:option>
                                  <xf:option value="method_invalid">Yöntem geçersiz</xf:option>
                                  <xf:option value="incomplete">Çözüm eksik</xf:option>
                                  <xf:option value="did_not_work">Sistemde çalışmadı</xf:option>
                                  <xf:option value="other">Diğer</xf:option>
                              </xf:select>
                          </label>
                          <label><span>Açıklama</span><xf:textbox name="message" value="{{ $wrxtVoteEntity ? $wrxtVoteEntity.message : '' }}" maxlength="500" /></label>
                          <label><span>Güncel konu ID</span><xf:numberbox name="alternative_thread_id" value="{{ $wrxtVoteEntity ? $wrxtVoteEntity.alternative_thread_id : 0 }}" min="0" /></label>
                      </div>
                  </details>
                  <div class="wrxtFreshness-saveBar">
                      <span class="wrxtFreshness-saveHint">Oy, Kaydet butonuna bastığınızda işlenir.</span>
                      <xf:button type="submit" class="button--primary wrxtFreshness-saveButton">Oyu kaydet</xf:button>
                  </div>
              </xf:form>
          </xf:if>'''

start = text.find('<xf:if is="$thread.canWrxtFreshnessVote()">')
end_marker = '\n\n          <xf:if is="$wrxtReasonSummary OR $wrxtVersionSummary">'
end = text.find(end_marker, start)
if start == -1 or end == -1:
    raise SystemExit('vote form block not found')
text = text[:start] + vote_block + text[end:]
replace.text = text
ET.indent(mods_tree, space='  ')
mods_tree.write(mods_path, encoding='utf-8', xml_declaration=True)

# Public LESS template
css = r'''.wrxtFreshness{width:100%;max-width:360px;margin:8px 0 12px auto;position:relative;z-index:3;font-size:13px}.wrxtFreshness>.block-container{border:1px solid @xf-borderColor;border-radius:8px;background:@xf-contentBg;box-shadow:0 2px 8px rgba(0,0,0,.12);overflow:visible}.wrxtFreshness-main{padding:10px}.wrxtFreshness-top{display:flex;align-items:center;justify-content:space-between;gap:8px}.wrxtFreshness-heading{display:flex;align-items:center;gap:6px;min-width:0;flex-wrap:wrap}.wrxtFreshness-title{font-size:13px;line-height:1.25;white-space:nowrap}.wrxtFreshness-heading .label{font-size:10px;line-height:1.25;padding:2px 6px}.wrxtFreshness-icons{display:flex;align-items:center;gap:5px;flex:0 0 auto}.wrxtFreshness-pop{position:relative}.wrxtFreshness-pop>summary,.wrxtFreshness-more>summary{list-style:none;cursor:pointer}.wrxtFreshness-pop>summary::-webkit-details-marker,.wrxtFreshness-more>summary::-webkit-details-marker{display:none}.wrxtFreshness-toolButton.button,.wrxtFreshness-moreButton.button{min-width:0;padding:5px 8px;font-size:11px;line-height:1.2;border-radius:6px}.wrxtFreshness-pop[open]>.wrxtFreshness-toolButton{border-color:@xf-linkColor;box-shadow:inset 0 0 0 1px @xf-linkColor}.wrxtFreshness-popBody{position:absolute;right:0;top:calc(100% + 6px);width:270px;padding:10px;border:1px solid @xf-borderColor;border-radius:7px;background:@xf-contentBg;box-shadow:0 7px 24px rgba(0,0,0,.24);z-index:30;font-size:12px}.wrxtFreshness-popBody p{margin:5px 0 0;color:@xf-textColorMuted;line-height:1.4}.wrxtFreshness-popBody--actions form+form{margin-top:7px}.wrxtFreshness-actionForm{display:flex;gap:6px;align-items:center}.wrxtFreshness-actionForm .input,.wrxtFreshness-actionForm select{min-width:0;flex:1 1 auto}.wrxtFreshness-meta{display:flex;gap:5px;align-items:center;flex-wrap:wrap;margin-top:8px}.wrxtFreshness-meta>span{display:inline-flex;align-items:center;min-height:22px;padding:2px 7px;border:1px solid @xf-borderColor;border-radius:999px;background:@xf-contentAltBg;color:@xf-textColorMuted;font-size:11px}.wrxtFreshness-meta b{color:@xf-textColor}.wrxtFreshness-ownerBadge{color:@xf-linkColor!important}.wrxtFreshness-replacement{margin-top:8px;font-size:12px}.wrxtFreshness-voteForm{margin-top:10px;padding-top:10px;border-top:1px solid @xf-borderColor}.wrxtFreshness-votePrompt{display:flex;flex-direction:column;gap:2px;margin-bottom:7px}.wrxtFreshness-votePrompt strong{font-size:12px}.wrxtFreshness-votePrompt span{color:@xf-textColorMuted;font-size:11px}.wrxtFreshness-choiceGroup{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:7px}.wrxtFreshness-choice{position:relative;display:block;min-width:0;cursor:pointer}.wrxtFreshness-choiceInput{position:absolute;opacity:0;width:1px;height:1px;pointer-events:none}.wrxtFreshness-choiceSurface.button{width:100%;min-height:50px;display:flex;align-items:center;justify-content:flex-start;gap:8px;padding:7px 8px;text-align:left;white-space:normal;border-radius:7px;border:1px solid @xf-borderColor;background:@xf-contentAltBg;color:@xf-textColor;box-shadow:none;transition:border-color .12s ease,background .12s ease,box-shadow .12s ease,transform .12s ease}.wrxtFreshness-choice:hover .wrxtFreshness-choiceSurface{border-color:@xf-linkColor;transform:translateY(-1px)}.wrxtFreshness-choiceInput:focus+.wrxtFreshness-choiceSurface{box-shadow:0 0 0 2px fade(@xf-linkColor,22%)}.wrxtFreshness-choiceMark{width:25px;height:25px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;flex:0 0 25px;border:1px solid @xf-borderColor;background:@xf-contentBg;font-weight:700}.wrxtFreshness-choiceText{display:flex;flex-direction:column;min-width:0;line-height:1.15}.wrxtFreshness-choiceText b{font-size:12px}.wrxtFreshness-choiceText small{margin-top:2px;color:@xf-textColorMuted;font-size:9px}.wrxtFreshness-choice--yes .wrxtFreshness-choiceInput:checked+.wrxtFreshness-choiceSurface{border-color:#43a047;background:fade(#43a047,16%);box-shadow:inset 0 0 0 1px fade(#43a047,45%)}.wrxtFreshness-choice--yes .wrxtFreshness-choiceInput:checked+.wrxtFreshness-choiceSurface .wrxtFreshness-choiceMark{border-color:#43a047;background:#43a047;color:#fff}.wrxtFreshness-choice--no .wrxtFreshness-choiceInput:checked+.wrxtFreshness-choiceSurface{border-color:#d9534f;background:fade(#d9534f,15%);box-shadow:inset 0 0 0 1px fade(#d9534f,42%)}.wrxtFreshness-choice--no .wrxtFreshness-choiceInput:checked+.wrxtFreshness-choiceSurface .wrxtFreshness-choiceMark{border-color:#d9534f;background:#d9534f;color:#fff}.wrxtFreshness-voteDetails{margin-top:7px}.wrxtFreshness-moreButton.button{display:inline-flex}.wrxtFreshness-fields,.wrxtFreshness-resultsBody{margin-top:7px;padding:8px;border:1px solid @xf-borderColor;border-radius:7px;background:@xf-contentAltBg}.wrxtFreshness-fields{display:grid;gap:7px}.wrxtFreshness-fields label{display:grid;gap:3px}.wrxtFreshness-fields label>span{color:@xf-textColorMuted;font-size:10px}.wrxtFreshness-saveBar{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:8px;padding-top:8px;border-top:1px solid @xf-borderColor}.wrxtFreshness-saveHint{max-width:170px;color:@xf-textColorMuted;font-size:10px;line-height:1.25}.wrxtFreshness-saveButton.button{min-width:105px;padding-left:12px;padding-right:12px}.wrxtFreshness-more{margin-top:7px}.wrxtFreshness-resultsBody strong{display:block;margin:6px 0 3px;font-size:11px}.wrxtFreshness-main--waiting{padding:9px 10px}@media(max-width:@xf-responsiveMedium){.wrxtFreshness{width:100%;max-width:none;margin:7px 0 10px}.wrxtFreshness-popBody{position:fixed;left:12px;right:12px;top:auto;width:auto}}@media(max-width:420px){.wrxtFreshness-choiceText small{display:none}.wrxtFreshness-choiceSurface.button{min-height:43px;padding:6px 7px}.wrxtFreshness-saveHint{display:none}.wrxtFreshness-saveButton.button{width:100%}}'''

templates_tree = ET.parse(templates_path)
templates_root = templates_tree.getroot()
less = None
for tpl in templates_root.findall('template'):
    if tpl.attrib.get('type') == 'public' and tpl.attrib.get('title') == 'wrxt_thread_freshness.less':
        less = tpl
        break
if less is None:
    raise SystemExit('LESS template not found')
less.attrib['version_id'] = '1010670'
less.attrib['version_string'] = '1.0.6'
less.text = css
ET.indent(templates_tree, space='  ')
templates_tree.write(templates_path, encoding='utf-8', xml_declaration=True)

# ACP navigation icons
nav_tree = ET.parse(nav_path)
nav_root = nav_tree.getroot()
icons = {
    'wrxtThreadFreshnessDashboard': 'fa-chart-line',
    'wrxtThreadFreshnessForums': 'fa-folder-tree',
    'wrxtThreadFreshnessSettings': 'fa-sliders'
}
for entry in nav_root.findall('admin_navigation_entry'):
    nav_id = entry.attrib.get('navigation_id')
    if nav_id in icons:
        entry.attrib['icon'] = icons[nav_id]
ET.indent(nav_tree, space='  ')
nav_tree.write(nav_path, encoding='utf-8', xml_declaration=True)

# Responsive placement safety
js_path.write_text("""(function () {\n    'use strict';\n    function placeFreshnessWidget() {\n        var widget = document.getElementById('wrxt-thread-freshness');\n        if (!widget) return;\n        var title = document.querySelector('.p-title');\n        if (title && title.parentNode && widget.dataset.wrxtPlaced !== '1') {\n            title.insertAdjacentElement('afterend', widget);\n            widget.dataset.wrxtPlaced = '1';\n        }\n        var mobile = window.matchMedia && window.matchMedia('(max-width: 650px)').matches;\n        widget.style.width = '100%';\n        widget.style.maxWidth = mobile ? 'none' : '360px';\n        widget.style.marginLeft = mobile ? '0' : 'auto';\n        widget.style.marginRight = '0';\n    }\n    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', placeFreshnessWidget, {once:true});\n    else placeFreshnessWidget();\n    window.addEventListener('load', placeFreshnessWidget, {once:true});\n    window.addEventListener('resize', placeFreshnessWidget);\n}());\n""", encoding='utf-8')

# Version + release tests
meta_path = addon / 'addon.json'
meta = json.loads(meta_path.read_text(encoding='utf-8'))
meta['version_id'] = 1010670
meta['version_string'] = '1.0.6'
meta_path.write_text(json.dumps(meta, ensure_ascii=False, indent=4) + '\n', encoding='utf-8')
for p in [root / 'tests/release_static.php', root / 'tests/release_data.php']:
    s = p.read_text(encoding='utf-8').replace('1010570', '1010670').replace("'1.0.5'", "'1.0.6'")
    p.write_text(s, encoding='utf-8')

rs_path = root / 'tests/release_static.php'
rs = rs_path.read_text(encoding='utf-8')
guard = """\nif (!str_contains($mods, 'wrxtFreshness-choiceSurface') || !str_contains($mods, 'Oyu kaydet') || !str_contains($mods, 'required=\\\"required\\\"'))\n{\n    throw new RuntimeException('Two-step selectable vote UI is missing');\n}\nforeach (['fa-chart-line', 'fa-folder-tree', 'fa-sliders'] as $icon)\n{\n    if (!str_contains($adminNav, $icon))\n    {\n        throw new RuntimeException('ACP navigation icon missing: ' . $icon);\n    }\n}\n"""
if 'Two-step selectable vote UI is missing' not in rs:
    rs = rs.replace('\necho "OK\\n";', guard + '\necho "OK\\n";')
rs_path.write_text(rs, encoding='utf-8')

changelog = root / 'CHANGELOG.md'
old = changelog.read_text(encoding='utf-8')
head = '# Değişiklik Günlüğü\n\n'
section = '''## 1.0.6 Stable - Oylama Arayüzü Yenilemesi\n\n- Çalıştı / Çalışmadı aksiyonları seçilebilir, seçildiğinde içi dolan iki seçenekli kontrol yapısına geçirildi.\n- Oyun gönderilmesi için ayrı bir “Oyu kaydet” butonu eklendi.\n- Bilgi, İşlemler ve Oy ayrıntısı kontrolleri XenForo buton görünümüne geçirildi.\n- Widget masaüstünde kompakt, mobilde tam genişlik olacak şekilde sağlamlaştırıldı.\n- ACP alt menülerine Genel Bakış, Forum/Kategori Ayarları ve Ayarlar ikonları eklendi.\n\n'''
if old.startswith(head) and '## 1.0.6 Stable' not in old:
    changelog.write_text(head + section + old[len(head):], encoding='utf-8')

readme = root / 'README.md'
r = readme.read_text(encoding='utf-8')
r = r.replace('1.0.5', '1.0.6')
readme.write_text(r, encoding='utf-8')
