<?php
session_start();
include('include/config.php');
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
} else {
    date_default_timezone_set('Asia/Kolkata');
    $currentTime = date('d-m-Y h:i:s A', time());

    // Fetch admin details and ACM reference
    $adminId = intval($_SESSION["aid"]);
    $adminQuery = mysqli_query($con, "SELECT * FROM admin WHERE id='$adminId'");
    $adminData = mysqli_fetch_array($adminQuery);
    $adminAcmRef = mysqli_real_escape_string($con, $adminData['acm_ref']); // Store admin's ACM reference

    if (isset($_GET['uid']) && $_GET['action'] == 'del') {
        $userid = intval($_GET['uid']);
        
        // Verify the user belongs to this admin's ACM reference before deleting
        $verifyQuery = mysqli_query($con, "SELECT id FROM users WHERE id='$userid' AND acm_ref='$adminAcmRef'");
        if (mysqli_num_rows($verifyQuery) > 0) {
            $query = mysqli_query($con, "DELETE FROM users WHERE id='$userid' AND acm_ref='$adminAcmRef'");
            echo '<script>alert("User Deleted Successfully")</script>';
        } else {
            echo '<script>alert("Unauthorized: You can only delete users assigned to your ACM reference")</script>';
        }
        echo "<script>window.location.href='manage-users.php'</script>";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users — ATIRE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@font-face { font-family:'SF UI Display'; font-weight:500; src:url('font/sf-ui-display-medium-58646be638f96.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:600; src:url('font/sf-ui-display-semibold-58646eddcae92.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:700; src:url('font/sf-ui-display-bold-58646a511e3d9.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:800; src:url('font/sf-ui-display-heavy-586470160b9e5.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:900; src:url('font/sf-ui-display-black-58646a6b80d5a.otf') format('opentype'); }

:root {
    --orange:    #f28018;
    --orange-dk: #d06e10;
    --orange-lt: rgba(242,128,24,0.10);
    --orange-gl: rgba(242,128,24,0.22);
    --teal:      #5bc0be;
    --teal-dk:   #47a8a6;
    --teal-lt:   rgba(91,192,190,0.12);
    --amber:     #f59e0b;
    --amber-dk:  #d97706;
    --amber-lt:  rgba(245,158,11,0.10);
    --green:     #10b981;
    --green-lt:  rgba(16,185,129,0.10);
    --green-gl:  rgba(16,185,129,0.22);
    --bg:        #f0f2f5;
    --white:     #ffffff;
    --g50:       #f9fafb;
    --g100:      #f3f4f6;
    --g200:      #e5e7eb;
    --g300:      #d1d5db;
    --g400:      #9ca3af;
    --g500:      #6b7280;
    --g700:      #374151;
    --g900:      #111827;
    --font:     'SF UI Display', -apple-system, BlinkMacSystemFont, sans-serif;
    --r-sm:      6px;
    --r-md:      10px;
    --r-lg:      14px;
    --shadow:    0 1px 3px rgba(0,0,0,0.07), 0 4px 16px rgba(0,0,0,0.06);
    --tr:        0.16s ease;
    --hh:        56px;
}

*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
body {
    font-family:var(--font);
    background:var(--bg);
    color:var(--g700);
    min-height:100vh;
    font-size:12px;
    line-height:1.4;
    -webkit-font-smoothing:antialiased;
}
::-webkit-scrollbar { width:4px; height:4px; }
::-webkit-scrollbar-track { background:transparent; }
::-webkit-scrollbar-thumb { background:var(--g300); border-radius:99px; }

/* ── HEADER ── */
.hdr {
    position:sticky; top:0; z-index:500;
    background:var(--white);
    border-bottom:2px solid var(--orange);
    box-shadow:0 2px 16px rgba(0,0,0,0.07);
    height:var(--hh);
}
.hdr-inner {
    max-width:1600px; margin:0 auto;
    padding:0 1.5rem; height:100%;
    display:flex; align-items:center; justify-content:space-between; gap:1rem;
}
.brand { display:flex; align-items:center; gap:8px; text-decoration:none; }
.brand-logo { height:24px; }
.hdr-r { display:flex; align-items:center; gap:6px; }
.hbtn {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 12px; border-radius:var(--r-sm);
    font-family:var(--font); font-weight:700; font-size:11px;
    text-decoration:none; border:1.5px solid var(--g200);
    background:var(--white); color:var(--g500);
    cursor:pointer; transition:var(--tr);
}
.hbtn:hover { border-color:var(--orange); color:var(--orange); background:var(--orange-lt); }
.hbtn.pri { background:var(--orange); color:var(--white); border-color:var(--orange); }
.hbtn.pri:hover { background:var(--orange-dk); }
.avatar {
    width:30px; height:30px; border-radius:50%;
    background:var(--orange); color:var(--white);
    display:flex; align-items:center; justify-content:center;
    font-weight:900; font-size:11px;
    box-shadow:0 2px 8px var(--orange-gl);
}

/* ── PAGE ── */
.wrap { max-width:1600px; margin:0 auto; padding:1.2rem 1.5rem 4rem; }

/* ── HERO ── */
.hero { display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:0.8rem; margin-bottom:1rem; }
.eyebrow {
    font-size:9px; font-weight:800; color:var(--orange);
    letter-spacing:.22em; text-transform:uppercase;
    margin-bottom:3px; display:flex; align-items:center; gap:5px;
}
.eyebrow::before { content:''; width:12px; height:2px; background:var(--orange); border-radius:2px; }
.page-title {
    font-size:clamp(20px,2.5vw,30px); font-weight:900;
    color:var(--g900); letter-spacing:-.02em; line-height:1.1;
    display:flex; align-items:center; gap:8px; flex-wrap:wrap;
}
.page-title span.hl { color:var(--orange); }
.sub { font-size:11px; color:var(--g400); font-weight:500; margin-top:3px; }

/* ── STATS ── */
.stats {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:8px; margin-bottom:1rem;
}
.scard {
    background:var(--white); border-radius:var(--r-md);
    border:1.5px solid var(--g200); padding:.7rem 1rem;
    display:flex; align-items:center; gap:10px;
    box-shadow:var(--shadow); transition:var(--tr);
}
.scard:hover { border-color:var(--orange); }
.sico {
    width:32px; height:32px; border-radius:var(--r-sm);
    background:var(--orange-lt);
    display:flex; align-items:center; justify-content:center;
    font-size:13px; color:var(--orange); flex-shrink:0;
}
.sico.gr { background:var(--green-lt); color:var(--green); }
.sico.am { background:var(--amber-lt);  color:var(--amber);  }
.slabel { font-size:9px; font-weight:800; color:var(--g400); letter-spacing:.07em; text-transform:uppercase; margin-bottom:1px; }
.sval   { font-size:18px; font-weight:900; color:var(--g900); line-height:1; }

/* ── PANEL ── */
.panel {
    background:var(--white);
    border:1.5px solid var(--g200);
    border-radius:var(--r-lg);
    box-shadow:var(--shadow);
}

/* ── TOOLBAR ── */
.toolbar {
    padding:.7rem 1.2rem;
    border-bottom:1.5px solid var(--g100);
    display:flex; align-items:center; justify-content:space-between;
    gap:.8rem; flex-wrap:wrap;
    background:var(--white);
    position:sticky; top:var(--hh); z-index:40;
}
.tb-l { display:flex; align-items:center; gap:8px; }
.tb-r { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
.tb-ico {
    width:26px; height:26px; border-radius:5px;
    background:var(--orange); color:var(--white);
    display:flex; align-items:center; justify-content:center; font-size:11px;
}
.tb-title { font-size:10px; font-weight:800; color:var(--g700); letter-spacing:.08em; text-transform:uppercase; }
.cpill {
    padding:2px 8px; border-radius:20px;
    font-size:9px; font-weight:700;
    background:var(--orange-lt); color:var(--orange);
    border:1px solid rgba(242,128,24,0.22);
}
.srch { position:relative; }
.srch input {
    padding:5px 28px 5px 10px;
    border:1.5px solid var(--g200); border-radius:var(--r-sm);
    font-family:var(--font); font-size:11.5px; font-weight:600;
    color:var(--g700); background:var(--white);
    outline:none; transition:var(--tr); width:200px;
}
.srch input:focus { border-color:var(--orange); box-shadow:0 0 0 3px var(--orange-gl); }
.srch i { position:absolute; right:9px; top:50%; transform:translateY(-50%); color:var(--g400); font-size:10px; pointer-events:none; }

/* ── FILTER SECTION ── */
.filter-section {
    background:var(--g50); border-radius:var(--r-md);
    padding:.8rem 1.2rem; margin-bottom:1rem;
    border:1.5px solid var(--g200);
}
.filter-title {
    font-size:10px; font-weight:800; color:var(--g700);
    letter-spacing:.08em; text-transform:uppercase;
    margin-bottom:.7rem; display:flex; align-items:center; gap:5px;
}
.filter-grid {
    display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));
    gap:8px; margin-bottom:.8rem;
}
.filter-input, .filter-select {
    padding:5px 10px; border:1.5px solid var(--g200);
    border-radius:var(--r-sm); background:var(--white);
    font-family:var(--font); font-size:11px; font-weight:600;
    color:var(--g700); outline:none; transition:var(--tr);
}
.filter-input:focus, .filter-select:focus {
    border-color:var(--orange); box-shadow:0 0 0 3px var(--orange-gl);
}
.filter-actions { display:flex; gap:6px; flex-wrap:wrap; }
.fbtn {
    display:inline-flex; align-items:center; gap:4px;
    padding:5px 12px; border-radius:var(--r-sm);
    font-family:var(--font); font-weight:700; font-size:10px;
    text-decoration:none; border:1.5px solid transparent;
    cursor:pointer; transition:var(--tr); text-transform:uppercase;
    letter-spacing:.04em;
}
.fbtn.pri { background:var(--orange); color:var(--white); border-color:var(--orange); }
.fbtn.pri:hover { background:var(--orange-dk); border-color:var(--orange-dk); }
.fbtn.sec { background:var(--white); color:var(--g500); border-color:var(--g200); }
.fbtn.sec:hover { border-color:var(--orange); color:var(--orange); background:var(--orange-lt); }

/* ── TABLE ── */
.tbl-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }

table.t {
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
    /* No min-width — let it fill the panel naturally */
}

/* Column widths — compact but readable */
table.t col.c0 { width:38px;  }   /* # */
table.t col.c1 { width:16%;   }   /* User */
table.t col.c2 { width:10%;   }   /* Customer ID */
table.t col.c3 { width:14%;   }   /* Email */
table.t col.c4 { width:9%;    }   /* Country */
table.t col.c5 { width:12%;   }   /* Manager */
table.t col.c6 { width:7%;    }   /* Status */
table.t col.c7 { width:9%;    }   /* Registered */
table.t col.c8 { width:13%;   }   /* Action */

table.t thead { background:var(--g50); }
table.t th {
    padding:7px 8px;
    text-align:left; font-size:9px; font-weight:800;
    color:var(--g400); letter-spacing:.10em; text-transform:uppercase;
    white-space:nowrap;
    border-bottom:2px solid var(--g200);
    border-right:1px solid var(--g200);
    overflow:hidden; text-overflow:ellipsis;
}
table.t th:last-child { border-right:none; }
table.t th.ctr { text-align:center; }
table.t th i.hi { color:var(--orange); margin-right:3px; font-size:8px; }

table.t tbody tr { border-bottom:1px solid var(--g100); transition:background var(--tr); }
table.t tbody tr:last-child { border-bottom:none; }
table.t tbody tr:hover { background:rgba(242,128,24,0.04); }

table.t td {
    padding:8px 8px;
    font-size:11.5px; font-weight:500; color:var(--g700);
    vertical-align:middle;
    overflow:hidden;
}

table.t td.c0 { text-align:center; }
table.t td.c6 { text-align:center; }
table.t td.c8 { text-align:center; }

.rno {
    width:20px; height:20px; border-radius:50%;
    background:var(--g100);
    display:inline-flex; align-items:center; justify-content:center;
    font-size:9px; font-weight:800; color:var(--g400);
}

.user-pill { display:flex; align-items:center; gap:7px; }
.user-avatar {
    width:28px; height:28px; border-radius:50%;
    background:var(--orange); color:var(--white);
    display:flex; align-items:center; justify-content:center;
    font-weight:900; font-size:10px; flex-shrink:0;
    box-shadow:0 2px 6px var(--orange-gl);
}
.user-info { display:flex; flex-direction:column; gap:1px; min-width:0; }
.user-name {
    font-weight:700; color:var(--g700); font-size:12px;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.user-company {
    font-size:10px; color:var(--g400);
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}

.cid-badge {
    display:inline-block;
    background:var(--orange-lt);
    color:var(--orange);
    border:1px solid rgba(242,128,24,0.25);
    border-radius:5px;
    padding:2px 7px;
    font-size:11px; font-weight:900; letter-spacing:.02em;
    white-space:nowrap;
}

.email-badge {
    display:inline-flex; align-items:center; gap:4px;
    color:var(--g500); font-size:11px;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    max-width:100%;
}
.email-badge i { color:var(--orange); font-size:10px; flex-shrink:0; }

.country-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 6px; border-radius:var(--r-sm);
    background:var(--g100); color:var(--g500);
    font-size:11px; font-weight:600; border:1px solid var(--g200);
    white-space:nowrap;
}

.acm-badge { display:flex; flex-direction:column; gap:1px; }
.acm-name {
    font-weight:700; color:var(--g700); font-size:11px;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.acm-ref { font-size:9px; color:var(--g400); font-weight:500; }

.status-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 8px; border-radius:20px;
    font-size:9px; font-weight:800; letter-spacing:.05em; text-transform:uppercase;
    white-space:nowrap; border:1px solid transparent;
}
.status-badge i { font-size:7px; }
.status-active   { background:#dcfce7; color:#166534; border-color:#86efac; }
.status-inactive { background:#fee2e2; color:#991b1b; border-color:#fca5a5; }

.date-badge { display:flex; flex-direction:column; gap:1px; }
.date-primary { font-weight:600; color:var(--g700); font-size:11px; white-space:nowrap; }
.date-secondary { font-size:9px; color:var(--g400); white-space:nowrap; }

/* Action buttons — compact row */
.action-group {
    display:inline-flex;
    align-items:center;
    gap:4px;
    justify-content:center;
    flex-wrap:nowrap;
}

.vbtn {
    display:inline-flex; align-items:center; gap:3px;
    padding:4px 8px; border-radius:5px;
    background:var(--orange); color:var(--white);
    font-family:var(--font); font-size:9px; font-weight:800;
    letter-spacing:.03em; text-transform:uppercase;
    text-decoration:none; border:none; cursor:pointer;
    transition:var(--tr); box-shadow:0 1px 5px rgba(242,128,24,0.22);
    white-space:nowrap;
}
.vbtn:hover { background:var(--orange-dk); transform:translateY(-1px); }
.vbtn i { font-size:8px; }
.vbtn.alt { background:var(--teal); box-shadow:0 1px 5px rgba(91,192,190,0.22); }
.vbtn.alt:hover { background:var(--teal-dk); }
.vbtn.danger { background:#ef4444; box-shadow:0 1px 5px rgba(239,68,68,0.22); }
.vbtn.danger:hover { background:#dc2626; }

/* Empty state */
.empty { text-align:center; padding:3rem 2rem; }
.empty-ico {
    width:50px; height:50px; border-radius:50%;
    background:var(--g100);
    display:inline-flex; align-items:center; justify-content:center;
    font-size:20px; color:var(--g300); margin-bottom:12px;
}
.empty h3 { font-size:15px; font-weight:800; color:var(--g700); margin-bottom:5px; }
.empty p  { font-size:12px; color:var(--g400); font-weight:500; margin-bottom:1.2rem; }
.empty-acts { display:flex; gap:8px; justify-content:center; flex-wrap:wrap; }

.nores { display:none; text-align:center; padding:1.5rem; border-top:1px solid var(--g100); }
.nores i { font-size:1.5rem; color:var(--g300); display:block; margin-bottom:6px; }
.nores p  { font-size:12px; font-weight:600; color:var(--g400); }

/* Responsive */
@media(max-width:1100px) {
    table.t col.c3 { width:0; }
    table.t th:nth-child(4),
    table.t td:nth-child(4) { display:none; }
}
@media(max-width:1000px) { .toolbar { position:static; } }
@media(max-width:768px)  {
    .stats { grid-template-columns:repeat(2,1fr); }
    .action-group { flex-direction:column; gap:3px; }
    .vbtn { width:100%; justify-content:center; }
}
@media(max-width:600px) {
    .wrap { padding:.8rem .8rem 4rem; }
    .hdr-inner { padding:0 .8rem; }
    .toolbar { flex-direction:column; align-items:stretch; }
    .srch input { width:100%; }
    .hero { flex-direction:column; align-items:flex-start; }
    .stats { grid-template-columns:1fr 1fr; }
    .filter-grid { grid-template-columns:1fr; }
}
</style>
</head>
<body>

<!-- HEADER -->
<header class="hdr">
    <div class="hdr-inner">
        <a href="dashboard.php" class="brand">
            <img src="atire.png" alt="ATIRE" class="brand-logo">
        </a>
        <div class="hdr-r">
            <a href="javascript:history.back()" class="hbtn"><i class="fas fa-arrow-left"></i> Back</a>
            <a href="dashboard.php" class="hbtn"><i class="fas fa-home"></i> Dashboard</a>
            <div class="avatar">
                <?php
                    $adminName = $adminData['name'] ?? $adminData['fullName'] ?? $adminData['admin_name'] ?? 'Admin';
                    $initials = strtoupper($adminName[0]);
                    if (strpos($adminName, ' ') !== false) {
                        $initials .= strtoupper(substr($adminName, strpos($adminName, ' ') + 1, 1));
                    }
                    echo htmlspecialchars($initials);
                ?>
            </div>
        </div>
    </div>
</header>

<div class="wrap">

    <!-- HERO -->
    <div class="hero">
        <div>
            <div class="eyebrow">User Management</div>
            <div class="page-title">
                Manage <span class="hl">Users</span>
            </div>
            <div class="sub">Administer users and customer accounts for your ACM Reference</div>
        </div>
        <a href="add-user.php" class="hbtn pri"><i class="fas fa-user-plus"></i> Add User</a>
    </div>

    <!-- STATS -->
    <div class="stats">
        <div class="scard">
            <div class="sico"><i class="fas fa-users"></i></div>
            <div>
                <div class="slabel">Total Users</div>
                <div class="sval">
                    <?php
                    $totalUsersQuery = mysqli_query($con, "SELECT COUNT(*) as total FROM users WHERE acm_ref='$adminAcmRef'");
                    $totalUsersData = mysqli_fetch_array($totalUsersQuery);
                    echo $totalUsersData['total'];
                    ?>
                </div>
            </div>
        </div>
        <div class="scard">
            <div class="sico gr"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="slabel">Active Users</div>
                <div class="sval">
                    <?php
                    $activeUsersQuery = mysqli_query($con, "SELECT COUNT(*) as total FROM users WHERE status = 1 AND acm_ref='$adminAcmRef'");
                    $activeUsersData = mysqli_fetch_array($activeUsersQuery);
                    echo $activeUsersData['total'];
                    ?>
                </div>
            </div>
        </div>
        <div class="scard">
            <div class="sico am"><i class="fas fa-user-plus"></i></div>
            <div>
                <div class="slabel">New This Month</div>
                <div class="sval">
                    <?php
                    $recentUsersQuery = mysqli_query($con, "SELECT COUNT(*) as total FROM users WHERE regDate >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND acm_ref='$adminAcmRef'");
                    $recentUsersData = mysqli_fetch_array($recentUsersQuery);
                    echo $recentUsersData['total'];
                    ?>
                </div>
            </div>
        </div>
        <div class="scard">
            <div class="sico"><i class="fas fa-globe"></i></div>
            <div>
                <div class="slabel">Countries</div>
                <div class="sval">
                    <?php
                    $countriesQuery = mysqli_query($con, "SELECT COUNT(DISTINCT Country) as total FROM users WHERE Country != '' AND acm_ref='$adminAcmRef'");
                    $countriesData = mysqli_fetch_array($countriesQuery);
                    echo $countriesData['total'];
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- PANEL -->
    <div class="panel">

        <!-- TOOLBAR -->
        <div class="toolbar">
            <div class="tb-l">
                <div class="tb-ico"><i class="fas fa-users"></i></div>
                <div class="tb-title">All Users</div>
                <span class="cpill" id="cPill">
                    <?php
                    $countQuery = mysqli_query($con, "SELECT COUNT(*) as cnt FROM users WHERE acm_ref='$adminAcmRef'");
                    $countData = mysqli_fetch_array($countQuery);
                    $totalCount = $countData['cnt'];
                    echo $totalCount . ' User' . ($totalCount !== 1 ? 's' : '');
                    ?>
                </span>
            </div>
            <div class="tb-r">
                <div class="srch">
                    <input type="text" id="searchInput" placeholder="Search name, email, ID…" autocomplete="off">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>

        <!-- FILTER SECTION -->
        <div style="padding:.8rem 1.2rem; border-bottom:1.5px solid var(--g100); background:var(--g50);">
            <div class="filter-title"><i class="fas fa-filter"></i> Filter Users</div>
            <div class="filter-grid">
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <label style="font-size:9px; font-weight:700; color:var(--g500); letter-spacing:.05em; text-transform:uppercase;">Country</label>
                    <select id="countryFilter" class="filter-select">
                        <option value="">All Countries</option>
                        <?php
                        $countryQuery = mysqli_query($con, "SELECT DISTINCT Country FROM users WHERE Country != '' AND acm_ref='$adminAcmRef' ORDER BY Country");
                        while ($country = mysqli_fetch_array($countryQuery)) {
                            echo '<option value="' . htmlentities($country['Country']) . '">' . htmlentities($country['Country']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <label style="font-size:9px; font-weight:700; color:var(--g500); letter-spacing:.05em; text-transform:uppercase;">Status</label>
                    <select id="statusFilter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <label style="font-size:9px; font-weight:700; color:var(--g500); letter-spacing:.05em; text-transform:uppercase;">Account Manager</label>
                    <select id="acmFilter" class="filter-select">
                        <option value="">All Managers</option>
                        <?php
                        $acmQuery = mysqli_query($con, "SELECT DISTINCT acm_name FROM users WHERE acm_name != '' AND acm_ref='$adminAcmRef' ORDER BY acm_name");
                        while ($acm = mysqli_fetch_array($acmQuery)) {
                            echo '<option value="' . htmlentities($acm['acm_name']) . '">' . htmlentities($acm['acm_name']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button class="fbtn pri" onclick="applyFilters()"><i class="fas fa-search"></i> Apply Filters</button>
                <button class="fbtn sec" onclick="resetFilters()"><i class="fas fa-redo"></i> Reset</button>
            </div>
        </div>

        <!-- TABLE -->
        <div class="tbl-wrap">
            <table class="t">
                <colgroup>
                    <col class="c0">
                    <col class="c1">
                    <col class="c2">
                    <col class="c3">
                    <col class="c4">
                    <col class="c5">
                    <col class="c6">
                    <col class="c7">
                    <col class="c8">
                </colgroup>
                <thead>
                    <tr>
                        <th class="ctr">#</th>
                        <th><i class="hi fas fa-user"></i>User</th>
                        <th><i class="hi fas fa-id-card"></i>Customer ID</th>
                        <th><i class="hi fas fa-envelope"></i>Email</th>
                        <th><i class="hi fas fa-globe"></i>Country</th>
                        <th><i class="hi fas fa-user-tie"></i>Manager</th>
                        <th class="ctr"><i class="hi fas fa-toggle-on"></i>Status</th>
                        <th><i class="hi fas fa-calendar-alt"></i>Registered</th>
                        <th class="ctr"><i class="hi fas fa-bolt"></i>Action</th>
                    </tr>
                </thead>
                <tbody id="tbody">

                <?php
                $query = mysqli_query($con, "SELECT * FROM users WHERE acm_ref='$adminAcmRef' ORDER BY regDate DESC");
                $cnt = 1;
                $rowCount = mysqli_num_rows($query);
                
                if ($rowCount > 0) {
                    while ($row = mysqli_fetch_array($query)) {
                        $statusClass = $row['status'] == 1 ? 'status-active' : 'status-inactive';
                        $statusText  = $row['status'] == 1 ? 'Active' : 'Inactive';
                        $statusIcon  = $row['status'] == 1 ? 'fa-check-circle' : 'fa-times-circle';
                        
                        $nameParts = explode(' ', $row['fullName']);
                        $initials = '';
                        foreach ($nameParts as $part) {
                            if (!empty($part)) {
                                $initials .= strtoupper($part[0]);
                                if (strlen($initials) >= 2) break;
                            }
                        }

                        $dPrimary = '—'; $dTime = '';
                        if (!empty($row['regDate'])) {
                            $ts = strtotime($row['regDate']);
                            if ($ts) {
                                $dPrimary = date('M j, Y', $ts);
                                $dTime    = date('g:i A', $ts);
                            }
                        }
                ?>
                <tr data-country="<?php echo htmlentities($row['Country']); ?>" 
                    data-status="<?php echo htmlentities($row['status']); ?>" 
                    data-acm="<?php echo htmlentities($row['acm_name']); ?>"
                    data-search="<?php echo htmlentities(strtolower($row['fullName'] . ' ' . $row['userEmail'] . ' ' . $row['cus_id'])); ?>">
                    
                    <td class="c0"><span class="rno"><?php echo $cnt; ?></span></td>
                    
                    <td class="c1">
                        <div class="user-pill">
                            <div class="user-avatar"><?php echo $initials; ?></div>
                            <div class="user-info">
                                <div class="user-name"><?php echo htmlentities($row['fullName']); ?></div>
                                <?php if ($row['company_rn']) { ?>
                                <div class="user-company"><i class="fas fa-building" style="font-size:8px;"></i> <?php echo htmlentities($row['company_rn']); ?></div>
                                <?php } ?>
                            </div>
                        </div>
                    </td>
                    
                    <td class="c2">
                        <span class="cid-badge"><?php echo htmlentities($row['cus_id'] ?: 'N/A'); ?></span>
                        <?php if ($row['customer_code']) { ?>
                        <div style="font-size:9px; color:var(--g400); margin-top:2px;">Code: <?php echo htmlentities($row['customer_code']); ?></div>
                        <?php } ?>
                    </td>
                    
                    <td class="c3">
                        <div class="email-badge">
                            <i class="fas fa-envelope"></i>
                            <span style="overflow:hidden;text-overflow:ellipsis;"><?php echo htmlentities($row['userEmail']); ?></span>
                        </div>
                    </td>
                    
                    <td class="c4">
                        <?php if ($row['Country']) { ?>
                        <span class="country-badge"><i class="fas fa-flag" style="font-size:8px;"></i> <?php echo htmlentities($row['Country']); ?></span>
                        <?php } else { ?>
                        <span style="color:var(--g400); font-size:11px;">N/A</span>
                        <?php } ?>
                    </td>
                    
                    <td class="c5">
                        <?php if ($row['acm_name']) { ?>
                        <div class="acm-badge">
                            <span class="acm-name"><i class="fas fa-user-tie" style="font-size:9px; margin-right:3px;"></i><?php echo htmlentities($row['acm_name']); ?></span>
                            <?php if ($row['acm_ref']) { ?>
                            <span class="acm-ref">Ref: <?php echo htmlentities($row['acm_ref']); ?></span>
                            <?php } ?>
                        </div>
                        <?php } else { ?>
                        <span style="color:var(--g400); font-size:11px;">Not Assigned</span>
                        <?php } ?>
                    </td>
                    
                    <td class="c6">
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <i class="fas <?php echo $statusIcon; ?>"></i> <?php echo $statusText; ?>
                        </span>
                    </td>
                    
                    <td class="c7">
                        <div class="date-badge">
                            <span class="date-primary"><?php echo $dPrimary; ?></span>
                            <?php if ($dTime) { ?>
                            <span class="date-secondary"><i class="fas fa-clock" style="font-size:8px; margin-right:1px;"></i><?php echo $dTime; ?></span>
                            <?php } ?>
                        </div>
                    </td>
                    
                    <td class="c8">
                        <div class="action-group">
                            <a href="edit-user.php?uid=<?php echo htmlentities($row['id']); ?>" class="vbtn" title="Edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="cus-confirmed-orders.php?acm_ref=<?php echo urlencode($adminAcmRef); ?>&cus_id=<?php echo urlencode($row['cus_id']); ?>" class="vbtn alt" title="View Orders">
                                <i class="fas fa-clipboard-list"></i> Orders
                            </a>
                            <a href="manage-users.php?uid=<?php echo htmlentities($row['id']); ?>&action=del" class="vbtn danger" title="Delete" onclick="return confirm('Are you sure you want to delete this user?')">
                                <i class="fas fa-trash"></i> Del
                            </a>
                        </div>
                    </td>
                </tr>
                <?php
                        $cnt++;
                    }
                } else {
                ?>
                <tr>
                    <td colspan="9">
                        <div class="empty">
                            <div class="empty-ico"><i class="fas fa-users"></i></div>
                            <h3>No Users Found</h3>
                            <p>There are no users assigned to your ACM reference yet.</p>
                            <div class="empty-acts">
                                <a href="add-user.php" class="hbtn pri"><i class="fas fa-user-plus"></i> Add First User</a>
                                <a href="dashboard.php" class="hbtn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php } ?>

                </tbody>
            </table>

            <div class="nores" id="noRes">
                <i class="fas fa-search"></i>
                <p>No users match your search.</p>
            </div>
        </div>

    </div><!-- /panel -->
</div><!-- /wrap -->

<script>
(function () {
    var inp  = document.getElementById('searchInput');
    var pill = document.getElementById('cPill');
    var noR  = document.getElementById('noRes');
    var rows = Array.prototype.slice.call(document.querySelectorAll('#tbody tr[data-search]'));
    
    if (!inp || !rows.length) return;

    function filterTable() {
        var q          = inp.value.toLowerCase().trim();
        var countryVal = document.getElementById('countryFilter').value.toLowerCase();
        var statusVal  = document.getElementById('statusFilter').value;
        var acmVal     = document.getElementById('acmFilter').value.toLowerCase();
        var n = 0;

        rows.forEach(function (r) {
            var searchStr = r.getAttribute('data-search');
            var country   = r.getAttribute('data-country').toLowerCase();
            var status    = r.getAttribute('data-status');
            var acm       = r.getAttribute('data-acm').toLowerCase();
            var show = true;

            if (q          && !searchStr.includes(q))    show = false;
            if (countryVal && country !== countryVal)     show = false;
            if (statusVal  && status  !== statusVal)      show = false;
            if (acmVal     && acm     !== acmVal)         show = false;

            r.style.display = show ? '' : 'none';
            if (show) n++;
        });

        noR.style.display  = (n === 0) ? 'block' : 'none';
        pill.textContent   = n + ' User' + (n !== 1 ? 's' : '');
    }

    inp.addEventListener('input', filterTable);
    document.getElementById('countryFilter').addEventListener('change', filterTable);
    document.getElementById('statusFilter').addEventListener('change', filterTable);
    document.getElementById('acmFilter').addEventListener('change', filterTable);

    window.applyFilters = filterTable;
    window.resetFilters = function () {
        inp.value = '';
        document.getElementById('countryFilter').value = '';
        document.getElementById('statusFilter').value  = '';
        document.getElementById('acmFilter').value     = '';
        filterTable();
    };
}());
</script>
</body>
</html>
<?php
    mysqli_close($con);
}
?>