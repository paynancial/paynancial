"""Compare two page_snapshot.py snapshots (clean staging BEFORE vs clean staging AFTER).

    python3 tests/staging/snapshot_compare.py BEFORE_DIR AFTER_DIR

Reports added and removed pages, and changed HTTP statuses, titles, canonical URLs, robots meta,
public content (visible text), markup (HTML only) and newly broken internal links.
Exit 0 only when nothing changed; the last line is the summary the gate records.
"""
import json, os, sys
b = json.load(open(os.path.join(sys.argv[1], '_manifest.json'))); a = json.load(open(os.path.join(sys.argv[2], '_manifest.json')))
sections = {'added pages': sorted(set(a) - set(b)), 'removed pages': sorted(set(b) - set(a))}
both = sorted(set(a) & set(b))
for field, label in [('status', 'changed HTTP statuses'), ('title', 'changed titles'), ('canonical', 'changed canonical URLs'), ('robots', 'changed robots meta')]:
    sections[label] = [f'{k}: {b[k][field]!r} -> {a[k][field]!r}' for k in both if b[k][field] != a[k][field]]
sections['changed public content'] = [k for k in both if b[k]['content_sha256'] != a[k]['content_sha256']]
sections['markup-only changes'] = [k for k in both if b[k]['content_sha256'] == a[k]['content_sha256'] and b[k]['markup_sha256'] != a[k]['markup_sha256']]
sections['newly broken links'] = sorted({f'{k}: {l}' for k in a for l in a[k].get('broken_links', []) if l not in b.get(k, {}).get('broken_links', [])})
for label, items in sections.items():
    print(f'## {label}: {len(items)}')
    for i in items: print('  ' + i)
changed = sum(len(v) for v in sections.values())
print(f"Snapshot compare: {len(b)} pages before, {len(a)} after; " + ', '.join(f'{k} {len(v)}' for k, v in sections.items()))
sys.exit(1 if changed else 0)
