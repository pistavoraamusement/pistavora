<style>
  .serif{font-family:'Source Serif 4',serif;}

  /* ===== TOOLBAR (same as Home / Inventory) ===== */
  .toolbar{background:var(--card);padding:22px 26px;margin-bottom:20px;border-radius:16px;box-shadow:var(--shadow-sm);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:18px;}
  .toolbar .eyebrow{font-size:.68rem;font-weight:700;color:var(--pink-deep);text-transform:uppercase;letter-spacing:.09em;margin-bottom:6px;}
  .toolbar h1{font-family:'Source Serif 4',serif;font-size:1.4rem;font-weight:700;color:var(--ink);letter-spacing:-.01em;}
  .toolbar .toolbar-desc{font-size:.8rem;color:var(--muted);margin-top:6px;max-width:60ch;line-height:1.5;}
  .toolbar-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}

  /* ===== BUTTONS ===== */
  .btn-primary,.btn-ghost{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:11px 18px;border-radius:10px;font-family:'Inter',sans-serif;font-size:13px;font-weight:600;white-space:nowrap;cursor:pointer;transition:background .15s,border-color .15s,box-shadow .15s;}
  .btn-primary{background:var(--pink-deep);color:#fff;border:none;}
  .btn-primary:hover{background:var(--pink-dark);box-shadow:0 0 0 3px var(--pink-light);}
  .btn-ghost{background:#fff;color:var(--ink);border:1px solid var(--line-strong);}
  .btn-ghost:hover{background:var(--bg);border-color:var(--pink);}

  /* ===== STATS ===== */
  .stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;}
  .stat-card{background:var(--card);border-radius:16px;padding:18px 20px;box-shadow:var(--shadow-sm);}
  .stat-card .label{font-size:.68rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:9px;}
  .stat-card .value{font-family:'Source Serif 4',serif;font-size:1.7rem;font-weight:700;color:var(--ink);line-height:1;}
  .stat-card .sub{font-size:11px;color:var(--muted);margin-top:7px;}
  .stat-card.warn .value,.stat-card.warn .sub{color:var(--amber);}

  /* ===== SECTION PANEL (same as Inventory .report-section) ===== */
  .report-section{background:var(--card);padding:20px;border-radius:16px;margin-bottom:20px;box-shadow:var(--shadow-sm);border:1px solid var(--line);}
  .report-title{font-family:'Source Serif 4',serif;font-size:1.1rem;font-weight:700;margin-bottom:14px;padding-bottom:12px;border-bottom:2px solid var(--pink-light);color:var(--ink);display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
  .report-title .title-icon{color:var(--pink-deep);font-size:.95rem;}
  .report-hint{margin-left:auto;font-family:'Inter',sans-serif;font-size:.74rem;font-weight:500;color:var(--muted);}
  .count-pill{display:inline-flex;align-items:center;justify-content:center;min-width:26px;padding:2px 9px;border-radius:999px;background:var(--pink-light);color:var(--pink-dark);font-family:'Inter',sans-serif;font-size:.72rem;font-weight:700;}

  .section-label{font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);margin:26px 0 12px;}

  @media(max-width:1000px){.stats-row{grid-template-columns:1fr 1fr;}}
  @media(max-width:900px){.toolbar h1{font-size:1.15rem;}.report-hint{display:none;}}
  @media(max-width:520px){.stats-row{grid-template-columns:1fr;}}
</style>