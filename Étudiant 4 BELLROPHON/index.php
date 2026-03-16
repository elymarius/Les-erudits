<?php
// =============================================
//  index.php — Tableau de bord (accès protégé)
// =============================================
session_start();

// Rediriger vers login si non connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur');
$user_role = htmlspecialchars($_SESSION['user_role'] ?? 'user');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>InfoProd – Interface de Consultation</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #0a0f14;
    --bg2: #0f1720;
    --bg3: #141f2b;
    --border: #1e3048;
    --accent-green: #00e5a0;
    --accent-solar: #f5a623;
    --accent-wind: #4fc3f7;
    --accent-co2: #b39ddb;
    --accent-temp: #ff7043;
    --text: #e8f0f7;
    --text-dim: #6b8599;
    --text-mid: #9ab3c9;
    --card-glow-green: rgba(0, 229, 160, 0.07);
    --card-glow-solar: rgba(245, 166, 35, 0.07);
    --card-glow-wind: rgba(79, 195, 247, 0.07);
    --radius: 14px;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'Syne', sans-serif;
    min-height: 100vh;
    overflow-x: hidden;
  }

  /* Ambient background dots */
  body::before {
    content: '';
    position: fixed; inset: 0;
    background-image: radial-gradient(circle, #1e3048 1px, transparent 1px);
    background-size: 32px 32px;
    opacity: 0.35;
    pointer-events: none;
    z-index: 0;
  }

  /* Top glow */
  body::after {
    content: '';
    position: fixed;
    top: -200px; left: 50%;
    transform: translateX(-50%);
    width: 800px; height: 400px;
    background: radial-gradient(ellipse, rgba(0, 229, 160, 0.08) 0%, transparent 70%);
    pointer-events: none;
    z-index: 0;
  }

  /* ===== TOPBAR ===== */
  header {
    position: sticky; top: 0; z-index: 100;
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 32px;
    height: 64px;
    background: rgba(10,15,20,0.85);
    backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--border);
  }

  .logo {
    display: flex; align-items: center; gap: 12px;
  }
  .logo-icon {
    width: 36px; height: 36px;
    background: linear-gradient(135deg, var(--accent-green), var(--accent-wind));
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
  }
  .logo-text {
    font-size: 18px; font-weight: 800; letter-spacing: 0.04em;
    color: var(--text);
  }
  .logo-text span { color: var(--accent-green); }

  .header-right {
    display: flex; align-items: center; gap: 20px;
  }
  .header-clock {
    font-family: 'Space Mono', monospace;
    font-size: 13px; color: var(--text-mid);
  }
  .header-clock #clock-time { color: var(--accent-green); font-weight: 700; }

  .status-dot {
    display: flex; align-items: center; gap: 6px;
    font-size: 12px; color: var(--text-dim);
  }
  .dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--accent-green);
    box-shadow: 0 0 8px var(--accent-green);
    animation: pulse 2s infinite;
  }
  @keyframes pulse {
    0%,100% { opacity: 1; }
    50% { opacity: 0.4; }
  }

  /* ===== NAV TABS ===== */
  nav {
    position: relative; z-index: 10;
    display: flex; gap: 4px;
    padding: 16px 32px 0;
  }
  .tab {
    padding: 9px 20px;
    border-radius: 10px 10px 0 0;
    font-size: 13px; font-weight: 600;
    cursor: pointer;
    color: var(--text-dim);
    border: 1px solid transparent;
    border-bottom: none;
    transition: all .2s;
    letter-spacing: 0.02em;
  }
  .tab:hover { color: var(--text-mid); background: var(--bg2); }
  .tab.active {
    color: var(--accent-green);
    background: var(--bg2);
    border-color: var(--border);
  }

  /* ===== MAIN ===== */
  main {
    position: relative; z-index: 1;
    padding: 0 32px 40px;
  }

  .page { display: none; }
  .page.active { display: block; animation: fadein .3s ease; }
  @keyframes fadein { from { opacity:0; transform: translateY(8px); } to { opacity:1; transform:none; } }

  /* ===== SECTION TITLE ===== */
  .section-header {
    display: flex; align-items: baseline; justify-content: space-between;
    padding: 28px 0 18px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 24px;
  }
  .section-title {
    font-size: 22px; font-weight: 800; color: var(--text);
  }
  .section-subtitle {
    font-size: 12px; color: var(--text-dim); font-weight: 400;
    font-family: 'Space Mono', monospace;
  }
  .badge {
    font-size: 11px; font-weight: 700; letter-spacing: 0.08em;
    padding: 4px 10px; border-radius: 20px;
    text-transform: uppercase;
  }
  .badge-green { background: rgba(0,229,160,.12); color: var(--accent-green); border: 1px solid rgba(0,229,160,.2); }
  .badge-solar { background: rgba(245,166,35,.12); color: var(--accent-solar); border: 1px solid rgba(245,166,35,.2); }

  /* ===== KPI CARDS ===== */
  .kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
  }
  .kpi-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    position: relative;
    overflow: hidden;
    transition: border-color .2s, transform .2s;
  }
  .kpi-card:hover { transform: translateY(-2px); }
  .kpi-card::before {
    content: '';
    position: absolute; inset: 0;
    opacity: 0;
    transition: opacity .3s;
  }
  .kpi-card:hover::before { opacity: 1; }
  .kpi-card.green { border-color: rgba(0,229,160,.2); }
  .kpi-card.green::before { background: var(--card-glow-green); }
  .kpi-card.solar { border-color: rgba(245,166,35,.2); }
  .kpi-card.solar::before { background: var(--card-glow-solar); }
  .kpi-card.wind { border-color: rgba(79,195,247,.2); }
  .kpi-card.wind::before { background: var(--card-glow-wind); }
  .kpi-card.temp { border-color: rgba(255,112,67,.2); }
  .kpi-card.co2 { border-color: rgba(179,157,219,.2); }

  .kpi-label {
    font-size: 11px; font-weight: 600; letter-spacing: 0.1em;
    text-transform: uppercase; color: var(--text-dim);
    margin-bottom: 10px;
    display: flex; align-items: center; gap: 6px;
  }
  .kpi-icon { font-size: 14px; }
  .kpi-value {
    font-family: 'Space Mono', monospace;
    font-size: 32px; font-weight: 700;
    line-height: 1;
    margin-bottom: 6px;
  }
  .kpi-card.green .kpi-value { color: var(--accent-green); }
  .kpi-card.solar .kpi-value { color: var(--accent-solar); }
  .kpi-card.wind .kpi-value { color: var(--accent-wind); }
  .kpi-card.temp .kpi-value { color: var(--accent-temp); }
  .kpi-card.co2 .kpi-value { color: var(--accent-co2); }

  .kpi-unit { font-size: 14px; color: var(--text-dim); }
  .kpi-trend {
    font-size: 11px; color: var(--text-dim);
    display: flex; align-items: center; gap: 4px;
  }
  .trend-up { color: var(--accent-green); }
  .trend-down { color: var(--accent-temp); }

  /* ===== CHARTS ZONE ===== */
  .charts-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 16px;
    margin-bottom: 28px;
  }
  @media (max-width: 900px) { .charts-grid { grid-template-columns: 1fr; } }

  .chart-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
  }
  .chart-title {
    font-size: 13px; font-weight: 700; color: var(--text-mid);
    letter-spacing: 0.05em; margin-bottom: 16px;
    display: flex; align-items: center; justify-content: space-between;
  }

  /* SVG Chart */
  .chart-svg { width: 100%; height: 160px; overflow: visible; }
  .chart-line { fill: none; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }
  .chart-area { opacity: 0.12; }

  /* Donut */
  .donut-wrap {
    display: flex; flex-direction: column; align-items: center; gap: 16px;
  }
  .donut-svg { width: 140px; height: 140px; }
  .donut-legend { width: 100%; }
  .legend-item {
    display: flex; align-items: center; justify-content: space-between;
    font-size: 12px; padding: 5px 0;
    border-bottom: 1px solid var(--border);
  }
  .legend-item:last-child { border-bottom: none; }
  .legend-dot { width: 8px; height: 8px; border-radius: 50%; margin-right: 6px; }
  .legend-name { color: var(--text-mid); display: flex; align-items: center; }
  .legend-val { color: var(--text); font-weight: 700; font-family: 'Space Mono', monospace; font-size: 11px; }

  /* ===== SITES TABLE ===== */
  .table-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    margin-bottom: 28px;
  }
  .table-head {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
  }
  .table-head-title { font-size: 14px; font-weight: 700; color: var(--text-mid); }
  table { width: 100%; border-collapse: collapse; }
  thead tr { background: var(--bg3); }
  th {
    padding: 10px 16px;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.08em; text-transform: uppercase;
    color: var(--text-dim);
    text-align: left;
  }
  td {
    padding: 12px 16px;
    font-size: 13px; color: var(--text-mid);
    border-bottom: 1px solid var(--border);
  }
  tr:last-child td { border-bottom: none; }
  tr:hover td { background: rgba(255,255,255,0.02); }
  .td-mono { font-family: 'Space Mono', monospace; font-size: 12px; }
  .status-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 700;
  }
  .status-ok { background: rgba(0,229,160,.1); color: var(--accent-green); border: 1px solid rgba(0,229,160,.2); }
  .status-warn { background: rgba(245,166,35,.1); color: var(--accent-solar); border: 1px solid rgba(245,166,35,.2); }
  .status-off { background: rgba(107,133,153,.1); color: var(--text-dim); border: 1px solid var(--border); }

  /* ===== GABARITS PAGE ===== */
  .gabarits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
  }
  .gabarit-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    cursor: pointer;
    transition: all .2s;
  }
  .gabarit-card:hover { border-color: var(--accent-green); transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,.3); }
  .gabarit-preview {
    background: #0a1520;
    padding: 16px;
    border-bottom: 1px solid var(--border);
    height: 160px;
    position: relative;
    overflow: hidden;
  }
  .preview-bar {
    background: #1a2840;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 9px; color: var(--accent-green);
    display: flex; justify-content: space-between;
    margin-bottom: 8px;
    font-family: 'Space Mono', monospace;
  }
  .preview-body {
    display: grid; grid-template-columns: 1fr 2fr 1fr;
    gap: 6px; height: calc(100% - 36px);
  }
  .preview-zone {
    border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 8px; font-weight: 700; text-align: center;
    letter-spacing: 0.05em; text-transform: uppercase; color: rgba(255,255,255,.4);
  }
  .pz1 { background: rgba(0,229,160,.08); border: 1px solid rgba(0,229,160,.15); }
  .pz2 { background: rgba(79,195,247,.06); border: 1px solid rgba(79,195,247,.1); }
  .pz3 { background: rgba(245,166,35,.08); border: 1px solid rgba(245,166,35,.15); }
  .preview-footer {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: rgba(239,68,68,.15); border-top: 1px solid rgba(239,68,68,.2);
    padding: 4px 8px;
    font-size: 8px; color: rgba(239,68,68,.8);
    font-family: 'Space Mono', monospace;
  }
  .gabarit-info { padding: 14px 16px; }
  .gabarit-name { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
  .gabarit-meta { font-size: 11px; color: var(--text-dim); display: flex; gap: 12px; }
  .gabarit-actions {
    display: flex; gap: 8px;
    padding: 0 16px 14px;
  }
  .btn-sm {
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 11px; font-weight: 700;
    cursor: pointer; border: 1px solid;
    transition: all .15s; font-family: 'Syne', sans-serif;
  }
  .btn-outline-green { background: transparent; border-color: rgba(0,229,160,.3); color: var(--accent-green); }
  .btn-outline-green:hover { background: rgba(0,229,160,.1); }
  .btn-outline { background: transparent; border-color: var(--border); color: var(--text-dim); }
  .btn-outline:hover { border-color: var(--text-dim); color: var(--text-mid); }

  /* ===== TEMPERATURE PAGE ===== */
  .temp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
  }
  .temp-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    text-align: center;
    transition: all .2s;
  }
  .temp-card:hover { border-color: rgba(255,112,67,.3); }
  .temp-location { font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-dim); margin-bottom: 12px; }
  .temp-gauge {
    width: 90px; height: 90px; margin: 0 auto 12px;
    position: relative;
  }
  .temp-gauge svg { width: 100%; height: 100%; }
  .temp-num {
    position: absolute; inset: 0;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
  }
  .temp-big { font-family: 'Space Mono', monospace; font-size: 20px; font-weight: 700; color: var(--accent-temp); }
  .temp-unit-small { font-size: 10px; color: var(--text-dim); }
  .temp-humidity { font-size: 12px; color: var(--text-dim); margin-top: 4px; }
  .temp-humidity span { color: var(--accent-wind); }
  .temp-updated { font-size: 10px; color: var(--text-dim); font-family: 'Space Mono', monospace; margin-top: 8px; }

  /* ===== DATABASE PAGE ===== */
  .db-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 24px; }
  .db-stat {
    background: var(--bg2); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 16px;
  }
  .db-stat-label { font-size: 11px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px; }
  .db-stat-value { font-family: 'Space Mono', monospace; font-size: 22px; font-weight: 700; color: var(--text); }
  .db-stat-sub { font-size: 11px; color: var(--text-dim); margin-top: 4px; }

  .filter-row {
    display: flex; gap: 10px; align-items: center;
    margin-bottom: 16px; flex-wrap: wrap;
  }
  .filter-select {
    background: var(--bg2); border: 1px solid var(--border);
    color: var(--text-mid); border-radius: 8px;
    padding: 7px 12px; font-size: 12px; font-family: 'Syne', sans-serif;
    cursor: pointer; outline: none;
  }
  .filter-select:focus { border-color: var(--accent-green); }
  .btn-primary {
    background: var(--accent-green); color: #0a0f14;
    border: none; border-radius: 8px;
    padding: 7px 16px; font-size: 12px; font-weight: 700;
    cursor: pointer; font-family: 'Syne', sans-serif;
    transition: opacity .15s;
  }
  .btn-primary:hover { opacity: .85; }

  /* ===== SCROLLBAR ===== */
  ::-webkit-scrollbar { width: 6px; }
  ::-webkit-scrollbar-track { background: var(--bg); }
  ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

  /* ===== FOOTER ===== */
  .page-footer {
    position: relative; z-index: 1;
    text-align: center; padding: 16px 32px;
    font-size: 11px; color: var(--text-dim);
    border-top: 1px solid var(--border);
    font-family: 'Space Mono', monospace;
  }
</style>
</head>
<body>
<script>
// Session injectée directement par PHP — aucun fetch nécessaire
document.addEventListener('DOMContentLoaded', function(){
  var u = document.getElementById('header-username');
  var r = document.getElementById('header-role');
  if(u) u.textContent = <?= json_encode($user_name) ?>;
  if(r) r.textContent = <?= json_encode($user_role) ?>;
});
</script>

<header>
  <div class="logo">
    <div class="logo-icon">⚡</div>
    <span class="logo-text">Info<span>Prod</span></span>
  </div>
  <div class="header-right">
    <div class="header-clock">
      <span id="clock-date"></span> — <span id="clock-time">--:--:--</span>
    </div>
    <div class="status-dot">
      <div class="dot"></div> <span data-i18n="sys-active">Système actif</span>
    </div>
    <div style="display:flex;align-items:center;gap:14px;border-left:1px solid var(--border);padding-left:18px;margin-left:4px">
      <span style="font-size:12px;color:var(--text-mid)">
        &#128100;&nbsp;<strong style="color:var(--text)"><span id="header-username">Utilisateur</span></strong>
        <span style="color:var(--text-dim);margin-left:4px;font-family:'Space Mono',monospace;font-size:10px">[<span id="header-role">user</span>]</span>
      </span>
      <button id="lang-toggle"
         onclick="toggleLang()"
         style="font-size:11px;font-weight:700;background:rgba(79,195,247,.1);color:#4fc3f7;border:1px solid rgba(79,195,247,.25);border-radius:7px;padding:5px 12px;cursor:pointer;font-family:'Syne',sans-serif;transition:background .2s;"
         onmouseover="this.style.background='rgba(79,195,247,.22)'"
         onmouseout="this.style.background='rgba(79,195,247,.1)'" >
        🌐 EN
      </button>
      <a href="logout.php"
         style="font-size:11px;font-weight:700;background:rgba(255,83,112,.1);color:#ff5370;border:1px solid rgba(255,83,112,.25);border-radius:7px;padding:5px 12px;text-decoration:none;"
         onmouseover="this.style.background='rgba(255,83,112,.22)'"
         onmouseout="this.style.background='rgba(255,83,112,.1)'" >
        <span data-i18n="logout">D&eacute;connexion</span>
      </a>
    </div>
  </div>
</header>

<nav>
  <div class="tab active" onclick="showPage('dashboard',this)">📊 <span data-i18n="tab-dashboard">Tableau de bord</span></div>
  <div class="tab" onclick="showPage('energie',this)">⚡ <span data-i18n="tab-energy">Production</span></div>
  <div class="tab" onclick="showPage('temperature',this)">🌡️ <span data-i18n="tab-temp">Températures</span></div>
  <div class="tab" onclick="showPage('gabarits',this)">🖥️ <span data-i18n="tab-templates">Gabarits</span></div>
  <div class="tab" onclick="showPage('bdd',this)">🗄️ <span data-i18n="tab-db">Base de données</span></div>
</nav>

<main>

<!-- ===== DASHBOARD ===== -->
<div id="page-dashboard" class="page active">
  <div class="section-header">
    <div>
      <div class="section-title" data-i18n="dash-title">Tableau de bord — LGT Joseph Gaillard</div>
      <div class="section-subtitle" data-i18n="dash-sub">Vue d'ensemble du système InfoProd</div>
    </div>
    <span class="badge badge-green">LIVE</span>
  </div>

  <div class="kpi-grid">
    <div class="kpi-card solar">
      <div class="kpi-label"><span class="kpi-icon">☀️</span> <span data-i18n="kpi-solar">Production solaire</span></div>
      <div class="kpi-value"><span id="kpi-solar">3.42</span> <span class="kpi-unit">kW</span></div>
      <div class="kpi-trend"><span class="trend-up">↑ +12%</span> <span data-i18n="vs-yesterday">vs hier</span></div>
    </div>
    <div class="kpi-card wind">
      <div class="kpi-label"><span class="kpi-icon">🌬️</span> <span data-i18n="kpi-wind">Production éolienne</span></div>
      <div class="kpi-value"><span id="kpi-wind">1.18</span> <span class="kpi-unit">kW</span></div>
      <div class="kpi-trend"><span class="trend-down">↓ -5%</span> <span data-i18n="vs-yesterday">vs hier</span></div>
    </div>
    <div class="kpi-card green">
      <div class="kpi-label"><span class="kpi-icon">🔋</span> <span data-i18n="kpi-battery">Énergie stockée</span></div>
      <div class="kpi-value"><span id="kpi-bat">74</span> <span class="kpi-unit">%</span></div>
      <div class="kpi-trend"><span class="trend-up">↑ <span data-i18n="charging">Charge en cours</span></span></div>
    </div>
    <div class="kpi-card co2">
      <div class="kpi-label"><span class="kpi-icon">🌿</span> <span data-i18n="kpi-co2">CO₂ économisé (jour)</span></div>
      <div class="kpi-value"><span id="kpi-co2">18.4</span> <span class="kpi-unit">kg</span></div>
      <div class="kpi-trend"><span class="trend-up">↑ <span data-i18n="cumulated">Cumulé</span> : 1 247 kg</span></div>
    </div>
    <div class="kpi-card temp">
      <div class="kpi-label"><span class="kpi-icon">🌡️</span> <span data-i18n="kpi-exttemp">Température ext.</span></div>
      <div class="kpi-value"><span id="kpi-temp">28.3</span> <span class="kpi-unit">°C</span></div>
      <div class="kpi-trend"><span data-i18n="humidity">Humidité</span> : <span style="color:var(--accent-wind)">78 %</span></div>
    </div>
    <div class="kpi-card green">
      <div class="kpi-label"><span class="kpi-icon">🖥️</span> <span data-i18n="kpi-displays">Afficheurs actifs</span></div>
      <div class="kpi-value"><span>5</span> <span class="kpi-unit">/ 6</span></div>
      <div class="kpi-trend"><span style="color:var(--accent-solar)">1 <span data-i18n="offline">hors ligne</span></span> (<span data-i18n="cafeteria">Réfectoire</span>)</div>
    </div>
  </div>

  <div class="charts-grid">
    <div class="chart-card">
      <div class="chart-title">
        <span data-i18n="chart-daily">Production journalière (kW)</span>
        <span class="badge badge-solar" data-i18n="today">Aujourd'hui</span>
      </div>
      <svg class="chart-svg" id="chart-prod" viewBox="0 0 500 160"></svg>
    </div>
    <div class="chart-card">
      <div class="chart-title" data-i18n="energy-share">Répartition énergie</div>
      <div class="donut-wrap">
        <svg class="donut-svg" viewBox="0 0 100 100">
          <!-- Solaire -->
          <circle cx="50" cy="50" r="36" fill="none" stroke="#f5a623" stroke-width="14"
            stroke-dasharray="142 84" stroke-dashoffset="0" transform="rotate(-90 50 50)"/>
          <!-- Eolien -->
          <circle cx="50" cy="50" r="36" fill="none" stroke="#4fc3f7" stroke-width="14"
            stroke-dasharray="55 171" stroke-dashoffset="-142" transform="rotate(-90 50 50)"/>
          <!-- Batterie -->
          <circle cx="50" cy="50" r="36" fill="none" stroke="#00e5a0" stroke-width="14"
            stroke-dasharray="29 197" stroke-dashoffset="-197" transform="rotate(-90 50 50)"/>
          <text x="50" y="47" text-anchor="middle" fill="#e8f0f7" font-size="11" font-family="Space Mono,monospace" font-weight="700">4.6</text>
          <text x="50" y="58" text-anchor="middle" fill="#6b8599" font-size="7" font-family="Syne,sans-serif">kW total</text>
        </svg>
        <div class="donut-legend">
          <div class="legend-item">
            <span class="legend-name"><span class="legend-dot" style="background:#f5a623"></span><span data-i18n="solar">Solaire</span></span>
            <span class="legend-val">3.42 kW</span>
          </div>
          <div class="legend-item">
            <span class="legend-name"><span class="legend-dot" style="background:#4fc3f7"></span><span data-i18n="wind">Éolien</span></span>
            <span class="legend-val">1.18 kW</span>
          </div>
          <div class="legend-item">
            <span class="legend-name"><span class="legend-dot" style="background:#00e5a0"></span><span data-i18n="battery">Batterie</span></span>
            <span class="legend-val">0.00 kW</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="table-card">
    <div class="table-head">
      <span class="table-head-title" data-i18n="displays-state">État des afficheurs</span>
      <span class="badge badge-green"><span data-i18n="updated-at">Mis à jour à</span> <span id="last-update-time"></span></span>
    </div>
    <table>
      <thead><tr>
        <th data-i18n="th-building">Bâtiment</th><th data-i18n="th-location">Emplacement</th><th>IP</th><th data-i18n="th-lastping">Dernier ping</th><th data-i18n="th-template">Gabarit</th><th data-i18n="th-status">Statut</th>
      </tr></thead>
      <tbody>
        <tr><td data-i18n="admin-bld">Administration</td><td data-i18n="loc-accueil">Accueil</td><td class="td-mono">192.168.1.51</td><td class="td-mono">3s</td><td data-i18n="tpl-admin">Gabarit Admin</td><td><span class="status-pill status-ok">● <span data-i18n="active">Actif</span></span></td></tr>
        <tr><td>Bât. C</td><td data-i18n="loc-vs-pre">Vie scolaire (pré-bac)</td><td class="td-mono">192.168.1.52</td><td class="td-mono">1s</td><td data-i18n="tpl-students">Gabarit Élèves</td><td><span class="status-pill status-ok">● <span data-i18n="active">Actif</span></span></td></tr>
        <tr><td>Bât. F</td><td data-i18n="loc-cafeteria">Réfectoire</td><td class="td-mono">192.168.1.53</td><td class="td-mono">—</td><td data-i18n="tpl-cafeteria">Gabarit Réfectoire</td><td><span class="status-pill status-warn">⚠ <span data-i18n="offline">Hors ligne</span></span></td></tr>
        <tr><td>Bât. E</td><td data-i18n="loc-vs-post">Vie scolaire (post-bac)</td><td class="td-mono">192.168.1.54</td><td class="td-mono">2s</td><td data-i18n="tpl-students">Gabarit Élèves</td><td><span class="status-pill status-ok">● <span data-i18n="active">Actif</span></span></td></tr>
        <tr><td>Bât. S</td><td data-i18n="loc-physics">Physique</td><td class="td-mono">192.168.1.55</td><td class="td-mono">4s</td><td data-i18n="tpl-energy">Gabarit Énergie</td><td><span class="status-pill status-ok">● <span data-i18n="active">Actif</span></span></td></tr>
        <tr><td>Bât. W</td><td data-i18n="loc-workshops">Ateliers</td><td class="td-mono">192.168.1.56</td><td class="td-mono">1s</td><td data-i18n="tpl-workshops">Gabarit Ateliers</td><td><span class="status-pill status-ok">● <span data-i18n="active">Actif</span></span></td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- ===== ÉNERGIE ===== -->
<div id="page-energie" class="page">
  <div class="section-header">
    <div>
      <div class="section-title" data-i18n="energy-title">Production d'énergie renouvelable</div>
      <div class="section-subtitle" data-i18n="energy-sub">Sites 1 & 2 — Photovoltaïque + Éolien</div>
    </div>
    <span class="badge badge-solar">SITE 1 + 2</span>
  </div>

  <div class="kpi-grid">
    <div class="kpi-card solar">
      <div class="kpi-label"><span class="kpi-icon">☀️</span> <span data-i18n="site1-solar">Site 1 — Solaire</span></div>
      <div class="kpi-value">2.14 <span class="kpi-unit">kW</span></div>
      <div class="kpi-trend"><span data-i18n="day-cumul">Cumul jour</span> : 12.8 kWh</div>
    </div>
    <div class="kpi-card solar">
      <div class="kpi-label"><span class="kpi-icon">☀️</span> <span data-i18n="site2-solar">Site 2 — Solaire (Bât. S)</span></div>
      <div class="kpi-value">1.28 <span class="kpi-unit">kW</span></div>
      <div class="kpi-trend"><span data-i18n="day-cumul">Cumul jour</span> : 7.6 kWh</div>
    </div>
    <div class="kpi-card wind">
      <div class="kpi-label"><span class="kpi-icon">🌬️</span> <span data-i18n="site1-wind">Site 1 — Éolienne</span></div>
      <div class="kpi-value">1.18 <span class="kpi-unit">kW</span></div>
      <div class="kpi-trend"><span data-i18n="day-cumul">Cumul jour</span> : 9.4 kWh</div>
    </div>
    <div class="kpi-card green">
      <div class="kpi-label"><span class="kpi-icon">🔋</span> <span data-i18n="battery-site1">Batterie Site 1</span></div>
      <div class="kpi-value">74 <span class="kpi-unit">%</span></div>
      <div class="kpi-trend">≈ 14.8 kWh <span data-i18n="stored">stockés</span></div>
    </div>
    <div class="kpi-card co2">
      <div class="kpi-label"><span class="kpi-icon">🌿</span> <span data-i18n="co2-month">CO₂ évité — Mois</span></div>
      <div class="kpi-value">312 <span class="kpi-unit">kg</span></div>
      <div class="kpi-trend">≈ 0.4 kg CO₂ / kWh <span data-i18n="grid">réseau</span></div>
    </div>
    <div class="kpi-card green">
      <div class="kpi-label"><span class="kpi-icon">📅</span> <span data-i18n="annual-prod">Production annuelle</span></div>
      <div class="kpi-value">3 241 <span class="kpi-unit">kWh</span></div>
      <div class="kpi-trend"><span data-i18n="since">Depuis le</span> 01/01/2026</div>
    </div>
  </div>

  <div class="chart-card" style="margin-bottom:20px">
    <div class="chart-title">
      <span data-i18n="chart-7days">Historique 7 jours — kWh/jour</span>
      <span class="badge badge-green" data-i18n="week">Semaine</span>
    </div>
    <svg class="chart-svg" id="chart-week" viewBox="0 0 700 160" style="height:180px"></svg>
  </div>

  <div class="table-card">
    <div class="table-head"><span class="table-head-title" data-i18n="recent-readings">Relevés récents — Site 1 & 2</span></div>
    <table>
      <thead><tr>
        <th data-i18n="th-timestamp">Horodatage</th>
        <th data-i18n="th-site">Site</th>
        <th data-i18n="th-source">Source</th>
        <th data-i18n="th-power">Puissance (kW)</th>
        <th data-i18n="th-cumul-energy">Énergie cumulée (kWh)</th>
        <th data-i18n="th-voltage">Tension (V)</th>
      </tr></thead>
      <tbody id="tb-energy"></tbody>
    </table>
  </div>
</div>

<!-- ===== TEMPÉRATURES ===== -->
<div id="page-temperature" class="page">
  <div class="section-header">
    <div>
      <div class="section-title" data-i18n="temp-title">Mesures de température</div>
      <div class="section-subtitle" data-i18n="temp-sub">Capteurs distribuées — mise à jour toutes les 60s</div>
    </div>
    <span class="badge badge-green">LIVE</span>
  </div>

  <div class="temp-grid" id="temp-grid"></div>

  <div class="chart-card" style="margin-bottom: 20px">
    <div class="chart-title">
      <span data-i18n="chart-temp-24h">Évolution températures (24h)</span>
      <span class="badge badge-solar" data-i18n="today">Aujourd'hui</span>
    </div>
    <svg class="chart-svg" id="chart-temp" viewBox="0 0 700 160" style="height:170px"></svg>
  </div>

  <div class="table-card">
    <div class="table-head"><span class="table-head-title" data-i18n="temp-log">Journal des mesures — Dernières 24h</span></div>
    <table>
      <thead><tr>
        <th data-i18n="th-timestamp">Horodatage</th>
        <th data-i18n="th-sensor">Capteur</th>
        <th data-i18n="th-location">Emplacement</th>
        <th data-i18n="th-temp-c">Température (°C)</th>
        <th data-i18n="th-humidity-pct">Humidité (%)</th>
      </tr></thead>
      <tbody id="tb-temp"></tbody>
    </table>
  </div>
</div>

<!-- ===== GABARITS ===== -->
<div id="page-gabarits" class="page">
  <div class="section-header">
    <div>
      <div class="section-title" data-i18n="templates-title">Gestion des gabarits d'affichage</div>
      <div class="section-subtitle" data-i18n="templates-sub">Modèles assignés à chaque afficheur</div>
    </div>
    <button class="btn-primary" onclick="alert('Fonctionnalité : créer un nouveau gabarit')">+ <span data-i18n="new-template">Nouveau gabarit</span></button>
  </div>

  <div class="gabarits-grid">
    <!-- Gabarit Admin -->
    <div class="gabarit-card">
      <div class="gabarit-preview">
        <div class="preview-bar"><span>LGT Joseph GAILLARD</span><span id="gab-time1"></span></div>
        <div class="preview-body">
          <div class="preview-zone pz1">Infos admin</div>
          <div class="preview-zone pz2">Actualités & annonces</div>
          <div class="preview-zone pz3">Énergie ⚡</div>
        </div>
        <div class="preview-footer">Urgences / Rappels (défilement)</div>
      </div>
      <div class="gabarit-info">
        <div class="gabarit-name" data-i18n="tpl-admin">Gabarit Admin</div>
        <div class="gabarit-meta"><span data-i18n="admin-bld">Bât. Administration</span><span style="color:var(--accent-green)">● <span data-i18n="active">Actif</span></span></div>
      </div>
      <div class="gabarit-actions">
        <button class="btn-sm btn-outline-green" data-i18n="btn-edit">Modifier</button>
        <button class="btn-sm btn-outline" data-i18n="btn-preview">Prévisualiser</button>
      </div>
    </div>

    <!-- Gabarit Élèves -->
    <div class="gabarit-card">
      <div class="gabarit-preview">
        <div class="preview-bar"><span>LGT Joseph GAILLARD</span><span id="gab-time2"></span></div>
        <div class="preview-body">
          <div class="preview-zone pz1" data-i18n="prev-timetable">Emploi du temps</div>
          <div class="preview-zone pz2" data-i18n="prev-schoollife">Infos vie scolaire</div>
          <div class="preview-zone pz3">Météo 🌡️</div>
        </div>
        <div class="preview-footer" data-i18n="prev-alerts">Rappels & Urgences</div>
      </div>
      <div class="gabarit-info">
        <div class="gabarit-name" data-i18n="tpl-students">Gabarit Élèves</div>
        <div class="gabarit-meta"><span>Bât. C & E</span><span style="color:var(--accent-green)">● <span data-i18n="active-x2">Actif (×2)</span></span></div>
      </div>
      <div class="gabarit-actions">
        <button class="btn-sm btn-outline-green" data-i18n="btn-edit">Modifier</button>
        <button class="btn-sm btn-outline" data-i18n="btn-preview">Prévisualiser</button>
      </div>
    </div>

    <!-- Gabarit Ateliers -->
    <div class="gabarit-card">
      <div class="gabarit-preview">
        <div class="preview-bar"><span>LGT Joseph GAILLARD — <span data-i18n="loc-workshops">Ateliers</span> W</span><span id="gab-time3"></span></div>
        <div class="preview-body">
          <div class="preview-zone pz1" data-i18n="prev-wind-prod">Production éolienne ☀️</div>
          <div class="preview-zone pz2" data-i18n="prev-solar-stats">Production solaire + statistiques</div>
          <div class="preview-zone pz3">CO₂ 🌿</div>
        </div>
        <div class="preview-footer" data-i18n="prev-alerts">Urgences / Rappels</div>
      </div>
      <div class="gabarit-info">
        <div class="gabarit-name" data-i18n="tpl-workshops">Gabarit Ateliers</div>
        <div class="gabarit-meta"><span>Bât. W — <span data-i18n="loc-workshops">Ateliers</span></span><span style="color:var(--accent-green)">● <span data-i18n="active">Actif</span></span></div>
      </div>
      <div class="gabarit-actions">
        <button class="btn-sm btn-outline-green" data-i18n="btn-edit">Modifier</button>
        <button class="btn-sm btn-outline" data-i18n="btn-preview">Prévisualiser</button>
      </div>
    </div>

    <!-- Gabarit Énergie -->
    <div class="gabarit-card">
      <div class="gabarit-preview">
        <div class="preview-bar"><span>LGT Joseph GAILLARD — <span data-i18n="loc-physics">Physique</span> S</span><span id="gab-time4"></span></div>
        <div class="preview-body">
          <div class="preview-zone pz2" data-i18n="prev-energy-charts">Graphiques production énergie</div>
          <div class="preview-zone pz1" data-i18n="prev-stats">Stats & historique</div>
          <div class="preview-zone pz3" data-i18n="prev-temp-hygro">Temp. & Hygro.</div>
        </div>
        <div class="preview-footer" data-i18n="prev-alerts">Urgences / Rappels</div>
      </div>
      <div class="gabarit-info">
        <div class="gabarit-name" data-i18n="tpl-energy">Gabarit Énergie</div>
        <div class="gabarit-meta"><span>Bât. S — <span data-i18n="loc-physics">Physique</span></span><span style="color:var(--accent-green)">● <span data-i18n="active">Actif</span></span></div>
      </div>
      <div class="gabarit-actions">
        <button class="btn-sm btn-outline-green" data-i18n="btn-edit">Modifier</button>
        <button class="btn-sm btn-outline" data-i18n="btn-preview">Prévisualiser</button>
      </div>
    </div>

    <!-- Gabarit Réfectoire -->
    <div class="gabarit-card">
      <div class="gabarit-preview">
        <div class="preview-bar"><span>LGT Joseph GAILLARD — <span data-i18n="loc-cafeteria">Réfectoire</span> F</span><span id="gab-time5"></span></div>
        <div class="preview-body">
          <div class="preview-zone pz1" data-i18n="prev-menu">Menu du jour 🍽️</div>
          <div class="preview-zone pz2" data-i18n="prev-general-info">Infos générales & Annonces</div>
          <div class="preview-zone pz3">Énergie ⚡</div>
        </div>
        <div class="preview-footer" data-i18n="prev-alerts">Urgences / Rappels</div>
      </div>
      <div class="gabarit-info">
        <div class="gabarit-name" data-i18n="tpl-cafeteria">Gabarit Réfectoire</div>
        <div class="gabarit-meta"><span>Bât. F — <span data-i18n="loc-cafeteria">Réfectoire</span></span><span style="color:var(--accent-solar)">⚠ <span data-i18n="display-offline">Afficheur hors ligne</span></span></div>
      </div>
      <div class="gabarit-actions">
        <button class="btn-sm btn-outline-green" data-i18n="btn-edit">Modifier</button>
        <button class="btn-sm btn-outline" data-i18n="btn-preview">Prévisualiser</button>
      </div>
    </div>
  </div>
</div>

<!-- ===== BASE DE DONNÉES ===== -->
<div id="page-bdd" class="page">
  <div class="section-header">
    <div>
      <div class="section-title" data-i18n="db-title">Base de données</div>
      <div class="section-subtitle" data-i18n="db-sub">Consultation et administration — MySQL / MariaDB</div>
    </div>
    <span class="badge badge-green" data-i18n="connected">Connecté</span>
  </div>

  <div class="db-grid">
    <div class="db-stat">
      <div class="db-stat-label" data-i18n="db-tables">Tables</div>
      <div class="db-stat-value">7</div>
      <div class="db-stat-sub">infoprod_db</div>
    </div>
    <div class="db-stat">
      <div class="db-stat-label" data-i18n="db-energy-meas">Mesures energie</div>
      <div class="db-stat-value">14 820</div>
      <div class="db-stat-sub" data-i18n="rows">lignes</div>
    </div>
    <div class="db-stat">
      <div class="db-stat-label" data-i18n="db-temp-meas">Mesures temp.</div>
      <div class="db-stat-value">8 432</div>
      <div class="db-stat-sub" data-i18n="rows">lignes</div>
    </div>
    <div class="db-stat">
      <div class="db-stat-label" data-i18n="db-users">Utilisateurs</div>
      <div class="db-stat-value">4</div>
      <div class="db-stat-sub" data-i18n="db-users-sub">admin + 3 consult.</div>
    </div>
    <div class="db-stat">
      <div class="db-stat-label" data-i18n="tab-templates">Gabarits</div>
      <div class="db-stat-value">5</div>
      <div class="db-stat-sub" data-i18n="active-plural">actifs</div>
    </div>
    <div class="db-stat">
      <div class="db-stat-label" data-i18n="db-size">Taille BDD</div>
      <div class="db-stat-value">42 MB</div>
      <div class="db-stat-sub">sur 500 GB</div>
    </div>
  </div>

  <div class="filter-row">
    <select class="filter-select" id="db-table">
      <option value="energie">mesures_energie</option>
      <option value="temp">mesures_temperature</option>
      <option value="gabarits">gabarits</option>
      <option value="users" data-i18n-option="db-users">utilisateurs</option>
    </select>
    <select class="filter-select">
      <option data-i18n="last-24h">Dernières 24h</option>
      <option data-i18n="last-7days">7 derniers jours</option>
      <option data-i18n="last-30days">30 derniers jours</option>
    </select>
    <button class="btn-primary" onclick="loadDbTable()">🔍 <span data-i18n="btn-show">Afficher</span></button>
  </div>

  <div class="table-card">
    <div class="table-head"><span class="table-head-title" id="db-table-title">mesures_energie</span></div>
    <table>
      <thead><tr id="db-thead"></tr></thead>
      <tbody id="db-tbody"></tbody>
    </table>
  </div>
</div>

</main>

<div class="page-footer" data-i18n="footer">
  InfoProd — BTS CIEL IR · LGT Joseph Gaillard · Fort-de-France · Session 2026 · Étudiant 4
</div>

<script>
// ========== TRANSLATION ENGINE ==========
const translations = {
  fr: {
    'logout': 'Déconnexion',
    'sys-active': 'Système actif',
    'tab-dashboard': 'Tableau de bord',
    'tab-energy': 'Production',
    'tab-temp': 'Températures',
    'tab-templates': 'Gabarits',
    'tab-db': 'Base de données',
    'dash-title': 'Tableau de bord — LGT Joseph Gaillard',
    'dash-sub': "Vue d'ensemble du système InfoProd",
    'kpi-solar': 'Production solaire',
    'kpi-wind': 'Production éolienne',
    'kpi-battery': 'Énergie stockée',
    'kpi-co2': 'CO₂ économisé (jour)',
    'kpi-exttemp': 'Température ext.',
    'kpi-displays': 'Afficheurs actifs',
    'vs-yesterday': 'vs hier',
    'charging': 'Charge en cours',
    'cumulated': 'Cumulé',
    'humidity': 'Humidité',
    'offline': 'Hors ligne',
    'cafeteria': 'Réfectoire',
    'chart-daily': 'Production journalière (kW)',
    'today': "Aujourd'hui",
    'energy-share': 'Répartition énergie',
    'solar': 'Solaire',
    'wind': 'Éolien',
    'battery': 'Batterie',
    'displays-state': 'État des afficheurs',
    'updated-at': 'Mis à jour à',
    'th-building': 'Bâtiment',
    'th-location': 'Emplacement',
    'th-lastping': 'Dernier ping',
    'th-template': 'Gabarit',
    'th-status': 'Statut',
    'admin-bld': 'Administration',
    'loc-accueil': 'Accueil',
    'tpl-admin': 'Gabarit Admin',
    'active': 'Actif',
    'loc-vs-pre': 'Vie scolaire (pré-bac)',
    'tpl-students': 'Gabarit Élèves',
    'loc-cafeteria': 'Réfectoire',
    'tpl-cafeteria': 'Gabarit Réfectoire',
    'loc-vs-post': 'Vie scolaire (post-bac)',
    'loc-physics': 'Physique',
    'tpl-energy': 'Gabarit Énergie',
    'loc-workshops': 'Ateliers',
    'tpl-workshops': 'Gabarit Ateliers',
    'energy-title': "Production d'énergie renouvelable",
    'energy-sub': 'Sites 1 & 2 — Photovoltaïque + Éolien',
    'site1-solar': 'Site 1 — Solaire',
    'site2-solar': 'Site 2 — Solaire (Bât. S)',
    'site1-wind': 'Site 1 — Éolienne',
    'battery-site1': 'Batterie Site 1',
    'co2-month': 'CO₂ évité — Mois',
    'annual-prod': 'Production annuelle',
    'day-cumul': 'Cumul jour',
    'stored': 'stockés',
    'grid': 'réseau',
    'since': 'Depuis le',
    'chart-7days': 'Historique 7 jours — kWh/jour',
    'week': 'Semaine',
    'recent-readings': 'Relevés récents — Site 1 & 2',
    'th-timestamp': 'Horodatage',
    'th-site': 'Site',
    'th-source': 'Source',
    'th-power': 'Puissance (kW)',
    'th-cumul-energy': 'Énergie cumulée (kWh)',
    'th-voltage': 'Tension (V)',
    'temp-title': 'Mesures de température',
    'temp-sub': 'Capteurs distribuées — mise à jour toutes les 60s',
    'chart-temp-24h': 'Évolution températures (24h)',
    'temp-log': 'Journal des mesures — Dernières 24h',
    'th-sensor': 'Capteur',
    'th-temp-c': 'Température (°C)',
    'th-humidity-pct': 'Humidité (%)',
    'templates-title': "Gestion des gabarits d'affichage",
    'templates-sub': 'Modèles assignés à chaque afficheur',
    'new-template': 'Nouveau gabarit',
    'btn-edit': 'Modifier',
    'btn-preview': 'Prévisualiser',
    'active-x2': 'Actif (×2)',
    'prev-timetable': 'Emploi du temps',
    'prev-schoollife': 'Infos vie scolaire',
    'prev-alerts': 'Urgences / Rappels',
    'prev-wind-prod': 'Production éolienne ☀️',
    'prev-solar-stats': 'Production solaire + statistiques',
    'prev-energy-charts': 'Graphiques production énergie',
    'prev-stats': 'Stats & historique',
    'prev-temp-hygro': 'Temp. & Hygro.',
    'prev-menu': 'Menu du jour 🍽️',
    'prev-general-info': 'Infos générales & Annonces',
    'display-offline': 'Afficheur hors ligne',
    'db-title': 'Base de données',
    'db-sub': 'Consultation et administration — MySQL / MariaDB',
    'connected': 'Connecté',
    'db-tables': 'Tables',
    'db-energy-meas': 'Mesures energie',
    'db-temp-meas': 'Mesures temp.',
    'db-users': 'Utilisateurs',
    'db-users-sub': 'admin + 3 consult.',
    'active-plural': 'actifs',
    'db-size': 'Taille BDD',
    'last-24h': 'Dernières 24h',
    'last-7days': '7 derniers jours',
    'last-30days': '30 derniers jours',
    'rows': 'lignes',
    'btn-show': 'Afficher',
    'footer': 'InfoProd — BTS CIEL IR · LGT Joseph Gaillard · Fort-de-France · Session 2026 · Étudiant 4',
  },
  en: {
    'logout': 'Logout',
    'sys-active': 'System active',
    'tab-dashboard': 'Dashboard',
    'tab-energy': 'Production',
    'tab-temp': 'Temperatures',
    'tab-templates': 'Templates',
    'tab-db': 'Database',
    'dash-title': 'Dashboard — LGT Joseph Gaillard',
    'dash-sub': 'InfoProd system overview',
    'kpi-solar': 'Solar production',
    'kpi-wind': 'Wind production',
    'kpi-battery': 'Stored energy',
    'kpi-co2': 'CO₂ saved (day)',
    'kpi-exttemp': 'Outdoor temp.',
    'kpi-displays': 'Active displays',
    'vs-yesterday': 'vs yesterday',
    'charging': 'Charging',
    'cumulated': 'Cumulated',
    'humidity': 'Humidity',
    'offline': 'Offline',
    'cafeteria': 'Cafeteria',
    'chart-daily': 'Daily production (kW)',
    'today': 'Today',
    'energy-share': 'Energy breakdown',
    'solar': 'Solar',
    'wind': 'Wind',
    'battery': 'Battery',
    'displays-state': 'Display status',
    'updated-at': 'Updated at',
    'th-building': 'Building',
    'th-location': 'Location',
    'th-lastping': 'Last ping',
    'th-template': 'Template',
    'th-status': 'Status',
    'admin-bld': 'Administration',
    'loc-accueil': 'Reception',
    'tpl-admin': 'Admin Template',
    'active': 'Active',
    'loc-vs-pre': 'Student services (pre-bac)',
    'tpl-students': 'Student Template',
    'loc-cafeteria': 'Cafeteria',
    'tpl-cafeteria': 'Cafeteria Template',
    'loc-vs-post': 'Student services (post-bac)',
    'loc-physics': 'Physics',
    'tpl-energy': 'Energy Template',
    'loc-workshops': 'Workshops',
    'tpl-workshops': 'Workshop Template',
    'energy-title': 'Renewable energy production',
    'energy-sub': 'Sites 1 & 2 — Photovoltaic + Wind',
    'site1-solar': 'Site 1 — Solar',
    'site2-solar': 'Site 2 — Solar (Bldg. S)',
    'site1-wind': 'Site 1 — Wind',
    'battery-site1': 'Battery Site 1',
    'co2-month': 'CO₂ avoided — Month',
    'annual-prod': 'Annual production',
    'day-cumul': 'Daily total',
    'stored': 'stored',
    'grid': 'grid',
    'since': 'Since',
    'chart-7days': '7-day history — kWh/day',
    'week': 'Week',
    'recent-readings': 'Recent readings — Site 1 & 2',
    'th-timestamp': 'Timestamp',
    'th-site': 'Site',
    'th-source': 'Source',
    'th-power': 'Power (kW)',
    'th-cumul-energy': 'Cumul. energy (kWh)',
    'th-voltage': 'Voltage (V)',
    'temp-title': 'Temperature readings',
    'temp-sub': 'Distributed sensors — updated every 60s',
    'chart-temp-24h': 'Temperature trends (24h)',
    'temp-log': 'Measurement log — Last 24h',
    'th-sensor': 'Sensor',
    'th-temp-c': 'Temperature (°C)',
    'th-humidity-pct': 'Humidity (%)',
    'templates-title': 'Display template management',
    'templates-sub': 'Models assigned to each display',
    'new-template': 'New template',
    'btn-edit': 'Edit',
    'btn-preview': 'Preview',
    'active-x2': 'Active (×2)',
    'prev-timetable': 'Timetable',
    'prev-schoollife': 'Student life info',
    'prev-alerts': 'Emergencies / Reminders',
    'prev-wind-prod': 'Wind production ☀️',
    'prev-solar-stats': 'Solar production + statistics',
    'prev-energy-charts': 'Energy production charts',
    'prev-stats': 'Stats & history',
    'prev-temp-hygro': 'Temp. & Hygro.',
    'prev-menu': "Today's menu 🍽️",
    'prev-general-info': 'General info & Announcements',
    'display-offline': 'Display offline',
    'db-title': 'Database',
    'db-sub': 'Consultation & administration — MySQL / MariaDB',
    'connected': 'Connected',
    'db-tables': 'Tables',
    'db-energy-meas': 'Energy readings',
    'db-temp-meas': 'Temp. readings',
    'db-users': 'Users',
    'db-users-sub': 'admin + 3 viewers',
    'active-plural': 'active',
    'db-size': 'DB size',
    'last-24h': 'Last 24h',
    'last-7days': 'Last 7 days',
    'last-30days': 'Last 30 days',
    'rows': 'rows',
    'btn-show': 'Show',
    'footer': 'InfoProd — BTS CIEL IR · LGT Joseph Gaillard · Fort-de-France · 2026 · Student 4',
  }
};

let currentLang = 'fr';

function applyLang(lang) {
  const t = translations[lang];
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (t[key] !== undefined) el.textContent = t[key];
  });
  // Update toggle button label
  const btn = document.getElementById('lang-toggle');
  if (btn) btn.textContent = lang === 'fr' ? '🌐 EN' : '🌐 FR';
  // Update <html> lang attribute
  document.documentElement.lang = lang;
}

function toggleLang() {
  currentLang = currentLang === 'fr' ? 'en' : 'fr';
  applyLang(currentLang);
}
// Apply default on load
applyLang('fr');
</script>

<script>
// ===== CLOCK =====
function updateClock() {
  const now = new Date();
  const days = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
  const months = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
  document.getElementById('clock-date').textContent =
    `${days[now.getDay()]} ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
  document.getElementById('clock-time').textContent =
    now.toLocaleTimeString('fr-FR');
  const t = now.toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit'});
  ['gab-time1','gab-time2','gab-time3','gab-time4','gab-time5'].forEach(id => {
    const el = document.getElementById(id);
    if(el) el.textContent = t;
  });
  const lu = document.getElementById('last-update-time');
  if(lu) lu.textContent = t;
}
updateClock();
setInterval(updateClock, 1000);

// ===== TABS =====
function showPage(name, tabEl) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  document.getElementById('page-' + name).classList.add('active');
  if (tabEl) tabEl.classList.add('active');
}

// ===== CHART HELPER =====
function drawLineChart(svgId, datasets, labels, viewW, viewH) {
  const svg = document.getElementById(svgId);
  if(!svg) return;
  const pw = viewW || 500, ph = viewH || 160;
  const pad = {t:10, r:10, b:30, l:40};
  const cw = pw - pad.l - pad.r, ch = ph - pad.t - pad.b;

  const allVals = datasets.flatMap(d => d.values);
  const minV = 0, maxV = Math.max(...allVals) * 1.15;

  const xs = labels.map((_, i) => pad.l + (i / (labels.length - 1)) * cw);
  const ys = (v) => pad.t + ch - ((v - minV) / (maxV - minV)) * ch;

  let html = '';
  // Grid lines
  for(let i = 0; i <= 4; i++) {
    const y = pad.t + (i / 4) * ch;
    const val = ((maxV - minV) * (1 - i/4) + minV).toFixed(1);
    html += `<line x1="${pad.l}" y1="${y}" x2="${pad.l+cw}" y2="${y}" stroke="#1e3048" stroke-width="1"/>`;
    html += `<text x="${pad.l - 4}" y="${y+4}" text-anchor="end" fill="#6b8599" font-size="9" font-family="Space Mono,monospace">${val}</text>`;
  }
  // X labels
  labels.forEach((l, i) => {
    if(i % Math.ceil(labels.length / 8) === 0 || i === labels.length-1) {
      html += `<text x="${xs[i]}" y="${pad.t+ch+18}" text-anchor="middle" fill="#6b8599" font-size="9" font-family="Space Mono,monospace">${l}</text>`;
    }
  });

  datasets.forEach(d => {
    const pts = d.values.map((v, i) => `${xs[i]},${ys(v)}`).join(' ');
    const apts = `${xs[0]},${ys(0)} ${pts} ${xs[xs.length-1]},${ys(0)}`;
    html += `<polygon points="${apts}" fill="${d.color}" class="chart-area"/>`;
    html += `<polyline points="${pts}" class="chart-line" stroke="${d.color}"/>`;
    // dots on hover simulation - just last point
    const lx = xs[d.values.length-1], ly = ys(d.values[d.values.length-1]);
    html += `<circle cx="${lx}" cy="${ly}" r="4" fill="${d.color}" stroke="#0a0f14" stroke-width="2"/>`;
  });

  svg.innerHTML = html;
}

// ===== GENERATE DATA =====
function genHours() {
  const h = [];
  for(let i=0;i<24;i++) h.push(i.toString().padStart(2,'0')+':00');
  return h;
}
function genDays() {
  const d = ['L','Ma','Me','J','V','S','D'];
  return d;
}
function solarCurve(n) {
  return Array.from({length:n}, (_,i) => {
    const h = i * (24/n);
    if(h < 6 || h > 19) return 0;
    const peak = Math.sin(Math.PI * (h-6)/13) * (3.2 + Math.random()*0.8);
    return Math.max(0, peak);
  });
}
function windCurve(n) {
  let v = 0.8;
  return Array.from({length:n}, () => {
    v = Math.max(0, Math.min(2.5, v + (Math.random()-0.48)*0.3));
    return parseFloat(v.toFixed(2));
  });
}

// ===== CHARTS INIT =====
setTimeout(() => {
  const hrs = genHours();
  drawLineChart('chart-prod', [
    { values: solarCurve(24), color: '#f5a623' },
    { values: windCurve(24), color: '#4fc3f7' }
  ], hrs, 500, 160);

  const days = genDays();
  drawLineChart('chart-week', [
    { values: days.map(() => (15 + Math.random()*10).toFixed(1)*1), color: '#f5a623' },
    { values: days.map(() => (8 + Math.random()*6).toFixed(1)*1), color: '#4fc3f7' }
  ], days, 700, 160);

  const tempHrs = genHours();
  drawLineChart('chart-temp', [
    { values: tempHrs.map((_,i) => 26 + Math.sin(i/4)*3 + Math.random()*0.5), color: '#ff7043' },
    { values: tempHrs.map((_,i) => 24 + Math.sin(i/4)*2 + Math.random()*0.4), color: '#4fc3f7' }
  ], tempHrs, 700, 160);
}, 100);

// ===== LIVE KPI UPDATE =====
setInterval(() => {
  const solar = (3 + Math.random()*0.8).toFixed(2);
  const wind = (1 + Math.random()*0.5).toFixed(2);
  const bat = Math.floor(72 + Math.random()*4);
  const co2 = (18 + Math.random()*1).toFixed(1);
  const temp = (28 + Math.random()*1.5).toFixed(1);
  document.getElementById('kpi-solar').textContent = solar;
  document.getElementById('kpi-wind').textContent = wind;
  document.getElementById('kpi-bat').textContent = bat;
  document.getElementById('kpi-co2').textContent = co2;
  document.getElementById('kpi-temp').textContent = temp;
}, 5000);

// ===== TEMPERATURE CARDS =====
const sensors = [
  { name: 'Capteur S1', loc: 'Ateliers (W)', t: 29.1, h: 76 },
  { name: 'Capteur S2', loc: 'Physique (S)', t: 27.4, h: 72 },
  { name: 'Capteur S3', loc: 'Administration', t: 22.8, h: 58 },
  { name: 'Capteur S4', loc: 'Vie scolaire (C)', t: 28.3, h: 74 },
  { name: 'Capteur S5', loc: 'Extérieur', t: 31.2, h: 82 },
  { name: 'Capteur S6', loc: 'Salle serveur', t: 20.1, h: 45 },
];

function tempToColor(t) {
  if(t < 22) return '#4fc3f7';
  if(t < 27) return '#00e5a0';
  if(t < 30) return '#f5a623';
  return '#ff7043';
}
function tempToStrokeDash(t, min=15, max=40) {
  const pct = (t - min) / (max - min);
  const circ = 2 * Math.PI * 36;
  const filled = pct * circ;
  return `${filled.toFixed(1)} ${(circ - filled).toFixed(1)}`;
}

const tg = document.getElementById('temp-grid');
sensors.forEach(s => {
  const col = tempToColor(s.t);
  tg.innerHTML += `
  <div class="temp-card">
    <div class="temp-location">${s.name}</div>
    <div class="temp-gauge">
      <svg viewBox="0 0 100 100">
        <circle cx="50" cy="50" r="36" fill="none" stroke="#1e3048" stroke-width="10"/>
        <circle cx="50" cy="50" r="36" fill="none" stroke="${col}" stroke-width="10"
          stroke-dasharray="${tempToStrokeDash(s.t)}"
          stroke-dashoffset="${2*Math.PI*36*0.25}" transform="rotate(-90 50 50)"
          stroke-linecap="round"/>
      </svg>
      <div class="temp-num">
        <span class="temp-big" style="color:${col}">${s.t}</span>
        <span class="temp-unit-small">°C</span>
      </div>
    </div>
    <div style="font-size:12px;color:var(--text-mid);font-weight:600">${s.loc}</div>
    <div class="temp-humidity">Humidité : <span>${s.h}%</span></div>
    <div class="temp-updated">Mis à jour il y a 12s</div>
  </div>`;
});

// ===== ENERGY TABLE =====
const tbE = document.getElementById('tb-energy');
const sites = ['Site 1','Site 1','Site 2','Site 2','Site 1'];
const sources = ['Solaire','Éolien','Solaire','Solaire','Éolien'];
const now = new Date();
for(let i=0;i<8;i++) {
  const d = new Date(now - i*600000);
  const ts = d.toLocaleString('fr-FR');
  const si = sites[i%5], so = sources[i%5];
  const pw = (Math.random()*3+0.5).toFixed(2);
  const cum = (20-i*0.8).toFixed(1);
  const v = (220 + Math.random()*5).toFixed(1);
  tbE.innerHTML += `<tr>
    <td class="td-mono">${ts}</td>
    <td>${si}</td>
    <td>${so}</td>
    <td class="td-mono" style="color:var(--accent-solar)">${pw}</td>
    <td class="td-mono">${cum}</td>
    <td class="td-mono">${v}</td>
  </tr>`;
}

// ===== TEMP TABLE =====
const tbT = document.getElementById('tb-temp');
for(let i=0;i<8;i++) {
  const d = new Date(now - i*900000);
  const s = sensors[i%sensors.length];
  tbT.innerHTML += `<tr>
    <td class="td-mono">${d.toLocaleString('fr-FR')}</td>
    <td>${s.name}</td>
    <td>${s.loc}</td>
    <td class="td-mono" style="color:${tempToColor(s.t)}">${(s.t+Math.random()*0.3-0.15).toFixed(1)}</td>
    <td class="td-mono">${s.h}</td>
  </tr>`;
}

// ===== DB TABLE =====
const dbSchemas = {
  energie: {
    cols: ['id','horodatage','site_id','source','puissance_kw','energie_kwh','tension_v'],
    rows: () => Array.from({length:8}, (_,i) => {
      const d = new Date(now - i*600000);
      return [i+1, d.toLocaleString('fr-FR'), i%2+1, i%2===0?'Solaire':'Éolien',
        (Math.random()*3+0.5).toFixed(2),(20-i*0.8).toFixed(2),(220+Math.random()*5).toFixed(1)];
    })
  },
  temp: {
    cols: ['id','horodatage','capteur_id','emplacement','temperature_c','humidite_pct'],
    rows: () => Array.from({length:8}, (_,i) => {
      const d = new Date(now - i*900000);
      const s = sensors[i%sensors.length];
      return [i+1, d.toLocaleString('fr-FR'), `S${i%6+1}`, s.loc,
        (s.t+Math.random()*0.3-0.15).toFixed(1), s.h];
    })
  },
  gabarits: {
    cols: ['id','nom','batiment','afficheur_ip','actif','derniere_modif'],
    rows: () => [
      [1,'Gabarit Admin','Administration','192.168.1.51',1,'2026-01-15'],
      [2,'Gabarit Élèves','Bât. C','192.168.1.52',1,'2026-01-15'],
      [3,'Gabarit Réfectoire','Bât. F','192.168.1.53',0,'2026-01-10'],
      [4,'Gabarit Ateliers','Bât. W','192.168.1.56',1,'2026-02-03'],
      [5,'Gabarit Énergie','Bât. S','192.168.1.55',1,'2026-02-10'],
    ]
  },
  users: {
    cols: ['id','nom','role','dernier_acces','ip'],
    rows: () => [
      [1,'admin','Administrateur','2026-03-03','192.168.1.10'],
      [2,'etudiant4','Étudiants','2026-03-03','192.168.1.25'],
      [3,'administration','Consultation','2026-03-02','192.168.1.5'],
      [4,'enseignant1','Consultation','2026-03-01','192.168.1.30'],
    ]
  }
};

function loadDbTable() {
  const key = document.getElementById('db-table').value;
  const schema = dbSchemas[key];
  document.getElementById('db-table-title').textContent = key === 'energie' ? 'mesures_energie' :
    key === 'temp' ? 'mesures_temperature' : key === 'gabarits' ? 'gabarits' : 'utilisateurs';
  const thead = document.getElementById('db-thead');
  thead.innerHTML = schema.cols.map(c => `<th>${c}</th>`).join('');
  const tbody = document.getElementById('db-tbody');
  tbody.innerHTML = schema.rows().map(row =>
    `<tr>${row.map(v => `<td class="td-mono">${v}</td>`).join('')}</tr>`
  ).join('');
}
loadDbTable();
</script>

</body>
</html>
