#!/usr/bin/env python3
"""
Rename Wonder Park -> Pistavora Amusement Park.

Run from inside laravel-app/:
    python rename_pistavora.py            # DRY RUN (lists changes, writes nothing)
    python rename_pistavora.py --apply    # applies changes (makes .bak-free; use git to undo)

Left alone on purpose: REKS, Dino Adventure, image/file names (wonderpark1logo.png),
the contact email, folder paths, vendor/, storage/, migrations, old patch scripts.
"""
import os, re, sys

APPLY = "--apply" in sys.argv
ROOT = os.getcwd()
if not os.path.isfile(os.path.join(ROOT, "artisan")):
    sys.exit("Run this inside laravel-app/ (the folder that has 'artisan').")

BRAND = "Pistavora Amusement Park"
SHORT = "Pistavora"

SKIP_DIRS = {"vendor", "node_modules", "storage", ".git", "build", "migrations", ".vscode"}
SKIP_FILES = {"composer.lock", "package-lock.json", "patch_user_pages.sh", "rename_pistavora.py"}
SKIP_SUFFIX = (".zip", ".png", ".jpg", ".jpeg", ".gif", ".svg", ".webp", ".ico", ".pdf",
               ".bat", ".patch", ".woff", ".woff2", ".ttf", ".map")
EXTS = (".php", ".js", ".css", ".html", ".md", ".txt", ".example", ".json")

B = r"(?:(?<![A-Za-z0-9_/@.\-])|(?<=\\n))"   # not glued to identifiers (but OK after a literal \n)
E = r"(?![A-Za-z0-9_])"

# Order matters: longest / most specific first. Case of the source is preserved
# (ALL CAPS stays caps, Title Case stays Title Case).
RULES = [
    # full company-style strings -> full brand
    (re.compile(B + r"WonderPark Amusement Com\.? Inc\.?"), BRAND),
    (re.compile(B + r"WONDERPARK AMUSEMENT COM\.? INC\.?"), BRAND.upper()),
    (re.compile(B + r"WONDER PARK AMUSEMENT INC\.?"), BRAND.upper()),
    (re.compile(B + r"Wonder Park Amusement Inc\.?"), BRAND),
    (re.compile(B + r"WonderPark Amusement" + E), BRAND),
    # short forms
    (re.compile(B + r"WONDER PARK" + E), SHORT.upper()),
    (re.compile(B + r"WONDERPARK" + E), SHORT.upper()),
    (re.compile(B + r"Wonder Park" + E), SHORT),
    (re.compile(B + r"WonderPark" + E), SHORT),
]

changed_files, total = 0, 0
for dp, dn, fn in os.walk(ROOT):
    dn[:] = [d for d in dn if d not in SKIP_DIRS]
    for f in fn:
        if f in SKIP_FILES or f.startswith("apply_") or f.lower().endswith(SKIP_SUFFIX):
            continue
        if not (f.endswith(EXTS) or f == ".env.example"):
            continue
        p = os.path.join(dp, f)
        try:
            with open(p, "r", encoding="utf-8", newline="") as fh:
                src = fh.read()
        except (UnicodeDecodeError, OSError):
            continue
        out, n = src, 0
        for rx, rep in RULES:
            out, k = rx.subn(rep, out)
            n += k

        # APP_NAME / config default
        rel = os.path.relpath(p, ROOT).replace("\\", "/")
        if rel == ".env.example":
            out2 = re.sub(r"(?m)^APP_NAME=.*$", 'APP_NAME="%s"' % BRAND, out)
            n += int(out2 != out); out = out2
        if rel == "config/app.php":
            out2 = out.replace("env('APP_NAME', 'Laravel')", "env('APP_NAME', '%s')" % BRAND)
            n += int(out2 != out); out = out2

        if out != src:
            changed_files += 1; total += n
            print(("%-6s" % ("EDIT" if APPLY else "WOULD")) + " %-55s %d" % (rel, n))
            if APPLY:
                with open(p, "w", encoding="utf-8", newline="") as fh:
                    fh.write(out)

# real .env: only APP_NAME
envp = os.path.join(ROOT, ".env")
if os.path.isfile(envp):
    with open(envp, "r", encoding="utf-8", newline="") as fh:
        e = fh.read()
    e2 = re.sub(r"(?m)^APP_NAME=.*$", 'APP_NAME="%s"' % BRAND, e)
    if e2 != e:
        print(("EDIT  " if APPLY else "WOULD ") + " .env (APP_NAME only)")
        if APPLY:
            with open(envp, "w", encoding="utf-8", newline="") as fh:
                fh.write(e2)

print("\n%s: %d files, %d replacements" % ("APPLIED" if APPLY else "DRY RUN", changed_files, total))
if APPLY:
    print("Next: php artisan config:clear && php artisan view:clear")
else:
    print("Looks right? Re-run with --apply")
