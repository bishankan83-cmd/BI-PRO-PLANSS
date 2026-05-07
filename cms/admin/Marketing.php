<?php
// dashboard.php
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
$adminData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$adminData) { session_destroy(); header("Location: index.php"); exit(); }
mysqli_stmt_close($stmt);

$isAccountManager = false;
$acmRef           = '';
$customerIds      = [];
$userIds          = [];

$stmt = mysqli_prepare($con, "SELECT acm_ref FROM account_managers WHERE id = ? AND status = 'active'");
mysqli_stmt_bind_param($stmt, "i", $adminId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $isAccountManager = true;
    $acmRef           = $row['acm_ref'];
    $stmt2 = mysqli_prepare($con, "SELECT id, cus_id FROM users WHERE acm_ref = ?");
    mysqli_stmt_bind_param($stmt2, "s", $acmRef);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);
    while ($customer = mysqli_fetch_assoc($result2)) {
        $customerIds[] = $customer['cus_id'];
        $userIds[]     = intval($customer['id']);
    }
    mysqli_stmt_close($stmt2);
}
mysqli_stmt_close($stmt);

// Init stats
$totusers = $totcom = $pendingcom = $inprocesscom = $closedcom = 0;
$totAccountManagers = $totOrders = $pendingOrders = $confirmedOrders = 0;
$customerConfirmedOrders = $cusPiConfirmOrders = $requestReviseOrders = 0;
$newPendingComplaints = $managerConfirmDiscountOrders = 0;

$userIdsStr = ($isAccountManager && !empty($userIds)) ? implode(",", $userIds) : '';

// ── Global notifications (no ACM filter) ──────────────────────────
$stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status = 'Pending'");
mysqli_stmt_execute($stmt);
$newPendingComplaints = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tire_orders WHERE status = 'Manager_confirm_discount'");
mysqli_stmt_execute($stmt);
$managerConfirmDiscountOrders = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];
mysqli_stmt_close($stmt);

// ── ACM-filtered stats ─────────────────────────────────────────────
$acm = $isAccountManager && !empty($userIds);

if ($acm) { $totusers = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM users WHERE id IN ($userIdsStr)"))['count']; }
else { $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM users"); mysqli_stmt_execute($stmt); $totusers = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count']; mysqli_stmt_close($stmt); }

if ($acm) { $totcom = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE userId IN ($userIdsStr)"))['count']; }
else { $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints"); mysqli_stmt_execute($stmt); $totcom = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count']; mysqli_stmt_close($stmt); }

if ($acm) { $pendingcom = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status='Pending' AND userId IN ($userIdsStr)"))['count']; }
else { $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status='Pending'"); mysqli_stmt_execute($stmt); $pendingcom = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count']; mysqli_stmt_close($stmt); }

if ($acm) { $inprocesscom = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status='in process' AND userId IN ($userIdsStr)"))['count']; }
else { $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status='in process'"); mysqli_stmt_execute($stmt); $inprocesscom = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count']; mysqli_stmt_close($stmt); }

if ($acm) { $closedcom = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status='closed' AND userId IN ($userIdsStr)"))['count']; }
else { $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status='closed'"); mysqli_stmt_execute($stmt); $closedcom = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count']; mysqli_stmt_close($stmt); }

if ($acm) { $totOrders = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM tire_orders WHERE customer_id IN ($userIdsStr)"))['count']; }
else { $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tire_orders"); mysqli_stmt_execute($stmt); $totOrders = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count']; mysqli_stmt_close($stmt); }

if ($acm) { $pendingOrders = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM tire_orders WHERE status='pending' AND customer_id IN ($userIdsStr)"))['count']; }
else { $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tire_orders WHERE status='pending'"); mysqli_stmt_execute($stmt); $pendingOrders = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count']; mysqli_stmt_close($stmt); }

if ($acm) { $confirmedOrders = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM tire_orders WHERE status='confirmed' AND customer_id IN ($userIdsStr)"))['count']; }
else { $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tire_orders WHERE status='confirmed'"); mysqli_stmt_execute($stmt); $confirmedOrders = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count']; mysqli_stmt_close($stmt); }

if ($acm) { $customerConfirmedOrders = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM tire_orders WHERE status='cus_confirmed' AND customer_id IN ($userIdsStr)"))['count']; }
else { $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tire_orders WHERE status='cus_confirmed'"); mysqli_stmt_execute($stmt); $customerConfirmedOrders = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count']; mysqli_stmt_close($stmt); }

if ($acm) { $cusPiConfirmOrders = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM tire_orders WHERE status='confirm_wait_marketing_man' AND customer_id IN ($userIdsStr)"))['count']; }
else { $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tire_orders WHERE status='confirm_wait_marketing_man'"); mysqli_stmt_execute($stmt); $cusPiConfirmOrders = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count']; mysqli_stmt_close($stmt); }

if ($acm) { $requestReviseOrders = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM tire_orders WHERE request_status='request_revise' AND customer_id IN ($userIdsStr)"))['count']; }
else { $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tire_orders WHERE request_status='request_revise'"); mysqli_stmt_execute($stmt); $requestReviseOrders = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count']; mysqli_stmt_close($stmt); }

if (!$isAccountManager) {
    $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM account_managers WHERE status='active'");
    mysqli_stmt_execute($stmt); $totAccountManagers = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count']; mysqli_stmt_close($stmt);
}

// ── Recent records ─────────────────────────────────────────────────
$rcWhere = $acm ? "WHERE tcmp.userId IN ($userIdsStr)" : "";
$recentComplaints = mysqli_query($con, "SELECT tcmp.*, u.fullName as userName FROM tbl_tire_complaints tcmp LEFT JOIN users u ON u.id = tcmp.userId $rcWhere ORDER BY tcmp.created_at DESC LIMIT 5");

$roWhere = $acm ? "WHERE tord.customer_id IN ($userIdsStr)" : "";
$recentOrders = mysqli_query($con, "SELECT tord.*, u.fullName as userName FROM tire_orders tord LEFT JOIN users u ON u.id = tord.customer_id $roWhere ORDER BY tord.order_date DESC LIMIT 5");

// ── Notification count ─────────────────────────────────────────────
$notificationCount = 0;
if ($newPendingComplaints         > 0) $notificationCount++;
if ($managerConfirmDiscountOrders > 0) $notificationCount++;
if ($customerConfirmedOrders      > 0) $notificationCount++;
if ($cusPiConfirmOrders           > 0) $notificationCount++;
if ($requestReviseOrders          > 0) $notificationCount++;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Customer Service Portal | Admin ID: <?php echo $adminId; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-orange:#F28018; --secondary-orange:#e67e22; --dark-gray:#333333;
            --bg-light:#f8fafc; --success:#27ae60; --warning:#f39c12; --error:#e74c3c;
            --info:#3498db; --purple:#9b59b6; --blue:#3498db; --teal:#1abc9c; --amber:#d97706;
            --text-gray:#64748b; --border-gray:#e0e0e0;
            --orange-light:rgba(242,128,24,.1); --success-light:rgba(39,174,96,.1);
            --warning-light:rgba(241,196,15,.1); --error-light:rgba(231,76,60,.1);
            --info-light:rgba(52,152,219,.1); --purple-light:rgba(155,89,182,.1);
            --blue-light:rgba(52,152,219,.1); --teal-light:rgba(26,188,156,.1);
            --amber-light:rgba(217,119,6,.1); --white:#ffffff;
            --gradient-1:linear-gradient(135deg,#F28018,#e67e22);
            --gradient-2:linear-gradient(135deg,#27ae60,#2ecc71);
            --gradient-3:linear-gradient(135deg,#e74c3c,#c0392b);
            --gradient-4:linear-gradient(135deg,#f39c12,#e67e22);
            --gradient-5:linear-gradient(135deg,#3498db,#2980b9);
            --gradient-6:linear-gradient(135deg,#9b59b6,#8e44ad);
            --gradient-7:linear-gradient(135deg,#1abc9c,#16a085);
            --gradient-8:linear-gradient(135deg,#d97706,#b45309);
            --shadow-sm:0 1px 2px 0 rgba(0,0,0,.05);
            --shadow:0 1px 3px 0 rgba(0,0,0,.1),0 1px 2px 0 rgba(0,0,0,.06);
            --shadow-md:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06);
            --shadow-lg:0 10px 15px -3px rgba(0,0,0,.1),0 4px 6px -2px rgba(0,0,0,.05);
            --shadow-xl:0 20px 25px -5px rgba(0,0,0,.1),0 10px 10px -5px rgba(0,0,0,.04);
        }
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;background:var(--bg-light);color:var(--dark-gray);line-height:1.6;-webkit-font-smoothing:antialiased}

        /* ── NOTIFICATIONS ── */
        .top-notification-alert{position:fixed;left:0;right:0;color:var(--white);padding:1rem 2rem;box-shadow:0 4px 12px rgba(0,0,0,.3);z-index:9999;animation:slideDown .4s ease-out;border-bottom:3px solid rgba(0,0,0,.2)}
        .pos-1{top:80px}.pos-2{top:160px}.pos-3{top:240px}.pos-4{top:320px}.pos-5{top:400px}
        .alert-teal  {background:linear-gradient(135deg,#1abc9c,#16a085);border-bottom-color:#0e6655}
        .alert-amber {background:linear-gradient(135deg,#d97706,#b45309);border-bottom-color:#92400e}
        .alert-red   {background:linear-gradient(135deg,#ff6b6b,#ee5a6f);border-bottom-color:#c92a2a}
        .alert-purple{background:linear-gradient(135deg,#9b59b6,#8e44ad);border-bottom-color:#6c3483}
        .alert-blue  {background:linear-gradient(135deg,#3498db,#2980b9);border-bottom-color:#1f5f8b}
        body.notifications-1 .main-content{padding-top:5rem}
        body.notifications-2 .main-content{padding-top:9rem}
        body.notifications-3 .main-content{padding-top:13rem}
        body.notifications-4 .main-content{padding-top:17rem}
        body.notifications-5 .main-content{padding-top:21rem}
        @keyframes slideDown{from{transform:translateY(-100%);opacity:0}to{transform:translateY(0);opacity:1}}
        .top-notification-content{max-width:1400px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:1rem}
        .top-notification-icon{width:48px;height:48px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;animation:pulse 2s ease-in-out infinite;flex-shrink:0}
        @keyframes pulse{0%,100%{transform:scale(1);box-shadow:0 0 0 0 rgba(255,255,255,.7)}50%{transform:scale(1.05);box-shadow:0 0 0 10px rgba(255,255,255,0)}}
        .top-notification-message{flex:1;display:flex;align-items:center;gap:1rem;font-size:1.1rem;font-weight:700;letter-spacing:.5px}
        .notification-count-badge{background:var(--white);padding:.4rem 1rem;border-radius:2rem;font-size:1.2rem;font-weight:900;min-width:50px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.2);animation:bounce 1s ease-in-out infinite}
        .alert-teal .notification-count-badge{color:#1abc9c}.alert-amber .notification-count-badge{color:#d97706}
        .alert-red .notification-count-badge{color:#ff6b6b}.alert-purple .notification-count-badge{color:#9b59b6}
        .alert-blue .notification-count-badge{color:#3498db}
        @keyframes bounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
        .top-notification-close{width:36px;height:36px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .3s;flex-shrink:0}
        .top-notification-close:hover{background:rgba(255,255,255,.3);transform:rotate(90deg)}

        /* ── BUTTONS ── */
        .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;border:none;border-radius:.75rem;font-weight:600;text-decoration:none;cursor:pointer;transition:all .2s;font-size:.9rem;white-space:nowrap;background:var(--gradient-1);color:var(--white);box-shadow:var(--shadow)}
        .btn:hover{transform:translateY(-2px);box-shadow:var(--shadow-lg)}
        .btn-primary{background:var(--gradient-1);color:var(--white)}
        .btn-secondary{background:var(--white);color:var(--text-gray);border:1px solid var(--border-gray);box-shadow:none}
        .btn-secondary:hover{background:var(--bg-light);border-color:var(--primary-orange);color:var(--primary-orange);transform:none}
        .btn-info{background:var(--gradient-5);color:var(--white)}

        /* ── LAYOUT ── */
        .container{display:flex;min-height:calc(100vh - 80px)}
        .main-content{flex:1;padding:2rem;overflow-x:hidden;transition:padding-top .3s ease}

        /* ── PAGE HEADER ── */
        .page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:2rem;flex-wrap:wrap;gap:1rem}
        .page-title-section{flex:1;min-width:300px}
        .page-title{font-size:2rem;font-weight:800;color:var(--dark-gray);margin-bottom:.5rem}
        .page-subtitle{color:var(--text-gray);font-size:1rem;line-height:1.5}
        .acm-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;background:var(--info-light);color:var(--info);border-radius:.5rem;font-size:.85rem;font-weight:600;margin-top:.5rem}
        .admin-info{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;background:var(--purple-light);color:var(--purple);border-radius:.5rem;font-size:.75rem;font-weight:600;margin-top:.5rem;margin-left:.5rem}
        .header-actions-right{display:flex;gap:1rem;flex-wrap:wrap}

        /* ── SECTION DIVIDER ── */
        .section-divider{display:flex;align-items:center;gap:1rem;margin:2.5rem 0 1.5rem}
        .section-divider-line{flex:1;height:1px;background:var(--border-gray)}
        .section-divider-pill{display:flex;align-items:center;gap:.6rem;padding:.5rem 1.25rem;border-radius:2rem;font-size:.88rem;font-weight:700;white-space:nowrap;letter-spacing:.3px}
        .pill-overview{background:var(--purple-light);color:var(--purple);border:1px solid rgba(155,89,182,.2)}
        .pill-claims  {background:var(--teal-light);  color:var(--teal);  border:1px solid rgba(26,188,156,.2)}
        .pill-orders  {background:var(--info-light);  color:var(--info);  border:1px solid rgba(52,152,219,.2)}

        /* ── STAT CARDS ── */
        .stats-container{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.5rem;margin-bottom:.5rem}
        .stat-card{background:var(--white);border-radius:1rem;padding:1.75rem;border:1px solid var(--border-gray);position:relative;overflow:hidden;transition:all .3s;box-shadow:var(--shadow-sm)}
        .stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:var(--gradient-1)}
        .stat-card.success::before{background:var(--gradient-2)}.stat-card.warning::before{background:var(--gradient-4)}
        .stat-card.danger::before{background:var(--gradient-3)}.stat-card.info::before{background:var(--gradient-5)}
        .stat-card.purple::before{background:var(--gradient-6)}.stat-card.teal::before{background:var(--gradient-7)}
        .stat-card.amber::before{background:var(--gradient-8)}
        .stat-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-xl)}
        .stat-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem}
        .stat-icon{width:3.25rem;height:3.25rem;border-radius:.875rem;display:flex;align-items:center;justify-content:center;font-size:1.35rem;background:var(--orange-light);color:var(--primary-orange)}
        .stat-card.success .stat-icon{background:var(--success-light);color:var(--success)}
        .stat-card.warning .stat-icon{background:var(--warning-light);color:var(--warning)}
        .stat-card.danger  .stat-icon{background:var(--error-light);color:var(--error)}
        .stat-card.info    .stat-icon{background:var(--info-light);color:var(--info)}
        .stat-card.purple  .stat-icon{background:var(--purple-light);color:var(--purple)}
        .stat-card.teal    .stat-icon{background:var(--teal-light);color:var(--teal)}
        .stat-card.amber   .stat-icon{background:var(--amber-light);color:var(--amber)}
        .stat-value{font-size:2.4rem;font-weight:900;color:var(--dark-gray);line-height:1;margin-bottom:.4rem}
        .stat-label{font-weight:600;color:var(--text-gray);margin-bottom:.2rem}
        .stat-description{font-size:.82rem;color:var(--text-gray);opacity:.8}
        .stat-link{display:inline-flex;align-items:center;gap:.25rem;margin-top:.75rem;color:var(--primary-orange);font-size:.82rem;font-weight:600;text-decoration:none;transition:gap .2s}
        .stat-link:hover{gap:.5rem}

        /* ── CONTENT GRID ── */
        .content-grid{display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-bottom:2rem}

        /* ── CARDS ── */
        .card{background:var(--white);border-radius:1rem;border:1px solid var(--border-gray);overflow:hidden;box-shadow:var(--shadow-sm)}
        .card-header{padding:1.5rem 2rem;border-bottom:1px solid var(--border-gray);display:flex;align-items:center;justify-content:space-between}
        .card-header.claims-hdr{background:linear-gradient(135deg,rgba(26,188,156,.05),rgba(26,188,156,.02))}
        .card-header.orders-hdr{background:linear-gradient(135deg,rgba(52,152,219,.05),rgba(52,152,219,.02))}
        .card-title{font-size:1.2rem;font-weight:700;color:var(--dark-gray);display:flex;align-items:center;gap:.75rem}
        .card-title.claims-title i{color:var(--teal)}
        .card-title.orders-title i{color:var(--info)}
        .card-body{padding:1.75rem}

        /* ── LIST ITEMS ── */
        .list-items{display:flex;flex-direction:column;gap:.85rem}
        .complaint-item,.order-item{display:flex;align-items:center;gap:1rem;padding:1.25rem;background:var(--bg-light);border-radius:.75rem;border:1px solid var(--border-gray);transition:all .2s;text-decoration:none;color:inherit}
        .complaint-item:hover{background:var(--white);border-color:var(--teal);transform:translateX(.25rem);box-shadow:var(--shadow-md)}
        .order-item:hover    {background:var(--white);border-color:var(--info);transform:translateX(.25rem);box-shadow:var(--shadow-md)}
        .complaint-avatar{width:2.75rem;height:2.75rem;border-radius:.65rem;background:var(--teal-light);display:flex;align-items:center;justify-content:center;color:var(--teal);font-size:1.1rem;flex-shrink:0}
        .order-avatar    {width:2.75rem;height:2.75rem;border-radius:.65rem;background:var(--info-light); display:flex;align-items:center;justify-content:center;color:var(--info);font-size:1.1rem;flex-shrink:0}
        .complaint-details,.order-details{flex:1;min-width:0}
        .complaint-title,.order-title{font-weight:600;color:var(--dark-gray);margin-bottom:.2rem;font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .complaint-meta,.order-meta{font-size:.78rem;color:var(--text-gray)}

        /* ── STATUS BADGES ── */
        .status-badge{padding:.3rem .75rem;border-radius:1rem;font-size:.72rem;font-weight:600;text-transform:capitalize;white-space:nowrap}
        .status-pending                 {background:var(--warning-light);color:var(--warning)}
        .status-in-process              {background:var(--orange-light); color:var(--primary-orange)}
        .status-closed                  {background:var(--success-light);color:var(--success)}
        .status-confirmed               {background:var(--info-light);   color:var(--info)}
        .status-cus-confirmed           {background:var(--success-light);color:var(--success)}
        .status-cus-pi-confirm          {background:var(--purple-light); color:var(--purple)}
        .status-request-revise          {background:var(--blue-light);   color:var(--blue)}
        .status-manager-confirm-discount{background:var(--amber-light);  color:var(--amber)}

        /* ── QUICK ACTIONS ── */
        .quick-actions{display:flex;flex-direction:column;gap:.85rem}
        .action-card{padding:1.25rem;background:var(--bg-light);border-radius:.75rem;border:1px solid var(--border-gray);text-decoration:none;color:inherit;transition:all .2s;display:flex;align-items:center;gap:1rem}
        .action-card.claims-action:hover{background:var(--white);border-color:var(--teal);transform:translateY(-2px);box-shadow:var(--shadow-lg)}
        .action-card.orders-action:hover{background:var(--white);border-color:var(--info);transform:translateY(-2px);box-shadow:var(--shadow-lg)}
        .action-icon{width:2.75rem;height:2.75rem;border-radius:.65rem;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
        .action-icon.claims {background:var(--teal-light);  color:var(--teal)}
        .action-icon.orders {background:var(--info-light);  color:var(--info)}
        .action-icon.amber  {background:var(--amber-light); color:var(--amber)}
        .action-icon.blue   {background:var(--blue-light);  color:var(--blue)}
        .action-icon.purple {background:var(--purple-light);color:var(--purple)}
        .action-icon.orange {background:var(--orange-light);color:var(--primary-orange)}
        .action-content h3{font-size:.92rem;font-weight:600;color:var(--dark-gray);margin-bottom:.2rem}
        .action-content p {font-size:.8rem;color:var(--text-gray)}

        .empty-state{text-align:center;padding:2.5rem 1rem;color:var(--text-gray)}
        .empty-icon{font-size:2.5rem;margin-bottom:.75rem;opacity:.4}

        /* ── RESPONSIVE ── */
        @media(max-width:1200px){.stats-container{grid-template-columns:repeat(auto-fit,minmax(240px,1fr))}}
        @media(max-width:1024px){.content-grid{grid-template-columns:1fr;gap:1.5rem}}
        @media(max-width:768px){
            .pos-1{top:60px}.pos-2{top:140px}.pos-3{top:220px}.pos-4{top:300px}.pos-5{top:380px}
            .top-notification-alert{padding:1rem}
            .top-notification-content{flex-direction:column;text-align:center}
            .top-notification-message{flex-direction:column;font-size:1rem}
            .main-content{padding:1rem}
            .page-header{flex-direction:column;gap:1rem;align-items:stretch}
            .page-title-section{min-width:100%}
            .header-actions-right{flex-direction:column}
            .stats-container{grid-template-columns:1fr}
            .btn{justify-content:center;width:100%}
            .admin-info{margin-left:0;margin-top:.25rem}
            .section-divider-pill{font-size:.8rem;padding:.4rem 1rem}
        }
        @keyframes slideIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
        .animate-in{animation:slideIn .6s ease-out forwards}
    </style>
</head>
<body class="notifications-<?php echo $notificationCount; ?>">

<?php include('include/header.php'); ?>

<?php
// ── Render notification banners ────────────────────────────────────
$notifications = [];
if ($newPendingComplaints > 0)
    $notifications[] = ['id'=>'topAlertTeal','class'=>'alert-teal','icon'=>'fas fa-exclamation-circle',
        'label_s'=>'NEW CLAIM','label_p'=>'NEW CLAIMS','count'=>$newPendingComplaints,
        'href'=>'notprocess-complaint_mar.php','btn'=>'View Claims','bicon'=>'fas fa-eye'];
if ($managerConfirmDiscountOrders > 0)
    $notifications[] = ['id'=>'topAlertAmber','class'=>'alert-amber','icon'=>'fas fa-tags',
        'label_s'=>'ORDER AWAITING DISCOUNT APPROVAL','label_p'=>'ORDERS AWAITING DISCOUNT APPROVAL',
        'count'=>$managerConfirmDiscountOrders,'href'=>'order_dis_approved.php?status=Manager_confirm_discount',
        'btn'=>'Review Discounts','bicon'=>'fas fa-percentage'];
if ($customerConfirmedOrders > 0)
    $notifications[] = ['id'=>'topAlertRed','class'=>'alert-red','icon'=>'fas fa-bell',
        'label_s'=>'NEW CUSTOMER CONFIRMED ORDER','label_p'=>'NEW CUSTOMER CONFIRMED ORDERS',
        'count'=>$customerConfirmedOrders,'href'=>'tire-orders_mar.php?status=cus_confirmed',
        'btn'=>'View Orders','bicon'=>'fas fa-eye'];
if ($cusPiConfirmOrders > 0)
    $notifications[] = ['id'=>'topAlertPurple','class'=>'alert-purple','icon'=>'fas fa-file-invoice',
        'label_s'=>'NEW PI CONFIRMED ORDER','label_p'=>'NEW PI CONFIRMED ORDERS',
        'count'=>$cusPiConfirmOrders,'href'=>'tire-orders-marketing-share-planning.php?status=confirm_wait_marketing_man',
        'btn'=>'View PI Orders','bicon'=>'fas fa-eye'];
if ($requestReviseOrders > 0)
    $notifications[] = ['id'=>'topAlertBlue','class'=>'alert-blue','icon'=>'fas fa-edit',
        'label_s'=>'ORDER REQUIRES REVISION','label_p'=>'ORDERS REQUIRE REVISION',
        'count'=>$requestReviseOrders,'href'=>'tire-orders-revise.php?request_status=request_revise',
        'btn'=>'View Revisions','bicon'=>'fas fa-eye'];

foreach ($notifications as $pos => $n): ?>
<div class="top-notification-alert <?php echo $n['class']; ?> pos-<?php echo $pos+1; ?>" id="<?php echo $n['id']; ?>">
    <div class="top-notification-content">
        <div class="top-notification-icon"><i class="<?php echo $n['icon']; ?>"></i></div>
        <div class="top-notification-message">
            <strong><?php echo $n['count'] > 1 ? $n['label_p'] : $n['label_s']; ?>!</strong>
            <span class="notification-count-badge"><?php echo $n['count']; ?></span>
        </div>
        <a href="<?php echo $n['href']; ?>" class="btn" style="margin:0;">
            <i class="<?php echo $n['bicon']; ?>"></i> <?php echo $n['btn']; ?>
        </a>
        <div class="top-notification-close" onclick="closeNotification('<?php echo $n['id']; ?>')">
            <i class="fas fa-times"></i>
        </div>
    </div>
</div>
<?php endforeach; ?>

<div class="container">
    <?php include('include/sidebar2.php'); ?>
    <main class="main-content">

        <!-- ── PAGE HEADER ── -->
        <div class="page-header">
            <div class="page-title-section">
                <h1 class="page-title">Dashboard Analytics</h1>
                <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($adminData['fullname']); ?>. Here's what's happening with your Customer Service today.</p>
                <?php if ($isAccountManager): ?>
                    <div class="acm-badge"><i class="fas fa-user-shield"></i>Account Manager View — Showing data for <?php echo count($customerIds); ?> customer<?php echo count($customerIds)!=1?'s':''; ?></div>
                    <div class="admin-info"><i class="fas fa-id-badge"></i>Admin ID: <?php echo $adminId; ?> | ACM Ref: <?php echo htmlspecialchars($acmRef); ?></div>
                <?php else: ?>
                    <div class="admin-info"><i class="fas fa-user-cog"></i>Admin ID: <?php echo $adminId; ?> | Role: Full Access Admin</div>
                <?php endif; ?>
            </div>
            <div class="header-actions-right">
                <a href="complaint-search.php"        class="btn btn-secondary"><i class="fas fa-search"></i> Search</a>
                <a href="all-complaint2_mar.php"       class="btn btn-info">    <i class="fas fa-eye"></i>    View All</a>
                <a href="notprocess-complaint_mar.php" class="btn btn-primary"> <i class="fas fa-plus"></i>   New Claim</a>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════ -->
        <!--  OVERVIEW                                          -->
        <!-- ══════════════════════════════════════════════════ -->
        <div class="section-divider">
            <div class="section-divider-line"></div>
            <div class="section-divider-pill pill-overview"><i class="fas fa-chart-pie"></i> Overview</div>
            <div class="section-divider-line"></div>
        </div>

        <div class="stats-container animate-in">
            <div class="stat-card">
                <div class="stat-header"><div class="stat-icon"><i class="fas fa-users"></i></div></div>
                <div class="stat-value"><?php echo $totusers; ?></div>
                <div class="stat-label"><?php echo $isAccountManager ? 'My Customers' : 'Total Users'; ?></div>
                <div class="stat-description"><?php echo $isAccountManager ? 'Assigned customer accounts' : 'Registered system users'; ?></div>
                <a href="manage-user2_mar.php" class="stat-link">View Details <i class="fas fa-arrow-right"></i></a>
            </div>
            <?php if (!$isAccountManager): ?>
            <div class="stat-card info">
                <div class="stat-header"><div class="stat-icon"><i class="fas fa-user-tie"></i></div></div>
                <div class="stat-value"><?php echo $totAccountManagers; ?></div>
                <div class="stat-label">Account Managers</div>
                <div class="stat-description">Active team members</div>
                <a href="manage-account-managers.php" class="stat-link">Manage Team <i class="fas fa-arrow-right"></i></a>
            </div>
            <?php endif; ?>
        </div>

        <!-- ══════════════════════════════════════════════════ -->
        <!--  CLAIMS                                            -->
        <!-- ══════════════════════════════════════════════════ -->
        <div class="section-divider">
            <div class="section-divider-line"></div>
            <div class="section-divider-pill pill-claims"><i class="fas fa-file-alt"></i> Claims</div>
            <div class="section-divider-line"></div>
        </div>

        <div class="stats-container animate-in">
            <div class="stat-card">
                <div class="stat-header"><div class="stat-icon"><i class="fas fa-file-alt"></i></div></div>
                <div class="stat-value"><?php echo $totcom; ?></div>
                <div class="stat-label">Total Claims</div>
                <div class="stat-description"><?php echo $isAccountManager ? 'From your customers' : 'All claims received'; ?></div>
                <a href="all-complaint2_mar.php" class="stat-link">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card teal">
                <div class="stat-header"><div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div></div>
                <div class="stat-value"><?php echo $pendingcom; ?></div>
                <div class="stat-label">New / Pending Claims</div>
                <div class="stat-description">
                    <?php if ($isAccountManager && $newPendingComplaints > $pendingcom): ?>
                        From your customers<br><small style="color:var(--teal)">(<?php echo $newPendingComplaints; ?> total system-wide)</small>
                    <?php else: ?>
                        Require immediate attention
                    <?php endif; ?>
                </div>
                <a href="notprocess-complaint_mar.php" class="stat-link">Take Action <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card warning">
                <div class="stat-header"><div class="stat-icon"><i class="fas fa-spinner"></i></div></div>
                <div class="stat-value"><?php echo $inprocesscom; ?></div>
                <div class="stat-label">In Process</div>
                <div class="stat-description">Currently being handled</div>
                <a href="inprocess-complaint.php" class="stat-link">View Progress <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card success">
                <div class="stat-header"><div class="stat-icon"><i class="fas fa-check-circle"></i></div></div>
                <div class="stat-value"><?php echo $closedcom; ?></div>
                <div class="stat-label">Resolved Claims</div>
                <div class="stat-description">Successfully closed cases</div>
                <a href="closed-complaint.php" class="stat-link">View Resolved <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Recent Claims + Claims Actions -->
        <div class="content-grid" style="margin-top:1.5rem">
            <div class="card">
                <div class="card-header claims-hdr">
                    <h2 class="card-title claims-title"><i class="fas fa-clock"></i> Recent Claims</h2>
                    <a href="all-complaint2_mar.php" class="btn btn-secondary" style="padding:.5rem 1rem;font-size:.8rem">View All</a>
                </div>
                <div class="card-body">
                    <?php if ($recentComplaints && mysqli_num_rows($recentComplaints) > 0): ?>
                        <div class="list-items">
                            <?php while ($complaint = mysqli_fetch_assoc($recentComplaints)):
                                $cs = strtolower($complaint['status']);
                                if ($cs==='pending')       {$bc='status-pending';   $bl='New / Pending';}
                                elseif($cs==='in process') {$bc='status-in-process';$bl='In Process';}
                                elseif($cs==='closed')     {$bc='status-closed';    $bl='Closed';}
                                else                       {$bc='status-pending';   $bl=ucfirst($complaint['status']);}
                            ?>
                                <a href="complaint-details.php?cid=<?php echo $complaint['complaintNumber']; ?>" class="complaint-item">
                                    <div class="complaint-avatar"><i class="fas fa-file-alt"></i></div>
                                    <div class="complaint-details">
                                        <div class="complaint-title">#<?php echo $complaint['complaintNumber']; ?> — <?php echo htmlspecialchars($complaint['userName'] ?? 'Anonymous'); ?></div>
                                        <div class="complaint-meta"><i class="fas fa-calendar"></i> <?php echo date('M d, Y - h:i A', strtotime($complaint['created_at'])); ?></div>
                                    </div>
                                    <div class="status-badge <?php echo $bc; ?>"><?php echo $bl; ?></div>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                            <h3>No recent claims</h3>
                            <p><?php echo $isAccountManager ? 'No claims from your customers yet.' : 'All caught up! No new claims.'; ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header claims-hdr">
                    <h2 class="card-title claims-title"><i class="fas fa-bolt"></i> Claims Actions</h2>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="notprocess-complaint_mar.php" class="action-card claims-action">
                            <div class="action-icon claims"><i class="fas fa-exclamation-circle"></i></div>
                            <div class="action-content"><h3>Review New / Pending</h3><p><?php echo $newPendingComplaints; ?> claim<?php echo $newPendingComplaints!=1?'s':''; ?> need attention (system-wide)</p></div>
                        </a>
                        <a href="inprocess-complaint.php" class="action-card claims-action">
                            <div class="action-icon claims"><i class="fas fa-spinner"></i></div>
                            <div class="action-content"><h3>In-Process Claims</h3><p><?php echo $inprocesscom; ?> currently being handled</p></div>
                        </a>
                        <a href="closed-complaint.php" class="action-card claims-action">
                            <div class="action-icon claims"><i class="fas fa-check-circle"></i></div>
                            <div class="action-content"><h3>Resolved Cases</h3><p><?php echo $closedcom; ?> claim<?php echo $closedcom!=1?'s':''; ?> closed</p></div>
                        </a>
                        <a href="complaint-search.php" class="action-card claims-action">
                            <div class="action-icon claims"><i class="fas fa-search"></i></div>
                            <div class="action-content"><h3>Search Claims</h3><p>Find specific claims quickly</p></div>
                        </a>
                        <a href="between-date-complaintreport.php" class="action-card claims-action">
                            <div class="action-icon claims"><i class="fas fa-chart-bar"></i></div>
                            <div class="action-content"><h3>Generate Reports</h3><p>Create detailed analytics reports</p></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════ -->
        <!--  ORDERS                                            -->
        <!-- ══════════════════════════════════════════════════ -->
        <div class="section-divider">
            <div class="section-divider-line"></div>
            <div class="section-divider-pill pill-orders"><i class="fas fa-shopping-cart"></i> Orders</div>
            <div class="section-divider-line"></div>
        </div>

        <div class="stats-container animate-in">
            <div class="stat-card info">
                <div class="stat-header"><div class="stat-icon"><i class="fas fa-shopping-cart"></i></div></div>
                <div class="stat-value"><?php echo $totOrders; ?></div>
                <div class="stat-label">Total Orders</div>
                <div class="stat-description"><?php echo $isAccountManager ? 'From your customers' : 'All tire orders'; ?></div>
                <a href="tire-orders_mar.php" class="stat-link">View Orders <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card warning">
                <div class="stat-header"><div class="stat-icon"><i class="fas fa-hourglass-half"></i></div></div>
                <div class="stat-value"><?php echo $pendingOrders; ?></div>
                <div class="stat-label">Pending Orders</div>
                <div class="stat-description">Awaiting confirmation</div>
                <a href="tire-orders_mar.php?status=pending" class="stat-link">Process Orders <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card success">
                <div class="stat-header"><div class="stat-icon"><i class="fas fa-check-double"></i></div></div>
                <div class="stat-value"><?php echo $confirmedOrders + $customerConfirmedOrders; ?></div>
                <div class="stat-label">Confirmed Orders</div>
                <div class="stat-description">Admin + customer confirmed</div>
                <a href="tire-orders_mar.php?status=confirmed" class="stat-link">View Confirmed <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card success">
                <div class="stat-header"><div class="stat-icon"><i class="fas fa-user-check"></i></div></div>
                <div class="stat-value"><?php echo $customerConfirmedOrders; ?></div>
                <div class="stat-label">Customer Confirmed</div>
                <div class="stat-description">Confirmed by customer</div>
                <a href="tire-orders_mar.php?status=cus_confirmed" class="stat-link">View <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card purple">
                <div class="stat-header"><div class="stat-icon"><i class="fas fa-file-invoice"></i></div></div>
                <div class="stat-value"><?php echo $cusPiConfirmOrders; ?></div>
                <div class="stat-label">PI Confirmed Orders</div>
                <div class="stat-description">Proforma invoice confirmed</div>
                <a href="tire-orders_mar.php?status=confirm_wait_marketing_man" class="stat-link">View PI Orders <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card amber">
                <div class="stat-header"><div class="stat-icon"><i class="fas fa-tags"></i></div></div>
                <div class="stat-value"><?php echo $managerConfirmDiscountOrders; ?></div>
                <div class="stat-label">Discount Approval Pending</div>
                <div class="stat-description">Awaiting manager confirmation</div>
                <a href="order_dis_approved.php?status=Manager_confirm_discount" class="stat-link">Review Discounts <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card info">
                <div class="stat-header"><div class="stat-icon"><i class="fas fa-edit"></i></div></div>
                <div class="stat-value"><?php echo $requestReviseOrders; ?></div>
                <div class="stat-label">Revision Requests</div>
                <div class="stat-description">Orders requiring revision</div>
                <a href="tire-orders-revise.php?request_status=request_revise" class="stat-link">Review Revisions <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Recent Orders + Orders Actions -->
        <div class="content-grid" style="margin-top:1.5rem">
            <div class="card">
                <div class="card-header orders-hdr">
                    <h2 class="card-title orders-title"><i class="fas fa-shopping-cart"></i> Recent Orders</h2>
                    <a href="tire-orders_mar.php" class="btn btn-secondary" style="padding:.5rem 1rem;font-size:.8rem">View All</a>
                </div>
                <div class="card-body">
                    <?php if ($recentOrders && mysqli_num_rows($recentOrders) > 0): ?>
                        <div class="list-items">
                            <?php while ($order = mysqli_fetch_assoc($recentOrders)): ?>
                                <a href="order-details.php?oid=<?php echo $order['order_id']; ?>" class="order-item">
                                    <div class="order-avatar"><i class="fas fa-box"></i></div>
                                    <div class="order-details">
                                        <div class="order-title">#<?php echo $order['order_id']; ?> — <?php echo htmlspecialchars($order['userName'] ?? 'Anonymous'); ?></div>
                                        <div class="order-meta">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('M d, Y - h:i A', strtotime($order['order_date'])); ?>
                                            &bull; <?php echo $order['total_items']; ?> items (<?php echo $order['total_quantity']; ?> qty)
                                        </div>
                                    </div>
                                    <div class="status-badge <?php
                                        if     ($order['status']==='pending')                  echo 'status-pending';
                                        elseif ($order['status']==='confirmed')                echo 'status-confirmed';
                                        elseif ($order['status']==='cus_confirmed')            echo 'status-cus-confirmed';
                                        elseif ($order['status']==='confirm_wait_marketing_man')        echo 'status-cus-pi-confirm';
                                        elseif ($order['status']==='Manager_confirm_discount') echo 'status-manager-confirm-discount';
                                    ?>">
                                        <?php
                                        if     ($order['status']==='cus_confirmed')            echo 'Customer Confirmed';
                                        elseif ($order['status']==='confirm_wait_marketing_man')        echo 'PI Confirmed';
                                        elseif ($order['status']==='Manager_confirm_discount') echo 'Discount Approval';
                                        else echo ucfirst($order['status']);
                                        ?>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                            <h3>No recent orders</h3>
                            <p><?php echo $isAccountManager ? 'No orders from your customers yet.' : 'No new tire orders to review.'; ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header orders-hdr">
                    <h2 class="card-title orders-title"><i class="fas fa-bolt"></i> Order Actions</h2>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="tire-orders_mar.php?status=pending" class="action-card orders-action">
                            <div class="action-icon orders"><i class="fas fa-cart-arrow-down"></i></div>
                            <div class="action-content"><h3>Process Pending Orders</h3><p><?php echo $pendingOrders; ?> order<?php echo $pendingOrders!=1?'s':''; ?> awaiting confirmation</p></div>
                        </a>
                        <a href="tire-orders_mar.php?status=cus_confirmed" class="action-card orders-action">
                            <div class="action-icon orders"><i class="fas fa-user-check"></i></div>
                            <div class="action-content"><h3>Customer Confirmed</h3><p><?php echo $customerConfirmedOrders; ?> order<?php echo $customerConfirmedOrders!=1?'s':''; ?> confirmed by customer</p></div>
                        </a>
                        <a href="tire-orders-marketing-share-planning.php?status=confirm_wait_marketing_man" class="action-card orders-action">
                            <div class="action-icon purple"><i class="fas fa-file-invoice"></i></div>
                            <div class="action-content"><h3>PI Confirmed Orders</h3><p><?php echo $cusPiConfirmOrders; ?> PI order<?php echo $cusPiConfirmOrders!=1?'s':''; ?> ready for processing</p></div>
                        </a>
                        <a href="order_dis_approved.php?status=Manager_confirm_discount" class="action-card orders-action">
                            <div class="action-icon amber"><i class="fas fa-tags"></i></div>
                            <div class="action-content"><h3>Discount Approvals</h3><p><?php echo $managerConfirmDiscountOrders; ?> order<?php echo $managerConfirmDiscountOrders!=1?'s':''; ?> awaiting confirmation (system-wide)</p></div>
                        </a>
                        <a href="tire-orders-revise.php?request_status=request_revise" class="action-card orders-action">
                            <div class="action-icon blue"><i class="fas fa-edit"></i></div>
                            <div class="action-content"><h3>Revision Requests</h3><p><?php echo $requestReviseOrders; ?> order<?php echo $requestReviseOrders!=1?'s':''; ?> need revision</p></div>
                        </a>
                        <a href="tire-orders_mar.php" class="action-card orders-action">
                            <div class="action-icon orders"><i class="fas fa-list"></i></div>
                            <div class="action-content"><h3>All Orders</h3><p>View complete order history</p></div>
                        </a>
                        <a href="manage-user2_mar.php" class="action-card orders-action">
                            <div class="action-icon orders"><i class="fas fa-users"></i></div>
                            <div class="action-content"><h3>Manage Users</h3><p><?php echo $isAccountManager ? 'View your customer accounts' : 'View and manage user accounts'; ?></p></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
    function closeNotification(alertId) {
        const el = document.getElementById(alertId);
        if (!el) return;
        el.style.animation = 'slideUp .4s ease-out forwards';
        setTimeout(() => { el.remove(); restackNotifications(); }, 400);
    }
    function restackNotifications() {
        const alerts = document.querySelectorAll('.top-notification-alert');
        alerts.forEach((a, i) => {
            a.classList.remove('pos-1','pos-2','pos-3','pos-4','pos-5');
            a.classList.add('pos-' + (i + 1));
        });
        document.body.classList.remove('notifications-1','notifications-2','notifications-3','notifications-4','notifications-5');
        if (alerts.length > 0) document.body.classList.add('notifications-' + alerts.length);
    }
    const s = document.createElement('style');
    s.textContent = '@keyframes slideUp{from{transform:translateY(0);opacity:1}to{transform:translateY(-100%);opacity:0}}';
    document.head.appendChild(s);
</script>
</body>
</html>