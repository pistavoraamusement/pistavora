<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>REKS Amusement - IBOMS</title>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Exo+2:wght@300;400;500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #0a0e1a;
  --bg2: #0f1628;
  --bg3: #141c35;
  --panel: #111827;
  --panel2: #1a2540;
  --border: #1e3a5f;
  --border2: #2a4a7f;
  --accent: #00d4ff;
  --accent2: #0099cc;
  --accent3: #00ff88;
  --accent4: #ff6b35;
  --accent5: #ffd700;
  --danger: #ff4444;
  --warning: #ffaa00;
  --success: #00cc66;
  --text: #e8f4ff;
  --text2: #8ab0d0;
  --text3: #4a6a8a;
  --glow: 0 0 20px rgba(0,212,255,0.3);
  --glow2: 0 0 30px rgba(0,212,255,0.5);
}

* { margin:0; padding:0; box-sizing:border-box; }

body {
  font-family: 'Exo 2', sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  overflow-x: hidden;
}

/* ═══════════════ SCROLLBAR ═══════════════ */
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: var(--bg2); }
::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 3px; }

/* ═══════════════ LOGIN SCREEN ═══════════════ */
#loginScreen {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg);
  position: relative;
  overflow: hidden;
}

.login-bg {
  position: absolute; inset: 0;
  background: 
    radial-gradient(ellipse at 20% 50%, rgba(0,80,160,0.15) 0%, transparent 60%),
    radial-gradient(ellipse at 80% 20%, rgba(0,212,255,0.08) 0%, transparent 50%),
    radial-gradient(ellipse at 60% 80%, rgba(0,255,136,0.05) 0%, transparent 50%);
}

.grid-overlay {
  position: absolute; inset: 0;
  background-image: 
    linear-gradient(rgba(0,212,255,0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0,212,255,0.03) 1px, transparent 1px);
  background-size: 40px 40px;
  animation: gridMove 20s linear infinite;
}

@keyframes gridMove {
  0% { transform: translateY(0); }
  100% { transform: translateY(40px); }
}

.login-card {
  position: relative;
  background: var(--panel);
  border: 1px solid var(--border2);
  border-radius: 16px;
  padding: 50px 45px;
  width: 460px;
  box-shadow: 0 0 60px rgba(0,212,255,0.1), inset 0 1px 0 rgba(255,255,255,0.05);
  animation: cardIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes cardIn {
  from { opacity:0; transform: translateY(30px) scale(0.95); }
  to { opacity:1; transform: translateY(0) scale(1); }
}

.login-logo {
  text-align: center;
  margin-bottom: 35px;
}

.login-logo .company-badge {
  display: inline-block;
  background: linear-gradient(135deg, #ff6b35, #ffd700);
  color: #0a0e1a;
  font-family: 'Rajdhani', sans-serif;
  font-size: 28px;
  font-weight: 700;
  letter-spacing: 3px;
  padding: 8px 20px;
  border-radius: 8px;
  margin-bottom: 10px;
}

.login-logo h2 {
  font-family: 'Exo 2', sans-serif;
  font-size: 11px;
  color: var(--text2);
  letter-spacing: 2px;
  text-transform: uppercase;
  margin-bottom: 4px;
}

.login-logo h3 {
  font-family: 'Rajdhani', sans-serif;
  font-size: 14px;
  color: var(--accent);
  letter-spacing: 1px;
}

.login-divider {
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--border2), transparent);
  margin: 25px 0;
}

.login-form label {
  display: block;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--text2);
  margin-bottom: 8px;
}

.login-form input, .login-form select {
  width: 100%;
  background: var(--bg2);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 13px 16px;
  color: var(--text);
  font-family: 'Exo 2', sans-serif;
  font-size: 14px;
  margin-bottom: 20px;
  transition: all 0.3s;
  outline: none;
}

.login-form input:focus, .login-form select:focus {
  border-color: var(--accent);
  box-shadow: 0 0 15px rgba(0,212,255,0.15);
}

.login-form select option { background: var(--panel); }

.btn-login {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, var(--accent2), var(--accent));
  border: none;
  border-radius: 8px;
  color: #0a0e1a;
  font-family: 'Rajdhani', sans-serif;
  font-size: 16px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 20px rgba(0,212,255,0.3);
}

.btn-login:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 30px rgba(0,212,255,0.5);
}

.login-hint {
  text-align: center;
  margin-top: 20px;
  font-size: 11px;
  color: var(--text3);
}

/* ═══════════════ MAIN LAYOUT ═══════════════ */
#mainApp { display: none; height: 100vh; flex-direction: column; }
#mainApp.active { display: flex; }

.topbar {
  height: 60px;
  background: var(--panel);
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  padding: 0 20px;
  gap: 20px;
  flex-shrink: 0;
  box-shadow: 0 2px 20px rgba(0,0,0,0.3);
}

.topbar-logo {
  font-family: 'Rajdhani', sans-serif;
  font-size: 18px;
  font-weight: 700;
  color: var(--accent);
  letter-spacing: 1px;
  white-space: nowrap;
}

.topbar-logo span { color: var(--accent4); }

.topbar-title {
  font-size: 11px;
  color: var(--text3);
  letter-spacing: 1px;
  flex: 1;
}

.topbar-user {
  display: flex;
  align-items: center;
  gap: 10px;
  background: var(--bg2);
  border: 1px solid var(--border);
  border-radius: 25px;
  padding: 6px 14px 6px 6px;
}

.user-avatar {
  width: 30px; height: 30px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--accent), var(--accent3));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 700;
  color: #0a0e1a;
}

.user-info { line-height: 1.3; }
.user-info .name { font-size: 12px; font-weight: 600; }
.user-info .role { font-size: 10px; color: var(--accent); letter-spacing: 1px; }

.btn-logout {
  background: none;
  border: 1px solid var(--border2);
  border-radius: 6px;
  color: var(--text2);
  padding: 5px 12px;
  font-size: 11px;
  cursor: pointer;
  font-family: 'Exo 2', sans-serif;
  transition: all 0.2s;
}
.btn-logout:hover { border-color: var(--danger); color: var(--danger); }

.topbar-clock {
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  color: var(--accent3);
  letter-spacing: 1px;
}

/* ═══════════════ BODY LAYOUT ═══════════════ */
.app-body {
  display: flex;
  flex: 1;
  overflow: hidden;
}

.sidebar {
  width: 220px;
  background: var(--panel);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  padding: 16px 0;
  flex-shrink: 0;
  overflow-y: auto;
}

.nav-section {
  font-size: 9px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--text3);
  padding: 12px 20px 6px;
  font-weight: 600;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 20px;
  cursor: pointer;
  transition: all 0.2s;
  border-left: 3px solid transparent;
  font-size: 13px;
  font-weight: 500;
  color: var(--text2);
  position: relative;
}

.nav-item:hover {
  background: var(--bg2);
  color: var(--text);
  border-left-color: var(--border2);
}

.nav-item.active {
  background: rgba(0,212,255,0.08);
  color: var(--accent);
  border-left-color: var(--accent);
}

.nav-icon { font-size: 16px; width: 20px; text-align: center; }

.nav-badge {
  margin-left: auto;
  background: var(--danger);
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 10px;
}

.content-area {
  flex: 1;
  overflow-y: auto;
  padding: 24px;
  background: var(--bg);
}

/* ═══════════════ PANEL CARDS ═══════════════ */
.page { display: none; }
.page.active { display: block; animation: pageIn 0.3s ease; }

@keyframes pageIn {
  from { opacity:0; transform: translateY(10px); }
  to { opacity:1; transform: translateY(0); }
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--border);
}

.page-header h1 {
  font-family: 'Rajdhani', sans-serif;
  font-size: 24px;
  font-weight: 700;
  color: var(--text);
  letter-spacing: 1px;
}

.page-header h1 span { color: var(--accent); }

.page-header .page-sub {
  font-size: 12px;
  color: var(--text3);
  margin-top: 2px;
}

.card {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
}

.card-title {
  font-family: 'Rajdhani', sans-serif;
  font-size: 15px;
  font-weight: 600;
  letter-spacing: 1px;
  color: var(--text);
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.card-title .ct-icon { color: var(--accent); }

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 18px;
  position: relative;
  overflow: hidden;
  transition: all 0.3s;
}

.stat-card:hover {
  border-color: var(--border2);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: var(--accent);
}

.stat-card.orange::before { background: var(--accent4); }
.stat-card.green::before { background: var(--accent3); }
.stat-card.gold::before { background: var(--accent5); }
.stat-card.red::before { background: var(--danger); }

.stat-label {
  font-size: 10px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--text3);
  margin-bottom: 8px;
  font-weight: 600;
}

.stat-value {
  font-family: 'Rajdhani', sans-serif;
  font-size: 28px;
  font-weight: 700;
  color: var(--text);
  line-height: 1;
}

.stat-value.accent { color: var(--accent); }
.stat-value.green { color: var(--accent3); }
.stat-value.orange { color: var(--accent4); }
.stat-value.gold { color: var(--accent5); }
.stat-value.red { color: var(--danger); }

.stat-sub { font-size: 11px; color: var(--text3); margin-top: 4px; }

/* ═══════════════ BUTTONS ═══════════════ */
.btn {
  padding: 9px 18px;
  border-radius: 7px;
  border: none;
  cursor: pointer;
  font-family: 'Exo 2', sans-serif;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.5px;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn-primary {
  background: linear-gradient(135deg, var(--accent2), var(--accent));
  color: #0a0e1a;
}

.btn-primary:hover { box-shadow: 0 4px 20px rgba(0,212,255,0.4); transform: translateY(-1px); }

.btn-success {
  background: linear-gradient(135deg, #009944, var(--accent3));
  color: #0a0e1a;
}

.btn-danger {
  background: linear-gradient(135deg, #cc0000, var(--danger));
  color: #fff;
}

.btn-warning {
  background: linear-gradient(135deg, #cc8800, var(--warning));
  color: #0a0e1a;
}

.btn-outline {
  background: transparent;
  border: 1px solid var(--border2);
  color: var(--text2);
}

.btn-outline:hover { border-color: var(--accent); color: var(--accent); }

/* ═══════════════ TABLES ═══════════════ */
.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.data-table th {
  background: var(--bg2);
  color: var(--text2);
  font-size: 10px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  padding: 10px 14px;
  text-align: left;
  font-weight: 600;
  border-bottom: 1px solid var(--border);
}

.data-table td {
  padding: 11px 14px;
  border-bottom: 1px solid rgba(30,58,95,0.4);
  color: var(--text);
  vertical-align: middle;
}

.data-table tr:hover td { background: rgba(0,212,255,0.03); }

.badge {
  display: inline-block;
  padding: 3px 9px;
  border-radius: 12px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.badge-green { background: rgba(0,204,102,0.15); color: var(--success); border: 1px solid rgba(0,204,102,0.3); }
.badge-red { background: rgba(255,68,68,0.15); color: var(--danger); border: 1px solid rgba(255,68,68,0.3); }
.badge-yellow { background: rgba(255,170,0,0.15); color: var(--warning); border: 1px solid rgba(255,170,0,0.3); }
.badge-blue { background: rgba(0,212,255,0.15); color: var(--accent); border: 1px solid rgba(0,212,255,0.3); }

/* ═══════════════ FORM CONTROLS ═══════════════ */
.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 16px;
}

.form-group { display: flex; flex-direction: column; gap: 6px; }

.form-group label {
  font-size: 10px;
  letter-spacing: 1px;
  text-transform: uppercase;
  color: var(--text2);
  font-weight: 600;
}

.form-group input,
.form-group select,
.form-group textarea {
  background: var(--bg2);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 10px 14px;
  color: var(--text);
  font-family: 'Exo 2', sans-serif;
  font-size: 13px;
  outline: none;
  transition: all 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: var(--accent);
  box-shadow: 0 0 12px rgba(0,212,255,0.1);
}

.form-group select option { background: var(--panel); }

/* ═══════════════ POS MODULE ═══════════════ */
.pos-layout {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 20px;
  height: calc(100vh - 170px);
}

.pos-items-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
  gap: 12px;
  overflow-y: auto;
  align-content: start;
  padding-right: 4px;
}

.pos-item {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 14px;
  cursor: pointer;
  text-align: center;
  transition: all 0.2s;
}

.pos-item:hover {
  border-color: var(--accent);
  box-shadow: 0 0 15px rgba(0,212,255,0.15);
  transform: translateY(-2px);
}

.pos-item .item-icon { font-size: 28px; margin-bottom: 8px; }
.pos-item .item-name { font-size: 12px; font-weight: 600; margin-bottom: 4px; }
.pos-item .item-price { font-size: 14px; font-weight: 700; color: var(--accent3); font-family: 'Rajdhani', sans-serif; }
.pos-item .item-stock { font-size: 10px; color: var(--text3); margin-top: 3px; }

.pos-cart {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.cart-header {
  padding: 16px;
  border-bottom: 1px solid var(--border);
  font-family: 'Rajdhani', sans-serif;
  font-size: 16px;
  font-weight: 700;
  color: var(--accent);
  letter-spacing: 1px;
}

.cart-items {
  flex: 1;
  overflow-y: auto;
  padding: 12px;
}

.cart-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  background: var(--bg2);
  border-radius: 8px;
  margin-bottom: 8px;
}

.ci-name { flex: 1; font-size: 12px; font-weight: 500; }
.ci-qty {
  display: flex;
  align-items: center;
  gap: 6px;
}

.ci-qty button {
  width: 24px; height: 24px;
  border: 1px solid var(--border2);
  border-radius: 4px;
  background: var(--bg);
  color: var(--text);
  cursor: pointer;
  font-size: 14px;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
}

.ci-qty button:hover { border-color: var(--accent); color: var(--accent); }
.ci-qty .qty { font-family: 'Share Tech Mono', monospace; font-size: 13px; width: 24px; text-align: center; }
.ci-price { font-family: 'Rajdhani', sans-serif; font-size: 14px; font-weight: 600; color: var(--accent3); min-width: 55px; text-align: right; }
.ci-del { background: none; border: none; color: var(--text3); cursor: pointer; font-size: 14px; transition: color 0.2s; }
.ci-del:hover { color: var(--danger); }

.cart-footer {
  padding: 16px;
  border-top: 1px solid var(--border);
}

.cart-summary { margin-bottom: 14px; }
.cart-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  padding: 3px 0;
  color: var(--text2);
}

.cart-row.total {
  font-family: 'Rajdhani', sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: var(--text);
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid var(--border);
}

.cart-row.total span:last-child { color: var(--accent3); }

.tender-input { margin-bottom: 10px; }

.pos-cat-tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}

.cat-tab {
  padding: 6px 14px;
  border-radius: 20px;
  border: 1px solid var(--border);
  background: transparent;
  color: var(--text2);
  cursor: pointer;
  font-size: 12px;
  font-family: 'Exo 2', sans-serif;
  transition: all 0.2s;
}

.cat-tab.active, .cat-tab:hover {
  border-color: var(--accent);
  color: var(--accent);
  background: rgba(0,212,255,0.08);
}

/* ═══════════════ INVENTORY ═══════════════ */
.inv-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 14px;
  margin-bottom: 24px;
}

.inv-card {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 16px;
  transition: all 0.2s;
}

.inv-card.low { border-color: rgba(255,68,68,0.4); background: rgba(255,68,68,0.04); }
.inv-card.ok { border-color: rgba(0,204,102,0.3); }

.inv-item-name { font-weight: 600; margin-bottom: 10px; font-size: 14px; }
.inv-item-icon { font-size: 24px; float: right; }

.inv-bar-container {
  background: var(--bg2);
  border-radius: 4px;
  height: 6px;
  margin: 10px 0 6px;
  overflow: hidden;
}

.inv-bar {
  height: 100%;
  border-radius: 4px;
  transition: width 0.5s;
}

.inv-bar.high { background: var(--accent3); }
.inv-bar.mid { background: var(--warning); }
.inv-bar.low { background: var(--danger); }

.inv-qty { font-family: 'Rajdhani', sans-serif; font-size: 20px; font-weight: 700; }
.inv-max { font-size: 11px; color: var(--text3); }

.inv-status { font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-top: 6px; }
.inv-status.ok { color: var(--success); }
.inv-status.low { color: var(--danger); }
.inv-status.mid { color: var(--warning); }

/* ═══════════════ EXCEL STYLE MODULE ═══════════════ */
.excel-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.excel-panel {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 20px;
}

.excel-panel.full { grid-column: 1 / -1; }

.excel-table-wrap { overflow-x: auto; }

.excel-cell-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
  font-family: 'Share Tech Mono', monospace;
}

.excel-cell-table th {
  background: var(--bg2);
  padding: 8px 12px;
  color: var(--accent);
  font-size: 11px;
  letter-spacing: 1px;
  border: 1px solid var(--border);
  text-align: left;
}

.excel-cell-table td {
  padding: 7px 12px;
  border: 1px solid var(--border);
  color: var(--text);
}

.excel-cell-table td input {
  background: transparent;
  border: none;
  color: var(--accent3);
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  width: 100%;
  outline: none;
}

.excel-cell-table .computed {
  color: var(--accent5);
  font-weight: 700;
}

.excel-cell-table .negative { color: var(--danger); }
.excel-cell-table .positive { color: var(--success); }

/* ═══════════════ RESERVATION ═══════════════ */
.scan-area {
  background: var(--bg2);
  border: 2px dashed var(--border2);
  border-radius: 12px;
  padding: 30px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  margin-bottom: 20px;
}

.scan-area:hover, .scan-area.scanning {
  border-color: var(--accent);
  background: rgba(0,212,255,0.04);
}

.scan-icon { font-size: 48px; margin-bottom: 12px; }
.scan-text { font-size: 14px; color: var(--text2); }
.scan-sub { font-size: 11px; color: var(--text3); margin-top: 4px; }

.reservation-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}

.res-card {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 16px;
  transition: all 0.3s;
}

.res-card:hover { border-color: var(--border2); }
.res-card-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px; }
.res-name { font-weight: 700; font-size: 15px; }
.res-date { font-size: 11px; color: var(--text3); margin-top: 2px; }
.res-details { font-size: 12px; color: var(--text2); line-height: 1.8; }
.res-packs { font-family: 'Rajdhani', sans-serif; font-size: 18px; font-weight: 700; color: var(--accent4); margin-top: 8px; }

/* ═══════════════ WAIVER ═══════════════ */
.waiver-form {
  max-width: 700px;
}

.waiver-scan-header {
  display: flex;
  align-items: center;
  gap: 16px;
  background: var(--bg2);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 16px;
  margin-bottom: 24px;
}

.waiver-scan-icon { font-size: 32px; }
.waiver-scan-text h3 { font-size: 14px; font-weight: 600; }
.waiver-scan-text p { font-size: 12px; color: var(--text3); margin-top: 3px; }

.waiver-submitted-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px;
  margin-top: 20px;
}

.waiver-entry {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 14px;
}

.we-name { font-weight: 700; margin-bottom: 4px; }
.we-detail { font-size: 11px; color: var(--text2); line-height: 1.7; }
.we-type { margin-top: 8px; }

/* ═══════════════ ATTENDANCE ═══════════════ */
.attendance-summary {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 20px;
}

.att-card {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 16px;
  text-align: center;
}

.att-icon { font-size: 24px; margin-bottom: 8px; }
.att-num { font-family: 'Rajdhani', sans-serif; font-size: 28px; font-weight: 700; }
.att-label { font-size: 11px; color: var(--text3); letter-spacing: 1px; text-transform: uppercase; margin-top: 3px; }

.payslip-card {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 24px;
  max-width: 500px;
}

.payslip-header {
  text-align: center;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--border);
  margin-bottom: 16px;
}

.payslip-company { font-family: 'Rajdhani', sans-serif; font-size: 18px; font-weight: 700; color: var(--accent4); }
.payslip-title { font-size: 12px; color: var(--text3); letter-spacing: 2px; text-transform: uppercase; }
.payslip-period { font-size: 13px; color: var(--accent); margin-top: 4px; }

.payslip-row {
  display: flex;
  justify-content: space-between;
  padding: 6px 0;
  font-size: 13px;
  border-bottom: 1px solid rgba(30,58,95,0.3);
}

.payslip-row.deduction span:last-child { color: var(--danger); }
.payslip-row.total-row {
  font-family: 'Rajdhani', sans-serif;
  font-size: 18px;
  font-weight: 700;
  color: var(--accent3);
  border-top: 2px solid var(--border2);
  padding-top: 10px;
  margin-top: 4px;
  border-bottom: none;
}

/* ═══════════════ ANALYTICS ═══════════════ */
.analytics-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.chart-placeholder {
  background: var(--bg2);
  border-radius: 8px;
  padding: 16px;
  min-height: 160px;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
}

.bar-chart { display: flex; align-items: flex-end; gap: 8px; height: 120px; }
.bar-wrap { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; height: 100%; justify-content: flex-end; }
.bar { width: 100%; border-radius: 4px 4px 0 0; transition: all 0.5s; position: relative; }
.bar-label { font-size: 9px; color: var(--text3); text-align: center; }
.bar-val { font-size: 10px; color: var(--text2); font-family: 'Share Tech Mono', monospace; }

.pie-visual {
  display: flex;
  align-items: center;
  gap: 20px;
}

.pie-circle {
  width: 100px; height: 100px;
  border-radius: 50%;
  position: relative;
  flex-shrink: 0;
}

.pie-legend { flex: 1; }
.pie-legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  font-size: 12px;
}

.pie-dot { width: 10px; height: 10px; border-radius: 50%; }

.quota-bar-wrap {
  margin-top: 12px;
}

.quota-label { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px; }
.quota-track {
  background: var(--bg2);
  border-radius: 6px;
  height: 18px;
  overflow: hidden;
  position: relative;
}

.quota-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--accent2), var(--accent3));
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: 700;
  color: #0a0e1a;
  transition: width 1s ease;
}

.quota-target {
  position: absolute;
  top: 0; bottom: 0;
  width: 2px;
  background: var(--accent5);
}

/* ═══════════════ CHATBOT ═══════════════ */
.chatbot-layout {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 20px;
  height: calc(100vh - 170px);
}

.chat-window {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.chat-header {
  padding: 16px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  gap: 12px;
}

.chat-avatar {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--accent4), var(--accent5));
  display: flex; align-items: center; justify-content: center;
  font-size: 18px;
}

.chat-online { width: 8px; height: 8px; background: var(--success); border-radius: 50%; margin-left: auto; }

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.msg {
  max-width: 75%;
  padding: 10px 14px;
  border-radius: 12px;
  font-size: 13px;
  line-height: 1.5;
}

.msg.bot {
  background: var(--bg2);
  border: 1px solid var(--border);
  align-self: flex-start;
  border-radius: 4px 12px 12px 12px;
}

.msg.user {
  background: linear-gradient(135deg, var(--accent2), var(--accent));
  color: #0a0e1a;
  align-self: flex-end;
  font-weight: 500;
  border-radius: 12px 4px 12px 12px;
}

.chat-input-row {
  padding: 12px;
  border-top: 1px solid var(--border);
  display: flex;
  gap: 8px;
}

.chat-input {
  flex: 1;
  background: var(--bg2);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 10px 14px;
  color: var(--text);
  font-family: 'Exo 2', sans-serif;
  font-size: 13px;
  outline: none;
}

.chat-input:focus { border-color: var(--accent); }

.chat-info-panel {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.chat-quick-btn {
  padding: 8px 14px;
  background: var(--bg2);
  border: 1px solid var(--border);
  border-radius: 8px;
  color: var(--text2);
  cursor: pointer;
  font-size: 12px;
  font-family: 'Exo 2', sans-serif;
  text-align: left;
  transition: all 0.2s;
  width: 100%;
  margin-bottom: 6px;
}

.chat-quick-btn:hover { border-color: var(--accent); color: var(--accent); }

/* ═══════════════ MODAL ═══════════════ */
.modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.7);
  backdrop-filter: blur(4px);
  z-index: 1000;
  align-items: center;
  justify-content: center;
}

.modal-overlay.active { display: flex; }

.modal-box {
  background: var(--panel);
  border: 1px solid var(--border2);
  border-radius: 16px;
  padding: 28px;
  width: 500px;
  max-width: 95vw;
  max-height: 85vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0,0,0,0.5);
  animation: modalIn 0.3s cubic-bezier(0.16,1,0.3,1);
}

@keyframes modalIn {
  from { opacity:0; transform: scale(0.9) translateY(20px); }
  to { opacity:1; transform: scale(1) translateY(0); }
}

.modal-title {
  font-family: 'Rajdhani', sans-serif;
  font-size: 20px;
  font-weight: 700;
  letter-spacing: 1px;
  margin-bottom: 20px;
  color: var(--accent);
}

.modal-close {
  float: right;
  background: none;
  border: none;
  color: var(--text3);
  font-size: 20px;
  cursor: pointer;
  transition: color 0.2s;
}

.modal-close:hover { color: var(--danger); }

/* ═══════════════ TOAST ═══════════════ */
.toast-container {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 2000;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.toast {
  background: var(--panel2);
  border: 1px solid var(--border2);
  border-radius: 10px;
  padding: 12px 18px;
  font-size: 13px;
  color: var(--text);
  box-shadow: 0 8px 30px rgba(0,0,0,0.4);
  display: flex;
  align-items: center;
  gap: 10px;
  animation: toastIn 0.3s ease;
  min-width: 260px;
}

@keyframes toastIn {
  from { opacity:0; transform: translateX(30px); }
  to { opacity:1; transform: translateX(0); }
}

.toast.success { border-left: 3px solid var(--success); }
.toast.error { border-left: 3px solid var(--danger); }
.toast.info { border-left: 3px solid var(--accent); }
.toast.warning { border-left: 3px solid var(--warning); }

/* ═══════════════ DASHBOARD WIDGETS ═══════════════ */
.dash-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 20px;
}

.activity-feed {
  max-height: 240px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 0;
  border-bottom: 1px solid rgba(30,58,95,0.3);
  font-size: 12px;
}

.activity-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.activity-text { flex: 1; color: var(--text2); }
.activity-time { color: var(--text3); font-size: 11px; font-family: 'Share Tech Mono', monospace; }

.alert-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  background: var(--bg2);
  border-radius: 8px;
  margin-bottom: 8px;
  border-left: 3px solid var(--danger);
  font-size: 12px;
}

.alert-item.warn { border-left-color: var(--warning); }
.alert-item.info { border-left-color: var(--accent); }

/* ═══════════════ RESPONSIVE ═══════════════ */
@media (max-width: 768px) {
  .sidebar { width: 60px; }
  .nav-item span:not(.nav-icon) { display: none; }
  .nav-section { display: none; }
  .pos-layout { grid-template-columns: 1fr; }
  .analytics-grid { grid-template-columns: 1fr; }
  .dash-grid { grid-template-columns: 1fr; }
  .chatbot-layout { grid-template-columns: 1fr; }
  .excel-grid { grid-template-columns: 1fr; }
}

/* ═══════════════ SCANNING ANIMATION ═══════════════ */
.scan-line {
  display: none;
  position: absolute;
  left: 0; right: 0;
  height: 2px;
  background: linear-gradient(90deg, transparent, var(--accent), transparent);
  animation: scanMove 2s linear infinite;
  box-shadow: 0 0 10px var(--accent);
}

.scan-area.scanning { position: relative; overflow: hidden; }
.scan-area.scanning .scan-line { display: block; }

@keyframes scanMove {
  0% { top: 0; }
  100% { top: 100%; }
}

/* ═══════════════ MISC ═══════════════ */
.text-accent { color: var(--accent); }
.text-green { color: var(--accent3); }
.text-orange { color: var(--accent4); }
.text-gold { color: var(--accent5); }
.text-red { color: var(--danger); }
.text-muted { color: var(--text3); }
.mb-4 { margin-bottom: 16px; }
.flex-gap { display: flex; gap: 10px; flex-wrap: wrap; }

.section-divider {
  height: 1px;
  background: linear-gradient(90deg, var(--border2), transparent);
  margin: 20px 0;
}

.number-display {
  font-family: 'Share Tech Mono', monospace;
  color: var(--accent3);
}

.receipt-box {
  background: #fff;
  color: #111;
  border-radius: 8px;
  padding: 20px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  max-width: 320px;
  margin: 0 auto;
  line-height: 1.8;
}

.receipt-box .r-title { text-align: center; font-weight: 700; font-size: 14px; }
.receipt-box .r-center { text-align: center; }
.receipt-box .r-divider { border-top: 1px dashed #999; margin: 6px 0; }
.receipt-box .r-row { display: flex; justify-content: space-between; }
.receipt-box .r-total { font-weight: 700; font-size: 14px; }

/* Attendance rule badge */
.rule-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.5px;
}
</style>
</head>
<body>

<!-- ═══════════ LOGIN SCREEN ═══════════ -->
<div id="loginScreen">
  <div class="login-bg"></div>
  <div class="grid-overlay"></div>
  <div class="login-card">
    <div class="login-logo">
      <div class="company-badge">⚡ REKS</div>
      <h2>Amusement Com Inc — Lipa</h2>
      <h3>Integrated Business Operations Management System</h3>
    </div>
    <div class="login-divider"></div>
    <div class="login-form">
      <label>Username / Employee ID</label>
      <input type="text" id="loginUser" placeholder="Enter your username..." />
      <label>Password</label>
      <input type="password" id="loginPass" placeholder="••••••••" />
      <label>Login As</label>
      <select id="loginRole">
        <option value="">— Select Role —</option>
        <option value="cashier">Cashier</option>
        <option value="manager">Manager / Team Leader</option>
        <option value="customer">Customer / Client</option>
        <option value="admin">Admin</option>
      </select>
      <button class="btn-login" onclick="doLogin()">🔐 SIGN IN</button>
    </div>
    <div class="login-hint">REKS Amusement Com Inc • Lipa City • IBOMS v2.0</div>
  </div>
</div>

<!-- ═══════════ MAIN APP ═══════════ -->
<div id="mainApp">
  <!-- TOPBAR -->
  <div class="topbar">
    <div class="topbar-logo">⚡ <span>REKS</span> BOMS</div>
    <div class="topbar-title">Integrated Business Operations Management System — Lipa</div>
    <div class="topbar-clock" id="topClock">00:00:00</div>
    <div class="topbar-user">
      <div class="user-avatar" id="userAvatar">U</div>
      <div class="user-info">
        <div class="name" id="userName">User</div>
        <div class="role" id="userRole">ROLE</div>
      </div>
    </div>
    <button class="btn-logout" onclick="doLogout()">⬅ Logout</button>
  </div>

  <!-- BODY -->
  <div class="app-body">
    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
      <div class="nav-section">Main</div>
      <div class="nav-item active" onclick="showPage('dashboard')" id="nav-dashboard">
        <span class="nav-icon">🏠</span><span>Dashboard</span>
      </div>

      <div class="nav-section" id="ns-pos">Point of Sale</div>
      <div class="nav-item" onclick="showPage('pos')" id="nav-pos">
        <span class="nav-icon">💳</span><span>POS System</span>
      </div>

      <div class="nav-section" id="ns-ops">Operations</div>
      <div class="nav-item" onclick="showPage('inventory')" id="nav-inventory">
        <span class="nav-icon">📦</span><span>Inventory</span>
        <span class="nav-badge" id="invBadge">3</span>
      </div>
      <div class="nav-item" onclick="showPage('excel')" id="nav-excel">
        <span class="nav-icon">📊</span><span>Excel Calc</span>
      </div>
      <div class="nav-item" onclick="showPage('reservation')" id="nav-reservation">
        <span class="nav-icon">📅</span><span>Reservation</span>
      </div>

      <div class="nav-section" id="ns-cust">Customer</div>
      <div class="nav-item" onclick="showPage('waiver')" id="nav-waiver">
        <span class="nav-icon">📋</span><span>Waiver</span>
      </div>
      <div class="nav-item" onclick="showPage('chatbot')" id="nav-chatbot">
        <span class="nav-icon">💬</span><span>Chat / Promo</span>
      </div>

      <div class="nav-section" id="ns-hr">HR & Payroll</div>
      <div class="nav-item" onclick="showPage('attendance')" id="nav-attendance">
        <span class="nav-icon">⏱️</span><span>Attendance</span>
      </div>

      <div class="nav-section" id="ns-anl">Reports</div>
      <div class="nav-item" onclick="showPage('analytics')" id="nav-analytics">
        <span class="nav-icon">📈</span><span>Analytics</span>
      </div>
    </div>

    <!-- CONTENT -->
    <div class="content-area">

      <!-- ═══ DASHBOARD ═══ -->
      <div class="page active" id="page-dashboard">
        <div class="page-header">
          <div>
            <h1>Main <span>Dashboard</span></h1>
            <div class="page-sub" id="dashDate">Loading...</div>
          </div>
          <div class="flex-gap">
            <button class="btn btn-outline" onclick="refreshDash()">🔄 Refresh</button>
          </div>
        </div>

        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-label">Today's Sales</div>
            <div class="stat-value accent">₱24,850</div>
            <div class="stat-sub">↑ 12% vs yesterday</div>
          </div>
          <div class="stat-card green">
            <div class="stat-label">Transactions</div>
            <div class="stat-value green">87</div>
            <div class="stat-sub">Completed today</div>
          </div>
          <div class="stat-card orange">
            <div class="stat-label">Reservations</div>
            <div class="stat-value orange">12</div>
            <div class="stat-sub">4 pending confirmation</div>
          </div>
          <div class="stat-card gold">
            <div class="stat-label">Quota Progress</div>
            <div class="stat-value gold">83%</div>
            <div class="stat-sub">₱5,150 remaining</div>
          </div>
          <div class="stat-card red">
            <div class="stat-label">Low Stock Items</div>
            <div class="stat-value red">3</div>
            <div class="stat-sub">Requires restock</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Employees Present</div>
            <div class="stat-value">18/20</div>
            <div class="stat-sub">2 on leave</div>
          </div>
        </div>

        <div class="dash-grid">
          <div class="card">
            <div class="card-title"><span class="ct-icon">⚡</span>Recent Activity</div>
            <div class="activity-feed">
              <div class="activity-item">
                <div class="activity-dot" style="background:var(--accent3)"></div>
                <div class="activity-text">Cashier Ana completed transaction #0087 — ₱420</div>
                <div class="activity-time">09:42</div>
              </div>
              <div class="activity-item">
                <div class="activity-dot" style="background:var(--accent4)"></div>
                <div class="activity-text">New reservation: Santos Family Party (15 pax)</div>
                <div class="activity-time">09:35</div>
              </div>
              <div class="activity-item">
                <div class="activity-dot" style="background:var(--danger)"></div>
                <div class="activity-text">⚠ Low stock alert: Piattos Cheese — 8 pcs left</div>
                <div class="activity-time">09:21</div>
              </div>
              <div class="activity-item">
                <div class="activity-dot" style="background:var(--accent)"></div>
                <div class="activity-text">Waiver submitted: Maria Dela Cruz (Senior)</div>
                <div class="activity-time">09:14</div>
              </div>
              <div class="activity-item">
                <div class="activity-dot" style="background:var(--warning)"></div>
                <div class="activity-text">Employee Lito — 22 mins late (₱25 deducted)</div>
                <div class="activity-time">08:52</div>
              </div>
              <div class="activity-item">
                <div class="activity-dot" style="background:var(--accent3)"></div>
                <div class="activity-text">Cashier Ana opened shift — ₱5,000 fund</div>
                <div class="activity-time">08:00</div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-title"><span class="ct-icon">🚨</span>Alerts</div>
            <div class="alert-item">
              <span>📦</span>
              <div>
                <strong>Piattos Cheese</strong> — 8 pcs (below minimum 20)<br>
                <span style="font-size:11px;color:var(--text3)">Inventory · Snacks</span>
              </div>
            </div>
            <div class="alert-item">
              <span>📦</span>
              <div>
                <strong>Socks (Kids S)</strong> — 5 pairs (restock needed)<br>
                <span style="font-size:11px;color:var(--text3)">Inventory · Socks</span>
              </div>
            </div>
            <div class="alert-item warn">
              <span>⏱️</span>
              <div>
                <strong>Quota @ 83%</strong> — ₱5,150 more to target<br>
                <span style="font-size:11px;color:var(--text3)">Daily Sales Target: ₱30,000</span>
              </div>
            </div>
            <div class="alert-item info">
              <span>📅</span>
              <div>
                <strong>4 Unconfirmed Reservations</strong><br>
                <span style="font-size:11px;color:var(--text3)">Requires TL approval</span>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-title"><span class="ct-icon">📊</span>Sales This Week</div>
          <div class="bar-chart">
            <div class="bar-wrap">
              <div class="bar-val">₱18k</div>
              <div class="bar" style="height:60%;background:linear-gradient(180deg,var(--accent2),var(--accent))"></div>
              <div class="bar-label">Mon</div>
            </div>
            <div class="bar-wrap">
              <div class="bar-val">₱22k</div>
              <div class="bar" style="height:74%;background:linear-gradient(180deg,var(--accent2),var(--accent))"></div>
              <div class="bar-label">Tue</div>
            </div>
            <div class="bar-wrap">
              <div class="bar-val">₱19k</div>
              <div class="bar" style="height:63%;background:linear-gradient(180deg,var(--accent2),var(--accent))"></div>
              <div class="bar-label">Wed</div>
            </div>
            <div class="bar-wrap">
              <div class="bar-val">₱28k</div>
              <div class="bar" style="height:93%;background:linear-gradient(180deg,var(--accent4),var(--accent5))"></div>
              <div class="bar-label">Thu</div>
            </div>
            <div class="bar-wrap">
              <div class="bar-val">₱24k</div>
              <div class="bar" style="height:80%;background:linear-gradient(180deg,var(--accent2),var(--accent))"></div>
              <div class="bar-label">Fri</div>
            </div>
            <div class="bar-wrap">
              <div class="bar-val">₱30k</div>
              <div class="bar" style="height:100%;background:linear-gradient(180deg,#00cc44,var(--accent3))"></div>
              <div class="bar-label">Sat</div>
            </div>
            <div class="bar-wrap">
              <div class="bar-val">₱25k</div>
              <div class="bar" style="height:83%;background:linear-gradient(180deg,var(--accent2),var(--accent));opacity:0.6"></div>
              <div class="bar-label">Today</div>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══ POS SYSTEM ═══ -->
      <div class="page" id="page-pos">
        <div class="page-header">
          <div>
            <h1>POS <span>System</span></h1>
            <div class="page-sub">Cashier Terminal — Shift Active</div>
          </div>
          <div class="flex-gap">
            <span id="shiftTimer" style="font-family:'Share Tech Mono',monospace;font-size:13px;color:var(--accent3);align-self:center">Shift: 00:00:00</span>
            <button class="btn btn-outline" onclick="openEndShiftModal()">🔒 End Shift</button>
            <button class="btn btn-primary" onclick="openModal('newTxnModal')">+ New Transaction</button>
          </div>
        </div>

        <div class="pos-cat-tabs">
          <button class="cat-tab active" onclick="filterCat('all',this)">All</button>
          <button class="cat-tab" onclick="filterCat('snacks',this)">🍿 Snacks</button>
          <button class="cat-tab" onclick="filterCat('socks',this)">🧦 Socks</button>
          <button class="cat-tab" onclick="filterCat('drinks',this)">🥤 Drinks</button>
          <button class="cat-tab" onclick="filterCat('packages',this)">🎉 Packages</button>
        </div>

        <div class="pos-layout">
          <div>
            <div class="pos-items-grid" id="posItemsGrid"></div>
          </div>

          <div class="pos-cart">
            <div class="cart-header">🛒 Current Cart <span style="font-size:13px;color:var(--text3);font-weight:400" id="cartCount">(0 items)</span></div>
            <div class="cart-items" id="cartItems">
              <div style="text-align:center;padding:40px;color:var(--text3);font-size:13px">
                Click items to add to cart...
              </div>
            </div>
            <div class="cart-footer">
              <div class="cart-summary">
                <div class="cart-row"><span>Subtotal</span><span class="number-display" id="cartSub">₱0.00</span></div>
                <div class="cart-row"><span>Discount</span><span class="number-display" id="cartDisc" style="color:var(--success)">-₱0.00</span></div>
                <div class="cart-row"><span>VAT (12%)</span><span class="number-display" id="cartVat">₱0.00</span></div>
                <div class="cart-row total"><span>TOTAL</span><span id="cartTotal">₱0.00</span></div>
              </div>
              <div class="form-group mb-4 tender-input">
                <label>Discount Type</label>
                <select id="discType" onchange="recalcCart()">
                  <option value="none">No Discount</option>
                  <option value="senior">Senior Citizen (20%)</option>
                  <option value="pwd">PWD (20%)</option>
                  <option value="minor">Minor (10%)</option>
                </select>
              </div>
              <div class="form-group mb-4 tender-input">
                <label>Cash Tendered</label>
                <input type="number" id="cashTendered" placeholder="0.00" oninput="calcChange()" />
              </div>
              <div class="cart-row" style="margin-bottom:12px">
                <span>Change</span>
                <span class="number-display" id="cartChange" style="color:var(--accent5)">₱0.00</span>
              </div>
              <div class="flex-gap">
                <button class="btn btn-danger" onclick="clearCart()" style="flex:1">🗑 Clear</button>
                <button class="btn btn-success" onclick="processPayment()" style="flex:2">✅ Process Payment</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══ INVENTORY ═══ -->
      <div class="page" id="page-inventory">
        <div class="page-header">
          <div>
            <h1>Inventory <span>Management</span></h1>
            <div class="page-sub">Snacks & Socks — Real-time Stock Monitoring</div>
          </div>
          <div class="flex-gap">
            <button class="btn btn-outline" onclick="openModal('addStockModal')">+ Add Stock</button>
            <button class="btn btn-primary" onclick="runCutoff()">📊 Run Cutoff</button>
          </div>
        </div>

        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-label">Total SKUs</div>
            <div class="stat-value">24</div>
          </div>
          <div class="stat-card red">
            <div class="stat-label">Low Stock</div>
            <div class="stat-value red" id="invLowCount">3</div>
          </div>
          <div class="stat-card green">
            <div class="stat-label">OK Stock</div>
            <div class="stat-value green">19</div>
          </div>
          <div class="stat-card orange">
            <div class="stat-label">Out of Stock</div>
            <div class="stat-value orange">2</div>
          </div>
        </div>

        <div class="card">
          <div class="card-title"><span class="ct-icon">🍿</span>Snacks Inventory</div>
          <div class="inv-grid" id="snacksInvGrid"></div>
        </div>

        <div class="card">
          <div class="card-title"><span class="ct-icon">🧦</span>Socks Inventory</div>
          <div class="inv-grid" id="socksInvGrid"></div>
        </div>

        <div class="card" id="cutoffPanel" style="display:none">
          <div class="card-title"><span class="ct-icon">📊</span>Cutoff Report</div>
          <div id="cutoffTable"></div>
        </div>
      </div>

      <!-- ═══ EXCEL STYLE ═══ -->
      <div class="page" id="page-excel">
        <div class="page-header">
          <div>
            <h1>Excel <span>Calculator</span></h1>
            <div class="page-sub">Automated Sales Computation & Cashier Variance</div>
          </div>
          <div class="flex-gap">
            <button class="btn btn-primary" onclick="computeExcel()">⚡ Auto-Compute</button>
            <button class="btn btn-outline" onclick="printExcel()">🖨 Print Report</button>
          </div>
        </div>

        <div class="excel-grid">
          <div class="excel-panel">
            <div class="card-title"><span class="ct-icon">💵</span>Sales Input</div>
            <div class="excel-table-wrap">
              <table class="excel-cell-table">
                <tr><th>Category</th><th>Qty Sold</th><th>Unit Price</th><th>Gross</th></tr>
                <tr>
                  <td>Snacks</td>
                  <td><input type="number" id="ex_snacks_qty" value="45" oninput="computeExcel()"/></td>
                  <td><input type="number" id="ex_snacks_price" value="35" oninput="computeExcel()"/></td>
                  <td class="computed" id="ex_snacks_gross">₱1,575.00</td>
                </tr>
                <tr>
                  <td>Socks</td>
                  <td><input type="number" id="ex_socks_qty" value="12" oninput="computeExcel()"/></td>
                  <td><input type="number" id="ex_socks_price" value="80" oninput="computeExcel()"/></td>
                  <td class="computed" id="ex_socks_gross">₱960.00</td>
                </tr>
                <tr>
                  <td>Drinks</td>
                  <td><input type="number" id="ex_drinks_qty" value="30" oninput="computeExcel()"/></td>
                  <td><input type="number" id="ex_drinks_price" value="25" oninput="computeExcel()"/></td>
                  <td class="computed" id="ex_drinks_gross">₱750.00</td>
                </tr>
                <tr>
                  <td>Packages</td>
                  <td><input type="number" id="ex_pkg_qty" value="3" oninput="computeExcel()"/></td>
                  <td><input type="number" id="ex_pkg_price" value="1200" oninput="computeExcel()"/></td>
                  <td class="computed" id="ex_pkg_gross">₱3,600.00</td>
                </tr>
                <tr>
                  <td colspan="3" style="text-align:right;font-weight:700;color:var(--accent5)">TOTAL GROSS SALES</td>
                  <td class="computed" id="ex_total_gross" style="color:var(--accent5)">₱6,885.00</td>
                </tr>
              </table>
            </div>
          </div>

          <div class="excel-panel">
            <div class="card-title"><span class="ct-icon">🧾</span>Cashier Variance</div>
            <div class="excel-table-wrap">
              <table class="excel-cell-table">
                <tr><th>Item</th><th>Amount</th></tr>
                <tr><td>Opening Fund</td><td><input type="number" id="ex_fund" value="5000" oninput="computeExcel()"/></td></tr>
                <tr><td>Total Gross Sales</td><td class="computed" id="ex_var_gross">₱6,885.00</td></tr>
                <tr><td>Discounts Given</td><td><input type="number" id="ex_discounts" value="0" oninput="computeExcel()"/></td></tr>
                <tr><td>Expected Cash</td><td class="computed" id="ex_expected">₱11,885.00</td></tr>
                <tr><td>Actual Cash</td><td><input type="number" id="ex_actual" value="11900" oninput="computeExcel()"/></td></tr>
                <tr>
                  <td style="font-weight:700">VARIANCE</td>
                  <td class="computed" id="ex_variance" style="font-size:16px">+₱15.00</td>
                </tr>
                <tr>
                  <td>Status</td>
                  <td id="ex_status"><span class="badge badge-green">OVER</span></td>
                </tr>
              </table>
            </div>
          </div>

          <div class="excel-panel full">
            <div class="card-title"><span class="ct-icon">📋</span>Discount Breakdown</div>
            <div class="excel-table-wrap">
              <table class="excel-cell-table">
                <tr><th>Type</th><th>Count</th><th>Rate</th><th>Amount</th><th>Net Sales</th></tr>
                <tr>
                  <td>Regular</td>
                  <td><input type="number" id="ex_reg" value="50" oninput="computeExcel()"/></td>
                  <td>0%</td>
                  <td class="computed" id="ex_reg_amt">₱0.00</td>
                  <td class="computed" id="ex_reg_net">₱6,885.00</td>
                </tr>
                <tr>
                  <td>Senior Citizen</td>
                  <td><input type="number" id="ex_senior" value="5" oninput="computeExcel()"/></td>
                  <td>20%</td>
                  <td class="computed negative" id="ex_senior_amt">-₱0.00</td>
                  <td class="computed" id="ex_senior_net">₱0.00</td>
                </tr>
                <tr>
                  <td>PWD</td>
                  <td><input type="number" id="ex_pwd" value="2" oninput="computeExcel()"/></td>
                  <td>20%</td>
                  <td class="computed negative" id="ex_pwd_amt">-₱0.00</td>
                  <td class="computed" id="ex_pwd_net">₱0.00</td>
                </tr>
                <tr>
                  <td>Minor</td>
                  <td><input type="number" id="ex_minor" value="8" oninput="computeExcel()"/></td>
                  <td>10%</td>
                  <td class="computed negative" id="ex_minor_amt">-₱0.00</td>
                  <td class="computed" id="ex_minor_net">₱0.00</td>
                </tr>
                <tr>
                  <td colspan="3" style="text-align:right;font-weight:700;color:var(--accent5)">NET SALES AFTER DISCOUNT</td>
                  <td colspan="2" class="computed" id="ex_net_sales" style="color:var(--accent5);font-size:15px">₱6,885.00</td>
                </tr>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══ RESERVATION ═══ -->
      <div class="page" id="page-reservation">
        <div class="page-header">
          <div>
            <h1>Party <span>Reservations</span></h1>
            <div class="page-sub">Scan QR or Book Manually</div>
          </div>
          <div class="flex-gap">
            <button class="btn btn-primary" onclick="openModal('newResModal')">+ New Reservation</button>
          </div>
        </div>

        <div class="scan-area" id="resScanArea" onclick="simulateScan('res')">
          <div class="scan-line"></div>
          <div class="scan-icon">📷</div>
          <div class="scan-text">Click to Scan Customer QR Code</div>
          <div class="scan-sub">Scan to auto-fill reservation details</div>
        </div>

        <div class="card">
          <div class="card-title"><span class="ct-icon">📅</span>Active Reservations</div>
          <div class="reservation-cards" id="reservationCards"></div>
        </div>

        <div class="card">
          <div class="card-title"><span class="ct-icon">📋</span>Reservation Log</div>
          <div style="overflow-x:auto">
            <table class="data-table" id="resTable">
              <thead>
                <tr>
                  <th>ID</th><th>Customer</th><th>Date</th><th>Packs</th><th>Food</th><th>Amount</th><th>Status</th><th>Action</th>
                </tr>
              </thead>
              <tbody id="resTableBody"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══ WAIVER ═══ -->
      <div class="page" id="page-waiver">
        <div class="page-header">
          <div>
            <h1>Customer <span>Waiver</span></h1>
            <div class="page-sub">Submit personal details — goes directly to database</div>
          </div>
        </div>

        <div class="waiver-form">
          <div class="waiver-scan-header">
            <div class="waiver-scan-icon">📷</div>
            <div class="waiver-scan-text">
              <h3>Scan or Fill Manually</h3>
              <p>Customer can scan QR to auto-fill details, or type manually below</p>
            </div>
            <button class="btn btn-outline" onclick="simulateScan('waiver')" style="margin-left:auto">📷 Scan QR</button>
          </div>

          <div class="card">
            <div class="card-title"><span class="ct-icon">📋</span>Waiver Form</div>
            <div class="form-row">
              <div class="form-group">
                <label>Full Name</label>
                <input type="text" id="w_name" placeholder="Juan Dela Cruz" />
              </div>
              <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" id="w_dob" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Contact Number</label>
                <input type="text" id="w_contact" placeholder="09XX-XXX-XXXX" />
              </div>
              <div class="form-group">
                <label>Customer Type</label>
                <select id="w_type">
                  <option value="regular">Regular</option>
                  <option value="senior">Senior Citizen</option>
                  <option value="pwd">PWD</option>
                  <option value="minor">Minor</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Address</label>
                <input type="text" id="w_address" placeholder="Lipa City, Batangas" />
              </div>
              <div class="form-group">
                <label>Emergency Contact</label>
                <input type="text" id="w_emergency" placeholder="Name — 09XX-XXX-XXXX" />
              </div>
            </div>
            <div class="form-group" style="margin-bottom:16px">
              <label>Medical Conditions / Notes</label>
              <textarea id="w_notes" rows="3" placeholder="None / Specify if any..."></textarea>
            </div>
            <div style="background:var(--bg2);border-radius:8px;padding:14px;margin-bottom:16px;font-size:12px;color:var(--text2);line-height:1.7">
              ✅ By submitting this form, I agree to the terms and conditions of REKS Amusement Com Inc. I understand that my data will be stored securely in the company database for safety and record purposes only.
            </div>
            <button class="btn btn-primary" onclick="submitWaiver()">✅ Submit Waiver</button>
          </div>
        </div>

        <div class="card" style="margin-top:20px">
          <div class="card-title"><span class="ct-icon">📂</span>Today's Submitted Waivers</div>
          <div class="waiver-submitted-list" id="waiverList"></div>
        </div>
      </div>

      <!-- ═══ CHATBOT ═══ -->
      <div class="page" id="page-chatbot">
        <div class="page-header">
          <div>
            <h1>Customer <span>Chat & Promos</span></h1>
            <div class="page-sub">Live Chat — Marketing / TL Channel</div>
          </div>
        </div>

        <div class="chatbot-layout">
          <div class="chat-window">
            <div class="chat-header">
              <div class="chat-avatar">🎉</div>
              <div>
                <div style="font-weight:700;font-size:14px">REKS Amusement Assistant</div>
                <div style="font-size:11px;color:var(--text3)">Marketing / Reservations Support</div>
              </div>
              <div class="chat-online"></div>
            </div>
            <div class="chat-messages" id="chatMessages">
              <div class="msg bot">
                👋 Hi! Welcome to <strong>REKS Amusement Com Inc - Lipa!</strong><br><br>
                I can help you with reservations, packages, promos, and more. How can I assist you today?
              </div>
            </div>
            <div class="chat-input-row">
              <input class="chat-input" id="chatInput" placeholder="Type your message..." onkeydown="if(event.key==='Enter') sendChat()" />
              <button class="btn btn-primary" onclick="sendChat()">Send ➤</button>
            </div>
          </div>

          <div class="chat-info-panel">
            <div class="card">
              <div class="card-title"><span class="ct-icon">⚡</span>Quick Replies</div>
              <button class="chat-quick-btn" onclick="quickChat('What packages do you offer?')">🎉 View Packages</button>
              <button class="chat-quick-btn" onclick="quickChat('How much is the entrance fee?')">💰 Entrance Fee</button>
              <button class="chat-quick-btn" onclick="quickChat('How do I make a reservation?')">📅 Make Reservation</button>
              <button class="chat-quick-btn" onclick="quickChat('Do you have promos?')">🏷 Current Promos</button>
              <button class="chat-quick-btn" onclick="quickChat('What are the age requirements?')">👶 Age Policy</button>
              <button class="chat-quick-btn" onclick="quickChat('What is your operating hours?')">🕐 Operating Hours</button>
            </div>
            <div class="card">
              <div class="card-title"><span class="ct-icon">📢</span>Current Promos</div>
              <div style="font-size:12px;color:var(--text2);line-height:2">
                🎉 <strong>Birthday Promo</strong> — Free 1 pack<br>
                👴 <strong>Senior/PWD</strong> — 20% off<br>
                👶 <strong>Minor (below 12)</strong> — 10% off<br>
                🎊 <strong>Group of 10+</strong> — 15% group discount<br>
                📅 <strong>Weekday Special</strong> — ₱50 off per head
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══ ATTENDANCE ═══ -->
      <div class="page" id="page-attendance">
        <div class="page-header">
          <div>
            <h1>Attendance & <span>Payroll</span></h1>
            <div class="page-sub">BIO-connected — Automated Deduction & Payslip</div>
          </div>
          <div class="flex-gap">
            <button class="btn btn-outline" onclick="openModal('addAttModal')">+ Manual Entry</button>
            <button class="btn btn-primary" onclick="generatePayslip()">📄 Generate Payslip</button>
          </div>
        </div>

        <div class="attendance-summary">
          <div class="att-card">
            <div class="att-icon">✅</div>
            <div class="att-num" style="color:var(--accent3)">16</div>
            <div class="att-label">On Time</div>
          </div>
          <div class="att-card">
            <div class="att-icon">⚠️</div>
            <div class="att-num" style="color:var(--warning)">3</div>
            <div class="att-label">Late (with deduct)</div>
          </div>
          <div class="att-card">
            <div class="att-icon">🟡</div>
            <div class="att-num" style="color:var(--accent)">1</div>
            <div class="att-label">Late (no deduct)</div>
          </div>
          <div class="att-card">
            <div class="att-icon">❌</div>
            <div class="att-num" style="color:var(--danger)">2</div>
            <div class="att-label">Absent</div>
          </div>
        </div>

        <div class="card">
          <div class="card-title"><span class="ct-icon">📜</span>Late Deduction Rules</div>
          <div class="flex-gap" style="flex-wrap:wrap;margin-bottom:8px">
            <div class="rule-badge" style="background:rgba(0,204,102,0.1);color:var(--success);border:1px solid rgba(0,204,102,0.3)">
              ✅ 1–15 mins late → NO DEDUCTION
            </div>
            <div class="rule-badge" style="background:rgba(255,170,0,0.1);color:var(--warning);border:1px solid rgba(255,170,0,0.3)">
              ⚠ 16+ mins late → WITH DEDUCTION (₱25/16-30min, ₱50/31-60min, ₱100/1hr+)
            </div>
          </div>
          <div style="font-size:11px;color:var(--text3)">Deductions are automated from BIO biometric data feed</div>
        </div>

        <div class="card">
          <div class="card-title"><span class="ct-icon">⏱️</span>Today's Attendance Log</div>
          <div style="overflow-x:auto">
            <table class="data-table">
              <thead>
                <tr><th>Employee</th><th>Time In</th><th>Scheduled</th><th>Late (mins)</th><th>Deduction</th><th>Status</th></tr>
              </thead>
              <tbody id="attTableBody"></tbody>
            </table>
          </div>
        </div>

        <div class="card" id="payslipSection" style="display:none">
          <div class="card-title"><span class="ct-icon">💵</span>Generated Payslip</div>
          <div class="form-row" style="margin-bottom:16px">
            <div class="form-group">
              <label>Select Employee</label>
              <select id="payslipEmp">
                <option>Ana Reyes</option>
                <option>Lito Cruz</option>
                <option>Maria Santos</option>
                <option>Jose Bautista</option>
              </select>
            </div>
            <div class="form-group">
              <label>Pay Period</label>
              <select id="payslipPeriod">
                <option>April 16-30, 2025</option>
                <option>May 1-15, 2025</option>
              </select>
            </div>
            <div style="align-self:flex-end">
              <button class="btn btn-primary" onclick="renderPayslip()">Generate</button>
            </div>
          </div>
          <div id="payslipBox"></div>
        </div>
      </div>

      <!-- ═══ ANALYTICS ═══ -->
      <div class="page" id="page-analytics">
        <div class="page-header">
          <div>
            <h1>Business <span>Analytics</span></h1>
            <div class="page-sub">Full Insights — Customers, Sales, HR, Quotas</div>
          </div>
          <div class="flex-gap">
            <button class="btn btn-outline" onclick="exportReport()">📥 Export Report</button>
          </div>
        </div>

        <!-- Customer Type Stats -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-label">Regular Customers</div>
            <div class="stat-value">248</div>
            <div class="stat-sub">This month</div>
          </div>
          <div class="stat-card green">
            <div class="stat-label">Minor Customers</div>
            <div class="stat-value green">89</div>
            <div class="stat-sub">10% discount applied</div>
          </div>
          <div class="stat-card gold">
            <div class="stat-label">Senior Citizens</div>
            <div class="stat-value gold">34</div>
            <div class="stat-sub">20% discount applied</div>
          </div>
          <div class="stat-card orange">
            <div class="stat-label">PWD Customers</div>
            <div class="stat-value orange">12</div>
            <div class="stat-sub">20% discount applied</div>
          </div>
        </div>

        <div class="analytics-grid">
          <!-- Monthly Reservations -->
          <div class="card">
            <div class="card-title"><span class="ct-icon">🎉</span>Party Reservations per Month</div>
            <div class="bar-chart">
              <div class="bar-wrap"><div class="bar-val">8</div><div class="bar" style="height:40%;background:var(--accent)"></div><div class="bar-label">Jan</div></div>
              <div class="bar-wrap"><div class="bar-val">12</div><div class="bar" style="height:60%;background:var(--accent)"></div><div class="bar-label">Feb</div></div>
              <div class="bar-wrap"><div class="bar-val">18</div><div class="bar" style="height:90%;background:linear-gradient(180deg,var(--accent4),var(--accent5))"></div><div class="bar-label">Mar</div></div>
              <div class="bar-wrap"><div class="bar-val">15</div><div class="bar" style="height:75%;background:var(--accent)"></div><div class="bar-label">Apr</div></div>
              <div class="bar-wrap"><div class="bar-val">10</div><div class="bar" style="height:50%;background:var(--accent)"></div><div class="bar-label">May</div></div>
              <div class="bar-wrap"><div class="bar-val">20</div><div class="bar" style="height:100%;background:linear-gradient(180deg,var(--accent4),var(--accent5))"></div><div class="bar-label">Jun</div></div>
            </div>
            <div style="font-size:11px;color:var(--accent4);margin-top:8px">🏆 Peak: March & June — 18-20 reservations</div>
          </div>

          <!-- Customer Type Pie -->
          <div class="card">
            <div class="card-title"><span class="ct-icon">👥</span>Customer Type Distribution</div>
            <div class="pie-visual">
              <div class="pie-circle">
                <svg viewBox="0 0 36 36" style="width:100%;height:100%;transform:rotate(-90deg)">
                  <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--bg2)" stroke-width="3.8"/>
                  <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--accent)" stroke-width="3.8"
                    stroke-dasharray="63 37" stroke-dashoffset="0"/>
                  <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--accent3)" stroke-width="3.8"
                    stroke-dasharray="23 77" stroke-dashoffset="-63"/>
                  <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--accent5)" stroke-width="3.8"
                    stroke-dasharray="9 91" stroke-dashoffset="-86"/>
                  <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--accent4)" stroke-width="3.8"
                    stroke-dasharray="5 95" stroke-dashoffset="-95"/>
                </svg>
              </div>
              <div class="pie-legend">
                <div class="pie-legend-item"><div class="pie-dot" style="background:var(--accent)"></div>Regular — 63%</div>
                <div class="pie-legend-item"><div class="pie-dot" style="background:var(--accent3)"></div>Minor — 23%</div>
                <div class="pie-legend-item"><div class="pie-dot" style="background:var(--accent5)"></div>Senior — 9%</div>
                <div class="pie-legend-item"><div class="pie-dot" style="background:var(--accent4)"></div>PWD — 5%</div>
              </div>
            </div>
          </div>

          <!-- Punctuality Report -->
          <div class="card">
            <div class="card-title"><span class="ct-icon">⏱️</span>Punctuality Monitoring</div>
            <table class="data-table">
              <thead><tr><th>Employee</th><th>On-Time</th><th>Late</th><th>Status</th></tr></thead>
              <tbody>
                <tr><td>Ana Reyes</td><td>28</td><td>0</td><td><span class="badge badge-green">🏆 Most Punctual</span></td></tr>
                <tr><td>Maria Santos</td><td>25</td><td>3</td><td><span class="badge badge-blue">On-Time</span></td></tr>
                <tr><td>Jose Bautista</td><td>20</td><td>8</td><td><span class="badge badge-yellow">⚠ Often Late</span></td></tr>
                <tr><td>Lito Cruz</td><td>15</td><td>13</td><td><span class="badge badge-red">🔴 Most Late</span></td></tr>
              </tbody>
            </table>
          </div>

          <!-- Sales Quota -->
          <div class="card">
            <div class="card-title"><span class="ct-icon">🎯</span>Daily Sales Quota Tracker</div>
            <div style="margin-bottom:20px">
              <div class="quota-label"><span>Today</span><span>₱24,850 / ₱30,000</span></div>
              <div class="quota-track">
                <div class="quota-fill" style="width:83%">83%</div>
                <div class="quota-target" style="left:100%"></div>
              </div>
              <div style="font-size:11px;color:var(--warning);margin-top:6px">⚠ ₱5,150 more needed to hit today's quota</div>
            </div>
            <div style="margin-bottom:20px">
              <div class="quota-label"><span>This Week</span><span>₱166,850 / ₱210,000</span></div>
              <div class="quota-track">
                <div class="quota-fill" style="width:79%">79%</div>
              </div>
            </div>
            <div>
              <div class="quota-label"><span>This Month</span><span>₱580,000 / ₱900,000</span></div>
              <div class="quota-track">
                <div class="quota-fill" style="width:64%;background:linear-gradient(90deg,var(--accent2),var(--warning))">64%</div>
              </div>
            </div>
          </div>

          <!-- Top Sellers -->
          <div class="card" style="grid-column:1/-1">
            <div class="card-title"><span class="ct-icon">📊</span>Top Selling Items</div>
            <div class="bar-chart" style="height:100px">
              <div class="bar-wrap"><div class="bar-val">142</div><div class="bar" style="height:100%;background:linear-gradient(180deg,var(--accent2),var(--accent))"></div><div class="bar-label">Piattos</div></div>
              <div class="bar-wrap"><div class="bar-val">128</div><div class="bar" style="height:90%;background:linear-gradient(180deg,var(--accent2),var(--accent))"></div><div class="bar-label">Oishi</div></div>
              <div class="bar-wrap"><div class="bar-val">98</div><div class="bar" style="height:69%;background:linear-gradient(180deg,var(--accent2),var(--accent))"></div><div class="bar-label">Nova</div></div>
              <div class="bar-wrap"><div class="bar-val">88</div><div class="bar" style="height:62%;background:linear-gradient(180deg,var(--accent4),var(--accent5))"></div><div class="bar-label">Socks-M</div></div>
              <div class="bar-wrap"><div class="bar-val">72</div><div class="bar" style="height:51%;background:linear-gradient(180deg,var(--accent4),var(--accent5))"></div><div class="bar-label">Socks-L</div></div>
              <div class="bar-wrap"><div class="bar-val">65</div><div class="bar" style="height:46%;background:linear-gradient(180deg,#009944,var(--accent3))"></div><div class="bar-label">Water</div></div>
              <div class="bar-wrap"><div class="bar-val">55</div><div class="bar" style="height:39%;background:linear-gradient(180deg,#009944,var(--accent3))"></div><div class="bar-label">Juice</div></div>
            </div>
          </div>
        </div>
      </div>

    </div><!-- end content-area -->
  </div><!-- end app-body -->
</div><!-- end mainApp -->

<!-- ═══════════════ MODALS ═══════════════ -->
<!-- End Shift Modal -->
<div class="modal-overlay" id="endShiftModal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal('endShiftModal')">✕</button>
    <div class="modal-title">🔒 End of Shift Report</div>
    <div class="form-group" style="margin-bottom:12px">
      <label>Actual Cash Count</label>
      <input type="number" id="shiftActualCash" placeholder="0.00" />
    </div>
    <div id="shiftSummary" style="font-size:13px;color:var(--text2);line-height:2;background:var(--bg2);border-radius:8px;padding:14px;margin-bottom:16px">
      Opening Fund: <strong>₱5,000.00</strong><br>
      Total Sales: <strong class="text-accent">₱24,850.00</strong><br>
      Expected Cash: <strong>₱29,850.00</strong>
    </div>
    <button class="btn btn-primary" style="width:100%" onclick="finalizeShift()">✅ Submit Shift Report</button>
  </div>
</div>

<!-- New Reservation Modal -->
<div class="modal-overlay" id="newResModal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal('newResModal')">✕</button>
    <div class="modal-title">📅 New Reservation</div>
    <div class="form-row">
      <div class="form-group">
        <label>Customer Name</label>
        <input type="text" id="res_name" placeholder="Full Name" />
      </div>
      <div class="form-group">
        <label>Date of Party</label>
        <input type="date" id="res_date" />
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Number of Packs</label>
        <input type="number" id="res_packs" value="1" min="1" />
      </div>
      <div class="form-group">
        <label>Package Type</label>
        <select id="res_pkg">
          <option>Basic Pack (₱1,200)</option>
          <option>Standard Pack (₱2,000)</option>
          <option>Premium Pack (₱3,500)</option>
          <option>Deluxe Pack (₱5,000)</option>
        </select>
      </div>
    </div>
    <div class="form-group" style="margin-bottom:12px">
      <label>Food Selection</label>
      <select id="res_food" multiple style="height:100px">
        <option>Palabok</option>
        <option>Spaghetti</option>
        <option>Pancit Canton</option>
        <option>Lumpia</option>
        <option>Rice + Ulam</option>
        <option>Sandwich Set</option>
      </select>
    </div>
    <div class="form-group" style="margin-bottom:16px">
      <label>Contact Number</label>
      <input type="text" id="res_contact" placeholder="09XX-XXX-XXXX" />
    </div>
    <button class="btn btn-primary" style="width:100%" onclick="submitReservation()">📅 Book Reservation</button>
  </div>
</div>

<!-- Add Stock Modal -->
<div class="modal-overlay" id="addStockModal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal('addStockModal')">✕</button>
    <div class="modal-title">📦 Add / Update Stock</div>
    <div class="form-row">
      <div class="form-group">
        <label>Category</label>
        <select id="stock_cat">
          <option>Snacks</option><option>Socks</option>
        </select>
      </div>
      <div class="form-group">
        <label>Item</label>
        <input type="text" id="stock_item" placeholder="Item name" />
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Qty to Add</label>
        <input type="number" id="stock_qty" value="0" />
      </div>
      <div class="form-group">
        <label>Remarks</label>
        <input type="text" id="stock_remarks" placeholder="Delivery / Adjustment" />
      </div>
    </div>
    <button class="btn btn-primary" style="width:100%;margin-top:8px" onclick="addStock()">✅ Update Stock</button>
  </div>
</div>

<!-- Add Attendance Modal -->
<div class="modal-overlay" id="addAttModal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal('addAttModal')">✕</button>
    <div class="modal-title">⏱️ Manual Attendance Entry</div>
    <div class="form-row">
      <div class="form-group">
        <label>Employee</label>
        <select id="att_emp">
          <option>Ana Reyes</option><option>Lito Cruz</option>
          <option>Maria Santos</option><option>Jose Bautista</option>
          <option>Carla Domingo</option>
        </select>
      </div>
      <div class="form-group">
        <label>Time In</label>
        <input type="time" id="att_timein" value="08:30" />
      </div>
    </div>
    <div class="form-group" style="margin-bottom:16px">
      <label>Scheduled Time</label>
      <input type="time" id="att_sched" value="08:00" />
    </div>
    <div id="att_calc" style="background:var(--bg2);border-radius:8px;padding:14px;font-size:13px;color:var(--text2);margin-bottom:16px">
      Fill in times to calculate...
    </div>
    <button class="btn btn-primary" style="width:100%" onclick="submitAttendance()">✅ Submit</button>
  </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- ═══════════════ SCRIPTS ═══════════════ -->
<script>
// ═══════════════════════════════════════════════
// GLOBAL STATE
// ═══════════════════════════════════════════════
let currentUser = null;
let currentRole = null;
let cart = [];
let shiftStartTime = null;
let shiftInterval = null;

const POS_ITEMS = [
  { id:1, name:'Piattos Cheese', cat:'snacks', icon:'🍟', price:35, stock:8 },
  { id:2, name:'Oishi Prawn', cat:'snacks', icon:'🦐', price:30, stock:25 },
  { id:3, name:'Nova', cat:'snacks', icon:'🌽', price:25, stock:40 },
  { id:4, name:'Chippy BBQ', cat:'snacks', icon:'🍪', price:20, stock:30 },
  { id:5, name:'Chiz Curls', cat:'snacks', icon:'🧀', price:25, stock:22 },
  { id:6, name:'Boy Bawang', cat:'snacks', icon:'🌰', price:15, stock:50 },
  { id:7, name:'Socks (S)', cat:'socks', icon:'🧦', price:80, stock:5 },
  { id:8, name:'Socks (M)', cat:'socks', icon:'🧦', price:80, stock:18 },
  { id:9, name:'Socks (L)', cat:'socks', icon:'🧦', price:80, stock:14 },
  { id:10, name:'Socks (XL)', cat:'socks', icon:'🧦', price:80, stock:8 },
  { id:11, name:'Water (500ml)', cat:'drinks', icon:'💧', price:25, stock:60 },
  { id:12, name:'Juice Drink', cat:'drinks', icon:'🥤', price:30, stock:35 },
  { id:13, name:'Softdrinks', cat:'drinks', icon:'🥃', price:35, stock:28 },
  { id:14, name:'Basic Pack', cat:'packages', icon:'🎁', price:1200, stock:99 },
  { id:15, name:'Standard Pack', cat:'packages', icon:'🎉', price:2000, stock:99 },
  { id:16, name:'Premium Pack', cat:'packages', icon:'🎊', price:3500, stock:99 },
];

const SNACKS_INV = [
  { name:'Piattos Cheese', icon:'🍟', qty:8, max:50 },
  { name:'Oishi Prawn', icon:'🦐', qty:25, max:50 },
  { name:'Nova', icon:'🌽', qty:40, max:60 },
  { name:'Chippy BBQ', icon:'🍪', qty:30, max:50 },
  { name:'Chiz Curls', icon:'🧀', qty:22, max:50 },
  { name:'Boy Bawang', icon:'🌰', qty:50, max:80 },
  { name:'Piatos BBQ', icon:'🍟', qty:0, max:50 },
];

const SOCKS_INV = [
  { name:'Kids Small', icon:'🧦', qty:5, max:30 },
  { name:'Kids Medium', icon:'🧦', qty:12, max:30 },
  { name:'Adult Small', icon:'🧦', qty:18, max:30 },
  { name:'Adult Medium', icon:'🧦', qty:14, max:30 },
  { name:'Adult Large', icon:'🧦', qty:8, max:30 },
  { name:'Adult XL', icon:'🧦', qty:20, max:30 },
];

let reservations = [
  { id:'RES-001', customer:'Santos Family', date:'2025-05-15', packs:5, food:'Palabok, Lumpia', amount:6000, status:'Confirmed' },
  { id:'RES-002', customer:'Dela Cruz Party', date:'2025-05-20', packs:3, food:'Spaghetti', amount:3600, status:'Pending' },
  { id:'RES-003', customer:'Bautista Kids', date:'2025-05-25', packs:2, food:'Palabok', amount:2400, status:'Confirmed' },
];

let waivers = [
  { name:'Maria Dela Cruz', type:'senior', dob:'1950-03-12', contact:'0912-345-6789' },
  { name:'Juan Reyes Jr.', type:'minor', dob:'2015-07-22', contact:'0917-111-2222' },
];

let attendanceLogs = [
  { emp:'Ana Reyes', timeIn:'08:00', sched:'08:00', lateMin:0, deduct:0, status:'On Time' },
  { emp:'Lito Cruz', timeIn:'08:52', sched:'08:00', lateMin:52, deduct:100, status:'Late' },
  { emp:'Maria Santos', timeIn:'08:10', sched:'08:00', lateMin:10, deduct:0, status:'On Time (grace)' },
  { emp:'Jose Bautista', timeIn:'08:22', sched:'08:00', lateMin:22, deduct:25, status:'Late' },
  { emp:'Carla Domingo', timeIn:'08:00', sched:'08:00', lateMin:0, deduct:0, status:'On Time' },
];

// ═══════════════════════════════════════════════
// AUTH
// ═══════════════════════════════════════════════
function doLogin() {
  const user = document.getElementById('loginUser').value.trim();
  const pass = document.getElementById('loginPass').value.trim();
  const role = document.getElementById('loginRole').value;

  if (!user || !pass || !role) {
    showToast('Please fill in all fields.', 'error');
    return;
  }

  currentUser = user;
  currentRole = role;

  document.getElementById('loginScreen').style.display = 'none';
  document.getElementById('mainApp').classList.add('active');

  document.getElementById('userAvatar').textContent = user[0].toUpperCase();
  document.getElementById('userName').textContent = user;
  document.getElementById('userRole').textContent = role.toUpperCase();

  applyRoleAccess(role);
  startClock();
  initDashDate();
  renderPOSItems('all');
  renderInventory();
  renderReservations();
  renderWaivers();
  renderAttendance();
  computeExcel();
  startShiftTimer();

  showToast(`Welcome back, ${user}! Logged in as ${role}.`, 'success');
}

function applyRoleAccess(role) {
  const allNavs = ['pos','inventory','excel','reservation','waiver','chatbot','attendance','analytics'];

  const access = {
    cashier: ['pos','inventory','excel'],
    manager: ['pos','inventory','excel','reservation','chatbot','attendance','analytics'],
    customer: ['reservation','waiver','chatbot'],
    admin: allNavs,
  };

  const allowed = access[role] || [];

  allNavs.forEach(nav => {
    const el = document.getElementById('nav-' + nav);
    if (el) {
      if (allowed.includes(nav)) {
        el.style.display = '';
      } else {
        el.style.display = 'none';
      }
    }
  });

  // Show dashboard always
  const firstAllowed = allowed[0] || 'dashboard';
  showPage('dashboard');
}

function doLogout() {
  currentUser = null;
  currentRole = null;
  cart = [];
  clearInterval(shiftInterval);
  document.getElementById('mainApp').classList.remove('active');
  document.getElementById('loginScreen').style.display = 'flex';
  document.getElementById('loginUser').value = '';
  document.getElementById('loginPass').value = '';
  document.getElementById('loginRole').value = '';
}

// ═══════════════════════════════════════════════
// NAVIGATION
// ═══════════════════════════════════════════════
function showPage(id) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  const pg = document.getElementById('page-' + id);
  if (pg) pg.classList.add('active');
  const nav = document.getElementById('nav-' + id);
  if (nav) nav.classList.add('active');
}

// ═══════════════════════════════════════════════
// CLOCK
// ═══════════════════════════════════════════════
function startClock() {
  setInterval(() => {
    const now = new Date();
    document.getElementById('topClock').textContent =
      now.toLocaleTimeString('en-PH', { hour12: false });
  }, 1000);
}

function initDashDate() {
  const now = new Date();
  document.getElementById('dashDate').textContent =
    now.toLocaleDateString('en-PH', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
}

function startShiftTimer() {
  shiftStartTime = Date.now();
  shiftInterval = setInterval(() => {
    const elapsed = Math.floor((Date.now() - shiftStartTime) / 1000);
    const h = String(Math.floor(elapsed / 3600)).padStart(2,'0');
    const m = String(Math.floor((elapsed % 3600) / 60)).padStart(2,'0');
    const s = String(elapsed % 60).padStart(2,'0');
    const el = document.getElementById('shiftTimer');
    if (el) el.textContent = `Shift: ${h}:${m}:${s}`;
  }, 1000);
}

// ═══════════════════════════════════════════════
// POS
// ═══════════════════════════════════════════════
function renderPOSItems(cat) {
  const grid = document.getElementById('posItemsGrid');
  const items = cat === 'all' ? POS_ITEMS : POS_ITEMS.filter(i => i.cat === cat);
  grid.innerHTML = items.map(item => `
    <div class="pos-item" onclick="addToCart(${item.id})">
      <div class="item-icon">${item.icon}</div>
      <div class="item-name">${item.name}</div>
      <div class="item-price">₱${item.price.toLocaleString()}</div>
      <div class="item-stock ${item.stock < 10 ? 'text-red' : 'text-muted'}">Stock: ${item.stock}</div>
    </div>
  `).join('');
}

function filterCat(cat, btn) {
  document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  renderPOSItems(cat);
}

function addToCart(itemId) {
  const item = POS_ITEMS.find(i => i.id === itemId);
  if (!item) return;
  if (item.stock <= 0) { showToast('Item out of stock!', 'error'); return; }

  const existing = cart.find(c => c.id === itemId);
  if (existing) {
    existing.qty++;
  } else {
    cart.push({ ...item, qty: 1 });
  }
  renderCart();
  showToast(`Added: ${item.name}`, 'success');
}

function renderCart() {
  const el = document.getElementById('cartItems');
  if (cart.length === 0) {
    el.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text3);font-size:13px">Click items to add to cart...</div>';
    document.getElementById('cartCount').textContent = '(0 items)';
    updateCartTotals();
    return;
  }

  el.innerHTML = cart.map(item => `
    <div class="cart-item">
      <span style="font-size:20px">${item.icon}</span>
      <div class="ci-name">${item.name}<div style="font-size:11px;color:var(--text3)">@₱${item.price}</div></div>
      <div class="ci-qty">
        <button onclick="changeQty(${item.id},-1)">−</button>
        <span class="qty">${item.qty}</span>
        <button onclick="changeQty(${item.id},1)">+</button>
      </div>
      <div class="ci-price">₱${(item.price * item.qty).toLocaleString()}</div>
      <button class="ci-del" onclick="removeFromCart(${item.id})">✕</button>
    </div>
  `).join('');

  document.getElementById('cartCount').textContent = `(${cart.reduce((s,c)=>s+c.qty,0)} items)`;
  updateCartTotals();
}

function changeQty(id, delta) {
  const item = cart.find(c => c.id === id);
  if (!item) return;
  item.qty += delta;
  if (item.qty <= 0) cart = cart.filter(c => c.id !== id);
  renderCart();
}

function removeFromCart(id) {
  cart = cart.filter(c => c.id !== id);
  renderCart();
}

function clearCart() {
  cart = [];
  renderCart();
  document.getElementById('cashTendered').value = '';
  document.getElementById('cartChange').textContent = '₱0.00';
}

function getDiscountRate() {
  const type = document.getElementById('discType').value;
  if (type === 'senior' || type === 'pwd') return 0.20;
  if (type === 'minor') return 0.10;
  return 0;
}

function recalcCart() { updateCartTotals(); }

function updateCartTotals() {
  const sub = cart.reduce((s,c) => s + (c.price * c.qty), 0);
  const disc = sub * getDiscountRate();
  const vat = (sub - disc) * 0.12;
  const total = sub - disc + vat;

  document.getElementById('cartSub').textContent = `₱${sub.toFixed(2)}`;
  document.getElementById('cartDisc').textContent = `-₱${disc.toFixed(2)}`;
  document.getElementById('cartVat').textContent = `₱${vat.toFixed(2)}`;
  document.getElementById('cartTotal').textContent = `₱${total.toFixed(2)}`;
  calcChange();
}

function calcChange() {
  const total = parseFloat(document.getElementById('cartTotal').textContent.replace('₱','').replace(',','')) || 0;
  const tendered = parseFloat(document.getElementById('cashTendered').value) || 0;
  const change = tendered - total;
  const el = document.getElementById('cartChange');
  el.textContent = `₱${Math.max(0, change).toFixed(2)}`;
  el.style.color = change >= 0 ? 'var(--accent5)' : 'var(--danger)';
}

function processPayment() {
  if (cart.length === 0) { showToast('Cart is empty!', 'error'); return; }
  const total = parseFloat(document.getElementById('cartTotal').textContent.replace('₱','').replace(',','')) || 0;
  const tendered = parseFloat(document.getElementById('cashTendered').value) || 0;
  if (tendered < total) { showToast('Insufficient cash tendered!', 'error'); return; }

  showToast(`✅ Payment successful! Change: ₱${(tendered - total).toFixed(2)}`, 'success');
  clearCart();
  document.getElementById('discType').value = 'none';
}

function openEndShiftModal() { openModal('endShiftModal'); }
function finalizeShift() {
  showToast('Shift report submitted successfully!', 'success');
  closeModal('endShiftModal');
}

// ═══════════════════════════════════════════════
// INVENTORY
// ═══════════════════════════════════════════════
function renderInventory() {
  renderInvGrid('snacksInvGrid', SNACKS_INV);
  renderInvGrid('socksInvGrid', SOCKS_INV);
}

function renderInvGrid(targetId, items) {
  const el = document.getElementById(targetId);
  el.innerHTML = items.map(item => {
    const pct = Math.round((item.qty / item.max) * 100);
    const lvl = pct > 50 ? 'high' : pct > 20 ? 'mid' : 'low';
    const statusText = item.qty === 0 ? 'OUT OF STOCK' : pct <= 20 ? 'LOW STOCK' : pct <= 50 ? 'MODERATE' : 'GOOD';
    return `
      <div class="inv-card ${pct <= 20 ? 'low' : 'ok'}">
        <div>${item.icon ? `<span class="inv-item-icon">${item.icon}</span>` : ''}
          <div class="inv-item-name">${item.name}</div>
        </div>
        <div class="inv-qty">${item.qty} <span style="font-size:14px;font-weight:400;color:var(--text3)">pcs</span></div>
        <div class="inv-bar-container"><div class="inv-bar ${lvl}" style="width:${pct}%"></div></div>
        <div class="inv-max">Min: ${Math.round(item.max*0.2)} | Max: ${item.max} (${pct}%)</div>
        <div class="inv-status ${lvl === 'high' ? 'ok' : lvl}">${statusText}</div>
      </div>
    `;
  }).join('');
}

function runCutoff() {
  const panel = document.getElementById('cutoffPanel');
  panel.style.display = 'block';
  document.getElementById('cutoffTable').innerHTML = `
    <table class="data-table">
      <thead><tr><th>Item</th><th>Opening</th><th>Added</th><th>Sold</th><th>Closing</th><th>Status</th></tr></thead>
      <tbody>
        ${SNACKS_INV.map(i => `
          <tr>
            <td>${i.icon} ${i.name}</td>
            <td>${i.qty + 5}</td>
            <td>0</td>
            <td>5</td>
            <td>${i.qty}</td>
            <td><span class="badge ${i.qty < i.max*0.2 ? 'badge-red' : 'badge-green'}">${i.qty < i.max*0.2 ? 'SHORT' : 'OK'}</span></td>
          </tr>
        `).join('')}
      </tbody>
    </table>
  `;
  showToast('Cutoff computed successfully!', 'success');
}

function addStock() {
  showToast('Stock updated successfully!', 'success');
  closeModal('addStockModal');
}

// ═══════════════════════════════════════════════
// EXCEL CALC
// ═══════════════════════════════════════════════
function computeExcel() {
  const get = id => parseFloat(document.getElementById(id)?.value) || 0;
  const fmt = n => `₱${n.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2})}`;

  const snacks = get('ex_snacks_qty') * get('ex_snacks_price');
  const socks  = get('ex_socks_qty')  * get('ex_socks_price');
  const drinks = get('ex_drinks_qty') * get('ex_drinks_price');
  const pkg    = get('ex_pkg_qty')    * get('ex_pkg_price');
  const total  = snacks + socks + drinks + pkg;

  document.getElementById('ex_snacks_gross').textContent = fmt(snacks);
  document.getElementById('ex_socks_gross').textContent  = fmt(socks);
  document.getElementById('ex_drinks_gross').textContent = fmt(drinks);
  document.getElementById('ex_pkg_gross').textContent    = fmt(pkg);
  document.getElementById('ex_total_gross').textContent  = fmt(total);

  const fund     = get('ex_fund');
  const discAmt  = get('ex_discounts');
  const expected = fund + total - discAmt;
  const actual   = get('ex_actual');
  const variance = actual - expected;

  document.getElementById('ex_var_gross').textContent = fmt(total);
  document.getElementById('ex_expected').textContent  = fmt(expected);
  document.getElementById('ex_variance').textContent  = (variance >= 0 ? '+' : '') + fmt(Math.abs(variance));

  const varEl = document.getElementById('ex_variance');
  varEl.className = 'computed ' + (variance >= 0 ? 'positive' : 'negative');
  varEl.textContent = (variance >= 0 ? '+' : '-') + fmt(Math.abs(variance));

  document.getElementById('ex_status').innerHTML =
    variance > 0 ? '<span class="badge badge-blue">OVER</span>' :
    variance < 0 ? '<span class="badge badge-red">SHORT</span>' :
    '<span class="badge badge-green">BALANCED</span>';

  // Discounts
  const avgPax = total / Math.max(1, get('ex_reg') + get('ex_senior') + get('ex_pwd') + get('ex_minor'));
  const avgSale = avgPax * 50; // simplified per-customer

  const seniorDisc = get('ex_senior') * avgSale * 0.20;
  const pwdDisc    = get('ex_pwd')    * avgSale * 0.20;
  const minorDisc  = get('ex_minor')  * avgSale * 0.10;

  document.getElementById('ex_reg_amt').textContent    = fmt(0);
  document.getElementById('ex_reg_net').textContent    = fmt(total);
  document.getElementById('ex_senior_amt').textContent = `-${fmt(seniorDisc)}`;
  document.getElementById('ex_senior_net').textContent = fmt(get('ex_senior') * avgSale - seniorDisc);
  document.getElementById('ex_pwd_amt').textContent    = `-${fmt(pwdDisc)}`;
  document.getElementById('ex_pwd_net').textContent    = fmt(get('ex_pwd') * avgSale - pwdDisc);
  document.getElementById('ex_minor_amt').textContent  = `-${fmt(minorDisc)}`;
  document.getElementById('ex_minor_net').textContent  = fmt(get('ex_minor') * avgSale - minorDisc);
  document.getElementById('ex_net_sales').textContent  = fmt(total - seniorDisc - pwdDisc - minorDisc);
}

function printExcel() { showToast('Printing report...', 'info'); window.print(); }

// ═══════════════════════════════════════════════
// RESERVATION
// ═══════════════════════════════════════════════
function renderReservations() {
  // Cards
  const cardsEl = document.getElementById('reservationCards');
  cardsEl.innerHTML = reservations.filter(r => r.status === 'Confirmed').map(r => `
    <div class="res-card">
      <div class="res-card-header">
        <div>
          <div class="res-name">🎉 ${r.customer}</div>
          <div class="res-date">📅 ${r.date}</div>
        </div>
        <span class="badge badge-green">${r.status}</span>
      </div>
      <div class="res-details">
        🍱 Food: ${r.food}<br>
        💰 Amount: <strong style="color:var(--accent3)">₱${r.amount.toLocaleString()}</strong>
      </div>
      <div class="res-packs">📦 ${r.packs} Pack(s)</div>
    </div>
  `).join('');

  // Table
  const tbody = document.getElementById('resTableBody');
  tbody.innerHTML = reservations.map(r => `
    <tr>
      <td class="text-accent">${r.id}</td>
      <td>${r.customer}</td>
      <td>${r.date}</td>
      <td>${r.packs}</td>
      <td>${r.food}</td>
      <td class="text-green">₱${r.amount.toLocaleString()}</td>
      <td><span class="badge ${r.status === 'Confirmed' ? 'badge-green' : 'badge-yellow'}">${r.status}</span></td>
      <td>
        <button class="btn btn-outline" style="padding:4px 10px;font-size:11px" onclick="confirmRes('${r.id}')">✅ Confirm</button>
        <button class="btn btn-danger" style="padding:4px 10px;font-size:11px" onclick="cancelRes('${r.id}')">✕</button>
      </td>
    </tr>
  `).join('');
}

function submitReservation() {
  const name = document.getElementById('res_name').value;
  if (!name) { showToast('Enter customer name!', 'error'); return; }
  const packs = parseInt(document.getElementById('res_packs').value) || 1;
  const pkg = document.getElementById('res_pkg').value;
  const pkgPrice = parseInt(pkg.match(/₱([\d,]+)/)[1].replace(',',''));
  const date = document.getElementById('res_date').value || '2025-06-01';
  const foods = [...document.getElementById('res_food').selectedOptions].map(o => o.value).join(', ') || 'TBD';

  reservations.push({
    id: 'RES-' + String(reservations.length + 1).padStart(3,'0'),
    customer: name,
    date, packs, food: foods,
    amount: pkgPrice * packs,
    status: 'Pending'
  });

  renderReservations();
  closeModal('newResModal');
  showToast(`Reservation created for ${name}!`, 'success');
}

function confirmRes(id) {
  const r = reservations.find(r => r.id === id);
  if (r) { r.status = 'Confirmed'; renderReservations(); showToast('Reservation confirmed!', 'success'); }
}

function cancelRes(id) {
  reservations = reservations.filter(r => r.id !== id);
  renderReservations();
  showToast('Reservation cancelled.', 'warning');
}

// ═══════════════════════════════════════════════
// WAIVER
// ═══════════════════════════════════════════════
function renderWaivers() {
  const el = document.getElementById('waiverList');
  el.innerHTML = waivers.map(w => `
    <div class="waiver-entry">
      <div class="we-name">👤 ${w.name}</div>
      <div class="we-detail">
        📱 ${w.contact}<br>
        🎂 ${w.dob}
      </div>
      <div class="we-type">
        <span class="badge ${
          w.type === 'senior' ? 'badge-yellow' :
          w.type === 'pwd' ? 'badge-blue' :
          w.type === 'minor' ? 'badge-green' : 'badge-blue'
        }">${w.type.toUpperCase()}</span>
      </div>
    </div>
  `).join('');
}

function submitWaiver() {
  const name = document.getElementById('w_name').value;
  if (!name) { showToast('Enter full name!', 'error'); return; }
  waivers.push({
    name,
    type: document.getElementById('w_type').value,
    dob: document.getElementById('w_dob').value || '—',
    contact: document.getElementById('w_contact').value || '—',
  });
  renderWaivers();
  ['w_name','w_dob','w_contact','w_address','w_emergency','w_notes'].forEach(id => document.getElementById(id).value = '');
  showToast('Waiver submitted successfully!', 'success');
}

// ═══════════════════════════════════════════════
// ATTENDANCE
// ═══════════════════════════════════════════════
function calcLateDeduction(lateMin) {
  if (lateMin <= 15) return 0;
  if (lateMin <= 30) return 25;
  if (lateMin <= 60) return 50;
  return 100;
}

function renderAttendance() {
  const tbody = document.getElementById('attTableBody');
  tbody.innerHTML = attendanceLogs.map(a => `
    <tr>
      <td><strong>${a.emp}</strong></td>
      <td class="number-display">${a.timeIn}</td>
      <td class="number-display">${a.sched}</td>
      <td class="${a.lateMin > 15 ? 'text-red' : a.lateMin > 0 ? 'text-gold' : 'text-green'}">${a.lateMin} mins</td>
      <td class="${a.deduct > 0 ? 'text-red' : 'text-green'}">-₱${a.deduct}</td>
      <td><span class="badge ${
        a.deduct > 0 ? 'badge-red' :
        a.lateMin > 0 ? 'badge-yellow' : 'badge-green'
      }">${a.status}</span></td>
    </tr>
  `).join('');
}

function submitAttendance() {
  const emp = document.getElementById('att_emp').value;
  const tin = document.getElementById('att_timein').value;
  const sched = document.getElementById('att_sched').value;

  const [th, tm] = tin.split(':').map(Number);
  const [sh, sm] = sched.split(':').map(Number);
  const lateMin = Math.max(0, (th * 60 + tm) - (sh * 60 + sm));
  const deduct = calcLateDeduction(lateMin);

  attendanceLogs.push({
    emp, timeIn: tin, sched,
    lateMin, deduct,
    status: lateMin === 0 ? 'On Time' : lateMin <= 15 ? 'On Time (grace)' : 'Late'
  });

  renderAttendance();
  closeModal('addAttModal');
  showToast(`Attendance recorded for ${emp}`, 'success');
}

function generatePayslip() {
  document.getElementById('payslipSection').style.display = 'block';
  renderPayslip();
}

function renderPayslip() {
  const emp = document.getElementById('payslipEmp').value;
  const period = document.getElementById('payslipPeriod').value;
  const logs = attendanceLogs.filter(a => a.emp === emp);
  const totalDeduct = logs.reduce((s, a) => s + a.deduct, 0);
  const baseSalary = 12000;
  const netPay = baseSalary - totalDeduct;

  document.getElementById('payslipBox').innerHTML = `
    <div class="payslip-card">
      <div class="payslip-header">
        <div class="payslip-company">⚡ REKS AMUSEMENT COM INC</div>
        <div class="payslip-title">EMPLOYEE PAYSLIP</div>
        <div class="payslip-period">${period}</div>
      </div>
      <div style="font-size:13px;margin-bottom:12px">
        <strong>Employee:</strong> ${emp}<br>
        <strong>Department:</strong> Operations
      </div>
      <div class="payslip-row"><span>Basic Salary</span><span>₱${baseSalary.toLocaleString()}.00</span></div>
      <div class="payslip-row deduction"><span>Late Deductions</span><span>-₱${totalDeduct}.00</span></div>
      <div class="payslip-row deduction"><span>SSS</span><span>-₱545.00</span></div>
      <div class="payslip-row deduction"><span>PhilHealth</span><span>-₱300.00</span></div>
      <div class="payslip-row deduction"><span>Pag-IBIG</span><span>-₱100.00</span></div>
      <div class="payslip-row total-row">
        <span>NET PAY</span>
        <span>₱${(netPay - 945).toLocaleString()}.00</span>
      </div>
    </div>
  `;
  showToast('Payslip generated!', 'success');
}

// ═══════════════════════════════════════════════
// CHATBOT
// ═══════════════════════════════════════════════
const chatResponses = {
  'package': '🎉 We have 4 packages:\n- Basic (₱1,200): 1 venue slot, snacks\n- Standard (₱2,000): + drinks & cake\n- Premium (₱3,500): + food platter\n- Deluxe (₱5,000): full buffet setup!',
  'entrance': '💰 Entrance fee includes socks rental. Adults ₱150, Kids (3-12) ₱100, Kids below 3 FREE!',
  'reservation': '📅 You can book a reservation here on the app! Click "New Reservation" or tell me the date and number of guests.',
  'promo': '🏷 Current promos:\n- Senior/PWD: 20% off\n- Minor (below 12): 10% off\n- Groups of 10+: 15% off\n- Weekday Special: ₱50 off/head\n- Birthday celebrant: 1 free pack!',
  'age': '👶 Age requirements: All guests under 12 must be accompanied by an adult. Socks are REQUIRED for all visitors (available at ₱80/pair).',
  'hours': '🕐 We are open:\nMonday–Friday: 10:00 AM – 9:00 PM\nSaturday–Sunday: 9:00 AM – 10:00 PM\n📍 Lipa City, Batangas',
  'default': '😊 Thanks for reaching out! Our team will be happy to help you. For urgent queries, please call our hotline or visit us directly at REKS Amusement, Lipa City!'
};

function getBotReply(msg) {
  const m = msg.toLowerCase();
  if (m.includes('package') || m.includes('pack')) return chatResponses['package'];
  if (m.includes('entrance') || m.includes('fee') || m.includes('price')) return chatResponses['entrance'];
  if (m.includes('reservation') || m.includes('book') || m.includes('reserve')) return chatResponses['reservation'];
  if (m.includes('promo') || m.includes('discount')) return chatResponses['promo'];
  if (m.includes('age') || m.includes('minor') || m.includes('kids')) return chatResponses['age'];
  if (m.includes('hour') || m.includes('open') || m.includes('time')) return chatResponses['hours'];
  return chatResponses['default'];
}

function sendChat() {
  const input = document.getElementById('chatInput');
  const msg = input.value.trim();
  if (!msg) return;

  addChatMsg(msg, 'user');
  input.value = '';

  setTimeout(() => {
    addChatMsg(getBotReply(msg), 'bot');
  }, 600);
}

function quickChat(msg) {
  document.getElementById('chatInput').value = msg;
  sendChat();
}

function addChatMsg(text, type) {
  const el = document.getElementById('chatMessages');
  const div = document.createElement('div');
  div.className = `msg ${type}`;
  div.innerHTML = text.replace(/\n/g, '<br>');
  el.appendChild(div);
  el.scrollTop = el.scrollHeight;
}

// ═══════════════════════════════════════════════
// SCAN SIMULATION
// ═══════════════════════════════════════════════
function simulateScan(type) {
  const area = document.getElementById(type === 'res' ? 'resScanArea' : null);
  if (area) {
    area.classList.add('scanning');
    setTimeout(() => {
      area.classList.remove('scanning');
      if (type === 'res') {
        document.getElementById('res_name').value = 'Juan Dela Cruz';
        document.getElementById('res_contact').value = '09171234567';
        showToast('QR scanned! Customer details loaded.', 'success');
        openModal('newResModal');
      }
    }, 2000);
    return;
  }

  if (type === 'waiver') {
    showToast('QR scanning...', 'info');
    setTimeout(() => {
      document.getElementById('w_name').value = 'Sample Customer';
      document.getElementById('w_dob').value = '1980-05-15';
      document.getElementById('w_contact').value = '0912-345-6789';
      document.getElementById('w_type').value = 'regular';
      document.getElementById('w_address').value = 'Lipa City, Batangas';
      showToast('QR scanned! Details auto-filled.', 'success');
    }, 1500);
  }
}

// ═══════════════════════════════════════════════
// ANALYTICS
// ═══════════════════════════════════════════════
function exportReport() {
  showToast('Exporting analytics report...', 'info');
  setTimeout(() => showToast('Report exported successfully!', 'success'), 1500);
}

function refreshDash() { showToast('Dashboard refreshed!', 'success'); }

// ═══════════════════════════════════════════════
// MODALS
// ═══════════════════════════════════════════════
function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('active');
  }
});

// ═══════════════════════════════════════════════
// TOAST
// ═══════════════════════════════════════════════
function showToast(msg, type = 'info') {
  const container = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  const icons = { success:'✅', error:'❌', warning:'⚠️', info:'ℹ️' };
  toast.className = `toast ${type}`;
  toast.innerHTML = `<span>${icons[type]}</span><span>${msg}</span>`;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 3500);
}

// Attendance modal live calc
document.addEventListener('change', e => {
  if (e.target.id === 'att_timein' || e.target.id === 'att_sched') {
    const tin = document.getElementById('att_timein').value;
    const sched = document.getElementById('att_sched').value;
    if (tin && sched) {
      const [th, tm] = tin.split(':').map(Number);
      const [sh, sm] = sched.split(':').map(Number);
      const lateMin = Math.max(0, (th * 60 + tm) - (sh * 60 + sm));
      const deduct = calcLateDeduction(lateMin);
      document.getElementById('att_calc').innerHTML =
        `Late: <strong style="color:${lateMin > 15 ? 'var(--danger)' : 'var(--success)'}">${lateMin} min(s)</strong> — 
         Deduction: <strong style="color:${deduct > 0 ? 'var(--danger)' : 'var(--success)'}">-₱${deduct}</strong>
         ${lateMin <= 15 && lateMin > 0 ? ' <span style="color:var(--accent)">(Grace Period)</span>' : ''}`;
    }
  }
});
</script>
</body>
</html>