
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Dashboard - ATIRE</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-ultralight-58646b19bf205.otf') format('opentype'); font-weight: 100; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-thin-58646e9b26e8b.otf') format('opentype');      font-weight: 200; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-medium-58646be638f96.otf') format('opentype');     font-weight: 400; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-semibold-58646eddcae92.otf') format('opentype');   font-weight: 600; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-bold-58646a511e3d9.otf') format('opentype');       font-weight: 700; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-heavy-586470160b9e5.otf') format('opentype');      font-weight: 800; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-black-58646a6b80d5a.otf') format('opentype');      font-weight: 900; }

        :root {
            --primary-orange:  #F28018;
            --dark-gray:       #333333;
            --light-gray:      #f0f0f0;
            --border-gray:     #D6D6D6;
            --bg-light:        #f9f9f9;
            --success:         #27ae60;
            --warning:         #f39c12;
            --error:           #e74c3c;
            --info:            #3498db;
            --text-gray:       #555555;
            --orange-light:    rgba(242, 128, 24, 0.1);
            --success-light:   rgba(39, 174, 96, 0.1);
            --warning-light:   rgba(241, 196, 15, 0.1);
            --error-light:     rgba(231, 76, 60, 0.1);
            --info-light:      rgba(52, 152, 219, 0.1);
            --white:           #ffffff;
            --black:           #000000;
            --shadow-soft:     0 4px 20px rgba(0,0,0,0.08);
            --shadow-hover:    0 8px 30px rgba(0,0,0,0.12);
            --shadow-active:   0 12px 40px rgba(242,128,24,0.3);
            --transition:      all 0.3s ease;
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'SF UI Display', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-light);
            color: var(--dark-gray);
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
            font-weight: 400;
        }

        /* ═══════════ SIDEBAR ═══════════ */
        .sidebar {
            width: 280px;
            background-color: var(--black);
            color: #ecf0f1;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 20px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            transition: var(--transition);
        }

        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(242,128,24,0.4); border-radius: 4px; }

        .sidebar-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.4rem 1.2rem 1rem;
            background-color: var(--black);
            border-bottom: 2px solid var(--primary-orange);
            gap: 12px;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            justify-content: center;
        }

        .sidebar-logo {
            width: 200px;
            height: 100px;
            object-fit: contain;
            border-radius: 10px;
           
            padding: 3px;
           
            
            flex-shrink: 0;
        }

        .sidebar-title {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--white);
            letter-spacing: 0.05em;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .sidebar-title span {
            display: block;
            font-size: 0.68rem;
            color: var(--primary-orange);
            font-weight: 600;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .sidebar-user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 0.2rem 0 0;
        }

        .sidebar-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--primary-orange);
            flex-shrink: 0;
            background: var(--primary-orange);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 1rem;
        }

        .sidebar-avatar img { width: 100%; height: 100%; object-fit: cover; }

        .sidebar-user-details { flex-grow: 1; min-width: 0; }

        .sidebar-username {
            font-weight: 700;
            color: var(--white);
            font-size: 0.95rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-userrole {
            color: #ecf0f1;
            opacity: 0.7;
            font-size: 0.8rem;
            font-weight: 400;
        }

        .sidebar-logout {
            background: var(--primary-orange);
            color: var(--white);
            padding: 0.7rem 1rem;
            border-radius: 50px;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 14px 16px;
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 0.03em;
            transition: var(--transition);
            gap: 8px;
            box-shadow: var(--shadow-soft);
        }

        .sidebar-logout:hover {
            background: #d4700f;
            transform: translateY(-2px);
            box-shadow: var(--shadow-active);
        }

        .sidebar-menu {
            flex-grow: 1;
            overflow-y: auto;
            padding-bottom: 1rem;
        }

        .sidebar-menu-item { list-style: none; position: relative; }

        .sidebar-menu-item > a {
            display: flex;
            align-items: center;
            color: #ecf0f1;
            text-decoration: none;
            padding: 11px 20px;
            transition: var(--transition);
            gap: 10px;
            font-size: 0.93rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            border-left: 3px solid transparent;
        }

        .sidebar-menu-item > a:hover {
            background-color: rgba(242,128,24,0.12);
            color: var(--primary-orange);
            border-left-color: var(--primary-orange);
        }

        .sidebar-menu-item > a i {
            width: 22px;
            text-align: center;
            font-size: 0.95rem;
            opacity: 0.85;
        }

        .sidebar-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease;
            background-color: rgba(0,0,0,0.25);
        }

        .sidebar-menu-item:hover .sidebar-submenu { max-height: 600px; }

        .sidebar-submenu a {
            padding: 9px 20px 9px 52px;
            color: rgba(236,240,241,0.75);
            text-decoration: none;
            display: block;
            transition: var(--transition);
            font-size: 0.85rem;
            font-weight: 400;
            border-left: 3px solid transparent;
        }

        .sidebar-submenu a:hover {
            background-color: rgba(242,128,24,0.15);
            color: var(--primary-orange);
            border-left-color: var(--primary-orange);
        }

        /* ═══════════ MAIN CONTENT ═══════════ */
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        /* ── Top Header Bar ── */
        .top-header {
            position: sticky;
            top: 0;
            z-index: 900;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-gray);
            box-shadow: var(--shadow-soft);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .top-header-left h1 {
            font-size: 1.4rem;
            font-weight: 900;
            color: var(--dark-gray);
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .top-header-left h1 span { color: var(--primary-orange); }

        .top-header-left p {
            font-size: 0.82rem;
            color: var(--text-gray);
            font-weight: 400;
            margin-top: 2px;
        }

        .top-header-right {
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }

        .user-info { text-align: right; }
        .user-info h4 { font-size: 0.95rem; font-weight: 700; color: var(--dark-gray); line-height: 1.2; }
        .user-info p  { font-size: 0.78rem; color: var(--text-gray); font-weight: 400; }

        .user-avatar-wrap {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--primary-orange);
            cursor: pointer;
            transition: var(--transition);
            background: var(--primary-orange);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 0.95rem;
        }

        .user-avatar-wrap:hover {
            transform: scale(1.1);
            box-shadow: 0 0 0 3px var(--primary-orange), var(--shadow-active);
        }

        .user-avatar-wrap img { width: 100%; height: 100%; object-fit: cover; }

        /* ── Page Body ── */
        .page-body { padding: 1.5rem 2rem 3rem; }

        /* ── Status Bar ── */
        .status-bar {
            background: linear-gradient(135deg, var(--primary-orange), #ff9a3c);
            color: var(--white);
            padding: 0.9rem 1.4rem;
            margin-bottom: 1.8rem;
            border-radius: 50px;
            white-space: nowrap;
            overflow: hidden;
            font-weight: 700;
            font-size: 0.88rem;
            letter-spacing: 0.03em;
            box-shadow: var(--shadow-active);
        }

        /* ── Section Titles ── */
        .section-title {
            font-size: 1.35rem;
            font-weight: 800;
            margin: 2.2rem 0 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--dark-gray);
            letter-spacing: -0.02em;
        }

        .title-icon {
            width: 38px;
            height: 38px;
            background: var(--orange-light);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-orange);
            font-size: 1rem;
            flex-shrink: 0;
        }

        .title-line {
            flex: 1;
            height: 1.5px;
            background: linear-gradient(90deg, var(--border-gray) 0%, transparent 100%);
            margin-left: 8px;
        }

        /* ── Stats Grid ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .stat-card {
            background: var(--white);
            border: 1px solid var(--border-gray);
            border-radius: 20px;
            padding: 1.6rem 1.4rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
        }

        .stat-card::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 4px;
            background: var(--primary-orange);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .stat-card:hover::after { transform: scaleX(1); }
        .stat-card:hover { border-color: var(--primary-orange); transform: translateY(-6px); box-shadow: var(--shadow-hover); }

        .stat-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            background: var(--orange-light);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: var(--primary-orange);
            transition: var(--transition);
        }

        .stat-card:hover .stat-icon {
            background: var(--primary-orange);
            color: white;
            transform: scale(1.08) rotate(-5deg);
        }

        .stat-badge {
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .badge-orange  { background: var(--orange-light); color: var(--primary-orange); }
        .badge-success { background: var(--success-light); color: var(--success); }
        .badge-info    { background: var(--info-light); color: var(--info); }
        .badge-warning { background: var(--warning-light); color: var(--warning); }

        .stat-value {
            font-size: 2.4rem;
            font-weight: 900;
            color: var(--primary-orange);
            line-height: 1;
            margin-bottom: 4px;
            letter-spacing: -1px;
        }

        .stat-label {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--dark-gray);
            margin-bottom: 2px;
        }

        .stat-desc {
            font-size: 0.82rem;
            color: var(--text-gray);
            font-weight: 400;
        }

        /* ── Quick Access Cards ── */
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1.1rem;
        }

        .card {
            background: var(--white);
            border: 1px solid var(--border-gray);
            border-radius: 20px;
            padding: 1.8rem 1.2rem 1.5rem;
            text-decoration: none;
            color: var(--dark-gray);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.8rem;
            box-shadow: var(--shadow-soft);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .card::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 4px;
            background: var(--primary-orange);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .card:hover::after { transform: scaleX(1); }
        .card:hover { border-color: var(--primary-orange); transform: translateY(-6px); box-shadow: var(--shadow-hover); }

        .card-icon {
            width: 62px;
            height: 62px;
            background: var(--orange-light);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary-orange);
            transition: var(--transition);
        }

        .card:hover .card-icon {
            background: var(--primary-orange);
            color: var(--white);
            transform: scale(1.08) rotate(-5deg);
        }

        .card-title {
            font-weight: 700;
            font-size: 0.9rem;
            text-align: center;
            color: var(--dark-gray);
            line-height: 1.3;
        }

        /* ── Chart Containers ── */
        .charts-section { margin-top: 0.5rem; }

        .chart-container {
            background: var(--white);
            border: 1px solid var(--border-gray);
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            margin: 0 0 1.5rem;
            padding: 1.8rem 2rem;
            width: 100%;
            transition: var(--transition);
            cursor: pointer;
        }

        .chart-container:hover {
            border-color: rgba(242,128,24,0.35);
            box-shadow: var(--shadow-hover);
        }

        .chart-container.large {
            box-shadow: 0 16px 50px rgba(242,128,24,0.15);
            border-color: var(--primary-orange);
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.4rem;
            flex-wrap: wrap;
            gap: 0.8rem;
        }

        .chart-header h1 {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--dark-gray);
            letter-spacing: -0.01em;
        }

        .chart-badge {
            background: var(--orange-light);
            color: var(--primary-orange);
            padding: 4px 14px;
            border-radius: 50px;
            font-size: 0.78rem;
            font-weight: 700;
            border: 1.5px solid rgba(242,128,24,0.2);
        }

        .chart-sub-label {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-gray);
            margin: 1.4rem 0 0.8rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        canvas {
            display: block;
            margin: 0 auto;
            width: 100% !important;
            height: 340px !important;
        }

        /* ── Scroll to Top ── */
        .scroll-to-top {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: var(--primary-orange);
            color: var(--white);
            border: none;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: none;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: var(--shadow-active);
            transition: var(--transition);
            font-size: 1.1rem;
            z-index: 999;
        }

        .scroll-to-top:hover { background: #d4700f; transform: scale(1.1) translateY(-2px); }

        /* ── Reveal Animations ── */
        .reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* ── Responsive ── */
        @media (max-width: 1024px) {
            .page-body { padding: 1.2rem 1.5rem 3rem; }
            .top-header { padding: 0.9rem 1.5rem; }
        }
        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content { margin-left: 0; width: 100%; }
            .cards-container { grid-template-columns: repeat(2, 1fr); }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .user-info { display: none; }
        }
        @media (max-width: 480px) {
            .cards-container { grid-template-columns: repeat(2, 1fr); }
            .stats-grid { grid-template-columns: 1fr; }
            .status-bar { border-radius: 14px; padding: 0.8rem 1rem; }
        }
    </style>
</head>
<body>

<!-- ═══════════ SIDEBAR ═══════════ -->
<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <img src="atire.png" alt="Atire Logo" class="sidebar-logo">
            
        </div>
        <div class="sidebar-user-profile">
           
           
        </div>
    </div>


    <ul class="sidebar-menu">
        <li class="sidebar-menu-item">
            <a href="#"><i class="fas fa-clipboard-list"></i>Work Order</a>
            <ul class="sidebar-submenu">
                <li><a href="add_workorder.php">Work order - New</a></li>
                <li><a href="comparee.php">Work order - Verify</a></li>
                <li><a href="workdelete.php">Work order - Remove</a></li>
                <li><a href="import22bnew32.php">Pause Stock Orders</a></li>
                <li><a href="stock_order_rep.php">Resume Stock Orders</a></li>
                <li><a href="worder_rev_button.php">Work order - Revise</a></li>
                <li><a href="add_work_order_hold.php">Work order - Hold</a></li>
                <li><a href="dispatchR.php">Work order - Reverse</a></li>
            </ul>
        </li>
        <li class="sidebar-menu-item">
            <a href="#"><i class="fas fa-chart-line"></i>Production Plan</a>
            <ul class="sidebar-submenu">
                <li><a href="convertstock.php">Plan - Work order</a></li>
                <li><a href="deleteplan.php">Plan - Remove</a></li>
                <li><a href="select_cav_prev.php">Plan - Get Auto Cavity</a></li>
                <li><a href="date_update12.php">Plan - Update</a></li>
                <li><a href="updatedate.php">Plan - Date Update</a></li>
                <li><a href="time_range2.php">Plan - Shift Wise</a></li>
                <li><a href="date_update.php">Plan - Date Change</a></li>
                <li><a href="stock_add.php">Plan - Stock</a></li>
                <li><a href="plan_import.php">Plan - Daily</a></li>
            </ul>
        </li>
        <li class="sidebar-menu-item">
            <a href="#"><i class="fas fa-circle-dot"></i>Tires Input</a>
            <ul class="sidebar-submenu">
                <li><a href="add_daily_production.php">Daily Production</a></li>
            </ul>
        </li>
        <li class="sidebar-menu-item">
            <a href="#"><i class="fas fa-times-circle"></i>Tires Output - QA</a>
            <ul class="sidebar-submenu">
                <li><a href="add_reject.php">Daily Reject</a></li>
                <li><a href="add_rejectb.php">Daily B Grade</a></li>
                <li><a href="#">Daily Hold</a></li>
            </ul>
        </li>
        <li class="sidebar-menu-item">
            <a href="#"><i class="fas fa-truck-loading"></i>Tire Output - Sales</a>
            <ul class="sidebar-submenu">
                <li><a href="dispatch.php">Order Dispatch</a></li>
            </ul>
        </li>
        <li class="sidebar-menu-item">
            <a href="#"><i class="fas fa-cog"></i>System</a>
            <ul class="sidebar-submenu">
                <li><a href="get_z.php">Refresh System</a></li>
                <li><a href="edit_data.php">Edit Data</a></li>
                <li><a href="notice_mangement.php">Edit Notice</a></li>
            </ul>
        </li>
        <li class="sidebar-menu-item">
            <a href="#"><i class="fas fa-rotate"></i>System Update</a>
            <ul class="sidebar-submenu">
                <li><a href="switch.php">Update System</a></li>
            </ul>
        </li>
    </ul>
</div>


<!-- ═══════════ MAIN CONTENT ═══════════ -->
<div class="main-content">

    <!-- Top Header -->
    <div class="top-header">
        <div class="top-header-left">
            <h1>Production <span>Dashboard</span></h1>
            <p>Real-time overview of your production operations</p>
        </div>
        <div class="top-header-right">
            <div class="user-info">
                <h4><?php echo $_SESSION['emp_name']; ?></h4>
                <p><?php echo $_SESSION['User_type']; ?></p>
            </div>
            <div class="user-avatar-wrap">
                <img src="user_profile/<?php echo $_SESSION['emp_pro']; ?>" alt="Avatar"
                     onerror="this.style.display='none'">
            </div>
        </div>
    </div>

    <!-- Page Body -->
    <div class="page-body">

        <!-- Status Bar -->
        <div class="status-bar">
            <marquee direction="right" onmouseover="this.stop();" onmouseout="this.start();">
                FG Stock: <?php echo $totalCStock; ?> &nbsp;|&nbsp;
                Total Requirement: <?php echo $totalnew; ?> &nbsp;|&nbsp;
                Free Stock: <?php echo $totalCStockk; ?> &nbsp;|&nbsp;
                To be produced: <?php echo $totaltobe; ?> &nbsp;|&nbsp;
                On Hand Work Orders: <?php echo $totalcount; ?> &nbsp;|&nbsp;
                Production Complete: <?php echo $result; ?> &nbsp;|&nbsp;
                To be Produced: <?php echo $erpCount; ?> &nbsp;|&nbsp;
                Cavity Utilization: 59 &nbsp;|&nbsp;
                Current Month Dispatched: <?php echo $totalcountt; ?>
            </marquee>
        </div>

       

        <!-- Quick Access -->
        <h2 class="section-title reveal">
            <span class="title-icon"><i class="fas fa-th-large"></i></span>
            Quick Access
            <span class="title-line"></span>
        </h2>

        <div class="cards-container reveal">
            <a href="work_order_show.php" class="card">
                <div class="card-icon"><i class="fas fa-tasks"></i></div>
                <div class="card-title">Work Order</div>
            </a>
            <a href="stock_button.php" class="card">
                <div class="card-icon"><i class="fas fa-boxes"></i></div>
                <div class="card-title">Stock Report</div>
            </a>
            <a href="dispatch_view.php" class="card">
                <div class="card-icon"><i class="fas fa-truck"></i></div>
                <div class="card-title">Dispatched Work Order</div>
            </a>
            <a href="mold_change.php" class="card">
                <div class="card-icon"><i class="fas fa-cogs"></i></div>
                <div class="card-title">Mold Changing</div>
            </a>
            <a href="order_quantity.php" class="card">
                <div class="card-icon"><i class="fas fa-clipboard-list"></i></div>
                <div class="card-title">On Hand Orders — Item Wise</div>
            </a>
            <a href="daily_production.php" class="card">
                <div class="card-icon"><i class="fas fa-industry"></i></div>
                <div class="card-title">Daily Production</div>
            </a>
            <a href="rejectbutton.php" class="card">
                <div class="card-icon"><i class="fas fa-ban"></i></div>
                <div class="card-title">Daily Reject</div>
            </a>
            <a href="bom_all.php" class="card">
                <div class="card-icon"><i class="fas fa-weight"></i></div>
                <div class="card-title">Green Tire Weight</div>
            </a>
            <a href="planbuttoon.php" class="card">
                <div class="card-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="card-title">Planning Reports</div>
            </a>
            <a href="show_mixing.php" class="card">
                <div class="card-icon"><i class="fas fa-blender"></i></div>
                <div class="card-title">Compound Production</div>
            </a>
            <a href="mixingdash_stock.php" class="card">
                <div class="card-icon"><i class="fas fa-flask"></i></div>
                <div class="card-title">Compound Stock</div>
            </a>
            <a href="lab_qr_details.php" class="card">
                <div class="card-icon"><i class="fas fa-qrcode"></i></div>
                <div class="card-title">QR Code Details</div>
            </a>
            <a href="band_summery.php" class="card">
                <div class="card-icon"><i class="fas fa-cubes"></i></div>
                <div class="card-title">Steel Band Summary</div>
            </a>
            <a href="dis_mold.php" class="card">
                <div class="card-icon"><i class="fas fa-circle-notch"></i></div>
                <div class="card-title">Mold Capacity</div>
            </a>
            <a href="all_check_se.php" class="card">
                <div class="card-icon"><i class="fas fa-barcode"></i></div>
                <div class="card-title">Check Serial Number</div>
            </a>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <h2 class="section-title reveal">
                <span class="title-icon"><i class="fas fa-chart-bar"></i></span>
                Production Analytics
                <span class="title-line"></span>
            </h2>

            <!-- Daily Production + Weight -->
            <div class="chart-container reveal" id="dailyContainer">
                <div class="chart-header">
                    <h1>Daily Tire Production — This Month</h1>
                    <span class="chart-badge"><i class="fas fa-calendar-day"></i> Monthly View</span>
                </div>
                <div class="chart-sub-label">
                    <i class="fas fa-circle-dot" style="color:var(--primary-orange);"></i> Tire Production
                </div>
                <canvas id="productionChart"></canvas>
                <div class="chart-sub-label" style="margin-top:1.8rem;">
                    <i class="fas fa-weight" style="color:var(--success);"></i> Tire Weight (kg)
                </div>
                <canvas id="weightChart"></canvas>
            </div>

            <!-- Monthly Production -->
            <div class="chart-container reveal" id="monthlyContainer">
                <div class="chart-header">
                    <h1>Monthly Tire Production — This Year</h1>
                    <span class="chart-badge"><i class="fas fa-chart-line"></i> Year View</span>
                </div>
                <canvas id="monthlyChart"></canvas>
            </div>

            <!-- Stock by Brand -->
            <div class="chart-container reveal" id="stockContainer">
                <div class="chart-header">
                    <h1>Total Stock by Brand</h1>
                    <span class="chart-badge"><i class="fas fa-boxes"></i> All Brands</span>
                </div>
                <canvas id="stockChart"></canvas>
            </div>
        </div>

    </div><!-- /.page-body -->
</div><!-- /.main-content -->


<!-- ═══════════ SCRIPTS ═══════════ -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Scroll-to-top ── */
    const scrollBtn = document.createElement('button');
    scrollBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    scrollBtn.className = 'scroll-to-top';
    document.body.appendChild(scrollBtn);
    scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    window.addEventListener('scroll', () => {
        scrollBtn.style.display = window.pageYOffset > 300 ? 'flex' : 'none';
    });

    /* ── Toggle chart enlarge ── */
    ['dailyContainer', 'monthlyContainer', 'stockContainer'].forEach(id => {
        document.getElementById(id).addEventListener('click', function () {
            this.classList.toggle('large');
        });
    });

    /* ── Scroll reveal ── */
    const observer = new IntersectionObserver(entries => {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

    /* ── Chart.js defaults ── */
    Chart.defaults.font.family = "'SF UI Display', -apple-system, sans-serif";
    Chart.defaults.font.weight = '600';
    Chart.defaults.color       = '#555555';

    /* ── Daily charts ── */
    fetch('get_daily_data.php')
        .then(r => r.json())
        .then(data => {
            new Chart(document.getElementById('productionChart'), {
                type: 'bar',
                data: {
                    labels: data.days,
                    datasets: [{
                        label: 'Daily Tire Production',
                        data: data.totals,
                        backgroundColor: 'rgba(242,128,24,0.18)',
                        borderColor: '#F28018',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { title: { display: true, text: 'Date', font: { weight: '700' } }, grid: { color: 'rgba(0,0,0,0.04)' } },
                        y: { beginAtZero: true, title: { display: true, text: 'Total Production', font: { weight: '700' } }, grid: { color: 'rgba(0,0,0,0.06)' } }
                    }
                }
            });

            new Chart(document.getElementById('weightChart'), {
                type: 'bar',
                data: {
                    labels: data.days,
                    datasets: [{
                        label: 'Daily Tire Weight',
                        data: data.stgreenweights,
                        backgroundColor: 'rgba(39,174,96,0.18)',
                        borderColor: '#27ae60',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { title: { display: true, text: 'Date', font: { weight: '700' } }, grid: { color: 'rgba(0,0,0,0.04)' } },
                        y: { beginAtZero: true, title: { display: true, text: 'Total Weight (kg)', font: { weight: '700' } }, grid: { color: 'rgba(0,0,0,0.06)' } }
                    }
                }
            });
        })
        .catch(e => console.error('Daily data error:', e));

    /* ── Monthly chart ── */
    fetch('get_monthly_data.php')
        .then(r => r.json())
        .then(data => {
            new Chart(document.getElementById('monthlyChart'), {
                type: 'bar',
                data: {
                    labels: data.months,
                    datasets: [{
                        label: 'Monthly Tire Production',
                        data: data.totals,
                        backgroundColor: 'rgba(52,152,219,0.18)',
                        borderColor: '#3498db',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { title: { display: true, text: 'Month', font: { weight: '700' } }, grid: { color: 'rgba(0,0,0,0.04)' } },
                        y: { beginAtZero: true, title: { display: true, text: 'Total Tire Production', font: { weight: '700' } }, grid: { color: 'rgba(0,0,0,0.06)' } }
                    }
                }
            });
        })
        .catch(e => console.error('Monthly data error:', e));

});
</script>

<?php
/* ── Stock by Brand Chart ── */
$servername  = "localhost";
$db_username = "planatir_task_managemen";
$db_password = "Bishan@1919";
$dbname      = "planatir_task_managemen";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT brand, SUM(cstock) AS total_stock FROM realstock GROUP BY brand ORDER BY total_stock DESC");
    $stockData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stockData = [];
}
?>

<script>
(function () {
    const chartData = <?php echo json_encode($stockData); ?>;
    if (!chartData.length) return;

    const palette = [
        'rgba(242,128,24,0.72)','rgba(39,174,96,0.72)','rgba(52,152,219,0.72)',
        'rgba(155,89,182,0.72)','rgba(231,76,60,0.72)','rgba(243,156,18,0.72)',
        'rgba(26,188,156,0.72)','rgba(52,73,94,0.72)',
    ];
    const borders = palette.map(c => c.replace('0.72','1'));

    new Chart(document.getElementById('stockChart'), {
        type: 'bar',
        data: {
            labels: chartData.map(d => d.brand),
            datasets: [{
                label: 'Total Stock',
                data: chartData.map(d => d.total_stock),
                backgroundColor: chartData.map((_, i) => palette[i % palette.length]),
                borderColor:     chartData.map((_, i) => borders[i % borders.length]),
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: 'rgba(0,0,0,0.04)' } },
                y: { beginAtZero: true, title: { display: true, text: 'Units in Stock', font: { weight: '700' } }, grid: { color: 'rgba(0,0,0,0.06)' } }
            }
        }
    });
})();
</script>

</body>
</html>