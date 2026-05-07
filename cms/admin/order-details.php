<?php
session_start();
include('include/config.php');

if (empty($_SESSION['id'])) { header('location:index.php'); exit(); }

// ── Handle delete ─────────────────────────────────────────────────────────
if (isset($_GET['delete']) && $_GET['delete'] == 'true' && isset($_GET['oid'])) {
    $orderIdToDelete = mysqli_real_escape_string($con, $_GET['oid']);
    $userId = $_SESSION['id'];
    mysqli_begin_transaction($con);
    try {
        $a = mysqli_query($con, "DELETE FROM order_summaries WHERE order_id = '$orderIdToDelete'");
        $b = mysqli_query($con, "DELETE FROM tire_order_items WHERE order_id = '$orderIdToDelete'");
        $c = mysqli_query($con, "DELETE FROM tire_orders WHERE order_id = '$orderIdToDelete' AND customer_id = '$userId'");
        if ($a && $b && $c) {
            mysqli_commit($con);
            $_SESSION['success_message'] = "Order #$orderIdToDelete deleted successfully.";
            header('location:cus-confirmed-orders.php'); exit();
        } else { mysqli_rollback($con); $_SESSION['error_message'] = "Failed to delete order."; }
    } catch (Exception $e) { mysqli_rollback($con); $_SESSION['error_message'] = $e->getMessage(); }
}

$orderId = isset($_GET['oid']) ? mysqli_real_escape_string($con, $_GET['oid']) : '';
$userId  = $_SESSION['id'];
if (empty($orderId)) { header('location:cus-confirmed-orders.php'); exit(); }

// ── Helpers ───────────────────────────────────────────────────────────────
date_default_timezone_set('Asia/Kolkata');
function fmtDate($d)      { return $d ? date('M j, Y \a\t g:i A', strtotime($d)) : '—'; }
function fmtDateShort($d) { return $d ? date('M j, Y', strtotime($d)) : '—'; }
function fmtMoney($v)     { return '$' . number_format((float)$v, 2); }
function fmtNum($v, $d=0) { return number_format((float)$v, $d); }
function fmtKg($v)        { return ($v !== null && $v !== '') ? number_format((float)$v, 2) . ' kg' : '—'; }
function statusBadge($s) {
    $s = strtolower(trim($s ?? 'pending'));
    $map = [
        'pending'       => ['status-pending',    'fa-clock',        'Pending'],
        'in process'    => ['status-in-process', 'fa-cog',          'In Process'],
        'in-process'    => ['status-in-process', 'fa-cog',          'In Process'],
        'closed'        => ['status-closed',     'fa-check-circle', 'Closed'],
        'completed'     => ['status-closed',     'fa-check-circle', 'Completed'],
        'complete'      => ['status-closed',     'fa-check-circle', 'Complete'],
        'pi_confirm'    => ['status-pi-confirm', 'fa-file-invoice', 'PI Confirm'],
        'revised'       => ['status-revised',    'fa-sync-alt',     'Revised'],
        'share_planning'=> ['status-in-process', 'fa-calendar-alt', 'In Planning'],
    ];
    [$cls, $icon, $label] = $map[$s] ?? ['status-pending', 'fa-circle', ucfirst(str_replace('_',' ',$s))];
    return "<span class=\"status-badge $cls\"><i class=\"fas $icon\"></i> " . htmlentities($label) . "</span>";
}

function shipmentStatusBadge($s, $type = 'generic') {
    $s = strtolower(trim($s ?? 'pending'));
    if ($type === 'payment') {
        $map = [
            'pending' => ['ship-badge-pending', 'fa-hourglass-half', 'Pending'],
            'paid'    => ['ship-badge-done',    'fa-check-circle',   'Paid'],
            'overdue' => ['ship-badge-overdue', 'fa-exclamation-circle', 'Overdue'],
            'partial' => ['ship-badge-partial', 'fa-adjust',         'Partial'],
        ];
    } else {
        $map = [
            'pending'   => ['ship-badge-pending', 'fa-hourglass-half', 'Pending'],
            'received'  => ['ship-badge-done',    'fa-check-circle',   'Received'],
            'issued'    => ['ship-badge-done',    'fa-check-circle',   'Issued'],
            'sent'      => ['ship-badge-done',    'fa-check-circle',   'Sent'],
            'completed' => ['ship-badge-done',    'fa-check-circle',   'Completed'],
            'n/a'       => ['ship-badge-na',      'fa-minus-circle',   'N/A'],
        ];
    }
    [$cls, $icon, $label] = $map[$s] ?? ['ship-badge-pending', 'fa-circle', ucfirst($s)];
    return "<span class=\"ship-badge $cls\"><i class=\"fas $icon\"></i> " . htmlentities($label) . "</span>";
}

// ── Fetch a single order row ──────────────────────────────────────────────
function fetchOrder($con, $oid, $uid) {
    $oid = mysqli_real_escape_string($con, $oid);
    $uid = mysqli_real_escape_string($con, $uid);
    $r = mysqli_query($con, "
        SELECT tor.*, u.fullName AS name, u.userEmail AS email
        FROM tire_orders tor
        JOIN users u ON u.id = tor.customer_id
        WHERE tor.order_id = '$oid' AND tor.customer_id = '$uid'
    ");
    return $r ? mysqli_fetch_assoc($r) : null;
}

// ── Fetch items for an order ──────────────────────────────────────────────
function fetchItems($con, $oid) {
    $oid = mysqli_real_escape_string($con, $oid);
    $r = mysqli_query($con, "
        SELECT ti.item_id, ti.icode, ti.quantity, ti.unit_price,
               ti.discount, ti.unit_weight, ti.total_weight,
               ti.payment_amount, ti.total_payment, ti.total_cbm,
               ti.unit_cbm, ti.rate_value, ti.revised,
               ti.original_order_id AS item_orig_order,
               td.description
        FROM tire_order_items ti
        LEFT JOIN tire_details td ON ti.icode = td.icode
        WHERE ti.order_id = '$oid'
        ORDER BY ti.item_id
    ");
    $items = [];
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            $qty   = floatval(preg_replace('/[^0-9.]/', '', $row['quantity']));
            $price = floatval(preg_replace('/[^0-9.]/', '', $row['unit_price']));
            $row['quantity_numeric']   = $qty;
            $row['unit_price_numeric'] = $price;
            $row['total_price']        = $qty * $price;
            $items[] = $row;
        }
    }
    return $items;
}

// ── Fetch shipment for the current order ─────────────────────────────────
function fetchShipment($con, $oid) {
    $oid = mysqli_real_escape_string($con, $oid);
    $r = mysqli_query($con, "SELECT * FROM shipments WHERE order_id = '$oid' LIMIT 1");
    return ($r && mysqli_num_rows($r) > 0) ? mysqli_fetch_assoc($r) : null;
}

// ── Load current order ────────────────────────────────────────────────────
$order = fetchOrder($con, $orderId, $userId);
if (!$order) { header('location:cus-confirmed-orders.php'); exit; }

$isRevision      = (int)$order['is_revision'] === 1;
$originalOrderId = $order['original_order_id'];
$revisedOrderId  = $order['revised_order_id'];

// Root of the revision chain
$rootOrderId = $isRevision ? $originalOrderId : $orderId;

// ── Build the full revision chain (original + all revisions) ──────────────
$allRevisions = [];

if (!empty($rootOrderId)) {
    $safeRoot = mysqli_real_escape_string($con, $rootOrderId);
    $origOrder = fetchOrder($con, $rootOrderId, $userId);
    if ($origOrder) {
        $allRevisions[] = [
            'order'       => $origOrder,
            'items'       => fetchItems($con, $rootOrderId),
            'version'     => 0,
            'is_original' => true,
            'order_id'    => $rootOrderId,
        ];
    }
    $revRes = mysqli_query($con, "
        SELECT order_id FROM tire_orders
        WHERE original_order_id = '$safeRoot' AND is_revision = 1
        ORDER BY order_id ASC
    ");
    if ($revRes) {
        $vNum = 1;
        while ($revRow = mysqli_fetch_assoc($revRes)) {
            $rid      = $revRow['order_id'];
            $revOrder = fetchOrder($con, $rid, $userId);
            if ($revOrder) {
                $allRevisions[] = [
                    'order'       => $revOrder,
                    'items'       => fetchItems($con, $rid),
                    'version'     => $vNum++,
                    'is_original' => false,
                    'order_id'    => $rid,
                ];
            }
        }
    }
}

// ── Current order items / totals ──────────────────────────────────────────
$items           = fetchItems($con, $orderId);
$totalOrderValue = array_sum(array_column($items, 'total_price'));

// ── Shipment data ─────────────────────────────────────────────────────────
$shipment = fetchShipment($con, $orderId);

// Default active tab = this order's position in the chain
$activeTabIndex = 0;
foreach ($allRevisions as $i => $rev) {
    if ($rev['order_id'] == $orderId) { $activeTabIndex = $i; break; }
}

// Revision version number
$revisionVersion = 0;
if ($isRevision && !empty($originalOrderId)) {
    $safeOrigId = mysqli_real_escape_string($con, $originalOrderId);
    $rvRes = mysqli_query($con, "
        SELECT COUNT(*) AS cnt FROM tire_orders
        WHERE original_order_id = '$safeOrigId' AND is_revision = 1 AND order_id <= '$orderId'
    ");
    if ($rvRes) { $rvRow = mysqli_fetch_assoc($rvRes); $revisionVersion = (int)$rvRow['cnt']; }
}

$status = strtolower(trim($order['status'] ?? 'pending'));

$charges = [];
for ($i = 1; $i <= 4; $i++) {
    $n = trim($order["charge{$i}_name"] ?? '');
    $v = trim($order["charge{$i}_value"] ?? '');
    if ($n !== '' && $v !== '') $charges[] = ['name' => $n, 'value' => $v];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - #<?php echo htmlentities($order['order_id']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #F28018;
            --border-gray: #e0e0e0;
            --bg-light: #f8fafc;
            --success: #27ae60;
            --warning: #f39c12;
            --error: #e74c3c;
            --revised: #9b59b6;
            --info: #2980b9;
            --teal: #5bc0be;
            --teal-dk: #47a8a6;
            --teal-lt: rgba(91,192,190,0.12);
            --amber: #f59e0b;
            --amber-dk: #d97706;
            --amber-lt: rgba(245,158,11,0.10);
            --text-gray: #64748b;
            --dark-gray: #333333;
            --orange-light: rgba(242,128,24,.10);
            --success-light: rgba(39,174,96,.10);
            --warning-light: rgba(241,196,15,.10);
            --error-light: rgba(231,76,60,.10);
            --revised-light: rgba(155,89,182,.10);
            --info-light: rgba(41,128,185,.10);
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg,#F28018 0%,#e67e22 100%);
            --gradient-danger: linear-gradient(135deg,#e74c3c 0%,#c0392b 100%);
            --gradient-ship: linear-gradient(135deg,#1a6fa8 0%,#2980b9 100%);
            --shadow-sm: 0 1px 2px rgba(0,0,0,.05);
            --shadow: 0 1px 3px rgba(0,0,0,.10),0 1px 2px rgba(0,0,0,.06);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,.10),0 4px 6px -2px rgba(0,0,0,.05);
        }
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;background:var(--bg-light);color:var(--dark-gray);line-height:1.6;padding:2rem;-webkit-font-smoothing:antialiased;}
        .container{max-width:1300px;margin:0 auto;}

        /* ── Page Header ── */
        .page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;}
        .page-title{font-size:2rem;font-weight:800;color:var(--dark-gray);margin-bottom:.35rem;display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;}
        .page-subtitle{color:var(--text-gray);font-size:.95rem;}
        .header-actions-right{display:flex;gap:.75rem;flex-wrap:wrap;}

        /* ── Buttons ── */
        .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.7rem 1.4rem;border:none;border-radius:.75rem;font-weight:600;text-decoration:none;cursor:pointer;transition:all .2s;font-size:.88rem;white-space:nowrap;}
        .btn-secondary{background:var(--white);color:var(--text-gray);border:1px solid var(--border-gray);}
        .btn-secondary:hover{background:var(--bg-light);border-color:var(--primary-orange);color:var(--primary-orange);}
        .btn-primary{background:var(--gradient-1);color:var(--white);box-shadow:var(--shadow);}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:var(--shadow-lg);}
        .btn-danger{background:var(--gradient-danger);color:var(--white);box-shadow:var(--shadow);}
        .btn-danger:hover{transform:translateY(-2px);box-shadow:var(--shadow-lg);}
        .btn-cancel{background:var(--white);color:var(--text-gray);border:1px solid var(--border-gray);}
        .btn-cancel:hover{background:var(--bg-light);}

        /* ── Cards ── */
        .card{background:var(--white);border-radius:1rem;border:1px solid var(--border-gray);overflow:hidden;box-shadow:var(--shadow-sm);margin-bottom:1.75rem;}
        .card-header{padding:1.25rem 1.75rem;border-bottom:1px solid var(--border-gray);display:flex;align-items:center;justify-content:space-between;background:linear-gradient(to bottom,var(--white),var(--bg-light));}
        .card-title{font-size:1.1rem;font-weight:700;color:var(--dark-gray);display:flex;align-items:center;gap:.65rem;}
        .card-title i{color:var(--primary-orange);}
        .card-title i.ship-icon{color:var(--info);}
        .card-body{padding:1.75rem;}

        /* ── Info Grid ── */
        .info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:0;}
        .info-row{display:flex;flex-direction:column;padding:.9rem 1.5rem;border-bottom:1px solid var(--border-gray);border-right:1px solid var(--border-gray);}
        .info-label{font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--text-gray);margin-bottom:.2rem;}
        .info-value{font-size:.93rem;color:var(--dark-gray);font-weight:500;}

        /* ── Status badges ── */
        .status-badge{padding:.28rem .75rem;border-radius:1rem;font-size:.73rem;font-weight:700;display:inline-flex;align-items:center;gap:.32rem;}
        .status-pending{background:var(--error-light);color:var(--error);}
        .status-in-process{background:var(--warning-light);color:var(--warning);}
        .status-closed{background:var(--success-light);color:var(--success);}
        .status-pi-confirm{background:rgba(52,152,219,.1);color:#2980b9;}
        .status-revised{background:var(--revised-light);color:var(--revised);}

        /* ── Shipment Badges ── */
        .ship-badge{padding:.24rem .65rem;border-radius:1rem;font-size:.7rem;font-weight:700;display:inline-flex;align-items:center;gap:.28rem;}
        .ship-badge-pending{background:var(--warning-light);color:var(--warning);}
        .ship-badge-done{background:var(--success-light);color:var(--success);}
        .ship-badge-overdue{background:var(--error-light);color:var(--error);}
        .ship-badge-partial{background:var(--orange-light);color:var(--primary-orange);}
        .ship-badge-na{background:#f0f0f0;color:#999;}

        /* ── Tables ── */
        .table-responsive{overflow-x:auto;}
        .table{width:100%;border-collapse:separate;border-spacing:0;font-size:.86rem;}
        .table thead th{background:var(--bg-light);color:var(--text-gray);font-weight:600;text-transform:uppercase;font-size:.7rem;padding:.75rem 1rem;border-bottom:2px solid var(--border-gray);text-align:left;white-space:nowrap;}
        .table tbody tr{transition:background .15s;}
        .table tbody tr:hover{background:var(--orange-light);}
        .table tbody td{padding:.8rem 1rem;border-bottom:1px solid var(--border-gray);vertical-align:middle;}
        .table tbody tr:last-child td{border-bottom:none;}

        /* ── Diff highlights ── */
        .row-added{background:rgba(39,174,96,.07)!important;}
        .row-removed{background:rgba(231,76,60,.07)!important;}
        .row-changed{background:rgba(242,128,24,.08)!important;}
        .row-added td:first-child{border-left:3px solid var(--success);}
        .row-removed td:first-child{border-left:3px solid var(--error);}
        .row-changed td:first-child{border-left:3px solid var(--primary-orange);}
        .change-pill{display:inline-block;font-size:.66rem;font-weight:700;padding:.12rem .48rem;border-radius:1rem;margin-left:.35rem;vertical-align:middle;}
        .pill-added{background:var(--success-light);color:var(--success);}
        .pill-removed{background:var(--error-light);color:var(--error);}
        .pill-changed{background:var(--orange-light);color:var(--primary-orange);}

        /* ── Revision History Timeline ── */
        .timeline{position:relative;padding:1.5rem 1.75rem;}
        .timeline::before{content:'';position:absolute;left:2.9rem;top:0;bottom:0;width:2px;background:linear-gradient(to bottom,var(--border-gray),transparent);}
        .timeline-item{position:relative;display:flex;gap:1rem;margin-bottom:1.1rem;}
        .timeline-item:last-child{margin-bottom:0;}
        .timeline-dot{width:2.2rem;height:2.2rem;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;flex-shrink:0;position:relative;z-index:1;border:2px solid var(--white);}
        .timeline-dot.is-original{background:#e8eaed;color:#555;box-shadow:0 0 0 2px #ccc;}
        .timeline-dot.is-revision{background:var(--revised);color:var(--white);box-shadow:0 0 0 2px rgba(155,89,182,.35);}
        .timeline-dot.is-current{box-shadow:0 0 0 3px var(--primary-orange)!important;}
        .timeline-content{flex:1;background:var(--bg-light);border:1px solid var(--border-gray);border-radius:.75rem;padding:.85rem 1.2rem;transition:border-color .2s,box-shadow .2s;cursor:pointer;}
        .timeline-content:hover{border-color:#bbb;}
        .timeline-content.is-current{border-color:var(--primary-orange);box-shadow:0 0 0 2px rgba(242,128,24,.12);background:rgba(242,128,24,.025);}
        .timeline-content.is-revision-current{border-color:var(--revised);box-shadow:0 0 0 2px rgba(155,89,182,.12);background:rgba(155,89,182,.025);}
        .timeline-top{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:.35rem;}
        .timeline-oid{font-weight:700;font-size:.9rem;}
        .timeline-oid a{color:var(--primary-orange);text-decoration:none;}
        .timeline-oid a:hover{text-decoration:underline;}
        .timeline-date{font-size:.78rem;color:var(--text-gray);margin-left:auto;}
        .timeline-meta{display:flex;gap:1.1rem;flex-wrap:wrap;font-size:.79rem;color:var(--text-gray);}
        .timeline-meta span strong{color:var(--dark-gray);}
        .timeline-current-tag{font-size:.66rem;font-weight:700;padding:.14rem .52rem;border-radius:1rem;background:var(--primary-orange);color:var(--white);}
        .timeline-ver-tag{font-size:.7rem;font-weight:600;padding:.1rem .45rem;border-radius:1rem;}
        .timeline-ver-tag.orig{background:#e8eaed;color:#555;}
        .timeline-ver-tag.rev{background:var(--revised-light);color:var(--revised);}

        /* ── Revision Tab Navigator ── */
        .rev-tabs-wrap{border-bottom:2px solid var(--border-gray);overflow-x:auto;white-space:nowrap;}
        .rev-tabs{display:inline-flex;padding:0 1.75rem;}
        .rev-tab{padding:.75rem 1.35rem;font-size:.84rem;font-weight:600;color:var(--text-gray);cursor:pointer;border-bottom:3px solid transparent;border-top:none;border-left:none;border-right:none;background:none;transition:all .18s;white-space:nowrap;user-select:none;}
        .rev-tab:hover{color:var(--primary-orange);}
        .rev-tab.active{color:var(--primary-orange);border-bottom-color:var(--primary-orange);}
        .rev-tab.active.is-rev{color:var(--revised);border-bottom-color:var(--revised);}
        .rev-panel{display:none;}
        .rev-panel.active{display:block;}

        /* ── Comparison grid ── */
        .comparison-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;}
        @media(max-width:900px){.comparison-grid{grid-template-columns:1fr;}}
        .comparison-panel{background:var(--white);border-radius:1rem;border:2px solid var(--border-gray);overflow:hidden;}
        .comparison-panel.cmp-prev .comparison-panel-header{background:#f4f6f8;color:var(--dark-gray);border-bottom:1px solid var(--border-gray);}
        .comparison-panel.cmp-cur{border-color:var(--revised);box-shadow:0 0 0 3px rgba(155,89,182,.09);}
        .comparison-panel.cmp-cur .comparison-panel-header{background:var(--revised-light);color:var(--revised);border-bottom:2px solid rgba(155,89,182,.2);}
        .comparison-panel-header{padding:.85rem 1.5rem;font-weight:700;font-size:.88rem;display:flex;align-items:center;gap:.6rem;}
        .c-badge{font-size:.67rem;padding:.16rem .52rem;border-radius:1rem;font-weight:700;margin-left:auto;}
        .cmp-prev .c-badge{background:#e8eaed;color:#555;}
        .cmp-cur .c-badge{background:var(--revised);color:#fff;}
        .panel-meta{display:grid;grid-template-columns:auto 1fr;gap:.4rem .7rem;padding:.85rem 1.5rem;font-size:.82rem;border-bottom:1px solid var(--border-gray);}
        .panel-meta dt{font-weight:600;color:var(--text-gray);white-space:nowrap;}
        .panel-meta dd{margin:0;color:var(--dark-gray);}

        /* ── Legend ── */
        .legend{display:flex;gap:1.1rem;flex-wrap:wrap;font-size:.78rem;padding:.65rem 1rem;background:var(--bg-light);border-radius:.6rem;border:1px solid var(--border-gray);}
        .legend-item{display:flex;align-items:center;gap:.4rem;font-weight:600;}
        .legend-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0;}

        /* ── Section label ── */
        .section-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-gray);margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem;padding:0 1.5rem;}
        .section-label::after{content:'';flex:1;height:1px;background:var(--border-gray);}

        /* ── Charges ── */
        .charges-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:.75rem;padding:1.25rem 1.75rem;}
        .charge-item{background:var(--bg-light);border:1px solid var(--border-gray);border-radius:.6rem;padding:.8rem 1rem;display:flex;justify-content:space-between;align-items:center;}
        .charge-name{font-size:.83rem;color:var(--text-gray);font-weight:500;}
        .charge-value{font-size:.93rem;font-weight:700;color:var(--dark-gray);}

        /* ── Totals row ── */
        .totals-row{background:linear-gradient(135deg,rgba(242,128,24,.06),rgba(230,126,34,.04));border-radius:.75rem;padding:1rem 1.5rem;display:flex;gap:2rem;flex-wrap:wrap;align-items:center;border:1px solid rgba(242,128,24,.18);}
        .total-item{display:flex;flex-direction:column;gap:.1rem;}
        .total-label{font-size:.7rem;font-weight:600;text-transform:uppercase;color:var(--text-gray);}
        .total-value{font-size:1.05rem;font-weight:800;color:var(--dark-gray);}
        .total-value.highlight{color:var(--primary-orange);}

        /* ── Banners ── */
        .banner{padding:1rem 1.25rem;border-radius:.5rem;margin-bottom:1.5rem;display:flex;align-items:flex-start;gap:.75rem;}
        .banner-rev{background:var(--revised-light);border-left:4px solid var(--revised);}
        .banner-orig{background:rgba(242,128,24,.07);border-left:4px solid var(--primary-orange);}
        .banner i{margin-top:2px;flex-shrink:0;}
        .banner-rev i{color:var(--revised);}
        .banner-orig i{color:var(--primary-orange);}

        /* ── Notes ── */
        .notes-box{background:var(--bg-light);border:1px solid var(--border-gray);border-radius:.6rem;padding:1rem 1.25rem;font-size:.88rem;color:var(--dark-gray);white-space:pre-wrap;line-height:1.6;}
        .no-val{font-style:italic;color:var(--text-gray);opacity:.6;}
        .desc-cell{max-width:230px;color:var(--text-gray);font-size:.81rem;line-height:1.4;}

        /* ── Empty state ── */
        .empty-state{text-align:center;padding:2rem 1rem;color:var(--text-gray);}
        .empty-icon{font-size:2rem;margin-bottom:.6rem;opacity:.35;}

        /* ═══════════════════════════════════════════
           SHIPMENT / LOGISTICS STYLES
        ═══════════════════════════════════════════ */
        .ship-card-header{background:linear-gradient(135deg,rgba(41,128,185,.06),rgba(26,111,168,.04));border-bottom:1px solid rgba(41,128,185,.18);}
        .ship-card-header .card-title i{color:var(--info);}

        /* Progress tracker */
        .ship-progress-wrap{padding:1.5rem 1.75rem;border-bottom:1px solid var(--border-gray);}
        .ship-progress-title{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-gray);margin-bottom:1rem;}
        .ship-steps{display:flex;align-items:flex-start;gap:0;position:relative;}
        .ship-steps::before{content:'';position:absolute;top:1rem;left:1rem;right:1rem;height:2px;background:var(--border-gray);z-index:0;}
        .ship-step{flex:1;display:flex;flex-direction:column;align-items:center;text-align:center;position:relative;z-index:1;}
        .ship-step-dot{width:2rem;height:2rem;border-radius:50%;border:2px solid var(--border-gray);background:var(--white);display:flex;align-items:center;justify-content:center;font-size:.65rem;color:var(--text-gray);margin-bottom:.45rem;transition:all .3s;}
        .ship-step.done .ship-step-dot{background:var(--success);border-color:var(--success);color:#fff;}
        .ship-step.active .ship-step-dot{background:var(--info);border-color:var(--info);color:#fff;box-shadow:0 0 0 3px rgba(41,128,185,.2);}
        .ship-step-label{font-size:.65rem;font-weight:600;color:var(--text-gray);line-height:1.3;max-width:5rem;}
        .ship-step.done .ship-step-label{color:var(--success);}
        .ship-step.active .ship-step-label{color:var(--info);font-weight:700;}

        /* Logistics info grid */
        .ship-info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:0;}
        .ship-info-row{display:flex;flex-direction:column;padding:.85rem 1.5rem;border-bottom:1px solid var(--border-gray);border-right:1px solid var(--border-gray);}
        .ship-info-label{font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#2980b9;margin-bottom:.2rem;display:flex;align-items:center;gap:.35rem;}
        .ship-info-label i{opacity:.7;}
        .ship-info-value{font-size:.93rem;color:var(--dark-gray);font-weight:500;}

        /* Section subheader inside ship card */
        .ship-section-head{padding:.7rem 1.75rem;background:linear-gradient(to right,rgba(41,128,185,.04),transparent);border-bottom:1px solid rgba(41,128,185,.1);border-top:1px solid rgba(41,128,185,.1);font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#2980b9;display:flex;align-items:center;gap:.5rem;}

        /* Docs status row */
        .docs-status-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:.75rem;padding:1.25rem 1.75rem;}
        .doc-status-item{background:var(--bg-light);border:1px solid var(--border-gray);border-radius:.65rem;padding:.85rem 1.1rem;display:flex;justify-content:space-between;align-items:center;gap:.5rem;}
        .doc-status-name{font-size:.82rem;color:var(--text-gray);font-weight:500;display:flex;align-items:center;gap:.4rem;}
        .doc-status-name i{color:var(--info);opacity:.75;}

        /* Date highlight */
        .date-highlight{display:inline-flex;align-items:center;gap:.35rem;font-size:.88rem;font-weight:600;color:var(--dark-gray);}
        .date-highlight i{color:var(--info);font-size:.78rem;}
        .date-warn{color:var(--error)!important;}
        .date-soon{color:var(--warning)!important;}

        /* No shipment placeholder */
        .no-shipment-box{padding:2.5rem 1.75rem;text-align:center;color:var(--text-gray);}
        .no-shipment-box i{font-size:2.8rem;opacity:.2;display:block;margin-bottom:.75rem;}
        .no-shipment-box p{font-size:.9rem;}

        /* ── Modal ── */
        .modal{display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);}
        .modal.show{display:flex;align-items:center;justify-content:center;}
        .modal-content{background:var(--white);border-radius:1rem;max-width:500px;width:90%;box-shadow:var(--shadow-lg);animation:slideUp .3s ease-out;}
        .modal-header{padding:1.5rem 2rem;border-bottom:1px solid var(--border-gray);display:flex;align-items:center;gap:1rem;}
        .modal-header i{font-size:1.8rem;color:var(--error);}
        .modal-title{font-size:1.15rem;font-weight:700;}
        .modal-body{padding:2rem;}
        .modal-footer{padding:1.25rem 2rem;border-top:1px solid var(--border-gray);display:flex;gap:.75rem;justify-content:flex-end;}

        @keyframes slideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
        @keyframes slideIn{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
        .animate-in{animation:slideIn .45s ease-out forwards;}

        @media(max-width:768px){
            body{padding:1rem;}
            .page-header{flex-direction:column;align-items:stretch;}
            .page-title{font-size:1.4rem;}
            .card-body{padding:1rem;}
            .header-actions-right{flex-direction:column;}
            .btn{justify-content:center;}
            .modal-footer{flex-direction:column-reverse;}
            .modal-footer .btn{width:100%;}
            .info-grid,.ship-info-grid{grid-template-columns:1fr;}
            .info-row,.ship-info-row{border-right:none;}
            .totals-row{gap:1rem;}
            .timeline::before{left:1.85rem;}
            .rev-tabs{padding:0 1rem;}
            .ship-steps{flex-wrap:wrap;gap:1rem;}
            .ship-steps::before{display:none;}
            .ship-step{min-width:4.5rem;}
            .docs-status-grid{grid-template-columns:1fr;}
        }
        @media print{.header-actions-right,.btn,.modal{display:none!important;}.card{box-shadow:none;}}
    </style>
</head>
<body>
<div class="container">

<!-- ══ Page Header ═══════════════════════════════════════════════════════════ -->
<div class="page-header">
    <div>
        <h1 class="page-title">
            Order Details &ndash; #<?php echo htmlentities($order['order_id']); ?>
            <?php if ($isRevision): ?>
                <span style="font-size:.88rem;font-weight:600;color:var(--revised);">
                    <i class="fas fa-sync-alt"></i> Revision v<?php echo $revisionVersion; ?>
                </span>
            <?php endif; ?>
        </h1>
        <p class="page-subtitle">Detailed information about your order</p>
    </div>
    <div class="header-actions-right">
        <?php if ($status === 'pending'): ?>
            <a href="confirm_order.php?id=<?php echo urlencode($orderId); ?>" class="btn btn-primary">
                <i class="fas fa-check-circle"></i> Confirm Order
            </a>
            <button onclick="showDeleteModal()" class="btn btn-danger">
                <i class="fas fa-trash-alt"></i> Delete Order
            </button>
        <?php elseif ($status === 'pi_confirm'): ?>
            <a href="confirm_pi.php?id=<?php echo urlencode($orderId); ?>" class="btn btn-primary">
                <i class="fas fa-check-circle"></i> Confirm PI
            </a>
        <?php endif; ?>
        <a href="cus-confirmed-orders.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>
</div>

<!-- ══ Context banners ════════════════════════════════════════════════════════ -->
<?php if ($isRevision && !empty($originalOrderId)): ?>
<div class="banner banner-rev animate-in">
    <i class="fas fa-info-circle fa-lg"></i>
    <div>
        This is <strong>Revision v<?php echo $revisionVersion; ?></strong> of original order
        <a href="?oid=<?php echo urlencode($originalOrderId); ?>" style="color:var(--revised);font-weight:700;">#<?php echo htmlentities($originalOrderId); ?></a>.
        <?php if (!empty($revisedOrderId)): ?>
            A newer revision <a href="?oid=<?php echo urlencode($revisedOrderId); ?>" style="color:var(--primary-orange);font-weight:700;">#<?php echo htmlentities($revisedOrderId); ?></a> also exists.
        <?php endif; ?>
        The full revision history and per-version comparison is shown below.
    </div>
</div>
<?php elseif (!$isRevision && !empty($revisedOrderId)): ?>
<div class="banner banner-orig animate-in">
    <i class="fas fa-info-circle fa-lg"></i>
    <div>
        This order has been revised. Latest revision:
        <a href="?oid=<?php echo urlencode($revisedOrderId); ?>" style="color:var(--primary-orange);font-weight:700;">#<?php echo htmlentities($revisedOrderId); ?></a>.
        The full revision history and per-version comparison is shown below.
    </div>
</div>
<?php endif; ?>

<!-- ══ ORDER INFORMATION CARD ════════════════════════════════════════════════ -->
<div class="card animate-in">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-info-circle"></i> Order Information</h2>
        <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
            <?php echo statusBadge($order['status']); ?>
        </div>
    </div>
    <div class="info-grid">
        <div class="info-row"><span class="info-label">Order ID</span><span class="info-value"><strong>#<?php echo htmlentities($order['order_id']); ?></strong></span></div>
        <div class="info-row"><span class="info-label">Customer</span><span class="info-value"><?php echo htmlentities($order['name']); ?></span></div>
        <div class="info-row"><span class="info-label">Order Date</span><span class="info-value"><?php echo fmtDate($order['order_date']); ?></span></div>
        <div class="info-row"><span class="info-label">Order Reference</span><span class="info-value"><?php echo !empty($order['order_reference']) ? htmlentities($order['order_reference']) : '<span class="no-val">—</span>'; ?></span></div>
        <div class="info-row"><span class="info-label">Invoice No.</span><span class="info-value"><?php echo !empty($order['invoice_no']) ? htmlentities($order['invoice_no']) : '<span class="no-val">—</span>'; ?></span></div>
        <div class="info-row"><span class="info-label">Total Items</span><span class="info-value"><?php echo htmlentities($order['total_items']); ?></span></div>
        <div class="info-row"><span class="info-label">Total Quantity</span><span class="info-value"><?php echo htmlentities($order['total_quantity']); ?></span></div>
        <div class="info-row"><span class="info-label">Total Weight</span><span class="info-value"><?php echo fmtKg($order['total_weight']); ?></span></div>
        <div class="info-row"><span class="info-label">Total CBM</span><span class="info-value"><?php echo !empty($order['total_cbm']) ? fmtNum($order['total_cbm'],4).' m³' : '<span class="no-val">—</span>'; ?></span></div>
        <div class="info-row">
            <span class="info-label">Total Payment</span>
            <span class="info-value" style="color:var(--primary-orange);font-weight:700;">
                <?php echo !empty($order['total_payment']) ? fmtMoney($order['total_payment']) : '<span class="no-val" style="color:var(--text-gray);font-weight:400;">—</span>'; ?>
            </span>
        </div>
        <?php if (!empty($order['destination_port'])): ?><div class="info-row"><span class="info-label">Destination Port</span><span class="info-value"><?php echo htmlentities($order['destination_port']); ?></span></div><?php endif; ?>
        <?php if (!empty($order['shipping_method'])): ?><div class="info-row"><span class="info-label">Shipping Method</span><span class="info-value"><?php echo htmlentities($order['shipping_method']); ?></span></div><?php endif; ?>
        <?php if (!empty($order['container_size'])): ?><div class="info-row"><span class="info-label">Container Size</span><span class="info-value"><?php echo htmlentities($order['container_size']); ?></span></div><?php endif; ?>
        <?php if (!empty($order['hs_code'])): ?><div class="info-row"><span class="info-label">HS Code</span><span class="info-value"><?php echo htmlentities($order['hs_code']); ?></span></div><?php endif; ?>
        <?php if (!empty($order['packing'])): ?><div class="info-row"><span class="info-label">Packing</span><span class="info-value"><?php echo htmlentities($order['packing']); ?></span></div><?php endif; ?>
        <?php if (!empty($order['plate'])): ?><div class="info-row"><span class="info-label">Plate</span><span class="info-value"><?php echo htmlentities($order['plate']); ?></span></div><?php endif; ?>
        <?php if ($isRevision): ?>
        <div class="info-row"><span class="info-label">Original Order</span><span class="info-value"><a href="?oid=<?php echo urlencode($originalOrderId); ?>" style="color:var(--primary-orange);font-weight:600;">#<?php echo htmlentities($originalOrderId); ?></a></span></div>
        <?php endif; ?>
        <?php if (!empty($order['revised_order_id'])): ?>
        <div class="info-row"><span class="info-label">Revised To</span><span class="info-value"><a href="?oid=<?php echo urlencode($order['revised_order_id']); ?>" style="color:var(--revised);font-weight:600;">#<?php echo htmlentities($order['revised_order_id']); ?> <i class="fas fa-external-link-alt fa-xs"></i></a></span></div>
        <?php endif; ?>
        <?php if (!empty($order['accepted_by'])): ?><div class="info-row"><span class="info-label">Accepted By</span><span class="info-value"><?php echo htmlentities($order['accepted_by']); ?></span></div><?php endif; ?>
        <?php if (!empty($order['accepted_at'])): ?><div class="info-row"><span class="info-label">Accepted At</span><span class="info-value"><?php echo fmtDate($order['accepted_at']); ?></span></div><?php endif; ?>
        <?php if (!empty($order['confirmed_at'])): ?><div class="info-row"><span class="info-label">Confirmed At</span><span class="info-value"><?php echo fmtDate($order['confirmed_at']); ?></span></div><?php endif; ?>
    </div>
</div>

<!-- ══ ADDITIONAL CHARGES ════════════════════════════════════════════════════ -->
<?php if (!empty($charges)): ?>
<div class="card animate-in">
    <div class="card-header"><h2 class="card-title"><i class="fas fa-plus-circle"></i> Additional Charges</h2></div>
    <div class="charges-list">
        <?php foreach ($charges as $c): ?>
        <div class="charge-item">
            <span class="charge-name"><?php echo htmlentities($c['name']); ?></span>
            <span class="charge-value"><?php echo htmlentities($c['value']); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ══ NOTES & COMMENTS ══════════════════════════════════════════════════════ -->
<?php if (!empty($order['order_notes']) || !empty($order['customer_comment']) || !empty($order['acm_comment'])): ?>
<div class="card animate-in">
    <div class="card-header"><h2 class="card-title"><i class="fas fa-comment-alt"></i> Notes &amp; Comments</h2></div>
    <div class="card-body" style="display:flex;flex-direction:column;gap:1.25rem;">
        <?php if (!empty($order['order_notes'])): ?>
        <div><div class="info-label" style="margin-bottom:.4rem;">Order Notes</div><div class="notes-box"><?php echo htmlentities($order['order_notes']); ?></div></div>
        <?php endif; ?>
        <?php if (!empty($order['customer_comment'])): ?>
        <div><div class="info-label" style="margin-bottom:.4rem;">Customer Comment</div><div class="notes-box"><?php echo htmlentities($order['customer_comment']); ?></div></div>
        <?php endif; ?>
        <?php if (!empty($order['acm_comment'])): ?>
        <div><div class="info-label" style="margin-bottom:.4rem;">ACM Comment</div><div class="notes-box"><?php echo htmlentities($order['acm_comment']); ?></div></div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════════════════
     LOGISTICS & SHIPMENT CARD
══════════════════════════════════════════════════════════════════════════════ -->
<div class="card animate-in">
    <div class="card-header ship-card-header">
        <h2 class="card-title"><i class="fas fa-ship ship-icon"></i> Logistics &amp; Shipment</h2>
        <?php if ($shipment): ?>
        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
            <?php echo shipmentStatusBadge($shipment['payment_status'], 'payment'); ?>
            <?php if (!empty($shipment['inco_term'])): ?>
            <span style="font-size:.75rem;font-weight:700;padding:.2rem .6rem;border-radius:.4rem;background:var(--info-light);color:var(--info);">
                <?php echo htmlentities($shipment['inco_term']); ?>
            </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($shipment): ?>

    <?php
    $today = date('Y-m-d');
    // ── 4-step progress: Order Placed → Loading → On Board → ETA ──
    $steps = [
        ['label' => 'Order Placed', 'done' => true,                                                                            'icon' => 'fa-file-alt'],
        ['label' => 'Loading',      'done' => !empty($shipment['loading_date']),                                                'icon' => 'fa-dolly'],
        ['label' => 'On Board',     'done' => !empty($shipment['on_board_date']),                                               'icon' => 'fa-ship'],
        ['label' => 'ETA',          'done' => (!empty($shipment['eta']) && $shipment['eta'] <= $today),                        'icon' => 'fa-flag-checkered'],
    ];
    $activeStepIdx = null;
    foreach ($steps as $si => $st) {
        if (!$st['done']) { $activeStepIdx = $si; break; }
    }
    ?>

    <div class="ship-progress-wrap">
        <div class="ship-progress-title"><i class="fas fa-route" style="margin-right:.4rem;"></i>Shipment Progress</div>
        <div class="ship-steps">
            <?php foreach ($steps as $si => $st):
                $cls = $st['done'] ? 'done' : ($si === $activeStepIdx ? 'active' : '');
            ?>
            <div class="ship-step <?php echo $cls; ?>">
                <div class="ship-step-dot">
                    <?php if ($st['done']): ?><i class="fas fa-check" style="font-size:.6rem;"></i><?php else: ?><i class="fas <?php echo $st['icon']; ?>" style="font-size:.6rem;"></i><?php endif; ?>
                </div>
                <div class="ship-step-label"><?php echo $st['label']; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="ship-section-head"><i class="fas fa-anchor"></i> Core Shipment Details</div>
    <div class="ship-info-grid">
        <div class="ship-info-row">
            <span class="ship-info-label"><i class="fas fa-handshake"></i> Incoterm</span>
            <span class="ship-info-value">
                <?php if (!empty($shipment['inco_term'])): ?>
                <span style="font-weight:700;font-size:1rem;color:var(--info);"><?php echo htmlentities($shipment['inco_term']); ?></span>
                <?php else: ?><span class="no-val">—</span><?php endif; ?>
            </span>
        </div>
        <div class="ship-info-row">
            <span class="ship-info-label"><i class="fas fa-calendar-alt"></i> Loading Date</span>
            <span class="ship-info-value">
                <?php echo !empty($shipment['loading_date']) ? '<span class="date-highlight"><i class="fas fa-calendar-check"></i>'.fmtDateShort($shipment['loading_date']).'</span>' : '<span class="no-val">—</span>'; ?>
            </span>
        </div>
        <div class="ship-info-row">
            <span class="ship-info-label"><i class="fas fa-building"></i> Freight Forwarder</span>
            <span class="ship-info-value"><?php echo !empty($shipment['freight_forwarder']) ? htmlentities($shipment['freight_forwarder']) : '<span class="no-val">—</span>'; ?></span>
        </div>
        <div class="ship-info-row">
            <span class="ship-info-label"><i class="fas fa-dollar-sign"></i> Freight Cost</span>
            <span class="ship-info-value" style="font-weight:700;color:var(--dark-gray);">
                <?php echo ($shipment['freight_cost'] !== null && $shipment['freight_cost'] !== '') ? fmtMoney($shipment['freight_cost']) : '<span class="no-val">—</span>'; ?>
            </span>
        </div>
        <div class="ship-info-row">
            <span class="ship-info-label"><i class="fas fa-ship"></i> Vessel / Voyage</span>
            <span class="ship-info-value"><?php echo !empty($shipment['vessel_voy']) ? htmlentities($shipment['vessel_voy']) : '<span class="no-val">—</span>'; ?></span>
        </div>
        <div class="ship-info-row">
            <span class="ship-info-label"><i class="fas fa-file-invoice"></i> B/L Number</span>
            <span class="ship-info-value" style="font-weight:600;"><?php echo !empty($shipment['bl_number']) ? htmlentities($shipment['bl_number']) : '<span class="no-val">—</span>'; ?></span>
        </div>
        <div class="ship-info-row">
            <span class="ship-info-label"><i class="fas fa-box"></i> Container No.</span>
            <span class="ship-info-value" style="font-weight:600;"><?php echo !empty($shipment['container_no']) ? htmlentities($shipment['container_no']) : '<span class="no-val">—</span>'; ?></span>
        </div>
        <div class="ship-info-row">
            <span class="ship-info-label"><i class="fas fa-calendar-check"></i> On Board Date</span>
            <span class="ship-info-value">
                <?php echo !empty($shipment['on_board_date']) ? '<span class="date-highlight"><i class="fas fa-anchor"></i>'.fmtDateShort($shipment['on_board_date']).'</span>' : '<span class="no-val">—</span>'; ?>
            </span>
        </div>
    </div>

    <div class="ship-section-head"><i class="fas fa-route"></i> Routing &amp; Delivery</div>
    <div class="ship-info-grid">
        <div class="ship-info-row">
            <span class="ship-info-label"><i class="fas fa-map-marker-alt"></i> Port of Discharge</span>
            <span class="ship-info-value"><?php echo !empty($shipment['port_of_discharge']) ? htmlentities($shipment['port_of_discharge']) : '<span class="no-val">—</span>'; ?></span>
        </div>
        <div class="ship-info-row">
            <span class="ship-info-label"><i class="fas fa-flag-checkered"></i> Final Destination</span>
            <span class="ship-info-value"><?php echo !empty($shipment['final_destination']) ? htmlentities($shipment['final_destination']) : '<span class="no-val">—</span>'; ?></span>
        </div>
        <div class="ship-info-row">
            <span class="ship-info-label"><i class="fas fa-clock"></i> ETA</span>
            <span class="ship-info-value">
                <?php if (!empty($shipment['eta'])):
                    $etaClass = ($shipment['eta'] < $today) ? '' : (($shipment['eta'] <= date('Y-m-d', strtotime('+7 days'))) ? 'date-soon' : '');
                ?>
                <span class="date-highlight <?php echo $etaClass; ?>">
                    <i class="fas fa-calendar-alt"></i> <?php echo fmtDateShort($shipment['eta']); ?>
                    <?php if ($shipment['eta'] < $today): ?><span style="font-size:.72rem;color:var(--success);font-weight:600;margin-left:.3rem;"><i class="fas fa-check-circle"></i> Arrived</span><?php endif; ?>
                    <?php if ($shipment['eta'] > $today && $shipment['eta'] <= date('Y-m-d', strtotime('+7 days'))): ?><span style="font-size:.72rem;color:var(--warning);font-weight:600;margin-left:.3rem;"><i class="fas fa-exclamation-triangle"></i> Soon</span><?php endif; ?>
                </span>
                <?php else: ?><span class="no-val">—</span><?php endif; ?>
            </span>
        </div>
        <div class="ship-info-row">
            <span class="ship-info-label"><i class="fas fa-calendar-times"></i> DDP Expected Date</span>
            <span class="ship-info-value">
                <?php if (!empty($shipment['ddp_expected_date'])):
                    $ddpClass = ($shipment['ddp_expected_date'] < $today) ? 'date-warn' : '';
                ?>
                <span class="date-highlight <?php echo $ddpClass; ?>">
                    <i class="fas fa-calendar-alt"></i> <?php echo fmtDateShort($shipment['ddp_expected_date']); ?>
                    <?php if ($shipment['ddp_expected_date'] < $today): ?><span style="font-size:.72rem;color:var(--error);font-weight:600;margin-left:.3rem;"><i class="fas fa-exclamation-circle"></i> Overdue</span><?php endif; ?>
                </span>
                <?php else: ?><span class="no-val">—</span><?php endif; ?>
            </span>
        </div>
    </div>

    <div class="ship-section-head"><i class="fas fa-folder-open"></i> Document Status</div>
    <div class="docs-status-grid">
        <div class="doc-status-item">
            <span class="doc-status-name"><i class="fas fa-shield-alt"></i> Insurance Certificate</span>
            <?php echo shipmentStatusBadge($shipment['insurance_cert_status']); ?>
        </div>
        <div class="doc-status-item">
            <span class="doc-status-name"><i class="fas fa-globe"></i> CO / Origin Certificate</span>
            <?php echo shipmentStatusBadge($shipment['co_origin_status']); ?>
        </div>
        <div class="doc-status-item">
            <span class="doc-status-name"><i class="fas fa-copy"></i> Copy Docs Informed</span>
            <?php echo !empty($shipment['copy_docs_inform_date'])
                ? '<span class="ship-badge ship-badge-done"><i class="fas fa-check-circle"></i> '.fmtDateShort($shipment['copy_docs_inform_date']).'</span>'
                : '<span class="ship-badge ship-badge-pending"><i class="fas fa-hourglass-half"></i> Pending</span>'; ?>
        </div>
        <div class="doc-status-item">
            <span class="doc-status-name"><i class="fas fa-file-alt"></i> Final Docs Informed</span>
            <?php echo !empty($shipment['final_docs_inform_date'])
                ? '<span class="ship-badge ship-badge-done"><i class="fas fa-check-circle"></i> '.fmtDateShort($shipment['final_docs_inform_date']).'</span>'
                : '<span class="ship-badge ship-badge-pending"><i class="fas fa-hourglass-half"></i> Pending</span>'; ?>
        </div>
        <div class="doc-status-item">
            <span class="doc-status-name"><i class="fas fa-paper-plane"></i> Original Docs Dispatched</span>
            <?php if (!empty($shipment['original_docs_dispatch'])): ?>
                <span class="ship-badge ship-badge-done"><i class="fas fa-check-circle"></i> <?php echo htmlentities($shipment['original_docs_dispatch']); ?></span>
            <?php else: ?>
                <span class="ship-badge ship-badge-pending"><i class="fas fa-hourglass-half"></i> Pending</span>
            <?php endif; ?>
        </div>
        <?php if (!empty($shipment['awb_no_date'])): ?>
        <div class="doc-status-item">
            <span class="doc-status-name"><i class="fas fa-plane"></i> AWB No. / Date</span>
            <span style="font-size:.85rem;font-weight:600;color:var(--dark-gray);"><?php echo htmlentities($shipment['awb_no_date']); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="ship-section-head"><i class="fas fa-credit-card"></i> Payment</div>
    <div class="ship-info-grid">
        <div class="ship-info-row">
            <span class="ship-info-label"><i class="fas fa-calendar-alt"></i> Payment Due Date</span>
            <span class="ship-info-value">
                <?php if (!empty($shipment['payment_due_date'])):
                    $pdClass = ($shipment['payment_due_date'] < $today && strtolower($shipment['payment_status']) !== 'paid') ? 'date-warn' : '';
                ?>
                <span class="date-highlight <?php echo $pdClass; ?>">
                    <i class="fas fa-calendar-alt"></i> <?php echo fmtDateShort($shipment['payment_due_date']); ?>
                    <?php if ($shipment['payment_due_date'] < $today && strtolower($shipment['payment_status']) !== 'paid'): ?>
                    <span style="font-size:.72rem;color:var(--error);font-weight:600;margin-left:.3rem;"><i class="fas fa-exclamation-circle"></i> Overdue</span>
                    <?php endif; ?>
                </span>
                <?php else: ?><span class="no-val">—</span><?php endif; ?>
            </span>
        </div>
        <div class="ship-info-row">
            <span class="ship-info-label"><i class="fas fa-check-circle"></i> Payment Status</span>
            <span class="ship-info-value"><?php echo shipmentStatusBadge($shipment['payment_status'], 'payment'); ?></span>
        </div>
        <div class="ship-info-row">
            <span class="ship-info-label"><i class="fas fa-clock"></i> Record Created</span>
            <span class="ship-info-value" style="font-size:.82rem;color:var(--text-gray);"><?php echo fmtDate($shipment['created_at']); ?></span>
        </div>
        <div class="ship-info-row">
            <span class="ship-info-label"><i class="fas fa-sync"></i> Last Updated</span>
            <span class="ship-info-value" style="font-size:.82rem;color:var(--text-gray);"><?php echo fmtDate($shipment['updated_at']); ?></span>
        </div>
    </div>

    <?php else: ?>
    <div class="no-shipment-box">
        <i class="fas fa-ship"></i>
        <p>No shipment record has been created for this order yet.</p>
    </div>
    <?php endif; ?>
</div><!-- /logistics card -->

<!-- ══ ITEMS — plain (no revisions at all) ════════════════════════════════════ -->
<?php if (!$isRevision && empty($revisedOrderId)): ?>
<div class="card animate-in">
    <div class="card-header"><h2 class="card-title"><i class="fas fa-list"></i> Order Items</h2></div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table class="table">
                <thead><tr>
                    <th>#</th><th>Item Code</th><th>Description</th>
                    <th>Qty</th><th>Unit Price</th><th>Discount</th>
                    <th>Unit Wt.</th><th>Total Wt.</th>
                    <th>Unit CBM</th><th>Total CBM</th>
                    <th>Payment Amt</th><th>Total Payment</th>
                </tr></thead>
                <tbody>
                <?php if (!empty($items)): $cnt=1; foreach ($items as $item): ?>
                <tr>
                    <td><?php echo $cnt++; ?></td>
                    <td><strong><?php echo htmlentities($item['icode']); ?></strong></td>
                    <td class="desc-cell"><?php echo !empty($item['description']) ? htmlentities($item['description']) : '<span class="no-val">—</span>'; ?></td>
                    <td><?php echo fmtNum($item['quantity_numeric']); ?></td>
                    <td><?php echo $item['unit_price_numeric'] ? fmtMoney($item['unit_price_numeric']) : '—'; ?></td>
                    <td><?php echo !empty($item['discount']) ? htmlentities($item['discount']).'%' : '—'; ?></td>
                    <td><?php echo fmtKg($item['unit_weight']); ?></td>
                    <td><?php echo fmtKg($item['total_weight']); ?></td>
                    <td><?php echo !empty($item['unit_cbm']) ? fmtNum($item['unit_cbm'],4).' m³' : '—'; ?></td>
                    <td><?php echo !empty($item['total_cbm']) ? fmtNum($item['total_cbm'],4).' m³' : '—'; ?></td>
                    <td><?php echo !empty($item['payment_amount']) ? fmtMoney($item['payment_amount']) : '—'; ?></td>
                    <td style="font-weight:700;"><?php echo !empty($item['total_payment']) ? fmtMoney($item['total_payment']) : '—'; ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="12"><div class="empty-state"><div class="empty-icon"><i class="fas fa-inbox"></i></div><p>No items found.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($items)): ?>
        <div style="padding:1.25rem 1.75rem;">
            <div class="totals-row">
                <div class="total-item"><span class="total-label">Items</span><span class="total-value"><?php echo count($items); ?></span></div>
                <div class="total-item"><span class="total-label">Quantity</span><span class="total-value"><?php echo fmtNum(array_sum(array_column($items,'quantity_numeric'))); ?> pcs</span></div>
                <div class="total-item"><span class="total-label">Weight</span><span class="total-value"><?php echo fmtKg(array_sum(array_column($items,'total_weight'))); ?></span></div>
                <div class="total-item"><span class="total-label">Order Value</span><span class="total-value highlight"><?php echo fmtMoney($totalOrderValue); ?></span></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════════════════
     REVISION HISTORY
══════════════════════════════════════════════════════════════════════════════ -->
<?php if (count($allRevisions) > 1): ?>

<div class="card animate-in">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-history"></i> Revision History</h2>
        <span style="font-size:.8rem;color:var(--text-gray);">
            <?php echo count($allRevisions); ?> version<?php echo count($allRevisions)>1?'s':''; ?> total
        </span>
    </div>
    <div class="timeline">
        <?php foreach ($allRevisions as $idx => $rev):
            $isCurrent = ($rev['order_id'] == $orderId);
            $dotClass  = ($rev['is_original'] ? 'is-original' : 'is-revision') . ($isCurrent ? ' is-current' : '');
            $contClass = $isCurrent ? ($rev['is_original'] ? 'is-current' : 'is-revision-current') : '';
            $dotLabel  = $rev['is_original'] ? 'O' : 'R'.$rev['version'];
            $pItems    = $rev['items'];
            $pQty      = array_sum(array_column($pItems,'quantity_numeric'));
        ?>
        <div class="timeline-item">
            <div class="timeline-dot <?php echo $dotClass; ?>"><?php echo $dotLabel; ?></div>
            <div class="timeline-content <?php echo $contClass; ?>" onclick="switchRevTab(<?php echo $idx; ?>)" title="View details for this version">
                <div class="timeline-top">
                    <span class="timeline-oid">
                        <?php if (!$isCurrent): ?>
                            <a href="?oid=<?php echo urlencode($rev['order_id']); ?>" onclick="event.stopPropagation();">#<?php echo htmlentities($rev['order_id']); ?></a>
                        <?php else: ?>
                            #<?php echo htmlentities($rev['order_id']); ?>
                        <?php endif; ?>
                    </span>
                    <?php if ($rev['is_original']): ?>
                        <span class="timeline-ver-tag orig">Original</span>
                    <?php else: ?>
                        <span class="timeline-ver-tag rev">Revision v<?php echo $rev['version']; ?></span>
                    <?php endif; ?>
                    <?php if ($isCurrent): ?><span class="timeline-current-tag"><i class="fas fa-eye" style="font-size:.6rem;"></i> Viewing</span><?php endif; ?>
                    <?php echo statusBadge($rev['order']['status']); ?>
                    <span class="timeline-date"><?php echo fmtDateShort($rev['order']['order_date']); ?></span>
                </div>
                <div class="timeline-meta">
                    <span><strong><?php echo (int)$rev['order']['total_items']; ?></strong> items</span>
                    <span><strong><?php echo fmtNum($pQty); ?></strong> pcs</span>
                    <span><strong><?php echo fmtKg($rev['order']['total_weight']); ?></strong></span>
                    <?php if (!empty($rev['order']['total_payment'])): ?>
                    <span>Payment: <strong><?php echo fmtMoney($rev['order']['total_payment']); ?></strong></span>
                    <?php endif; ?>
                    <?php if (!empty($rev['order']['invoice_no'])): ?>
                    <span>Invoice: <strong><?php echo htmlentities($rev['order']['invoice_no']); ?></strong></span>
                    <?php endif; ?>
                    <?php if ($idx > 0):
                        $prev    = $allRevisions[$idx-1];
                        $pMap    = []; foreach ($prev['items'] as $pi) { $pMap[$pi['icode']] = $pi; }
                        $cMap    = []; foreach ($pItems as $ci) { $cMap[$ci['icode']] = $ci; }
                        $added   = count(array_filter($pItems, fn($ci) => !isset($pMap[$ci['icode']])));
                        $removed = count(array_filter($prev['items'], fn($pi) => !isset($cMap[$pi['icode']])));
                        $changed = count(array_filter($pItems, fn($ci) => isset($pMap[$ci['icode']]) && $pMap[$ci['icode']]['quantity_numeric'] != $ci['quantity_numeric']));
                    ?>
                    <?php if ($added):   ?><span style="color:var(--success);font-weight:600;font-size:.76rem;"><i class="fas fa-plus-circle"></i> <?php echo $added; ?> added</span><?php endif; ?>
                    <?php if ($removed): ?><span style="color:var(--error);font-weight:600;font-size:.76rem;"><i class="fas fa-minus-circle"></i> <?php echo $removed; ?> removed</span><?php endif; ?>
                    <?php if ($changed): ?><span style="color:var(--primary-orange);font-weight:600;font-size:.76rem;"><i class="fas fa-edit"></i> <?php echo $changed; ?> changed</span><?php endif; ?>
                    <?php if (!$added && !$removed && !$changed): ?>
                    <span style="color:var(--text-gray);font-size:.76rem;">No item changes</span>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="card animate-in">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-layer-group"></i> Version Details &amp; Comparison</h2>
        <span style="font-size:.8rem;color:var(--text-gray);">Click a timeline entry or tab to switch</span>
    </div>

    <div class="rev-tabs-wrap">
        <div class="rev-tabs" id="revTabBar">
            <?php foreach ($allRevisions as $idx => $rev):
                $isActive = ($idx === $activeTabIndex);
                $label    = $rev['is_original'] ? 'Original #'.$rev['order_id'] : 'Rev v'.$rev['version'].' #'.$rev['order_id'];
                $cls      = 'rev-tab' . ($isActive ? ' active' : '') . (!$rev['is_original'] ? ' is-revision-tab' : '') . ($isActive && !$rev['is_original'] ? ' is-rev' : '');
            ?>
            <button class="<?php echo $cls; ?>" onclick="switchRevTab(<?php echo $idx; ?>)" id="revTab<?php echo $idx; ?>">
                <?php if ($rev['order_id'] == $orderId): ?><i class="fas fa-eye" style="font-size:.68rem;opacity:.7;margin-right:.18rem;"></i><?php endif; ?>
                <?php echo htmlentities($label); ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>

    <?php foreach ($allRevisions as $idx => $rev):
        $isActive   = ($idx === $activeTabIndex);
        $panelItems = $rev['items'];
        $panelOrder = $rev['order'];
        $panelTotalQty = array_sum(array_column($panelItems,'quantity_numeric'));
        $panelTotalVal = array_sum(array_column($panelItems,'total_price'));

        $prevItems   = ($idx > 0) ? $allRevisions[$idx-1]['items'] : [];
        $prevItemMap = []; foreach ($prevItems as $pi) { $prevItemMap[$pi['icode']] = $pi; }
        $curItemMap  = []; foreach ($panelItems as $ci) { $curItemMap[$ci['icode']] = $ci; }
    ?>
    <div class="rev-panel <?php echo $isActive ? 'active' : ''; ?>" id="revPanel<?php echo $idx; ?>">

        <?php if ($idx === 0): ?>
        <div style="padding:1.25rem 1.75rem 0;">
            <div class="legend">
                <i class="fas fa-info-circle" style="color:var(--text-gray);flex-shrink:0;"></i>
                <span style="font-size:.82rem;color:var(--text-gray);">
                    This is the original order. Switch to a revision tab to see what changed in each version.
                </span>
            </div>
        </div>

        <?php else: ?>
        <div style="padding:1.25rem 1.75rem 0;">
            <div class="legend" style="margin-bottom:1rem;">
                <span class="legend-item"><span class="legend-dot" style="background:var(--success)"></span> Added</span>
                <span class="legend-item"><span class="legend-dot" style="background:var(--error)"></span> Removed</span>
                <span class="legend-item"><span class="legend-dot" style="background:var(--primary-orange)"></span> Qty changed</span>
                <span class="legend-item"><span class="legend-dot" style="background:#e8eaed;border:1px solid #ccc;"></span> Unchanged</span>
                <span style="margin-left:auto;font-size:.77rem;color:var(--text-gray);">
                    Comparing <strong>v<?php echo $rev['version']; ?></strong>
                    vs <strong><?php echo $idx === 1 ? 'Original' : 'v'.($rev['version']-1); ?></strong>
                </span>
            </div>

            <div class="comparison-grid" style="margin-bottom:1.25rem;">
                <div class="comparison-panel cmp-prev">
                    <div class="comparison-panel-header">
                        <i class="fas fa-file-alt"></i>
                        <?php echo $idx === 1 ? 'Original' : 'Revision v'.($rev['version']-1); ?>
                        <span class="c-badge">#<?php echo htmlentities($allRevisions[$idx-1]['order_id']); ?></span>
                    </div>
                    <dl class="panel-meta">
                        <dt>Date</dt><dd><?php echo fmtDateShort($allRevisions[$idx-1]['order']['order_date']); ?></dd>
                        <dt>Items / Qty</dt><dd><?php echo (int)$allRevisions[$idx-1]['order']['total_items']; ?> items / <?php echo fmtNum(array_sum(array_column($prevItems,'quantity_numeric'))); ?> pcs</dd>
                        <dt>Weight</dt><dd><?php echo fmtKg($allRevisions[$idx-1]['order']['total_weight']); ?></dd>
                        <dt>Payment</dt><dd style="font-weight:700;color:var(--primary-orange);"><?php echo !empty($allRevisions[$idx-1]['order']['total_payment']) ? fmtMoney($allRevisions[$idx-1]['order']['total_payment']) : '—'; ?></dd>
                    </dl>
                    <div style="padding:.75rem 0 .5rem;">
                        <div class="section-label"><i class="fas fa-list"></i> Items</div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead><tr><th>#</th><th>Code</th><th>Description</th><th>Qty</th><th>Unit Price</th><th>Weight</th></tr></thead>
                                <tbody>
                                <?php if (!empty($prevItems)): $cnt=1; foreach ($prevItems as $pi):
                                    $rc = '';
                                    if (!isset($curItemMap[$pi['icode']])) $rc = 'row-removed';
                                    elseif ($curItemMap[$pi['icode']]['quantity_numeric'] != $pi['quantity_numeric']) $rc = 'row-changed';
                                ?>
                                <tr class="<?php echo $rc; ?>">
                                    <td><?php echo $cnt++; ?></td>
                                    <td><strong><?php echo htmlentities($pi['icode']); ?></strong>
                                        <?php if ($rc==='row-removed'): ?><span class="change-pill pill-removed">Removed</span>
                                        <?php elseif ($rc==='row-changed'): ?><span class="change-pill pill-changed">Changed</span><?php endif; ?>
                                    </td>
                                    <td class="desc-cell"><?php echo !empty($pi['description']) ? htmlentities($pi['description']) : '<span class="no-val">—</span>'; ?></td>
                                    <td><?php echo fmtNum($pi['quantity_numeric']); ?></td>
                                    <td><?php echo $pi['unit_price_numeric'] ? fmtMoney($pi['unit_price_numeric']) : '—'; ?></td>
                                    <td><?php echo fmtKg($pi['total_weight']); ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="6"><div class="empty-state"><i class="fas fa-inbox empty-icon"></i><p>No items</p></div></td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="comparison-panel cmp-cur">
                    <div class="comparison-panel-header">
                        <i class="fas fa-sync-alt"></i>
                        Revision v<?php echo $rev['version']; ?>
                        <span class="c-badge">v<?php echo $rev['version']; ?></span>
                    </div>
                    <dl class="panel-meta">
                        <dt>Date</dt><dd><?php echo fmtDateShort($panelOrder['order_date']); ?></dd>
                        <dt>Items / Qty</dt><dd><?php echo (int)$panelOrder['total_items']; ?> items / <?php echo fmtNum($panelTotalQty); ?> pcs</dd>
                        <dt>Weight</dt><dd><?php echo fmtKg($panelOrder['total_weight']); ?></dd>
                        <dt>Payment</dt><dd style="font-weight:700;color:var(--primary-orange);"><?php echo !empty($panelOrder['total_payment']) ? fmtMoney($panelOrder['total_payment']) : '—'; ?></dd>
                    </dl>
                    <div style="padding:.75rem 0 .5rem;">
                        <div class="section-label"><i class="fas fa-list"></i> Items</div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead><tr><th>#</th><th>Code</th><th>Description</th><th>Qty</th><th>Unit Price</th><th>Weight</th></tr></thead>
                                <tbody>
                                <?php if (!empty($panelItems)): $cnt=1; foreach ($panelItems as $ci):
                                    $rc = '';
                                    if (!isset($prevItemMap[$ci['icode']])) $rc = 'row-added';
                                    elseif ($prevItemMap[$ci['icode']]['quantity_numeric'] != $ci['quantity_numeric']) $rc = 'row-changed';
                                ?>
                                <tr class="<?php echo $rc; ?>">
                                    <td><?php echo $cnt++; ?></td>
                                    <td><strong><?php echo htmlentities($ci['icode']); ?></strong>
                                        <?php if ($rc==='row-added'): ?><span class="change-pill pill-added">New</span>
                                        <?php elseif ($rc==='row-changed'): ?>
                                            <span class="change-pill pill-changed"><?php echo fmtNum($prevItemMap[$ci['icode']]['quantity_numeric']); ?> &rarr; <?php echo fmtNum($ci['quantity_numeric']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="desc-cell"><?php echo !empty($ci['description']) ? htmlentities($ci['description']) : '<span class="no-val">—</span>'; ?></td>
                                    <td><?php echo fmtNum($ci['quantity_numeric']); ?></td>
                                    <td><?php echo $ci['unit_price_numeric'] ? fmtMoney($ci['unit_price_numeric']) : '—'; ?></td>
                                    <td><?php echo fmtKg($ci['total_weight']); ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="6"><div class="empty-state"><i class="fas fa-inbox empty-icon"></i><p>No items</p></div></td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div style="padding:0 1.75rem 1.5rem;">
            <div class="info-label" style="margin-bottom:.55rem;padding-top:.25rem;">
                <i class="fas fa-th-list" style="color:var(--primary-orange);margin-right:.3rem;"></i>
                <?php echo $rev['is_original'] ? 'Full Item List — Original Order' : 'Full Item List — Revision v'.$rev['version']; ?>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr>
                        <th>#</th><th>Item Code</th><th>Description</th>
                        <th>Qty</th><th>Unit Price</th><th>Discount</th>
                        <th>Unit Wt.</th><th>Total Wt.</th>
                        <th>Unit CBM</th><th>Total CBM</th>
                        <th>Payment Amt</th><th>Total Payment</th>
                    </tr></thead>
                    <tbody>
                    <?php if (!empty($panelItems)): $cnt=1; foreach ($panelItems as $item):
                        $rc = '';
                        if ($idx > 0) {
                            if (!isset($prevItemMap[$item['icode']])) $rc = 'row-added';
                            elseif ($prevItemMap[$item['icode']]['quantity_numeric'] != $item['quantity_numeric']) $rc = 'row-changed';
                        }
                    ?>
                    <tr class="<?php echo $rc; ?>">
                        <td><?php echo $cnt++; ?></td>
                        <td><strong><?php echo htmlentities($item['icode']); ?></strong>
                            <?php if ($rc==='row-added'): ?><span class="change-pill pill-added">New</span>
                            <?php elseif ($rc==='row-changed'): ?>
                                <span class="change-pill pill-changed"><?php echo fmtNum($prevItemMap[$item['icode']]['quantity_numeric']); ?> &rarr; <?php echo fmtNum($item['quantity_numeric']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="desc-cell"><?php echo !empty($item['description']) ? htmlentities($item['description']) : '<span class="no-val">—</span>'; ?></td>
                        <td><?php echo fmtNum($item['quantity_numeric']); ?></td>
                        <td><?php echo $item['unit_price_numeric'] ? fmtMoney($item['unit_price_numeric']) : '—'; ?></td>
                        <td><?php echo !empty($item['discount']) ? htmlentities($item['discount']).'%' : '—'; ?></td>
                        <td><?php echo fmtKg($item['unit_weight']); ?></td>
                        <td><?php echo fmtKg($item['total_weight']); ?></td>
                        <td><?php echo !empty($item['unit_cbm']) ? fmtNum($item['unit_cbm'],4).' m³' : '—'; ?></td>
                        <td><?php echo !empty($item['total_cbm']) ? fmtNum($item['total_cbm'],4).' m³' : '—'; ?></td>
                        <td><?php echo !empty($item['payment_amount']) ? fmtMoney($item['payment_amount']) : '—'; ?></td>
                        <td style="font-weight:700;"><?php echo !empty($item['total_payment']) ? fmtMoney($item['total_payment']) : '—'; ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="12"><div class="empty-state"><div class="empty-icon"><i class="fas fa-inbox"></i></div><p>No items found.</p></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($panelItems)): ?>
            <div style="margin-top:1rem;">
                <div class="totals-row">
                    <div class="total-item"><span class="total-label">Items</span><span class="total-value"><?php echo count($panelItems); ?></span></div>
                    <div class="total-item"><span class="total-label">Quantity</span><span class="total-value"><?php echo fmtNum($panelTotalQty); ?> pcs</span></div>
                    <div class="total-item"><span class="total-label">Weight</span><span class="total-value"><?php echo fmtKg($panelOrder['total_weight']); ?></span></div>
                    <div class="total-item"><span class="total-label">Order Value</span><span class="total-value highlight"><?php echo fmtMoney($panelTotalVal); ?></span></div>
                    <?php if (!empty($panelOrder['total_payment'])): ?>
                    <div class="total-item"><span class="total-label">Total Payment</span><span class="total-value highlight"><?php echo fmtMoney($panelOrder['total_payment']); ?></span></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div>
    <?php endforeach; ?>

</div>
<?php endif; ?>

</div><!-- /container -->

<!-- ══ Delete Modal ══════════════════════════════════════════════════════════ -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fas fa-exclamation-triangle"></i>
            <h3 class="modal-title">Confirm Delete</h3>
        </div>
        <div class="modal-body">
            <p style="margin-bottom:1rem;font-size:1rem;">
                Are you sure you want to delete order <strong>#<?php echo htmlentities($orderId); ?></strong>?
            </p>
            <p style="color:var(--error);font-weight:600;margin-bottom:.5rem;">
                <i class="fas fa-info-circle"></i> This action cannot be undone.
            </p>
            <p style="color:var(--text-gray);font-size:.9rem;">The following data will be permanently deleted:</p>
            <ul style="margin:.5rem 0 0 1.5rem;color:var(--text-gray);font-size:.9rem;">
                <li>Order information from tire_orders</li>
                <li>All order items from tire_order_items</li>
                <li>Order summaries from order_summaries</li>
            </ul>
        </div>
        <div class="modal-footer">
            <button onclick="hideDeleteModal()" class="btn btn-cancel"><i class="fas fa-times"></i> Cancel</button>
            <a href="?oid=<?php echo urlencode($orderId); ?>&delete=true" class="btn btn-danger"><i class="fas fa-trash-alt"></i> Yes, Delete Order</a>
        </div>
    </div>
</div>

<script>
    const observer = new IntersectionObserver(entries => {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('animate-in'); });
    }, { threshold: 0.05 });
    document.querySelectorAll('.card').forEach(el => observer.observe(el));

    const totalTabs = <?php echo count($allRevisions); ?>;

    function switchRevTab(idx) {
        for (let i = 0; i < totalTabs; i++) {
            const tab   = document.getElementById('revTab' + i);
            const panel = document.getElementById('revPanel' + i);
            if (!tab || !panel) continue;
            const active = (i === idx);
            tab.classList.toggle('active', active);
            const isRevTab = tab.classList.contains('is-revision-tab');
            tab.classList.toggle('is-rev', active && isRevTab);
            panel.classList.toggle('active', active);
        }
        const card = document.getElementById('revPanel' + idx)?.closest('.card');
        if (card) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function showDeleteModal() { document.getElementById('deleteModal').classList.add('show'); document.body.style.overflow='hidden'; }
    function hideDeleteModal() { document.getElementById('deleteModal').classList.remove('show'); document.body.style.overflow=''; }
    document.getElementById('deleteModal').addEventListener('click', function(e) { if (e.target===this) hideDeleteModal(); });
    document.addEventListener('keydown', e => { if (e.key==='Escape') hideDeleteModal(); });
</script>
</body>
</html>