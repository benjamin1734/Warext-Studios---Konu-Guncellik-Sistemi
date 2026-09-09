from pathlib import Path
import json
import xml.etree.ElementTree as ET

root = Path(__file__).resolve().parents[1]
addon = root / 'upload/src/addons/WarextStudios/ThreadFreshness'

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

needle = '<div class="wrxtFreshness-fields">\n                          <label><span>Sürüm</span>'
replacement = '<div class="wrxtFreshness-fields">\n                          <div class="wrxtFreshness-fieldsHead"><strong>Oy ayrıntısı</strong><button type="button" class="button button--link wrxtFreshness-fieldsClose" data-wrxt-detail-close="1" aria-label="Kapat">✕</button></div>\n                          <label><span>Sürüm</span>'
if needle not in text:
    raise SystemExit('Vote details fields block not found')
text = text.replace(needle, replacement, 1)
replace.text = text
ET.indent(tree, space='  ')
tree.write(mods_path, encoding='utf-8', xml_declaration=True)

templates_path = addon / '_data/templates.xml'
ttree = ET.parse(templates_path)
troot = ttree.getroot()
css_tpl = next((t for t in troot.findall('template') if t.get('title') == 'wrxt_thread_freshness.less'), None)
if css_tpl is None:
    raise SystemExit('Freshness LESS template not found')
css_tpl.set('version_id', '1011170')
css_tpl.set('version_string', '1.1.0')
css_tpl.text = '''.wrxtFreshness{width:100%;max-width:340px;margin:6px 0 10px auto;position:relative;z-index:3;font-size:12px}.wrxtFreshness>.block-container{border:1px solid @xf-borderColor;border-radius:8px;background:@xf-contentBg;box-shadow:0 2px 7px rgba(0,0,0,.1);overflow:visible}.wrxtFreshness-main{padding:8px}.wrxtFreshness-top{display:flex;align-items:center;justify-content:space-between;gap:6px}.wrxtFreshness-heading{display:flex;align-items:center;gap:5px;min-width:0;flex-wrap:wrap}.wrxtFreshness-title{font-size:12px;line-height:1.2;white-space:nowrap}.wrxtFreshness-heading .label{font-size:9px;line-height:1.2;padding:2px 5px}.wrxtFreshness-icons{display:flex;align-items:center;gap:4px;flex:0 0 auto}.wrxtFreshness-pop{position:relative}.wrxtFreshness-pop>summary,.wrxtFreshness-more>summary{list-style:none;cursor:pointer}.wrxtFreshness-pop>summary::-webkit-details-marker,.wrxtFreshness-more>summary::-webkit-details-marker{display:none}.wrxtFreshness-toolButton.button,.wrxtFreshness-moreButton.button{min-width:0;padding:4px 7px;font-size:10px;line-height:1.15;border-radius:6px}.wrxtFreshness-pop[open]>.wrxtFreshness-toolButton{border-color:@xf-linkColor;box-shadow:inset 0 0 0 1px @xf-linkColor}.wrxtFreshness-popBody{position:absolute;right:0;top:calc(100% + 5px);width:255px;padding:9px;border:1px solid @xf-borderColor;border-radius:7px;background:@xf-contentBg;box-shadow:0 7px 22px rgba(0,0,0,.22);z-index:30;font-size:11px}.wrxtFreshness-popBody p{margin:4px 0 0;color:@xf-textColorMuted;line-height:1.35}.wrxtFreshness-popBody--actions form+form{margin-top:6px}.wrxtFreshness-actionForm{display:flex;gap:5px;align-items:center}.wrxtFreshness-actionForm .input,.wrxtFreshness-actionForm select{min-width:0;flex:1 1 auto}.wrxtFreshness-meta{display:flex;gap:4px;align-items:center;flex-wrap:wrap;margin-top:6px}.wrxtFreshness-meta>span{display:inline-flex;align-items:center;min-height:20px;padding:1px 6px;border:1px solid @xf-borderColor;border-radius:999px;background:@xf-contentAltBg;color:@xf-textColorMuted;font-size:10px}.wrxtFreshness-meta b{color:@xf-textColor}.wrxtFreshness-ownerBadge{color:@xf-linkColor!important}.wrxtFreshness-replacement{margin-top:6px;font-size:11px}.wrxtFreshness-voteForm{margin-top:7px;padding-top:7px;border-top:1px solid @xf-borderColor;display:grid;grid-template-columns:minmax(0,1fr) auto;gap:6px;align-items:center;position:relative}.wrxtFreshness-choiceGroup{display:flex;align-items:center;gap:5px;min-width:0}.wrxtFreshness-choice{position:relative;display:block;min-width:0;cursor:pointer}.wrxtFreshness-choiceInput{position:absolute;opacity:0;width:1px;height:1px;pointer-events:none}.wrxtFreshness-choiceSurface.button{width:auto;min-width:0;min-height:32px;display:inline-flex;align-items:center;justify-content:center;gap:5px;padding:4px 8px;text-align:center;white-space:nowrap;border-radius:6px;border:1px solid @xf-borderColor;background:@xf-contentAltBg;color:@xf-textColor;box-shadow:none;transition:border-color .12s ease,background .12s ease,box-shadow .12s ease}.wrxtFreshness-choice:hover .wrxtFreshness-choiceSurface{border-color:@xf-linkColor}.wrxtFreshness-choiceInput:focus+.wrxtFreshness-choiceSurface{box-shadow:0 0 0 2px fade(@xf-linkColor,22%)}.wrxtFreshness-choiceMark{width:18px;height:18px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;flex:0 0 18px;border:1px solid @xf-borderColor;background:@xf-contentBg;font-size:10px;font-weight:700}.wrxtFreshness-choiceText{display:inline-flex;align-items:center;line-height:1}.wrxtFreshness-choiceText b{font-size:11px}.wrxtFreshness-choice--yes .wrxtFreshness-choiceInput:checked+.wrxtFreshness-choiceSurface{border-color:#43a047;background:fade(#43a047,16%);box-shadow:inset 0 0 0 1px fade(#43a047,40%)}.wrxtFreshness-choice--yes .wrxtFreshness-choiceInput:checked+.wrxtFreshness-choiceSurface .wrxtFreshness-choiceMark{border-color:#43a047;background:#43a047;color:#fff}.wrxtFreshness-choice--no .wrxtFreshness-choiceInput:checked+.wrxtFreshness-choiceSurface{border-color:#d9534f;background:fade(#d9534f,15%);box-shadow:inset 0 0 0 1px fade(#d9534f,40%)}.wrxtFreshness-choice--no .wrxtFreshness-choiceInput:checked+.wrxtFreshness-choiceSurface .wrxtFreshness-choiceMark{border-color:#d9534f;background:#d9534f;color:#fff}.wrxtFreshness-saveBar{grid-column:2;grid-row:1;margin:0;padding:0;border:0}.wrxtFreshness-saveButton.button{min-width:0;min-height:32px;padding:4px 9px;font-size:11px}.wrxtFreshness-voteDetails{grid-column:1/-1;margin-top:0;position:static;justify-self:start}.wrxtFreshness-moreButton.button{display:inline-flex}.wrxtFreshness-fields,.wrxtFreshness-resultsBody{margin-top:6px;padding:7px;border:1px solid @xf-borderColor;border-radius:7px;background:@xf-contentAltBg}.wrxtFreshness-voteDetails>.wrxtFreshness-fields{position:absolute;right:calc(100% + 10px);top:0;width:300px;margin:0;padding:10px;background:@xf-contentBg;border:1px solid @xf-borderColor;border-radius:8px;box-shadow:0 10px 30px rgba(0,0,0,.25);z-index:60}.wrxtFreshness-fieldsHead{display:flex;align-items:center;justify-content:space-between;gap:8px;margin:-2px 0 8px;padding-bottom:7px;border-bottom:1px solid @xf-borderColor}.wrxtFreshness-fieldsHead strong{font-size:12px}.wrxtFreshness-fieldsClose.button{min-width:0;padding:2px 6px;font-size:11px;line-height:1.2}.wrxtFreshness-fields{display:grid;gap:6px}.wrxtFreshness-fields label{display:grid;gap:3px}.wrxtFreshness-fields label>span{color:@xf-textColorMuted;font-size:10px}.wrxtFreshness-more{margin-top:6px}.wrxtFreshness-resultsBody strong{display:block;margin:5px 0 3px;font-size:10px}.wrxtFreshness-main--waiting{padding:8px}.wrxtFreshness-detailBackdrop{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:999}.wrxtFreshness-detailSheetOpen{overflow:hidden}@media(max-width:900px){.wrxtFreshness-voteDetails>.wrxtFreshness-fields{position:fixed;left:50%;right:auto;top:50%;bottom:auto;width:min(360px,calc(100vw - 24px));max-height:78vh;overflow:auto;margin:0;transform:translate(-50%,-50%);z-index:1000;box-shadow:0 18px 50px rgba(0,0,0,.42)}}@media(max-width:@xf-responsiveMedium){.wrxtFreshness{width:100%;max-width:none;margin:6px 0 9px}.wrxtFreshness-popBody{position:fixed;left:12px;right:12px;top:auto;width:auto}}@media(max-width:420px){.wrxtFreshness-main{padding:7px}.wrxtFreshness-voteForm{grid-template-columns:1fr auto;gap:5px}.wrxtFreshness-choiceGroup{gap:4px}.wrxtFreshness-choiceSurface.button{min-height:30px;padding:3px 6px}.wrxtFreshness-choiceMark{width:16px;height:16px;flex-basis:16px}.wrxtFreshness-choiceText b{font-size:10px}.wrxtFreshness-saveButton.button{min-height:30px;padding:3px 7px}.wrxtFreshness-voteDetails>.wrxtFreshness-fields{width:calc(100vw - 20px);max-height:82vh;padding:9px}}@media(min-width:651px){.p-title>.wrxtFreshness{flex:0 0 340px;align-self:flex-start;min-width:285px}.p-title>.wrxtFreshness>.block-container{margin:0}}'''
ET.indent(ttree, space='  ')
ttree.write(templates_path, encoding='utf-8', xml_declaration=True)

js_path = root / 'upload/js/warext/thread_freshness.js'
js_path.write_text("""(function () {
    'use strict';

    var detailBackdrop = null;
    var activeDetail = null;

    function isSheetMode() {
        return window.matchMedia && window.matchMedia('(max-width: 900px)').matches;
    }

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
            widget.style.maxWidth = '340px';
            widget.style.margin = '0 0 0 auto';
        }
        widget.dataset.wrxtPlaced = '1';
    }

    function removeBackdrop() {
        if (detailBackdrop && detailBackdrop.parentNode) detailBackdrop.parentNode.removeChild(detailBackdrop);
        detailBackdrop = null;
        document.documentElement.classList.remove('wrxtFreshness-detailSheetOpen');
        document.body.classList.remove('wrxtFreshness-detailSheetOpen');
    }

    function closeDetail(detail) {
        if (detail) detail.removeAttribute('open');
        if (activeDetail === detail) activeDetail = null;
        removeBackdrop();
    }

    function syncDetail(detail) {
        if (!detail.open || !isSheetMode()) {
            if (activeDetail === detail) activeDetail = null;
            removeBackdrop();
            return;
        }
        if (activeDetail && activeDetail !== detail) activeDetail.removeAttribute('open');
        activeDetail = detail;
        if (!detailBackdrop) {
            detailBackdrop = document.createElement('div');
            detailBackdrop.className = 'wrxtFreshness-detailBackdrop';
            detailBackdrop.setAttribute('aria-hidden', 'true');
            detailBackdrop.addEventListener('click', function () { closeDetail(activeDetail); });
            document.body.appendChild(detailBackdrop);
        }
        document.documentElement.classList.add('wrxtFreshness-detailSheetOpen');
        document.body.classList.add('wrxtFreshness-detailSheetOpen');
    }

    function initDetailSheets() {
        document.querySelectorAll('details.wrxtFreshness-voteDetails').forEach(function (detail) {
            if (detail.dataset.wrxtDetailInit) return;
            detail.dataset.wrxtDetailInit = '1';
            detail.addEventListener('toggle', function () { syncDetail(detail); });
        });
    }

    document.addEventListener('click', function (event) {
        var closer = event.target.closest ? event.target.closest('[data-wrxt-detail-close]') : null;
        if (!closer) return;
        event.preventDefault();
        closeDetail(closer.closest('details.wrxtFreshness-voteDetails'));
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && activeDetail) closeDetail(activeDetail);
    });

    function refreshLayout() {
        placeFreshnessWidget();
        initDetailSheets();
        if (activeDetail) syncDetail(activeDetail);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', refreshLayout, {once:true}); else refreshLayout();
    window.addEventListener('load', refreshLayout, {once:true});
    window.addEventListener('resize', refreshLayout);
}());
""", encoding='utf-8')

meta_path = addon / 'addon.json'
meta = json.loads(meta_path.read_text(encoding='utf-8'))
meta['version_id'] = 1011170
meta['version_string'] = '1.1.0'
meta_path.write_text(json.dumps(meta, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')

release_data = root / 'tests/release_data.php'
s = release_data.read_text(encoding='utf-8').replace('1011070', '1011170').replace("'1.0.10'", "'1.1.0'")
release_data.write_text(s, encoding='utf-8')

release_static = root / 'tests/release_static.php'
s = release_static.read_text(encoding='utf-8').replace('1011070', '1011170').replace("'1.0.10'", "'1.1.0'")
marker = '$widgetJsSource = (string)file_get_contents($widgetJs);'
extra = """if (!str_contains($mods, 'wrxtFreshness-fieldsHead') || !str_contains($templatesData, 'right:calc(100% + 10px)') || !str_contains($templatesData, '@media(max-width:900px)'))
{
    throw new RuntimeException('Side vote details panel / responsive sheet contract is missing');
}
if (!str_contains($widgetJsSource = (string)file_get_contents($widgetJs), 'wrxtFreshness-detailBackdrop') || !str_contains($widgetJsSource, 'data-wrxt-detail-close') || !str_contains($widgetJsSource, "widget.style.maxWidth = '340px'"))
{
    throw new RuntimeException('Responsive vote details behavior is missing');
}

"""
if extra not in s:
    if marker not in s:
        raise SystemExit('release_static widget marker missing')
    s = s.replace(marker, extra + marker, 1)
release_static.write_text(s, encoding='utf-8')

changelog = root / 'CHANGELOG.md'
c = changelog.read_text(encoding='utf-8')
header = '# Değişiklik Günlüğü\n\n'
section = '''## 1.1.0 Stable - Yandan Açılan Oy Ayrıntıları\n\n- Oy ayrıntıları artık masaüstünde ana güncellik kartını aşağı doğru büyütmek yerine kartın solunda ayrı bir panel olarak açılır.\n- Yan panel sürüm, neden, açıklama ve güncel konu ID alanlarını aynı form içinde tutar; oy kaydetme/güncelleme davranışı değişmez.\n- 900px ve altındaki ekranlarda ayrıntılar ortalanmış, arka planı karartan responsive bir sheet/modal görünümünde açılır.\n- Mobil ayrıntı paneline kapatma düğmesi, arka plana tıklayarak kapatma ve Escape ile kapatma desteği eklendi.\n- Masaüstü widget genişliği JavaScript tarafında da 340px ile eşitlendi.\n\n'''
if c.startswith(header) and '## 1.1.0 Stable' not in c:
    changelog.write_text(header + section + c[len(header):], encoding='utf-8')

readme = root / 'README.md'
r = readme.read_text(encoding='utf-8')
r = r.replace('1.0.10 Stable', '1.1.0 Stable')
r = r.replace('Konu-Guncellik-1.0.10.zip', 'Konu-Guncellik-1.1.0.zip')
r = r.replace('/releases/download/v1.0.10/', '/releases/download/v1.1.0/')
readme.write_text(r, encoding='utf-8')
