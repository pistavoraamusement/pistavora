#!/usr/bin/env python3
# Self-contained: generates the Pistavora logos (SVG) and swaps them into the Laravel views.
# Run inside laravel-app/:   python install_pistavora_logo.py   (dry-run)   |   --apply
import math, os, re, shutil, sys

APPLY = "--apply" in sys.argv
ROOT = os.getcwd()
if not os.path.isfile(os.path.join(ROOT, "artisan")):
    sys.exit("Run this inside laravel-app/.")
IMG = os.path.join(ROOT, "public", "images")

NAVY, WHITE = "#1B2A57", "#FFFFFF"
GOND = ["#FF7A45", "#FFC83D", "#1FA37A", "#4C6FFF", "#8B5CF6", "#FF5E7E", "#FF7A45", "#FFC83D"]
G = {
 'P': ("M0,7 V93 M0,7 H28 A21,21 0 0 1 28,49 H0", 0, 49), 'I': ("M0,7 V93", 0, 0),
 'S': ("M36,22 C34,3 2,3 2,27 C2,50 38,46 38,71 C38,97 4,97 2,78", 2, 38),
 'T': ("M-2,7 H40 M19,7 V93", -2, 40), 'A': ("M0,93 L22,7 L44,93 M9,66 H35", 0, 44),
 'V': ("M0,7 L22,93 L44,7", 0, 44), 'O': ("M0,50 A22,43 0 1 0 44,50 A22,43 0 1 0 0,50", 0, 44),
 'R': ("M0,7 V93 M0,7 H28 A21,21 0 0 1 28,49 H0 M18,49 L44,93", 0, 44),
 'M': ("M0,93 V7 L23,56 L46,7 V93", 0, 46), 'U': ("M0,7 V60 A22,33 0 0 0 44,60 V7", 0, 44),
 'E': ("M36,7 H0 V93 H36 M0,50 H30", 0, 36), 'N': ("M0,93 V7 L42,93 V7", 0, 42),
 'K': ("M0,7 V93 M40,7 L2,56 M14,42 L40,93", 0, 40),
}
MARK_VB = (29, 5, 193, 250)

def text_paths(txt, sw, gap, color):
    x, out = 0, []
    for ch in txt:
        if ch == ' ':
            x += 60; continue
        d, x0, x1 = G[ch]
        out.append('<path d="%s" transform="translate(%.1f,0)"/>' % (d, x - x0)); x += (x1 - x0) + gap
    w = x - gap
    return ('<g fill="none" stroke="%s" stroke-width="%s" stroke-linecap="round" stroke-linejoin="round">%s</g>'
            % (color, sw, "".join(out)), w)

def text_block(txt, cx, top, cap, sw, gap, color, left=False):
    inner, w = text_paths(txt, sw, gap, color); s = cap / 100.0; full = (w + sw) * s
    x = (cx if left else cx - full / 2) + (sw / 2) * s
    return '<g transform="translate(%.2f,%.2f) scale(%.4f)">%s</g>' % (x, top, s, inner), full

def mark(structure, mono=None, stem=236):
    cx, cy, R = 130, 92, 72; p = []
    for k in range(8):
        a = math.radians(22.5 + 45 * k)
        p.append('<line x1="%d" y1="%d" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="4" stroke-linecap="round"/>'
                 % (cx, cy, cx + R * math.cos(a), cy + R * math.sin(a), structure))
    p.append('<circle cx="%d" cy="%d" r="%d" fill="none" stroke="%s" stroke-width="11"/>' % (cx, cy, R, structure))
    p.append('<circle cx="%d" cy="%d" r="21" fill="none" stroke="%s" stroke-width="5"/>' % (cx, cy, structure))
    p.append('<circle cx="%d" cy="%d" r="9" fill="%s"/>' % (cx, cy, mono or "#FFC83D"))
    p.append('<path d="M%d,20 V%d M%d,%d H%d" fill="none" stroke="%s" stroke-width="18" stroke-linecap="round" stroke-linejoin="round"/>'
             % (cx - R, stem, cx - R - 16, stem + 4, cx - R + 34, structure))
    for k in range(8):
        a = math.radians(22.5 + 45 * k)
        p.append('<circle cx="%.1f" cy="%.1f" r="13.5" fill="%s"/>' % (cx + R * math.cos(a), cy + R * math.sin(a), mono or GOND[k]))
    return "".join(p)

def swoosh(cx, y, half, uid):
    return ('<defs><linearGradient id="g%s" x1="0" x2="1"><stop offset="0" stop-color="#FF7A45"/><stop offset=".35" stop-color="#FFC83D"/>'
            '<stop offset=".65" stop-color="#1FA37A"/><stop offset="1" stop-color="#4C6FFF"/></linearGradient></defs>'
            '<path d="M%d,%d Q%d,%d %d,%d" fill="none" stroke="url(#g%s)" stroke-width="6" stroke-linecap="round"/>'
            % (uid, cx - half, y + 16, cx, y - 14, cx + half, y + 16, uid))

def svg(w, h, body):
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" width="%d" height="%d">%s</svg>' % (w, h, w, h, body)

def stacked(color, uid, W=740, H=337):
    cx = W / 2; s = 164 / MARK_VB[3]; mw = MARK_VB[2] * s
    mk = '<g transform="translate(%.1f,6) scale(%.4f) translate(%d,%d)">%s</g>' % (cx - mw / 2, s, -MARK_VB[0], -MARK_VB[1], mark(color))
    return svg(W, H, mk + text_block("PISTAVORA", cx, 178, 62, 15, 30, color)[0]
               + text_block("AMUSEMENT PARK", cx, 252, 14, 17, 62, color)[0] + swoosh(cx, 288, 230, uid))

def horizontal(color, W=600, H=180):
    s = 150 / MARK_VB[3]; x0 = 14 + MARK_VB[2] * s + 22
    mk = '<g transform="translate(14,15) scale(%.4f) translate(%d,%d)">%s</g>' % (s, -MARK_VB[0], -MARK_VB[1], mark(color))
    return svg(W, H, mk + text_block("PISTAVORA", x0, 46, 72, 15, 30, color, True)[0]
               + text_block("AMUSEMENT PARK", x0, 136, 15, 17, 62, color, True)[0])

def square(structure, mono=None, size=512, pad=0.07):
    x, y, w, h = 29, 5, 193, 186; s = size * (1 - 2 * pad) / h; iw = w * s
    return svg(size, size, '<g transform="translate(%.1f,%.1f) scale(%.4f) translate(%d,%d)">%s</g>'
               % ((size - iw) / 2, size * pad, s, -x, -y, mark(structure, mono, stem=172)))

ASSETS = {
    "pistavora-mark.svg": square(NAVY),               # white tiles
    "pistavora-mark-dark.svg": square(WHITE),         # dark chat avatar
    "pistavora-mark-white.svg": square(WHITE, WHITE), # analytics (all white)
    "pistavora-logo-dark.svg": stacked(WHITE, "d"),   # dark nav, white text
    "pistavora-logo-horizontal.svg": horizontal(NAVY),# light topbar / landing
}
FAVICON = square(NAVY, size=64, pad=0.02)

print("== assets")
for n, c in list(ASSETS.items()) + [("../favicon.svg", FAVICON)]:
    dst = os.path.join(IMG, n)
    print(("WRITE " if APPLY else "WOULD ") + os.path.relpath(os.path.normpath(dst), ROOT))
    if APPLY:
        os.makedirs(IMG, exist_ok=True)
        with open(dst, "w", encoding="utf-8") as fh:
            fh.write(c)

A = lambda n: "{{ asset('images/%s') }}" % n
O1 = r"\{\{\s*asset\('images/wonderpark1logo\.png'\)\s*\}\}"
O2 = r"\{\{\s*asset\('images/wonderpark2logo\.png'\)\s*\}\}"
ALT = r'alt="[^"]*"'
EDITS = [
 ("resources/views/partials/bottomnav.blade.php", r'(<div class="brand-mark"><img src=")' + O1 + r'(" )' + ALT,
  lambda m: m.group(1) + A("pistavora-mark.svg") + m.group(2) + 'alt="Pistavora"', "customer nav tile"),
 ("resources/views/layouts/sidebar.blade.php", r'(<div class="mark"><img src=")' + O1 + r'(" )' + ALT,
  lambda m: m.group(1) + A("pistavora-mark.svg") + m.group(2) + 'alt="Pistavora"', "ops sidebar tile"),
 ("resources/views/layouts/sidebar.blade.php", r'(<div class="mobile-topbar[^"]*">\s*<img src=")' + O1 + r'(" )' + ALT,
  lambda m: m.group(1) + A("pistavora-logo-horizontal.svg") + m.group(2) + 'alt="Pistavora"', "mobile topbar"),
 ("resources/views/user/partials/bottomnav.blade.php", r'(<img src=")' + O1 + r'(" )' + ALT + r'( class="nav-brand-logo")',
  lambda m: m.group(1) + A("pistavora-logo-dark.svg") + m.group(2) + 'alt="Pistavora"' + m.group(3), "user desktop nav"),
 ("resources/views/partials/chat-widget.blade.php", r'(<span class="chat-avatar">\s*<img src=")' + O1 + r'(" )' + ALT,
  lambda m: m.group(1) + A("pistavora-mark-dark.svg") + m.group(2) + 'alt="Pistavora"', "chat widget avatar"),
 ("public/js/reks-assist.js", r'/images/wonderpark2logo\.png" alt="[^"]*"',
  lambda m: '/images/pistavora-mark-dark.svg" alt="Pistavora"', "chat bubble avatar"),
 ("resources/views/landing.blade.php", r'(<img src=")' + O2 + r'(" )' + ALT,
  lambda m: m.group(1) + A("pistavora-logo-horizontal.svg") + m.group(2) + 'alt="Pistavora"', "landing header"),
 ("resources/views/analytics/index.blade.php", r"\{\{\s*asset\('images/reks-logo-white\.png'\)\s*\}\}",
  lambda m: A("pistavora-mark-white.svg"), "analytics logo"),
]
print("== views")
for rel, rx, fn, what in EDITS:
    p = os.path.join(ROOT, rel)
    if not os.path.isfile(p):
        print("skip  %-46s (file not found)" % rel); continue
    with open(p, "r", encoding="utf-8", newline="") as fh:
        s = fh.read()
    new, n = re.subn(rx, fn, s)
    if n == 0:
        print("skip  %-46s %s (no match / already done)" % (rel, what)); continue
    print(("EDIT  " if APPLY else "WOULD ") + "%-46s %s (x%d)" % (rel, what, n))
    if APPLY:
        if not os.path.isfile(p + ".orig"):
            shutil.copy2(p, p + ".orig")
        with open(p, "w", encoding="utf-8", newline="") as fh:
            fh.write(new)

for rel in ("resources/views/layouts/sidebar.blade.php", "resources/views/layouts/user.blade.php",
            "resources/views/landing.blade.php", "resources/views/layouts/waiver-only.blade.php"):
    p = os.path.join(ROOT, rel)
    if not os.path.isfile(p):
        continue
    with open(p, "r", encoding="utf-8", newline="") as fh:
        s = fh.read()
    if "favicon.svg" in s:
        continue
    link = '<link rel="icon" type="image/svg+xml" href="{{ asset(\'favicon.svg\') }}">\n    '
    new = re.sub(r"(<title>)", lambda m: link + m.group(1), s, count=1)
    if new != s:
        print(("EDIT  " if APPLY else "WOULD ") + "%-46s favicon link" % rel)
        if APPLY:
            with open(p, "w", encoding="utf-8", newline="") as fh:
                fh.write(new)

print("\n" + ("DONE. Now: php artisan view:clear   then Ctrl+Shift+R in the browser" if APPLY else "DRY RUN. Re-run with --apply"))
