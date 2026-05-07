<?php
// export.php
session_start();
include('include/config.php');

if (!isset($_SESSION["aid"])) {
    header("Location: index.php");
    exit();
}

$adminId = intval($_SESSION["aid"]);

$stmt = mysqli_prepare($con, "SELECT * FROM admin WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $adminId);
mysqli_stmt_execute($stmt);
$adminResult = mysqli_stmt_get_result($stmt);
$adminData   = mysqli_fetch_assoc($adminResult);
mysqli_stmt_close($stmt);

$isAccountManager = false;
$acmRef      = '';
$customerIds = [];

$stmt = mysqli_prepare($con, "SELECT acm_ref FROM account_managers WHERE id = ? AND status = 'active'");
mysqli_stmt_bind_param($stmt, "i", $adminId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $isAccountManager = true;
    $acmRef = $row['acm_ref'];

    $stmt2 = mysqli_prepare($con, "SELECT cus_id FROM users WHERE acm_ref = ?");
    mysqli_stmt_bind_param($stmt2, "s", $acmRef);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);
    while ($customer = mysqli_fetch_assoc($result2)) {
        $customerIds[] = $customer['cus_id'];
    }
    mysqli_stmt_close($stmt2);
}
mysqli_stmt_close($stmt);

$customerIdsStr = '';
if ($isAccountManager && !empty($customerIds)) {
    $customerIdsStr = "'" . implode("','", array_map(function($id) use ($con) {
        return mysqli_real_escape_string($con, $id);
    }, $customerIds)) . "'";
}

// ── FETCH STATISTICS ────────────────────────────────────────────────
$totalOrders            = 0;
$pendingPriceListOrders = 0;
$priceListCount         = 0;
$pricePendingOrders     = [];

if ($isAccountManager && !empty($customerIds)) {

    $r = mysqli_query($con, "SELECT COUNT(*) AS total FROM tire_orders tord
                             LEFT JOIN users u ON u.id = tord.customer_id
                             WHERE u.cus_id IN ($customerIdsStr)");
    if ($r) { $totalOrders = mysqli_fetch_assoc($r)['total']; }

    $r = mysqli_query($con, "SELECT COUNT(*) AS total FROM tire_orders tord
                             LEFT JOIN users u ON u.id = tord.customer_id
                             WHERE u.cus_id IN ($customerIdsStr)
                             AND tord.status = 'price_pending'");
    if ($r) { $pendingPriceListOrders = mysqli_fetch_assoc($r)['total']; }

    $r = mysqli_query($con, "SELECT COUNT(*) AS total FROM customer_items WHERE cus_id IN ($customerIdsStr)");
    if ($r) { $priceListCount = mysqli_fetch_assoc($r)['total']; }

    // Fetch price_pending orders WITH item codes from order items
    $r = mysqli_query($con, "SELECT tord.order_id, tord.order_reference, tord.order_date,
                                    u.cus_id, u.fullname AS customer_name
                             FROM tire_orders tord
                             LEFT JOIN users u ON u.id = tord.customer_id
                             WHERE u.cus_id IN ($customerIdsStr)
                             AND tord.status = 'price_pending'
                             ORDER BY tord.order_date DESC LIMIT 20");
    if ($r) { while ($row = mysqli_fetch_assoc($r)) { $pricePendingOrders[] = $row; } }

} else {

    $r = mysqli_query($con, "SELECT COUNT(*) AS total FROM tire_orders");
    if ($r) { $totalOrders = mysqli_fetch_assoc($r)['total']; }

    $r = mysqli_query($con, "SELECT COUNT(*) AS total FROM tire_orders WHERE status = 'price_pending'");
    if ($r) { $pendingPriceListOrders = mysqli_fetch_assoc($r)['total']; }

    $r = mysqli_query($con, "SELECT COUNT(*) AS total FROM customer_items");
    if ($r) { $priceListCount = mysqli_fetch_assoc($r)['total']; }

    $r = mysqli_query($con, "SELECT tord.order_id, tord.order_reference, tord.order_date,
                                    u.cus_id, u.fullname AS customer_name
                             FROM tire_orders tord
                             LEFT JOIN users u ON u.id = tord.customer_id
                             WHERE tord.status = 'price_pending'
                             ORDER BY tord.order_date DESC LIMIT 20");
    if ($r) { while ($row = mysqli_fetch_assoc($r)) { $pricePendingOrders[] = $row; } }
}

// ── For each price_pending order, fetch its item codes ──────────────────────
// This is used to build the deep-link to customer_items.php with auto-filter
foreach ($pricePendingOrders as &$po) {
    $oid = mysqli_real_escape_string($con, $po['order_id']);

    // Fetch item codes from this order
    $ri = mysqli_query($con, "SELECT icode FROM tire_order_items WHERE order_id = '$oid'");
    $icodes = [];
    if ($ri) {
        while ($ir = mysqli_fetch_assoc($ri)) {
            if (!empty($ir['icode'])) $icodes[] = $ir['icode'];
        }
    }
    $po['icodes'] = $icodes;

    // Build the deep-link URL — include cus_id, order_id, AND icodes[]
    $params = [
        'cus_id'   => $po['cus_id'],
        'order_id' => $po['order_id'],
        'icodes'   => $icodes,          // passed as icodes[]=AAA&icodes[]=BBB&...
    ];
    $po['price_link'] = 'price-list.php?' . http_build_query($params);
}
unset($po);
unset($po);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Customer Service Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --orange:          #F28018;
            --orange-dark:     #d96d0c;
            --orange-xlight:   #FFF4E8;
            --orange-mid:      rgba(242,128,24,.13);
            --orange-glow:     rgba(242,128,24,.28);
            --white:           #ffffff;
            --off-white:       #F8F8F8;
            --gray-50:         #F5F5F5;
            --gray-100:        #EEEEEE;
            --gray-200:        #E0E0E0;
            --gray-400:        #9E9E9E;
            --gray-600:        #616161;
            --gray-800:        #2C2C2C;
            --blue:            #1565C0;
            --blue-light:      rgba(21,101,192,.10);
            --green:           #2E7D32;
            --green-light:     rgba(46,125,50,.10);
            --red:             #D32F2F;
            --red-light:       rgba(211,47,47,.10);
            --shadow-xs:       0 1px 3px rgba(0,0,0,.07);
            --shadow-sm:       0 2px 8px rgba(0,0,0,.08);
            --shadow-md:       0 4px 18px rgba(0,0,0,.10);
            --shadow-orange:   0 6px 24px rgba(242,128,24,.25);
            --radius-sm:       8px;
            --radius-md:       14px;
            --radius-lg:       20px;
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        html { scroll-behavior:smooth; }
        body {
            font-family:'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--off-white); color: var(--gray-800);
            min-height:100vh; display:flex; flex-direction:column;
            -webkit-font-smoothing:antialiased;
        }

        /* TOPBAR */
        .topbar {
            position:sticky; top:0; z-index:200; height:64px;
            background:var(--white); border-bottom:1px solid var(--gray-200);
            box-shadow:var(--shadow-xs); display:flex; align-items:center;
            padding:0 2rem; gap:1rem;
        }
       .brand { display:flex; align-items:center; gap:.65rem; text-decoration:none; flex-shrink:0; }
      
        .brand-icon img {
            width:150px; height:150px; object-fit:contain; border-radius:10px;
            display:block;
        }
        .brand-name { font-size:.95rem; font-weight:800; color:var(--gray-800); letter-spacing:-.02em; }
        .brand-name em { font-style:normal; color:var(--orange); }
        .topnav { display:flex; align-items:center; gap:2px; flex:1; }
        .nav-link {
            display:flex; align-items:center; gap:.45rem; padding:.48rem .9rem;
            border-radius:var(--radius-sm); color:var(--gray-600); font-size:.84rem;
            font-weight:500; text-decoration:none; transition:background .15s, color .15s; white-space:nowrap;
        }
        .topnav { display:flex; align-items:center; gap:2px; flex:1; }
        .nav-link {
            display:flex; align-items:center; gap:.45rem; padding:.48rem .9rem;
            border-radius:var(--radius-sm); color:var(--gray-600); font-size:.84rem;
            font-weight:500; text-decoration:none; transition:background .15s, color .15s; white-space:nowrap;
        }
        .nav-link i { font-size:.78rem; }
        .nav-link:hover { background:var(--gray-50); color:var(--gray-800); }
        .nav-link.active { background:var(--orange-mid); color:var(--orange); font-weight:700; }

        /* NOTIFICATION BELL */
        .notif-wrap { position:relative; flex-shrink:0; }
        .notif-btn {
            width:40px; height:40px; border-radius:50%;
            border:1.5px solid var(--gray-200); background:var(--white);
            display:flex; align-items:center; justify-content:center;
            font-size:.95rem; color:var(--gray-600); cursor:pointer; position:relative;
            transition:border-color .2s, color .2s;
        }
        .notif-btn:hover { border-color:var(--orange); color:var(--orange); }
        .notif-badge {
            position:absolute; top:-4px; right:-4px; min-width:18px; height:18px;
            background:var(--red); color:#fff; font-size:.62rem; font-weight:800;
            border-radius:999px; display:flex; align-items:center; justify-content:center;
            padding:0 4px; border:2px solid var(--white); animation:badgePulse 2.2s infinite;
        }
        @keyframes badgePulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.18)} }
        .notif-panel {
            display:none; position:absolute; top:calc(100% + 10px); right:0;
            width:390px; background:var(--white); border:1px solid var(--gray-200);
            border-radius:var(--radius-md); box-shadow:var(--shadow-md); z-index:300; overflow:hidden;
        }
        .notif-panel.open { display:block; animation:dropIn .18s ease; }
        @keyframes dropIn { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
        .np-header {
            padding:.9rem 1.25rem .75rem; border-bottom:1px solid var(--gray-100);
            display:flex; align-items:center; justify-content:space-between;
        }
        .np-header h4 { font-size:.85rem; font-weight:700; color:var(--gray-800); display:flex; align-items:center; gap:.4rem; }
        .np-header h4 i { color:var(--orange); }
        .np-count { background:var(--red-light); color:var(--red); font-size:.68rem; font-weight:700; padding:2px 8px; border-radius:999px; }
        .np-list { max-height:340px; overflow-y:auto; }
        .np-list::-webkit-scrollbar { width:4px; }
        .np-list::-webkit-scrollbar-thumb { background:var(--gray-200); border-radius:4px; }
        .np-item {
            display:flex; align-items:flex-start; gap:.75rem;
            padding:.75rem 1.25rem; border-bottom:1px solid var(--gray-100); text-decoration:none; transition:background .15s;
        }
        .np-item:last-child { border-bottom:none; }
        .np-item:hover { background:var(--orange-xlight); }
        .np-dot { width:8px; height:8px; border-radius:50%; background:var(--orange); margin-top:4px; flex-shrink:0; }
        .np-body { flex:1; min-width:0; }
        .np-title { font-size:.8rem; font-weight:600; color:var(--gray-800); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .np-sub { font-size:.74rem; color:var(--gray-400); margin-top:1px; }
        .np-actions { display:flex; gap:.4rem; margin-top:.45rem; }
        .np-btn {
            display:inline-flex; align-items:center; gap:.3rem; padding:.26rem .65rem;
            border-radius:6px; font-size:.7rem; font-weight:700; text-decoration:none; white-space:nowrap; transition:all .15s;
        }
        .np-btn-price { background:var(--orange-mid); color:var(--orange); border:1px solid rgba(242,128,24,.25); }
        .np-btn-price:hover { background:var(--orange); color:#fff; }
        .np-btn-order { background:var(--blue-light); color:var(--blue); border:1px solid rgba(21,101,192,.2); }
        .np-btn-order:hover { background:var(--blue); color:#fff; }
        .np-time { font-size:.7rem; color:var(--gray-400); white-space:nowrap; flex-shrink:0; }
        .np-empty { padding:1.75rem 1.25rem; text-align:center; font-size:.82rem; color:var(--gray-400); }
        .np-footer { padding:.6rem 1.25rem; border-top:1px solid var(--gray-100); text-align:center; }
        .np-footer a { font-size:.78rem; font-weight:600; color:var(--orange); text-decoration:none; }
        .np-footer a:hover { text-decoration:underline; }

        /* USER CHIP */
        .user-chip {
            display:flex; align-items:center; gap:.6rem; padding:.3rem .85rem .3rem .3rem;
            border:1.5px solid var(--gray-200); border-radius:999px; background:var(--white); flex-shrink:0;
        }
        .user-avatar {
            width:30px; height:30px; background:var(--orange); border-radius:50%;
            display:flex; align-items:center; justify-content:center; font-size:.72rem; font-weight:800; color:#fff;
        }
        .user-name { font-size:.8rem; font-weight:600; color:var(--gray-800); max-width:120px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

        /* ALERT BANNER */
        .alert-banner {
            background: linear-gradient(90deg, #FFF4E8 0%, #FDE8CC 100%);
            border-left:4px solid var(--orange); border-bottom:1px solid #F5CFA0;
            padding:.85rem 2rem; display:flex; align-items:center; gap:1rem; flex-wrap:wrap;
        }
        .ab-icon {
            width:36px; height:36px; border-radius:50%; background:var(--orange); color:#fff;
            display:flex; align-items:center; justify-content:center; font-size:.95rem; flex-shrink:0;
            animation:bellRing 3.5s ease infinite;
        }
        @keyframes bellRing {
            0%,85%,100%{transform:rotate(0)} 88%{transform:rotate(-14deg)}
            92%{transform:rotate(14deg)} 96%{transform:rotate(-8deg)}
        }
        .ab-text { flex:1; min-width:180px; }
        .ab-text strong { display:block; font-size:.88rem; font-weight:700; color:var(--gray-800); margin-bottom:2px; }
        .ab-text span { font-size:.78rem; color:var(--gray-600); }
        .ab-link {
            padding:.42rem 1.05rem; background:var(--orange); color:#fff;
            border-radius:var(--radius-sm); font-size:.78rem; font-weight:600;
            text-decoration:none; white-space:nowrap; transition:background .2s;
        }
        .ab-link:hover { background:var(--orange-dark); }
        .ab-dismiss {
            background:none; border:none; cursor:pointer; color:var(--gray-400);
            font-size:.95rem; padding:.25rem; transition:color .2s;
        }
        .ab-dismiss:hover { color:var(--gray-800); }

        /* PAGE LAYOUT */
        .page { flex:1; padding:2.5rem 2rem; max-width:1280px; margin:0 auto; width:100%; }
        .page-header { display:flex; align-items:flex-end; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1.75rem; }
        .page-header h1 { font-size:1.75rem; font-weight:800; color:var(--gray-800); letter-spacing:-.03em; line-height:1.2; }
        .page-header h1 em { font-style:normal; color:var(--orange); }
        .page-header p { font-size:.88rem; color:var(--gray-600); margin-top:.25rem; }
        .acm-pill {
            display:inline-flex; align-items:center; gap:.45rem; padding:.42rem 1rem;
            background:#FFF8E1; color:#E65100; border:1px solid #FFCC80;
            border-radius:999px; font-size:.76rem; font-weight:600;
        }
        .odivider { height:3px; background:linear-gradient(90deg, var(--orange) 0%, rgba(242,128,24,.3) 55%, transparent 100%); border-radius:2px; margin-bottom:2rem; }

        /* STAT CARDS */
        .stat-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1.25rem; margin-bottom:2.5rem; }
        .stat-card {
            background:var(--white); border:1px solid var(--gray-200);
            border-radius:var(--radius-lg); padding:1.75rem 1.75rem 1.5rem;
            position:relative; overflow:hidden; box-shadow:var(--shadow-xs);
            transition:transform .25s, box-shadow .25s; animation:riseUp .5s ease both;
        }
        .stat-card:nth-child(1){animation-delay:.04s} .stat-card:nth-child(2){animation-delay:.12s} .stat-card:nth-child(3){animation-delay:.20s}
        @keyframes riseUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        .stat-card:hover { transform:translateY(-6px); box-shadow:var(--shadow-orange); }
        .stat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; border-radius:var(--radius-lg) var(--radius-lg) 0 0; }
        .c-orange::before { background:linear-gradient(90deg,#F28018,#FFAB40); }
        .c-blue::before   { background:linear-gradient(90deg,#1565C0,#42A5F5); }
        .c-green::before  { background:linear-gradient(90deg,#2E7D32,#66BB6A); }
        .stat-card::after { content:''; position:absolute; bottom:-30px; right:-30px; width:110px; height:110px; border-radius:50%; pointer-events:none; filter:blur(38px); }
        .c-orange::after { background:rgba(242,128,24,.14); } .c-blue::after { background:rgba(21,101,192,.10); } .c-green::after { background:rgba(46,125,50,.10); }
        .sc-top { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:1.25rem; }
        .sc-label { font-size:.73rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--gray-400); }
        .sc-icon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.05rem; flex-shrink:0; }
        .c-orange .sc-icon { background:var(--orange-mid); color:var(--orange); }
        .c-blue   .sc-icon { background:var(--blue-light); color:var(--blue); }
        .c-green  .sc-icon { background:var(--green-light); color:var(--green); }
        .sc-value { font-size:clamp(2.4rem,4vw,3.2rem); font-weight:900; letter-spacing:-.04em; line-height:1; margin-bottom:.4rem; }
        .c-orange .sc-value { color:var(--orange); } .c-blue .sc-value { color:var(--blue); } .c-green .sc-value { color:var(--green); }
        .sc-desc { font-size:.76rem; color:var(--gray-400); }

        /* QUICK ACTIONS */
        .section-label { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:var(--gray-400); margin-bottom:.9rem; }
        .action-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(230px,1fr)); gap:1rem; }
        .action-card {
            display:flex; align-items:center; gap:1rem; padding:1.1rem 1.35rem;
            background:var(--white); border:1.5px solid var(--gray-200); border-radius:var(--radius-md);
            text-decoration:none; color:var(--gray-600); font-size:.86rem; font-weight:500;
            box-shadow:var(--shadow-xs); transition:all .2s ease; animation:riseUp .5s ease both;
        }
        .action-card:nth-child(1){animation-delay:.28s} .action-card:nth-child(2){animation-delay:.36s} .action-card:nth-child(3){animation-delay:.44s}
        .action-card:hover { border-color:var(--orange); color:var(--gray-800); box-shadow:var(--shadow-orange); transform:translateY(-3px); }
        .ac-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:.9rem; flex-shrink:0; background:var(--orange-mid); color:var(--orange); transition:background .2s, color .2s; }
        .action-card:hover .ac-icon { background:var(--orange); color:#fff; }
        .ac-text { flex:1; } .ac-text strong { display:block; font-weight:700; color:var(--gray-800); margin-bottom:1px; }
        .ac-arrow { font-size:.68rem; color:var(--gray-400); transition:transform .2s, color .2s; }
        .action-card:hover .ac-arrow { transform:translateX(4px); color:var(--orange); }

        /* ── PRICE PENDING ORDERS PANEL ── */
        .pending-section { margin-top:2.5rem; }
        .pending-header {
            display:flex; align-items:center; justify-content:space-between;
            gap:1rem; flex-wrap:wrap; margin-bottom:1rem;
        }
        .pending-header-left { display:flex; align-items:center; gap:.75rem; }
        .pending-header-left h2 { font-size:1.05rem; font-weight:800; color:var(--gray-800); }
        .pending-badge {
            background:var(--red-light); color:var(--red); font-size:.72rem; font-weight:700;
            padding:.25rem .7rem; border-radius:999px; animation:badgePulse 2.5s infinite;
        }
        .pending-card {
            background:var(--white); border:1px solid var(--gray-200); border-radius:var(--radius-md);
            overflow:hidden; box-shadow:var(--shadow-xs); margin-bottom:.85rem;
            animation:riseUp .45s ease both;
        }
        .pending-card:nth-child(1){animation-delay:.1s} .pending-card:nth-child(2){animation-delay:.18s} .pending-card:nth-child(3){animation-delay:.26s} .pending-card:nth-child(4){animation-delay:.34s}
        .pending-card-header {
            display:flex; align-items:center; gap:1rem; padding:1rem 1.35rem;
            border-bottom:1px solid var(--gray-100); flex-wrap:wrap; cursor:pointer;
            transition:background .15s;
        }
        .pending-card-header:hover { background:var(--orange-xlight); }
        .pending-order-icon {
            width:38px; height:38px; border-radius:10px;
            background:linear-gradient(135deg,#FFF4E8,#FFE0B2);
            border:1.5px solid #FFCC80;
            display:flex; align-items:center; justify-content:center;
            font-size:.9rem; color:var(--orange); flex-shrink:0;
        }
        .pending-order-info { flex:1; min-width:0; }
        .pending-order-ref { font-size:.88rem; font-weight:700; color:var(--gray-800); }
        .pending-order-meta { font-size:.74rem; color:var(--gray-400); margin-top:2px; display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; }
        .pending-order-meta strong { color:var(--gray-600); }
        .pending-status-pill {
            padding:.22rem .7rem; border-radius:999px; font-size:.68rem; font-weight:700;
            background:rgba(211,47,47,.1); color:var(--red); border:1px solid rgba(211,47,47,.2);
            white-space:nowrap;
        }
        .pending-date { font-size:.74rem; color:var(--gray-400); white-space:nowrap; }
        .pending-chevron { color:var(--gray-400); font-size:.75rem; transition:transform .25s; flex-shrink:0; }
        .pending-card.expanded .pending-chevron { transform:rotate(180deg); }

        .pending-card-body { display:none; padding:1.15rem 1.35rem; background:var(--gray-50); }
        .pending-card.expanded .pending-card-body { display:block; }

        .pcb-row { display:flex; align-items:flex-start; gap:1.25rem; flex-wrap:wrap; }
        .pcb-col { flex:1; min-width:200px; }
        .pcb-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--gray-400); margin-bottom:.5rem; }
        .icode-chips { display:flex; flex-wrap:wrap; gap:.4rem; }
        .icode-chip {
            display:inline-flex; align-items:center; gap:.3rem; padding:.22rem .65rem;
            background:var(--blue-light); color:var(--blue); border-radius:6px;
            font-size:.75rem; font-weight:700; font-family:monospace;
        }
        .icode-chip-empty { font-size:.78rem; color:var(--gray-400); font-style:italic; }

        .pcb-actions { display:flex; gap:.6rem; flex-wrap:wrap; margin-top:1rem; padding-top:.85rem; border-top:1px solid var(--gray-200); }
        .pcb-btn {
            display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1.1rem;
            border-radius:var(--radius-sm); font-size:.82rem; font-weight:600;
            text-decoration:none; white-space:nowrap; transition:all .2s; font-family:inherit; border:none; cursor:pointer;
        }
        .pcb-btn-primary { background:var(--orange); color:#fff; box-shadow:0 3px 10px var(--orange-glow); }
        .pcb-btn-primary:hover { background:var(--orange-dark); transform:translateY(-1px); box-shadow:var(--shadow-orange); }
        .pcb-btn-secondary { background:var(--white); color:var(--blue); border:1.5px solid rgba(21,101,192,.25); }
        .pcb-btn-secondary:hover { background:var(--blue-light); border-color:var(--blue); }

        /* FOOTER */
        .page-footer { border-top:1px solid var(--gray-200); padding:1rem 2rem; text-align:center; font-size:.73rem; color:var(--gray-400); }

        @media (max-width:900px) { .stat-grid { grid-template-columns:1fr 1fr; } .stat-card:nth-child(3) { grid-column:span 2; } }
        @media (max-width:640px) {
            .topnav { display:none; } .topbar { padding:0 1rem; } .page { padding:1.5rem 1rem; }
            .stat-grid { grid-template-columns:1fr; } .stat-card:nth-child(3) { grid-column:unset; }
            .page-header { flex-direction:column; align-items:flex-start; }
            .notif-panel { width:320px; right:-50px; } .alert-banner { padding:.85rem 1rem; }
        }
    </style>
</head>
<body>

<!-- TOPBAR -->
<header class="topbar">
   <a href="finace.php" class="brand">
        <div class="brand-icon">
            <img src="atire.png">
        </div>
        <span class="brand-name"> Customer Service</span>
    </a>

  <nav class="topnav">
        <a href="finace.php"         class="nav-link"><i class="fas fa-home"></i> Dashboard</a>
        <a href="orders-list.php"    class="nav-link"><i class="fas fa-shopping-cart"></i> Orders</a>
        <a href="customers.php"      class="nav-link"><i class="fas fa-users"></i> Customers</a>
        <a href="price-list.php" class="nav-link active"><i class="fas fa-tags"></i> Price List</a>
        <a href="reports.php"        class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a>
        <a href="settings.php"       class="nav-link"><i class="fas fa-cog"></i> Settings</a>
</nav>

    <!-- NOTIFICATION BELL -->
    <div class="notif-wrap" id="notifWrap">
        <button class="notif-btn" onclick="toggleNotif(event)" aria-label="Price pending notifications">
            <i class="fas fa-bell"></i>
            <?php if (!empty($pricePendingOrders)): ?>
                <span class="notif-badge"><?php echo count($pricePendingOrders); ?></span>
            <?php endif; ?>
        </button>

        <div class="notif-panel" id="notifPanel">
            <div class="np-header">
                <h4><i class="fas fa-tag"></i> Price Pending Orders</h4>
                <span class="np-count"><?php echo count($pricePendingOrders); ?> order<?php echo count($pricePendingOrders) != 1 ? 's' : ''; ?></span>
            </div>

            <div class="np-list">
                <?php if (empty($pricePendingOrders)): ?>
                    <div class="np-empty">
                        <i class="fas fa-check-circle" style="color:#2E7D32;margin-right:.4rem;"></i>
                        No price pending orders — all clear!
                    </div>
                <?php else: ?>
                    <?php foreach ($pricePendingOrders as $po): ?>
                        <div class="np-item">
                            <div class="np-dot"></div>
                            <div class="np-body">
                                <div class="np-title">
                                    <?php echo htmlspecialchars($po['order_reference'] ?: $po['order_id']); ?>
                                    <?php if (!empty($po['customer_name'])): ?>
                                        &mdash; <?php echo htmlspecialchars($po['customer_name']); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="np-sub">Status: <strong style="color:var(--orange)">Price Pending</strong></div>
                                <div class="np-actions">
                                    <!-- Deep link: opens price-list.php with customer + order pre-selected -->
                                    <a href="<?php echo htmlspecialchars($po['price_link']); ?>" class="np-btn np-btn-price">
                                        <i class="fas fa-tags" style="font-size:.65rem;"></i> Set Prices
                                    </a>
                                    <a href="orders-list.php?order_id=<?php echo urlencode($po['order_id']); ?>" class="np-btn np-btn-order">
                                        <i class="fas fa-eye" style="font-size:.65rem;"></i> View Order
                                    </a>
                                </div>
                            </div>
                            <div class="np-time"><?php echo date('M j', strtotime($po['order_date'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="np-footer">
                <a href="orders-list.php?status=price_pending">View all price pending orders &rarr;</a>
            </div>
        </div>
    </div>

    <div class="user-chip">
        <div class="user-avatar"><?php echo strtoupper(substr($adminData['fullname'], 0, 1)); ?></div>
        <span class="user-name"><?php echo htmlspecialchars($adminData['fullname']); ?></span>
    </div>
</header>

<!-- ALERT BANNER -->
<?php if (!empty($pricePendingOrders)): ?>
<div class="alert-banner" id="alertBanner">
    <div class="ab-icon"><i class="fas fa-bell"></i></div>
    <div class="ab-text">
        <strong>Price Pending Orders Require Attention</strong>
        <span>
            <strong><?php echo count($pricePendingOrders); ?></strong>
            order<?php echo count($pricePendingOrders) != 1 ? 's are' : ' is'; ?>
            currently in <strong>price_pending</strong> status and need<?php echo count($pricePendingOrders) == 1 ? 's' : ''; ?> pricing review.
            Scroll down to set prices directly.
        </span>
    </div>
    <a href="#pendingSection" class="ab-link" onclick="document.getElementById('pendingSection').scrollIntoView({behavior:'smooth'});return false;">
        <i class="fas fa-arrow-down"></i>&nbsp; View Below
    </a>
    <button class="ab-dismiss" onclick="document.getElementById('alertBanner').style.display='none'" title="Dismiss">
        <i class="fas fa-times"></i>
    </button>
</div>
<?php endif; ?>

<!-- MAIN PAGE -->
<main class="page">

    <div class="page-header">
        <div>
            <h1>Dashboard <em>Overview</em></h1>
            <p>Welcome back, <?php echo htmlspecialchars($adminData['fullname']); ?> — here's a snapshot of today's activity.</p>
        </div>
        <?php if ($isAccountManager): ?>
            <div class="acm-pill">
                <i class="fas fa-user-shield"></i>
                Account Manager &mdash; <?php echo count($customerIds); ?> customer<?php echo count($customerIds) != 1 ? 's' : ''; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="odivider"></div>

    <!-- 3 STAT CARDS -->
    <div class="stat-grid">
        <div class="stat-card c-orange">
            <div class="sc-top">
                <span class="sc-label">Total Orders</span>
                <div class="sc-icon"><i class="fas fa-shopping-cart"></i></div>
            </div>
            <div class="sc-value"><?php echo number_format($totalOrders); ?></div>
            <div class="sc-desc">All-time orders in the system</div>
        </div>
        <div class="stat-card c-blue">
            <div class="sc-top">
                <span class="sc-label">Pending Price List Orders</span>
                <div class="sc-icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
            <div class="sc-value"><?php echo number_format($pendingPriceListOrders); ?></div>
            <div class="sc-desc">Orders awaiting price confirmation</div>
        </div>
        <div class="stat-card c-green">
            <div class="sc-top">
                <span class="sc-label">Price List</span>
                <div class="sc-icon"><i class="fas fa-tags"></i></div>
            </div>
            <div class="sc-value"><?php echo number_format($priceListCount); ?></div>
            <div class="sc-desc">Active price list entries</div>
        </div>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="section-label">Quick Actions</div>
    <div class="action-grid">
        <a href="orders-list.php" class="action-card">
            <div class="ac-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="ac-text"><strong>All Orders</strong>View and manage every customer order</div>
            <i class="fas fa-chevron-right ac-arrow"></i>
        </a>
        <a href="price-list.php" class="action-card">
            <div class="ac-icon"><i class="fas fa-tags"></i></div>
            <div class="ac-text"><strong>Price List</strong>Browse and edit your active price list</div>
            <i class="fas fa-chevron-right ac-arrow"></i>
        </a>
        <a href="customers.php" class="action-card">
            <div class="ac-icon"><i class="fas fa-users"></i></div>
            <div class="ac-text"><strong>Customers</strong>Manage accounts and contacts</div>
            <i class="fas fa-chevron-right ac-arrow"></i>
        </a>
    </div>

    <!-- ── PRICE PENDING ORDERS SECTION ── -->
    <?php if (!empty($pricePendingOrders)): ?>
    <div class="pending-section" id="pendingSection">

        <div class="pending-header">
            <div class="pending-header-left">
                <h2><i class="fas fa-exclamation-circle" style="color:var(--red);margin-right:.4rem;font-size:.95rem;"></i>Price Pending Orders</h2>
                <span class="pending-badge"><?php echo count($pricePendingOrders); ?> require<?php echo count($pricePendingOrders) == 1 ? 's' : ''; ?> action</span>
            </div>
            <a href="orders-list.php?status=price_pending" style="font-size:.78rem;font-weight:600;color:var(--orange);text-decoration:none;">
                View all in Orders &rarr;
            </a>
        </div>

        <?php foreach ($pricePendingOrders as $idx => $po): ?>
        <div class="pending-card" id="pcard_<?php echo htmlspecialchars($po['order_id']); ?>">
            <div class="pending-card-header" onclick="togglePendingCard('<?php echo htmlspecialchars($po['order_id']); ?>')">
                <div class="pending-order-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div class="pending-order-info">
                    <div class="pending-order-ref">
                        <?php echo htmlspecialchars($po['order_reference'] ?: $po['order_id']); ?>
                    </div>
                    <div class="pending-order-meta">
                        <span><i class="fas fa-user" style="font-size:.65rem;"></i> <strong><?php echo htmlspecialchars($po['customer_name'] ?? 'N/A'); ?></strong></span>
                        <span><i class="fas fa-id-badge" style="font-size:.65rem;"></i> <?php echo htmlspecialchars($po['cus_id']); ?></span>
                        <?php if (!empty($po['icodes'])): ?>
                        <span><i class="fas fa-cubes" style="font-size:.65rem;"></i> <?php echo count($po['icodes']); ?> item<?php echo count($po['icodes']) != 1 ? 's' : ''; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <span class="pending-status-pill"><i class="fas fa-clock" style="font-size:.6rem;margin-right:3px;"></i> Price Pending</span>
                <div class="pending-date"><?php echo date('M j, Y', strtotime($po['order_date'])); ?></div>
                <i class="fas fa-chevron-down pending-chevron"></i>
            </div>

            <div class="pending-card-body">
                <div class="pcb-row">
                    <div class="pcb-col">
                        <div class="pcb-label"><i class="fas fa-barcode" style="color:var(--orange);"></i> &nbsp;Item Codes in this Order</div>
                        <div class="icode-chips">
                            <?php if (!empty($po['icodes'])): ?>
                                <?php foreach ($po['icodes'] as $ic): ?>
                                    <span class="icode-chip"><i class="fas fa-tag" style="font-size:.6rem;"></i><?php echo htmlspecialchars($ic); ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="icode-chip-empty">No item codes found for this order</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="pcb-col" style="flex:0;min-width:180px;">
                        <div class="pcb-label"><i class="fas fa-info-circle" style="color:var(--orange);"></i> &nbsp;Order Details</div>
                        <div style="font-size:.8rem;color:var(--gray-600);line-height:1.7;">
                            <div><strong>Order ID:</strong> <?php echo htmlspecialchars($po['order_id']); ?></div>
                            <div><strong>Customer:</strong> <?php echo htmlspecialchars($po['cus_id']); ?></div>
                            <div><strong>Date:</strong> <?php echo date('d M Y', strtotime($po['order_date'])); ?></div>
                        </div>
                    </div>
                </div>

                <div class="pcb-actions">
                    <!--
                        PRIMARY action: opens price-list.php with:
                         - cus_id  → auto-selects the customer
                         - order_id → price-list.php reads item codes from this order and pre-filters the price table
                    -->
                    <a href="<?php echo htmlspecialchars($po['price_link']); ?>" class="pcb-btn pcb-btn-primary">
                        <i class="fas fa-tags"></i> Set Prices for This Order
                    </a>
                   
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    </div>
    <?php endif; ?>

</main>

<footer class="page-footer">
    &copy; <?php echo date('Y'); ?> Customer Service Portal &mdash; Admin Dashboard
</footer>

<script>
function toggleNotif(e) {
    e.stopPropagation();
    document.getElementById('notifPanel').classList.toggle('open');
}
document.addEventListener('click', function(e) {
    const wrap = document.getElementById('notifWrap');
    if (wrap && !wrap.contains(e.target)) {
        document.getElementById('notifPanel').classList.remove('open');
    }
});

function togglePendingCard(orderId) {
    const card = document.getElementById('pcard_' + orderId);
    if (!card) return;
    const isExpanded = card.classList.contains('expanded');
    // Collapse all
    document.querySelectorAll('.pending-card.expanded').forEach(c => c.classList.remove('expanded'));
    // Expand clicked one (unless it was already open)
    if (!isExpanded) card.classList.add('expanded');
}
</script>
</body>
</html>