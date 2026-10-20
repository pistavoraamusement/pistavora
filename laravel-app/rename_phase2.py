#!/usr/bin/env python3
"""
Phase 2 rename (display text only):
    REKS            -> Pistavora
    Dino Adventure  -> DinoLakbay
    RollerFever     -> Skataya
    Field of Rides  -> PiyestaRides
    (optional) logo swap

Run inside laravel-app/:
    python rename_phase2.py                                   # DRY RUN
    python rename_phase2.py --apply
    python rename_phase2.py --apply --logo new.png --logo-white new-white.png

NOT touched (they are data keys, routes or identifiers; changing them would break
POS, forecasts, existing bookings or URLs):
    dino_adventure / rollerfever / field_of_rides ids, route names, /RollerFever URL,
    ZoneForecastController, routes/web.php, config/system_log.php, zone_forecast view,
    pos.js, CSS, REKS_* / reksLoadBar JS identifiers, reks-assist.js filename.
Lines that look like comparisons or HTML data attributes are SKIPPED and listed
at the end so you can review them by hand.
"""
import os, re, sys, shutil

APPLY = "--apply" in sys.argv
def argval(flag):
    return sys.argv[sys.argv.index(flag) + 1] if flag in sys.argv else None

ROOT = os.getcwd()
if not os.path.isfile(os.path.join(ROOT, "artisan")):
    sys.exit("Run this inside laravel-app/ (the folder that has 'artisan').")

BRAND = "Pistavora Amusement Park"

SKIP_DIRS = {"vendor", "node_modules", "storage", ".git", "build", "migrations", ".vscode"}
SKIP_FILES = {"composer.lock", "package-lock.json", "patch_user_pages.sh", "web.php",
              "system_log.php", "ZoneForecastController.php", "MlForecastController.php",
              "zone_forecast.blade.php", "rename_pistavora.py", "rename_phase2.py"}
SKIP_SUFFIX = (".zip", ".png", ".jpg", ".jpeg", ".gif", ".svg", ".webp", ".ico", ".pdf",
               ".bat", ".patch", ".woff", ".woff2", ".ttf", ".map", ".css", ".js")
EXTS = (".php",)   # blade files are .php too

B = r"(?:(?<![A-Za-z0-9_/@.\-])|(?<=\\n))"
E = r"(?![A-Za-z0-9_\-])"

# (regex, replacement, kind)   kind: 'reks' or 'zone'
RULES = [
    (re.compile(B + r"REKS Amusement Com\.? Inc\.?"), BRAND, "reks"),
    (re.compile(B + r"REKS Amusement Facilities"), "Pistavora Amusement Park facilities", "reks"),
    (re.compile(B + r"REKS Amusement booking system"), BRAND + " booking system", "reks"),
    (re.compile(r"REKS_Inventory_"), "Pistavora_Inventory_", "reks"),
    (re.compile(B + r"REKS" + E), "Pistavora", "reks"),
    (re.compile(B + r"Dino Adventure" + E), "DinoLakbay", "zone"),
    (re.compile(B + r"Roller ?Fever" + E), "Skataya", "zone"),
    (re.compile(B + r"Field of Rides" + E), "PiyestaRides", "zone"),
]

# Comparison-style lines: the name is probably a lookup key -> skip and report.
KEY_CMP = re.compile(r"(===?|!==?|in_array|case\s+['\"]|->where|where\()")
# Lines with HTML attributes that carry the name as a value: change visible text only.
KEY_ATTR = re.compile(r"(data-(?!msg|preview-target)[\w-]+=|\bvalue=)")
# Name sits inside a URL / route / view / asset string -> never touch.
PATH_CTX = re.compile(r"\b(is|routeIs|url|route|asset|redirect|view|include|extends)\(\s*['\"][^'\"]*$")

def in_text_node(line, pos):
    return line.rfind(">", 0, pos) > line.rfind("<", 0, pos)

def files():
    for dp, dn, fn in os.walk(ROOT):
        dn[:] = [d for d in dn if d not in SKIP_DIRS]
        for f in fn:
            if f in SKIP_FILES or f.startswith("apply_") or f.startswith("patch_"):
                continue
            if f.lower().endswith(SKIP_SUFFIX) or not f.endswith(EXTS):
                continue
            yield os.path.join(dp, f)

files_changed, total, skipped = 0, 0, []
for p in files():
    rel = os.path.relpath(p, ROOT).replace("\\", "/")
    try:
        with open(p, "r", encoding="utf-8", newline="") as fh:
            lines = fh.read().split("\n")
    except (UnicodeDecodeError, OSError):
        continue

    n_file, out_lines = 0, []
    for i, line in enumerate(lines, 1):
        new = line
        for rx, rep, kind in RULES:
            if not rx.search(new):
                continue
            if kind == "zone":
                # PosController: only the human label is safe to change
                if rel.endswith("PosController.php") and "'zone_label'" not in new:
                    continue
                if re.search(r"\$\w*[Rr]outes?\w*\s*=", new):
                    continue   # URL-path lists such as $zoneRoutes = ['RollerFever', ...]
                if KEY_CMP.search(new) and "'name'" not in new and "label" not in new:
                    skipped.append("%s:%d  %s" % (rel, i, line.strip()[:110]))
                    continue
            attr_line = kind == "zone" and bool(KEY_ATTR.search(new))
            def sub(m, rep=rep, kind=kind, attr_line=attr_line, cur=new):
                global n_hit
                if kind == "zone":
                    if PATH_CTX.search(cur[max(0, m.start() - 80):m.start()]):
                        return m.group(0)
                    if attr_line and not in_text_node(cur, m.start()):
                        return m.group(0)
                n_hit += 1
                return rep
            n_hit = 0
            new = rx.sub(sub, new)
            n_file += n_hit
        out_lines.append(new)

    if n_file:
        files_changed += 1; total += n_file
        print(("%-6s" % ("EDIT" if APPLY else "WOULD")) + " %-58s %d" % (rel, n_file))
        if APPLY:
            with open(p, "w", encoding="utf-8", newline="") as fh:
                fh.write("\n".join(out_lines))

# optional logo swap: overwrite the existing filenames so no code references change
for flag, targets in (("--logo", ["wonderpark1logo.png", "wonderpark2logo.png"]),
                      ("--logo-white", ["reks-logo-white.png"])):
    src = argval(flag)
    if not src:
        continue
    if not os.path.isfile(src):
        print("!! %s: file not found: %s" % (flag, src)); continue
    for t in targets:
        dst = os.path.join(ROOT, "public", "images", t)
        print(("LOGO   " if APPLY else "WOULD  ") + "%s -> public/images/%s" % (src, t))
        if APPLY:
            if os.path.isfile(dst) and not os.path.isfile(dst + ".orig"):
                shutil.copy2(dst, dst + ".orig")
            shutil.copy2(src, dst)

print("\n%s: %d files, %d replacements" % ("APPLIED" if APPLY else "DRY RUN", files_changed, total))
if skipped:
    print("\nSKIPPED (look like lookup keys; check by hand if they show text to users):")
    for s in skipped:
        print("  " + s)
print("\nNext: php artisan view:clear && php artisan config:clear" if APPLY else "\nLooks right? Re-run with --apply")
