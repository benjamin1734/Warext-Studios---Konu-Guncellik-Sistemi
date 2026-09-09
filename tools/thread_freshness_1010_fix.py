from pathlib import Path

path = Path(__file__).resolve().parents[1] / 'tests/release_static.php'
s = path.read_text(encoding='utf-8')
old = '''if (!str_contains($mods, 'Oyunu güncelle') || !str_contains($mods, 'Seçimini değiştirip yeniden kaydedebilirsin.'))
{
    throw new RuntimeException('Vote update UX is missing');
}
'''
new = '''if (!str_contains($mods, "'Güncelle' : 'Kaydet'"))
{
    throw new RuntimeException('Vote update UX is missing');
}
'''
if old not in s:
    raise SystemExit('Legacy vote update UX guard not found')
path.write_text(s.replace(old, new, 1), encoding='utf-8')
