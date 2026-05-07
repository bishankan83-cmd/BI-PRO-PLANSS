<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TirePlan Pro — Production Planning System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
/* ── DESIGN SYSTEM ─────────────────────────────────── */
:root {
  --bg:        #0a0c0f;
  --surface:   #111418;
  --surface2:  #181d24;
  --border:    #1f2630;
  --border2:   #2a3340;
  --amber:     #f5a623;
  --amber-dim: #c07d0f;
  --green:     #22c55e;
  --red:       #ef4444;
  --blue:      #38bdf8;
  --text:      #e2e8f0;
  --muted:     #64748b;
  --muted2:    #94a3b8;
  --mono:      'JetBrains Mono', monospace;
  --sans:      'Syne', sans-serif;
  --radius:    6px;
  --shadow:    0 4px 24px rgba(0,0,0,.5);
}
* { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 14px; }
body {
  background: var(--bg);
  color: var(--text);
  font-family: var(--sans);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* ── SCROLLBAR ──────────────────────────────────────── */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: var(--bg); }
::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 3px; }

/* ── HEADER ─────────────────────────────────────────── */
.header {
  display: flex; align-items: center; gap: 20px;
  padding: 0 28px;
  height: 58px;
  border-bottom: 1px solid var(--border);
  background: var(--surface);
  position: sticky; top: 0; z-index: 100;
}
.header-logo {
  display: flex; align-items: center; gap: 10px;
  font-size: 1.1rem; font-weight: 800; letter-spacing: -.5px;
  color: var(--amber);
}
.header-logo .dot { color: var(--text); }
.header-nav { display: flex; gap: 4px; margin-left: auto; }
.nav-btn {
  padding: 6px 14px; border-radius: var(--radius);
  border: none; background: transparent;
  color: var(--muted2); font-family: var(--sans);
  font-size: .85rem; font-weight: 600;
  cursor: pointer; letter-spacing: .3px;
  transition: all .15s;
}
.nav-btn:hover { color: var(--text); background: var(--surface2); }
.nav-btn.active { color: var(--amber); background: rgba(245,166,35,.1); }
.badge {
  display: inline-flex; align-items: center; justify-content: center;
  width: 18px; height: 18px; border-radius: 50%;
  background: var(--amber); color: #000;
  font-size: .65rem; font-weight: 700; margin-left: 5px;
}

/* ── LAYOUT ─────────────────────────────────────────── */
.main { display: flex; flex: 1; overflow: hidden; }
.page { display: none; flex: 1; overflow-y: auto; padding: 28px; }
.page.active { display: block; }

/* ── STAT CARDS ─────────────────────────────────────── */
.stats-row { display: grid; grid-template-columns: repeat(3,1fr); gap: 14px; margin-bottom: 28px; }
.stat-card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--radius); padding: 18px 20px;
  display: flex; align-items: flex-start; gap: 14px;
  animation: fadeUp .3s ease both;
}
.stat-icon {
  width: 38px; height: 38px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.2rem; flex-shrink: 0;
}
.stat-num { font-size: 1.8rem; font-weight: 800; line-height: 1; font-family: var(--mono); }
.stat-label { color: var(--muted2); font-size: .78rem; font-weight: 600; letter-spacing: .5px; text-transform: uppercase; margin-top: 3px; }

/* ── SECTION HEADER ──────────────────────────────────── */
.section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.section-title { font-size: 1rem; font-weight: 700; color: var(--text); }

/* ── CARD ─────────────────────────────────────────────── */
.card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--radius); overflow: hidden;
}
.card-header {
  padding: 14px 18px; border-bottom: 1px solid var(--border);
  display: flex; align-items: center; gap: 10px;
  font-weight: 700; font-size: .9rem;
}
.card-body { padding: 18px; }

/* ── FORM ELEMENTS ────────────────────────────────────── */
.form-group { margin-bottom: 16px; }
.form-label {
  display: block; font-size: .75rem; font-weight: 700;
  letter-spacing: .6px; text-transform: uppercase;
  color: var(--muted2); margin-bottom: 6px;
}
.form-control {
  width: 100%; padding: 9px 12px;
  background: var(--bg); border: 1px solid var(--border2);
  border-radius: var(--radius);
  color: var(--text); font-family: var(--sans); font-size: .9rem;
  transition: border-color .15s;
}
.form-control:focus { outline: none; border-color: var(--amber); }
.form-control::placeholder { color: var(--muted); }
select.form-control { cursor: pointer; }

.btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 9px 18px; border-radius: var(--radius);
  border: none; font-family: var(--sans);
  font-size: .85rem; font-weight: 700; cursor: pointer;
  transition: all .15s; white-space: nowrap;
}
.btn-primary { background: var(--amber); color: #000; }
.btn-primary:hover { background: #e8951a; }
.btn-secondary { background: var(--surface2); color: var(--text); border: 1px solid var(--border2); }
.btn-secondary:hover { background: var(--border2); }
.btn-success { background: rgba(34,197,94,.15); color: var(--green); border: 1px solid rgba(34,197,94,.3); }
.btn-danger { background: rgba(239,68,68,.12); color: var(--red); border: 1px solid rgba(239,68,68,.3); }
.btn-sm { padding: 5px 12px; font-size: .78rem; }
.btn:disabled { opacity: .45; cursor: not-allowed; }

/* ── SEARCH BOX ───────────────────────────────────────── */
.search-wrapper { position: relative; }
.search-wrapper .search-icon {
  position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
  color: var(--muted); pointer-events: none;
}
.search-wrapper .form-control { padding-left: 34px; }
.search-dropdown {
  position: absolute; top: calc(100% + 4px); left: 0; right: 0;
  background: var(--surface2); border: 1px solid var(--border2);
  border-radius: var(--radius); max-height: 280px; overflow-y: auto;
  z-index: 200; box-shadow: var(--shadow);
}
.search-item {
  padding: 10px 14px; cursor: pointer; display: flex; gap: 10px;
  align-items: flex-start; border-bottom: 1px solid var(--border);
  transition: background .1s;
}
.search-item:last-child { border-bottom: none; }
.search-item:hover { background: rgba(245,166,35,.07); }
.search-item .icode { font-family: var(--mono); font-size: .78rem; color: var(--amber); font-weight: 600; }
.search-item .desc { font-size: .85rem; color: var(--text); }
.search-item .time { font-family: var(--mono); font-size: .75rem; color: var(--muted2); }

/* ── OPTION CARDS ─────────────────────────────────────── */
.options-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(280px,1fr)); gap: 12px;
  max-height: 480px; overflow-y: auto; padding: 2px;
}
.option-card {
  background: var(--surface2); border: 2px solid var(--border);
  border-radius: var(--radius); padding: 14px; cursor: pointer;
  transition: all .15s; position: relative;
}
.option-card:hover { border-color: var(--amber); }
.option-card.selected { border-color: var(--amber); background: rgba(245,166,35,.06); }
.option-card .opt-badge {
  position: absolute; top: 10px; right: 10px;
  background: var(--green); color: #000;
  font-size: .65rem; font-weight: 800;
  padding: 2px 7px; border-radius: 10px; letter-spacing: .5px;
}
.opt-row { display: flex; align-items: center; gap: 8px; margin-bottom: 7px; font-size: .82rem; }
.opt-label { color: var(--muted2); font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; min-width: 55px; }
.opt-val { font-family: var(--mono); color: var(--text); font-weight: 600; }
.opt-time { font-size: .78rem; color: var(--muted2); margin-top: 8px; border-top: 1px solid var(--border); padding-top: 8px; }
.wait-chip {
  display: inline-block; padding: 2px 8px; border-radius: 10px;
  font-size: .7rem; font-weight: 700;
}
.wait-0  { background: rgba(34,197,94,.15);  color: var(--green); }
.wait-sm { background: rgba(245,166,35,.15); color: var(--amber); }
.wait-lg { background: rgba(239,68,68,.12);  color: var(--red); }

/* ── SCHEDULE TABLE ───────────────────────────────────── */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: .85rem; }
thead th {
  padding: 10px 14px; background: var(--surface2);
  border-bottom: 1px solid var(--border);
  font-size: .72rem; font-weight: 700; letter-spacing: .6px;
  text-transform: uppercase; color: var(--muted2); text-align: left;
  white-space: nowrap;
}
tbody tr { border-bottom: 1px solid var(--border); transition: background .1s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: rgba(255,255,255,.02); }
td { padding: 11px 14px; vertical-align: middle; }
.mono { font-family: var(--mono); }

/* ── STATUS CHIPS ─────────────────────────────────────── */
.status-chip {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 3px 9px; border-radius: 10px;
  font-size: .72rem; font-weight: 700; letter-spacing: .3px;
}
.status-planned     { background: rgba(56,189,248,.1);  color: var(--blue);  border: 1px solid rgba(56,189,248,.2); }
.status-in_progress { background: rgba(245,166,35,.1);  color: var(--amber); border: 1px solid rgba(245,166,35,.2); }
.status-completed   { background: rgba(34,197,94,.1);   color: var(--green); border: 1px solid rgba(34,197,94,.2); }
.status-cancelled   { background: rgba(239,68,68,.1);   color: var(--red);   border: 1px solid rgba(239,68,68,.2); }

/* ── PROGRESS BAR ─────────────────────────────────────── */
.step-indicator {
  display: flex; align-items: center; margin-bottom: 24px; gap: 0;
}
.step {
  display: flex; align-items: center; gap: 8px;
  flex: 1; position: relative;
}
.step-num {
  width: 28px; height: 28px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: .75rem; font-weight: 800;
  border: 2px solid var(--border2); background: var(--surface2);
  color: var(--muted); transition: all .25s; flex-shrink: 0;
}
.step.done .step-num  { background: var(--green); border-color: var(--green); color: #000; }
.step.active .step-num { background: var(--amber); border-color: var(--amber); color: #000; }
.step-label { font-size: .78rem; font-weight: 600; color: var(--muted2); }
.step.active .step-label { color: var(--amber); }
.step.done  .step-label { color: var(--green); }
.step-line { flex: 1; height: 1px; background: var(--border2); margin: 0 6px; }
.step-line.done { background: var(--green); }

/* ── FILTER ROW ───────────────────────────────────────── */
.filter-row { display: flex; gap: 10px; margin-bottom: 16px; align-items: flex-end; flex-wrap: wrap; }
.filter-row .form-group { margin-bottom: 0; min-width: 140px; }

/* ── EMPTY STATE ──────────────────────────────────────── */
.empty-state {
  text-align: center; padding: 60px 20px; color: var(--muted);
}
.empty-icon { font-size: 3rem; margin-bottom: 12px; opacity: .4; }

/* ── TOAST ──────────────────────────────────────────────  */
#toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 999; display: flex; flex-direction: column; gap: 8px; }
.toast {
  padding: 12px 18px; border-radius: var(--radius);
  font-size: .85rem; font-weight: 600;
  animation: toastIn .2s ease; box-shadow: var(--shadow);
  display: flex; align-items: center; gap: 8px;
}
.toast-success { background: rgba(34,197,94,.15); color: var(--green); border: 1px solid rgba(34,197,94,.3); }
.toast-error   { background: rgba(239,68,68,.12);  color: var(--red);   border: 1px solid rgba(239,68,68,.3); }
.toast-info    { background: rgba(56,189,248,.1);  color: var(--blue);  border: 1px solid rgba(56,189,248,.2); }

/* ── ANIMATIONS ───────────────────────────────────────── */
@keyframes fadeUp   { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:none; } }
@keyframes toastIn  { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:none; } }
@keyframes spin     { to { transform: rotate(360deg); } }
.spin { animation: spin .8s linear infinite; display: inline-block; }

/* ── LOADER ───────────────────────────────────────────── */
.loader {
  display: inline-block; width: 16px; height: 16px;
  border: 2px solid var(--border2);
  border-top-color: var(--amber); border-radius: 50%;
  animation: spin .7s linear infinite;
}

/* ── MODAL ────────────────────────────────────────────── */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,.7);
  z-index: 500; display: flex; align-items: center; justify-content: center;
  padding: 20px;
}
.modal {
  background: var(--surface); border: 1px solid var(--border2);
  border-radius: 10px; width: 100%; max-width: 480px;
  box-shadow: var(--shadow); animation: fadeUp .2s ease;
}
.modal-header {
  padding: 16px 20px; border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
  font-weight: 700;
}
.modal-body { padding: 20px; }
.modal-footer { padding: 14px 20px; border-top: 1px solid var(--border); display: flex; gap: 8px; justify-content: flex-end; }
.close-btn { background: none; border: none; color: var(--muted2); cursor: pointer; font-size: 1.2rem; }

/* ── TIMELINE MINI ────────────────────────────────────── */
.timeline-bar {
  height: 6px; border-radius: 3px;
  background: var(--border2); overflow: hidden; margin-top: 4px;
}
.timeline-fill {
  height: 100%; border-radius: 3px;
  background: linear-gradient(90deg, var(--amber), var(--green));
}

/* ── GANTT SECTION ────────────────────────────────────── */
.gantt-wrap {
  overflow-x: auto; margin-top: 8px;
  border: 1px solid var(--border); border-radius: var(--radius);
}
</style>
</head>
<body>

<!-- HEADER -->
<header class="header">
  <div class="header-logo">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/>
      <line x1="12" y1="2" x2="12" y2="5"/><line x1="12" y1="19" x2="12" y2="22"/>
      <line x1="2" y1="12" x2="5" y2="12"/><line x1="19" y1="12" x2="22" y2="12"/>
    </svg>
    TirePlan<span class="dot">.</span>Pro
  </div>
  <nav class="header-nav">
    <button class="nav-btn active" onclick="showPage('dashboard')">📊 Dashboard</button>
    <button class="nav-btn" onclick="showPage('planner')">⚙️ New Plan</button>
    <button class="nav-btn" onclick="showPage('schedule')">📅 Schedule</button>
  </nav>
</header>

<div class="main">

<!-- ══════════════════════════════════════════════════════════ -->
<!-- PAGE: DASHBOARD                                            -->
<!-- ══════════════════════════════════════════════════════════ -->
<section class="page active" id="page-dashboard">
  <div class="stats-row" id="stats-row">
    <div class="stat-card" style="animation-delay:.05s">
      <div class="stat-icon" style="background:rgba(245,166,35,.12)">🔧</div>
      <div><div class="stat-num" id="s-planned">—</div><div class="stat-label">Planned Runs</div></div>
    </div>
    <div class="stat-card" style="animation-delay:.1s">
      <div class="stat-icon" style="background:rgba(245,166,35,.15)">⚡</div>
      <div><div class="stat-num" id="s-progress">—</div><div class="stat-label">In Progress</div></div>
    </div>
    <div class="stat-card" style="animation-delay:.15s">
      <div class="stat-icon" style="background:rgba(34,197,94,.12)">✅</div>
      <div><div class="stat-num" id="s-done">—</div><div class="stat-label">Completed Today</div></div>
    </div>
    <div class="stat-card" style="animation-delay:.2s">
      <div class="stat-icon" style="background:rgba(56,189,248,.1)">🧱</div>
      <div><div class="stat-num" id="s-molds">—</div><div class="stat-label">Active Molds</div></div>
    </div>
    <div class="stat-card" style="animation-delay:.25s">
      <div class="stat-icon" style="background:rgba(56,189,248,.1)">🏭</div>
      <div><div class="stat-num" id="s-cav">—</div><div class="stat-label">Cavities Available</div></div>
    </div>
    <div class="stat-card" style="animation-delay:.3s">
      <div class="stat-icon" style="background:rgba(245,166,35,.1)">🛞</div>
      <div><div class="stat-num" id="s-tires">—</div><div class="stat-label">Tire SKUs</div></div>
    </div>
  </div>

  <!-- Upcoming plans preview -->
  <div class="card">
    <div class="card-header">
      📋 Upcoming Production Runs
      <button class="btn btn-secondary btn-sm" style="margin-left:auto" onclick="showPage('schedule')">View All →</button>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Tire Code</th><th>Description</th><th>Mold</th>
            <th>Press</th><th>Cavity</th><th>Start</th><th>End</th>
            <th>Duration</th><th>Status</th>
          </tr>
        </thead>
        <tbody id="dash-plans-body">
          <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--muted)"><div class="loader"></div></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- PAGE: PLANNER (3-step wizard)                              -->
<!-- ══════════════════════════════════════════════════════════ -->
<section class="page" id="page-planner">

  <!-- Step indicator -->
  <div class="step-indicator">
    <div class="step active" id="step-1">
      <div class="step-num">1</div>
      <div class="step-label">Select Tire</div>
    </div>
    <div class="step-line" id="line-1"></div>
    <div class="step" id="step-2">
      <div class="step-num">2</div>
      <div class="step-label">Choose Slot</div>
    </div>
    <div class="step-line" id="line-2"></div>
    <div class="step" id="step-3">
      <div class="step-num">3</div>
      <div class="step-label">Confirm &amp; Save</div>
    </div>
  </div>

  <!-- Step 1 : Tire Search -->
  <div id="wizard-step-1">
    <div class="card">
      <div class="card-header">🔍 Step 1 — Search &amp; Select Tire</div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Search by icode or description</label>
          <div class="search-wrapper">
            <span class="search-icon">🔍</span>
            <input type="text" id="tire-search" class="form-control"
                   placeholder="e.g.  139001  or  ACHIEVER  or  300-15…"
                   oninput="onTireSearch(this.value)">
            <div class="search-dropdown" id="tire-dropdown" style="display:none"></div>
          </div>
        </div>
        <div id="tire-selected-card" style="display:none">
          <div style="background:var(--surface2);border:1px solid var(--border2);border-radius:var(--radius);padding:14px">
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;color:var(--amber);font-weight:700;margin-bottom:6px">Selected Tire</div>
            <div id="tire-selected-info"></div>
          </div>
          <div class="form-group" style="margin-top:14px">
            <label class="form-label">Filter by Press (optional)</label>
            <select class="form-control" id="press-filter">
              <option value="">All Presses</option>
            </select>
          </div>
          <button class="btn btn-primary" style="margin-top:4px" onclick="findScheduleOptions()">
            Find Best Slots →
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Step 2 : Slot Selection -->
  <div id="wizard-step-2" style="display:none;margin-top:16px">
    <div class="card">
      <div class="card-header">
        ⚡ Step 2 — Available Scheduling Options
        <span id="opt-count" style="margin-left:auto;font-size:.78rem;color:var(--muted2);font-weight:400"></span>
      </div>
      <div class="card-body">
        <div id="options-container">
          <div style="text-align:center;padding:30px"><div class="loader"></div></div>
        </div>
        <div style="margin-top:16px;display:flex;gap:8px;justify-content:flex-end">
          <button class="btn btn-secondary" onclick="goStep(1)">← Back</button>
          <button class="btn btn-primary" id="btn-next-step3" onclick="goStep(3)" disabled>Next → Review</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Step 3 : Confirm -->
  <div id="wizard-step-3" style="display:none;margin-top:16px">
    <div class="card">
      <div class="card-header">✅ Step 3 — Review &amp; Confirm</div>
      <div class="card-body">
        <div id="confirm-summary"></div>
        <div class="form-group" style="margin-top:16px">
          <label class="form-label">Notes (optional)</label>
          <textarea class="form-control" id="plan-notes" rows="3" placeholder="Any production notes…" style="resize:vertical"></textarea>
        </div>
        <div style="display:flex;gap:8px;margin-top:16px">
          <button class="btn btn-secondary" onclick="goStep(2)">← Back</button>
          <button class="btn btn-primary" id="btn-save-plan" onclick="savePlan()">
            💾 Save Production Plan
          </button>
        </div>
      </div>
    </div>
  </div>

</section>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- PAGE: SCHEDULE                                             -->
<!-- ══════════════════════════════════════════════════════════ -->
<section class="page" id="page-schedule">
  <div class="filter-row">
    <div class="form-group">
      <label class="form-label">From Date</label>
      <input type="date" class="form-control" id="f-from" style="width:150px">
    </div>
    <div class="form-group">
      <label class="form-label">To Date</label>
      <input type="date" class="form-control" id="f-to" style="width:150px">
    </div>
    <div class="form-group">
      <label class="form-label">Status</label>
      <select class="form-control" id="f-status" style="width:150px">
        <option value="">All</option>
        <option value="planned">Planned</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>
    <button class="btn btn-primary" onclick="loadSchedule()" style="margin-bottom:1px">🔍 Filter</button>
    <button class="btn btn-secondary" onclick="resetFilter()" style="margin-bottom:1px">Reset</button>
    <button class="btn btn-success" onclick="showPage('planner')" style="margin-left:auto;margin-bottom:1px">+ New Plan</button>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Tire Code</th><th>Description</th>
            <th>Mold</th><th>Press</th><th>Cavity</th>
            <th>Start</th><th>End</th><th>Min</th>
            <th>Status</th><th>Actions</th>
          </tr>
        </thead>
        <tbody id="schedule-body">
          <tr><td colspan="11" style="text-align:center;padding:30px;color:var(--muted)"><div class="loader"></div></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

</div><!-- .main -->

<!-- Toast container -->
<div id="toast-container"></div>

<!-- Status update modal -->
<div id="status-modal" class="modal-overlay" style="display:none">
  <div class="modal">
    <div class="modal-header">
      Update Status
      <button class="close-btn" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">New Status</label>
        <select class="form-control" id="modal-status">
          <option value="planned">Planned</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="confirmStatusUpdate()">Update</button>
    </div>
  </div>
</div>

<script>
// ── STATE ───────────────────────────────────────────────────
const API = 'api/index.php';
let selectedTire    = null;
let selectedOption  = null;
let updateTargetId  = null;
let searchTimer     = null;

// ── NAVIGATION ───────────────────────────────────────────────
function showPage(id) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('page-' + id).classList.add('active');
  document.querySelectorAll('.nav-btn')[['dashboard','planner','schedule'].indexOf(id)].classList.add('active');
  if (id === 'dashboard') { loadStats(); loadDashPlans(); }
  if (id === 'schedule')  { loadSchedule(); loadSchedule(); }
}

// ── API HELPER ───────────────────────────────────────────────
async function api(action, params = {}) {
  const url = `${API}?action=${action}`;
  const isGet = Object.keys(params).length === 0;
  const opts = isGet ? {} : {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(params)
  };
  const r = await fetch(url, opts);
  return r.json();
}

// ── STATS ────────────────────────────────────────────────────
async function loadStats() {
  const d = await api('stats');
  document.getElementById('s-planned').textContent  = d.total_planned  ?? 0;
  document.getElementById('s-progress').textContent = d.in_progress    ?? 0;
  document.getElementById('s-done').textContent     = d.completed_today ?? 0;
  document.getElementById('s-molds').textContent    = d.total_molds    ?? 0;
  document.getElementById('s-cav').textContent      = d.total_cavities ?? 0;
  document.getElementById('s-tires').textContent    = d.total_tires    ?? 0;
}

// ── DASHBOARD PLANS ───────────────────────────────────────────
async function loadDashPlans() {
  const today = new Date().toISOString().split('T')[0];
  const d = await api('get_plan', { from: today });
  const plans = (d.plans || []).slice(0, 10);
  const tbody = document.getElementById('dash-plans-body');
  if (!plans.length) {
    tbody.innerHTML = `<tr><td colspan="9"><div class="empty-state"><div class="empty-icon">📭</div>No plans scheduled</div></td></tr>`;
    return;
  }
  tbody.innerHTML = plans.map(p => `
    <tr>
      <td class="mono">${p.icode}</td>
      <td style="max-width:220px;font-size:.82rem">${p.tire_description}</td>
      <td class="mono">${p.mold_id}</td>
      <td class="mono">P-${p.press_id}</td>
      <td class="mono">${p.cavity_name || p.cavity_id}</td>
      <td class="mono" style="font-size:.8rem">${fmtDT(p.planned_start)}</td>
      <td class="mono" style="font-size:.8rem">${fmtDT(p.planned_end)}</td>
      <td class="mono">${p.time_taken}m</td>
      <td>${statusChip(p.status)}</td>
    </tr>`).join('');
}

// ── TIRE SEARCH ───────────────────────────────────────────────
function onTireSearch(val) {
  clearTimeout(searchTimer);
  const dd = document.getElementById('tire-dropdown');
  if (val.length < 2) { dd.style.display = 'none'; return; }
  searchTimer = setTimeout(async () => {
    const d = await api('search_tires', { q: val });
    renderTireDropdown(d.tires || []);
  }, 280);
}

function renderTireDropdown(tires) {
  const dd = document.getElementById('tire-dropdown');
  if (!tires.length) { dd.style.display='none'; return; }
  dd.innerHTML = tires.map(t => `
    <div class="search-item" onclick="selectTire(${JSON.stringify(t).replace(/"/g,'&quot;')})">
      <div>
        <div class="icode">${t.icode}</div>
        <div class="desc">${t.description}</div>
        <div class="time">⏱ ${t.time_taken} min &nbsp;|&nbsp; Molds: ${t.compatible_molds || '—'}</div>
      </div>
    </div>`).join('');
  dd.style.display = 'block';
}

async function selectTire(tire) {
  selectedTire = tire;
  document.getElementById('tire-search').value = `${tire.icode} — ${tire.description}`;
  document.getElementById('tire-dropdown').style.display = 'none';

  document.getElementById('tire-selected-card').style.display = 'block';
  document.getElementById('tire-selected-info').innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:.85rem">
      <div><span style="color:var(--muted2)">icode: </span><span class="mono" style="color:var(--amber)">${tire.icode}</span></div>
      <div><span style="color:var(--muted2)">Duration: </span><span class="mono">${tire.time_taken} min</span></div>
      <div style="grid-column:1/-1"><span style="color:var(--muted2)">Description: </span>${tire.description}</div>
      <div style="grid-column:1/-1"><span style="color:var(--muted2)">Compatible Molds: </span><span class="mono" style="font-size:.8rem">${tire.compatible_molds || '—'}</span></div>
    </div>`;

  // Load press list
  const pd = await api('get_presses');
  const sel = document.getElementById('press-filter');
  sel.innerHTML = '<option value="">All Presses</option>' +
    (pd.presses || []).map(p => `<option value="${p.press_id}">Press-${p.press_id} (${p.cavity_count} cavities)</option>`).join('');
}

// ── FIND OPTIONS ──────────────────────────────────────────────
async function findScheduleOptions() {
  if (!selectedTire) return;
  goStep(2);
  document.getElementById('options-container').innerHTML = '<div style="text-align:center;padding:40px"><div class="loader"></div><br><small style="color:var(--muted)">Calculating optimal slots…</small></div>';

  const pressId = parseInt(document.getElementById('press-filter').value) || 0;
  const d = await api('schedule', {
    icode:      selectedTire.icode,
    time_taken: selectedTire.time_taken,
    press_id:   pressId
  });

  const opts = d.options || [];
  document.getElementById('opt-count').textContent = `${opts.length} slot${opts.length !== 1 ? 's' : ''} found`;

  if (!opts.length) {
    document.getElementById('options-container').innerHTML =
      `<div class="empty-state"><div class="empty-icon">🚫</div>No available slots found. All molds/cavities may be busy.</div>`;
    return;
  }

  document.getElementById('options-container').innerHTML =
    `<div class="options-grid">${opts.map((o,i) => optCard(o,i)).join('')}</div>`;
}

function optCard(o, i) {
  const waitClass = o.wait_minutes === 0 ? 'wait-0' : o.wait_minutes < 120 ? 'wait-sm' : 'wait-lg';
  const waitLabel = o.wait_minutes === 0 ? 'Available Now' :
    o.wait_minutes < 60 ? `${o.wait_minutes}m wait` :
    `${Math.round(o.wait_minutes/60)}h wait`;
  const isFirst = i === 0;
  return `
    <div class="option-card${isFirst?' selected':''}" id="opt-${i}" onclick="selectOption(${i}, ${JSON.stringify(o).replace(/"/g,'&quot;')})">
      ${isFirst ? '<span class="opt-badge">BEST</span>' : ''}
      <div class="opt-row"><span class="opt-label">Mold</span>  <span class="opt-val">${o.mold_id}</span></div>
      <div class="opt-row"><span class="opt-label">Press</span> <span class="opt-val">${o.press_name}</span></div>
      <div class="opt-row"><span class="opt-label">Cavity</span><span class="opt-val">${o.cavity_name}</span></div>
      <div class="opt-time">
        <div>▶ Start: <strong>${o.planned_start}</strong></div>
        <div>⏹ End: &nbsp;<strong>${o.planned_end}</strong></div>
        <div style="margin-top:6px"><span class="wait-chip ${waitClass}">${waitLabel}</span></div>
        <div class="timeline-bar" style="margin-top:8px">
          <div class="timeline-fill" style="width:${Math.min(100, Math.max(5, 100 - o.wait_minutes/10))}%"></div>
        </div>
      </div>
    </div>`;
}

function selectOption(idx, opt) {
  selectedOption = opt;
  document.querySelectorAll('.option-card').forEach(c => c.classList.remove('selected'));
  document.getElementById('opt-' + idx)?.classList.add('selected');
  document.getElementById('btn-next-step3').disabled = false;
}

// ── WIZARD STEPS ──────────────────────────────────────────────
function goStep(n) {
  [1,2,3].forEach(i => {
    document.getElementById('wizard-step-' + i).style.display = i === n ? 'block' : 'none';
    const s = document.getElementById('step-' + i);
    s.className = 'step' + (i < n ? ' done' : i === n ? ' active' : '');
  });
  [1,2].forEach(i => {
    const l = document.getElementById('line-' + i);
    l.className = 'step-line' + (i < n ? ' done' : '');
  });
  if (n === 3) buildConfirmSummary();
}

function buildConfirmSummary() {
  const t = selectedTire, o = selectedOption;
  if (!t || !o) return;
  document.getElementById('confirm-summary').innerHTML = `
    <div style="display:grid;gap:12px">
      <div style="background:var(--surface2);border:1px solid var(--border2);border-radius:var(--radius);padding:14px">
        <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.6px;color:var(--amber);font-weight:700;margin-bottom:10px">Production Run Summary</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:.85rem">
          <div><span style="color:var(--muted2)">Tire Code: </span><span class="mono">${t.icode}</span></div>
          <div><span style="color:var(--muted2)">Duration: </span><span class="mono">${t.time_taken} min</span></div>
          <div style="grid-column:1/-1"><span style="color:var(--muted2)">Description: </span>${t.description}</div>
          <div><span style="color:var(--muted2)">Mold: </span><span class="mono">${o.mold_id}</span></div>
          <div><span style="color:var(--muted2)">Press: </span><span class="mono">${o.press_name}</span></div>
          <div><span style="color:var(--muted2)">Cavity: </span><span class="mono">${o.cavity_name}</span></div>
          <div><span style="color:var(--muted2)">Start: </span><span class="mono" style="color:var(--green)">${o.planned_start}</span></div>
          <div><span style="color:var(--muted2)">End: &nbsp;</span><span class="mono" style="color:var(--green)">${o.planned_end}</span></div>
        </div>
      </div>
    </div>`;
}

// ── SAVE PLAN ─────────────────────────────────────────────────
async function savePlan() {
  const btn = document.getElementById('btn-save-plan');
  btn.disabled = true; btn.textContent = '⏳ Saving…';
  try {
    const d = await api('save_plan', {
      icode:            selectedTire.icode,
      tire_description: selectedTire.description,
      mold_id:          selectedOption.mold_id,
      press_id:         selectedOption.press_id,
      cavity_id:        selectedOption.cavity_id,
      cavity_name:      selectedOption.cavity_name,
      planned_start:    selectedOption.planned_start,
      planned_end:      selectedOption.planned_end,
      time_taken:       selectedTire.time_taken,
      notes:            document.getElementById('plan-notes').value
    });
    if (d.success) {
      toast('✅ Plan saved! ID #' + d.id, 'success');
      resetWizard();
      showPage('schedule');
    } else {
      toast('Error: ' + (d.error || 'Unknown error'), 'error');
    }
  } finally {
    btn.disabled = false; btn.textContent = '💾 Save Production Plan';
  }
}

function resetWizard() {
  selectedTire = selectedOption = null;
  document.getElementById('tire-search').value = '';
  document.getElementById('tire-selected-card').style.display = 'none';
  document.getElementById('wizard-step-2').style.display = 'none';
  document.getElementById('wizard-step-3').style.display = 'none';
  document.getElementById('plan-notes').value = '';
  goStep(1);
}

// ── SCHEDULE PAGE ─────────────────────────────────────────────
async function loadSchedule() {
  const from   = document.getElementById('f-from').value;
  const to     = document.getElementById('f-to').value;
  const status = document.getElementById('f-status').value;
  const tbody  = document.getElementById('schedule-body');
  tbody.innerHTML = `<tr><td colspan="11" style="text-align:center;padding:30px"><div class="loader"></div></td></tr>`;
  const d = await api('get_plan', { from, to, status });
  const plans = d.plans || [];
  if (!plans.length) {
    tbody.innerHTML = `<tr><td colspan="11"><div class="empty-state"><div class="empty-icon">📭</div>No production plans found</div></td></tr>`;
    return;
  }
  tbody.innerHTML = plans.map(p => `
    <tr>
      <td class="mono" style="color:var(--muted2)">#${p.id}</td>
      <td class="mono">${p.icode}</td>
      <td style="max-width:200px;font-size:.8rem">${p.tire_description}</td>
      <td class="mono">${p.mold_id}</td>
      <td class="mono">P-${p.press_id}</td>
      <td class="mono">${p.cavity_name || p.cavity_id}</td>
      <td class="mono" style="font-size:.8rem">${fmtDT(p.planned_start)}</td>
      <td class="mono" style="font-size:.8rem">${fmtDT(p.planned_end)}</td>
      <td class="mono">${p.time_taken}</td>
      <td>${statusChip(p.status)}</td>
      <td>
        <div style="display:flex;gap:5px">
          <button class="btn btn-secondary btn-sm" onclick="openStatusModal(${p.id},'${p.status}')">✏️</button>
          <button class="btn btn-danger btn-sm" onclick="deletePlan(${p.id})">🗑</button>
        </div>
      </td>
    </tr>`).join('');
}

function resetFilter() {
  document.getElementById('f-from').value = '';
  document.getElementById('f-to').value = '';
  document.getElementById('f-status').value = '';
  loadSchedule();
}

// ── STATUS MODAL ───────────────────────────────────────────────
function openStatusModal(id, current) {
  updateTargetId = id;
  document.getElementById('modal-status').value = current;
  document.getElementById('status-modal').style.display = 'flex';
}
function closeModal() {
  document.getElementById('status-modal').style.display = 'none';
  updateTargetId = null;
}
async function confirmStatusUpdate() {
  const status = document.getElementById('modal-status').value;
  await api('update_status', { id: updateTargetId, status });
  closeModal();
  toast('Status updated', 'success');
  loadSchedule();
  if (document.getElementById('page-dashboard').classList.contains('active')) loadDashPlans();
}

// ── DELETE PLAN ────────────────────────────────────────────────
async function deletePlan(id) {
  if (!confirm('Delete plan #' + id + '?')) return;
  await api('delete_plan', { id });
  toast('Plan deleted', 'info');
  loadSchedule();
}

// ── HELPERS ────────────────────────────────────────────────────
function fmtDT(dt) {
  if (!dt) return '—';
  const d = new Date(dt.replace(' ','T'));
  return d.toLocaleDateString('en-GB',{day:'2-digit',month:'short'}) + ' ' +
    d.toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});
}

function statusChip(s) {
  const labels = { planned:'Planned', in_progress:'In Progress', completed:'Completed', cancelled:'Cancelled' };
  return `<span class="status-chip status-${s}">${labels[s]||s}</span>`;
}

function toast(msg, type='info') {
  const c = document.getElementById('toast-container');
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.textContent = msg;
  c.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}

// Close search dropdown on outside click
document.addEventListener('click', e => {
  if (!e.target.closest('.search-wrapper')) {
    document.getElementById('tire-dropdown').style.display = 'none';
  }
});

// ── INIT ────────────────────────────────────────────────────────
loadStats();
loadDashPlans();

// Set default date filter to today
const today = new Date().toISOString().split('T')[0];
document.getElementById('f-from').value = today;
</script>
</body>
</html>
