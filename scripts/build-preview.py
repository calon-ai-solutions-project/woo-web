"""Turn a wget mirror of the local site into a static, look-only preview.

Used by scripts/build-preview.sh. Strips version tags from asset file names,
makes links root-relative, fetches assets the mirror missed, marks every page
noindex and adds a "Preview only" bar that switches forms off.

Usage: python3 scripts/build-preview.py PREVIEW_FOLDER [SITE_URL]
"""
import os, re, sys, urllib.request, urllib.parse
root = sys.argv[1]
base = sys.argv[2] if len(sys.argv) > 2 else 'http://localhost:8080'

# 1. Strip ?ver=... from file names.
for d, _, files in os.walk(root):
    for f in files:
        if '?' in f:
            src = os.path.join(d, f)
            dst = os.path.join(d, f.split('?')[0])
            if os.path.exists(dst):
                os.remove(src)
            else:
                os.rename(src, dst)

ver = re.compile(r'(\.(?:css|js|woff2?|svg|png|jpe?g|webp|gif|ico))(?:%3F|\?)(?:ver|v)=[^"\'\s)<>]*')
banner = ('<div id="clo-preview-bar" style="position:fixed;left:0;right:0;bottom:0;z-index:99999;'
          'background:#FFD21F;color:#13233F;font:600 16px/1.4 \'Barlow Condensed\',Arial,sans-serif;'
          'letter-spacing:.03em;text-align:center;padding:.6rem 1rem;box-shadow:0 -2px 8px rgba(19,35,63,.2)">'
          'PREVIEW ONLY: this is how the Clearance Liquidation Outlet website will look. '
          'Shopping, the basket and forms are switched off until the shop opens.</div>'
          '<div style="height:3rem" aria-hidden="true"></div>'
          '<script>document.addEventListener("submit",function(e){e.preventDefault();'
          'alert("This is a preview. Shopping and forms are switched off until the shop opens.");},true);</script>')
head_drop = re.compile(r'<link[^>]+(?:EditURI|application/json\+oembed|text/xml\+oembed|application/rss\+xml|https://api\.w\.org/|wp-json|shortlink)[^>]*>\s*')
wanted = set()
for d, _, files in os.walk(root):
    for f in files:
        p = os.path.join(d, f)
        if not f.endswith(('.html', '.css', '.js')):
            continue
        t = open(p, encoding='utf-8', errors='surrogateescape').read()
        o = t
        t = t.replace(base, '').replace(base.replace('/', '\\/'), '')
        t = ver.sub(r'\1', t)
        if f.endswith('.html'):
            t = head_drop.sub('', t)
            if 'noindex' not in t:
                t = t.replace('<head>', '<head><meta name="robots" content="noindex, nofollow">', 1)
            if 'clo-preview-bar' not in t:
                t = t.replace('</body>', banner + '</body>', 1)
        # Root-relative assets referenced anywhere, to fetch if missing.
        for m in re.finditer(r'(/wp-(?:content|includes|admin/js)/[^"\'\s)<>?#]+\.(?:css|js|woff2?|svg|png|jpe?g|webp|gif))', t):
            wanted.add(m.group(1).replace('\\/', '/'))
        if t != o:
            open(p, 'w', encoding='utf-8', errors='surrogateescape').write(t)

# 2b. A branded page for anything that is not part of the preview (Vercel serves 404.html).
home = open(os.path.join(root, 'index.html'), encoding='utf-8', errors='surrogateescape').read()
start, end = home.find('<main'), home.find('</main>')
if start != -1 and end != -1:
    notice = ('<main id="main" class="site-main"><div class="content-container site-container" style="padding:4rem 1.5rem;text-align:center">'
              '<h1>Not part of the preview</h1>'
              '<p style="font-size:1.3rem">This page works once the shop opens. For now, have a look around the '
              '<a href="/">homepage</a>, the <a href="/wholesale-bulk-buy/">wholesale pages</a> and the '
              '<a href="/shop/">shop categories</a>.</p></div></main>')
    page404 = home[:start] + notice + home[end + len('</main>'):]
    # Vercel serves 404.html at any missing address, so links copied from the
    # homepage must start from the site root rather than from the current folder.
    page404 = re.sub(r'\b(href|src)="(?!/|[a-z]+:|#)([^"]+)"', r'\1="/\2"', page404)
    open(os.path.join(root, '404.html'), 'w', encoding='utf-8', errors='surrogateescape').write(page404)

# 3. Fetch assets that pages reference but the mirror missed.
fetched = 0
for path in sorted(wanted):
    dst = os.path.join(root, path.lstrip('/'))
    if os.path.exists(dst):
        continue
    try:
        data = urllib.request.urlopen(base + path, timeout=10).read()
    except Exception:
        continue
    os.makedirs(os.path.dirname(dst), exist_ok=True)
    open(dst, 'wb').write(data)
    fetched += 1
print('fetched missing assets:', fetched)
