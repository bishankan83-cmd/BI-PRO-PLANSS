<?php
session_start();

define('DB_SERVER', 'localhost');
define('DB_USER', 'planatir_task_managemen');
define('DB_PASS', 'Bishan@1919');
define('DB_NAME', 'planatir_cms');

if (!isset($_SESSION['id']) || strlen($_SESSION['id']) == 0) {
    header('location:index2.php');
    exit;
}

$inventory   = [];
$message     = '';
$messageType = 'info';
$userId      = $_SESSION['id'];
$paymentRate = 0;
define('CONTAINER_20FT_CAPACITY', 18000);
define('CONTAINER_40FT_CAPACITY', 25000);

/* ── Currency symbol helper ───────────────────────────────────────────────── */
function getCurrencySymbol(string $code): string {
    $map = [
        'USD'=>'$',  'EUR'=>'€',    'GBP'=>'£',    'JPY'=>'¥',
        'AUD'=>'A$', 'CAD'=>'C$',   'CHF'=>'CHF ',  'CNY'=>'¥',
        'LKR'=>'Rs ','INR'=>'₹',   'SGD'=>'S$',    'AED'=>'AED ',
        'SAR'=>'SAR ','MYR'=>'RM ', 'THB'=>'฿',    'HKD'=>'HK$',
        'NZD'=>'NZ$','NOK'=>'NOK ','SEK'=>'SEK ',  'DKK'=>'DKK ',
        'ZAR'=>'R',  'BRL'=>'R$',   'MXN'=>'MX$',  'PHP'=>'₱',
        'IDR'=>'Rp ','VND'=>'₫',   'KRW'=>'₩',    'TRY'=>'₺',
        'PKR'=>'Rs ','BDT'=>'৳',
    ];
    return $map[strtoupper(trim($code))] ?? (trim($code) . ' ');
}

$pdo = null;
try {
    $pdo = new PDO(
        "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false]
    );
} catch (PDOException $e) {
    $message     = "Database connection failed: " . $e->getMessage();
    $messageType = 'error';
}

/* ── AJAX ────────────────────────────────────────────────────────────────── */
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    try {
        /* -- Resolve customer ------------------------------------------------ */
        $ajaxUserId = $_SESSION['id'];
        $ajaxCusRow = $pdo->prepare("SELECT cus_id FROM users WHERE id=?");
        $ajaxCusRow->execute([$ajaxUserId]);
        $ajaxCusData = $ajaxCusRow->fetch();
        $ajaxCusId   = $ajaxCusData['cus_id'] ?? $ajaxUserId;

        /* -- Allowed brands for this customer -------------------------------- */
        $ajaxAllowedBrands      = [];
        $ajaxHasBrandRestriction = false;
        if (!empty($ajaxCusId)) {
            $ajaxBrandStmt = $pdo->prepare(
                "SELECT DISTINCT brand FROM customers_brand WHERE cus_id = ?"
            );
            $ajaxBrandStmt->execute([$ajaxCusId]);
            $ajaxBrandRows = $ajaxBrandStmt->fetchAll();
            if (!empty($ajaxBrandRows)) {
                $ajaxAllowedBrands       = array_column($ajaxBrandRows, 'brand');
                $ajaxHasBrandRestriction = true;
            }
        }

        $whereConditions = [];
        $params          = [];

        // 1. Brand restriction
        if ($ajaxHasBrandRestriction && !empty($ajaxAllowedBrands)) {
            $phs               = implode(',', array_fill(0, count($ajaxAllowedBrands), '?'));
            $whereConditions[] = "t.brand IN ($phs)";
            foreach ($ajaxAllowedBrands as $b) $params[] = $b;
        }

        // 2. Weight guard
        $hasActiveFilters = !empty($_GET['icode_select']) || !empty($_GET['tire_size_select']) ||
                            !empty($_GET['brand_select']) ||
                            (!empty($_GET['col_select'])  && $_GET['col_select']  !== 'all') ||
                            (!empty($_GET['rim_select'])  && $_GET['rim_select']  !== 'all');
        if (!$hasActiveFilters) {
            $whereConditions[] = "(t.fweight IS NOT NULL AND t.fweight > 0)";
        }

        // 3. UI filter fields
        $filterMap = ['icode_select'=>'r.icode','tire_size_select'=>'t.tire_size','brand_select'=>'t.brand','col_select'=>'r.col','rim_select'=>'r.rim'];
        foreach ($filterMap as $key => $col) {
            if (!empty($_GET[$key]) && $_GET[$key] !== 'all') {
                $whereConditions[] = "$col LIKE ?";
                $params[] = '%' . $_GET[$key] . '%';
            }
        }

        $sortOptions = ['code_asc'=>'r.icode ASC','code_desc'=>'r.icode DESC','brand_asc'=>'t.brand ASC','brand_desc'=>'t.brand DESC','size_asc'=>'r.t_size ASC','size_desc'=>'r.t_size DESC','tire_size_asc'=>'t.tire_size ASC','tire_size_desc'=>'t.tire_size DESC','weight_asc'=>'t.fweight ASC','weight_desc'=>'t.fweight DESC'];
        $sortBy = (isset($_GET['sort']) && isset($sortOptions[$_GET['sort']])) ? $sortOptions[$_GET['sort']] : 't.brand ASC, r.t_size ASC';

        $sql  = "SELECT r.id, r.icode, r.t_size, r.brand, r.col, r.rim, t.fweight, t.tire_size, t.cbm, t.brand as tire_brand
                 FROM realstock r LEFT JOIN tire_details t ON r.icode = t.icode"
              . (empty($whereConditions) ? '' : ' WHERE ' . implode(' AND ', $whereConditions))
              . " ORDER BY {$sortBy}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $inventory = $stmt->fetchAll();

        /* -- Customer item prices ------------------------------------------- */
        $ajaxItemPrices      = [];
        $ajaxCusHasItemPrices = false;
        if (!empty($ajaxCusId)) {
            $stmt2 = $pdo->prepare("SELECT icode, price FROM customer_items WHERE cus_id=?");
            $stmt2->execute([$ajaxCusId]);
            $rows2 = $stmt2->fetchAll();
            $ajaxCusHasItemPrices = count($rows2) > 0;
            foreach ($rows2 as $ci) {
                $ajaxItemPrices[strtolower(trim($ci['icode']))] = (float)$ci['price'];
            }
        }

        foreach ($inventory as &$row) {
            $ik = strtolower(trim($row['icode'] ?? ''));
            $row['customer_price'] = isset($ajaxItemPrices[$ik]) ? $ajaxItemPrices[$ik] : null;
        }
        unset($row);

        echo json_encode([
            'success'          => true,
            'data'             => $inventory,
            'count'            => count($inventory),
            'cusHasItemPrices' => $ajaxCusHasItemPrices,
        ]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

/* ── ORDER PLACEMENT ─────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    $orderItems = json_decode($_POST['order_data'] ?? '', true);
    if (!empty($orderItems)) {
        try {
            $pdo->beginTransaction();
            $newOrderId           = '';
            $originalOidFromParam = trim($_POST['original_oid'] ?? '');
            if (!empty($originalOidFromParam)) {
                if (preg_match('/^(\d+R)(\d+)$/', $originalOidFromParam, $m))      $newOrderId = $m[1] . ((int)$m[2] + 1);
                elseif (preg_match('/^(\d+)$/', $originalOidFromParam, $m))        $newOrderId = $m[1] . 'R1';
                if (!empty($newOrderId)) {
                    try { $pdo->prepare("UPDATE tire_orders SET status='revised' WHERE order_id=?")->execute([$originalOidFromParam]); }
                    catch (PDOException $e) { error_log("Failed to update original order: " . $e->getMessage()); }
                }
            }
            if (empty($newOrderId)) {
                $last = $pdo->query("SELECT order_id FROM tire_orders ORDER BY id DESC LIMIT 1")->fetch();
                $newOrderId = ($last && preg_match('/^(\d+R)(\d+)$/', $last['order_id'], $m)) ? $m[1] . ((int)$m[2]+1) : '1R1';
            }
            $totalQuantity = array_sum(array_column($orderItems, 'quantity'));
            $totalWeight   = array_sum(array_map(fn($i) => $i['quantity'] * $i['fweight'], $orderItems));
            $totalCBM      = array_sum(array_map(fn($i) => $i['quantity'] * ($i['cbm'] ?? 0), $orderItems));
            $totalPayment  = $totalWeight * $paymentRate;
            $orderNotes    = "Order placed via web at " . date('Y-m-d H:i:s') . " | Rate: $" . number_format($paymentRate, 2);
            if (!empty($originalOidFromParam)) $orderNotes .= " | Revised from: " . $originalOidFromParam;
            $pdo->prepare("INSERT INTO tire_orders (order_id,customer_id,status,total_items,total_quantity,total_weight,total_cbm,total_payment,order_notes,created_at) VALUES (?,?,'pending',?,?,?,?,?,?,NOW())")
                ->execute([$newOrderId, $userId, count($orderItems), $totalQuantity, $totalWeight, $totalCBM, $totalPayment, $orderNotes]);
            $orderSummary = [];
            foreach ($orderItems as $item) {
                if (!isset($item['id'], $item['icode'], $item['quantity']) || !is_numeric($item['quantity']) || $item['quantity'] <= 0)
                    throw new Exception("Invalid item data");
                $uw = $item['fweight'] ?? 0; $tw = $item['quantity'] * $uw;
                $uc = $item['cbm'] ?? 0;     $tc = $item['quantity'] * $uc;
                $ip = $tw * $paymentRate;
                $pdo->prepare("INSERT INTO tire_order_items (order_id,product_id,icode,quantity,unit_weight,total_weight,unit_cbm,total_cbm,payment_amount) VALUES (?,?,?,?,?,?,?,?,?)")
                    ->execute([$newOrderId, $item['id'], $item['icode'], (int)$item['quantity'], $uw, $tw, $uc, $tc, $ip]);
                $orderSummary[] = "{$item['icode']} ({$item['brand']} - {$item['size']}): {$item['quantity']} units, {$tw} kg";
            }
            $summaryDetails = implode("\n", $orderSummary);
            $pdo->prepare("INSERT INTO order_summaries (order_id,user_id,total_items,total_quantity,total_weight,total_cbm,total_payment,payment_rate,summary_details,created_at) VALUES (?,?,?,?,?,?,?,?,?,NOW())")
                ->execute([$newOrderId, $userId, count($orderItems), $totalQuantity, $totalWeight, $totalCBM, $totalPayment, $paymentRate, $summaryDetails]);
            $pdo->commit();
            $_SESSION['order_success'] = true;
            $_SESSION['order_id']      = $newOrderId;
            header('Location: order_review.php?order_id=' . urlencode($newOrderId));
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Order failed: " . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = "No items selected.";
        $messageType = 'error';
    }
}

/* ── MAIN DATA ───────────────────────────────────────────────────────────── */
$functionalCurrency = 'USD';
$currencySymbol     = '$';

try {
    $stmt = $pdo->prepare("SELECT fullName, userEmail, functional_currency FROM users WHERE id=?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();
    if (!$userData) { header('location:index2.php'); exit; }

    $functionalCurrency = $userData['functional_currency'] ?? 'USD';
    $currencySymbol     = getCurrencySymbol($functionalCurrency);

    /* -- Payment rate ------------------------------------------------------- */
    $stmt = $pdo->prepare("SELECT payment_rate FROM users WHERE id=?");
    $stmt->execute([$userId]);
    $rateData    = $stmt->fetch();
    $paymentRate = $rateData ? (float)$rateData['payment_rate'] : 0;

    /* -- Customer ID -------------------------------------------------------- */
    $stmt   = $pdo->prepare("SELECT cus_id FROM users WHERE id=?");
    $stmt->execute([$userId]);
    $cusRow = $stmt->fetch();
    $cusId  = $cusRow['cus_id'] ?? $userId;

    /* -- Allowed brands (from customers_brand table) ------------------------ */
    $allowedBrands       = [];
    $hasBrandRestriction = false;
    if (!empty($cusId)) {
        $brandStmt = $pdo->prepare(
            "SELECT DISTINCT brand FROM customers_brand WHERE cus_id = ?"
        );
        $brandStmt->execute([$cusId]);
        $brandRows = $brandStmt->fetchAll();
        if (!empty($brandRows)) {
            $allowedBrands       = array_column($brandRows, 'brand');
            $hasBrandRestriction = true;
        }
    }

    /* -- Brand payment rates ------------------------------------------------ */
    $brandRates = [];
    if (!empty($cusId)) {
        $stmt = $pdo->prepare("SELECT brand, payment_rate FROM customer_rate WHERE cus_id=?");
        $stmt->execute([$cusId]);
        foreach ($stmt->fetchAll() as $cr) {
            $brandRates[strtolower(trim($cr['brand']))] = (float)$cr['payment_rate'];
        }
    }

    /* -- Item-specific prices ----------------------------------------------- */
    $itemPrices      = [];
    $cusHasItemPrices = false;
    if (!empty($cusId)) {
        $stmt = $pdo->prepare("SELECT icode, price FROM customer_items WHERE cus_id=?");
        $stmt->execute([$cusId]);
        $ciRows = $stmt->fetchAll();
        $cusHasItemPrices = count($ciRows) > 0;
        foreach ($ciRows as $ci) {
            $itemPrices[strtolower(trim($ci['icode']))] = (float)$ci['price'];
        }
    }

    /* -- Main inventory query (filtered by allowed brands) ------------------ */
    if ($hasBrandRestriction && !empty($allowedBrands)) {
        $phs  = implode(',', array_fill(0, count($allowedBrands), '?'));
        $sql  = "SELECT r.id, r.icode, r.t_size, r.brand, r.col, r.rim,
                        t.fweight, t.tire_size, t.cbm, t.brand AS tire_brand
                 FROM realstock r
                 LEFT JOIN tire_details t ON r.icode = t.icode
                 WHERE t.fweight IS NOT NULL AND t.fweight > 0
                   AND t.brand IN ($phs)
                 ORDER BY t.brand ASC, r.t_size ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($allowedBrands);
    } else {
        $sql  = "SELECT r.id, r.icode, r.t_size, r.brand, r.col, r.rim,
                        t.fweight, t.tire_size, t.cbm, t.brand AS tire_brand
                 FROM realstock r
                 LEFT JOIN tire_details t ON r.icode = t.icode
                 WHERE t.fweight IS NOT NULL AND t.fweight > 0
                 ORDER BY t.brand ASC, r.t_size ASC";
        $stmt = $pdo->query($sql);
    }
    $inventory = $stmt->fetchAll();

    foreach ($inventory as &$row) {
        $ik = strtolower(trim($row['icode'] ?? ''));
        $row['customer_price'] = isset($itemPrices[$ik]) ? $itemPrices[$ik] : null;
    }
    unset($row);

    /* -- Autocomplete: iCodes ---------------------------------------------- */
    $icodes = array_column($pdo->query(
        "SELECT DISTINCT r.icode FROM realstock r LEFT JOIN tire_details t ON r.icode=t.icode
         WHERE r.icode!='' AND t.fweight>0 ORDER BY r.icode"
    )->fetchAll(), 'icode');

    /* -- Autocomplete: tire sizes ------------------------------------------ */
    $tireSizes = array_column($pdo->query(
        "SELECT DISTINCT t.tire_size FROM tire_details t WHERE t.tire_size!='' AND t.fweight>0 ORDER BY t.tire_size"
    )->fetchAll(), 'tire_size');

    /* -- Brands dropdown (filtered to allowed brands) ---------------------- */
    if ($hasBrandRestriction && !empty($allowedBrands)) {
        $phs   = implode(',', array_fill(0, count($allowedBrands), '?'));
        $bStmt = $pdo->prepare(
            "SELECT DISTINCT t.brand FROM tire_details t
             WHERE t.brand IN ($phs) AND t.brand IS NOT NULL AND t.brand != ''
             ORDER BY t.brand"
        );
        $bStmt->execute($allowedBrands);
    } else {
        $bStmt = $pdo->query(
            "SELECT DISTINCT t.brand FROM tire_details t
             WHERE t.brand IS NOT NULL AND t.brand != '' ORDER BY t.brand"
        );
    }
    $brands = array_column($bStmt->fetchAll(), 'brand');

    /* -- Colors ------------------------------------------------------------ */
    $colors = array_column($pdo->query(
        "SELECT DISTINCT r.col FROM realstock r LEFT JOIN tire_details t ON r.icode=t.icode
         WHERE r.col!='' AND t.fweight>0 ORDER BY r.col"
    )->fetchAll(), 'col');

    /* -- Rims -------------------------------------------------------------- */
    $rims = array_column($pdo->query(
        "SELECT DISTINCT r.rim FROM realstock r LEFT JOIN tire_details t ON r.icode=t.icode
         WHERE r.rim!='' AND t.fweight>0 ORDER BY r.rim"
    )->fetchAll(), 'rim');

} catch (PDOException $e) {
    $message          = "Database error: " . $e->getMessage();
    $messageType      = 'error';
    $inventory        = [];
    $itemPrices       = [];
    $cusHasItemPrices = false;
    $functionalCurrency = 'USD';
    $currencySymbol     = '$';
    $brands = $colors = $rims = $icodes = $tireSizes = [];
    $brandRates = [];
}

$initials = strtoupper(substr($userData['fullName'], 0, 1));
if (strpos($userData['fullName'], ' ') !== false)
    $initials .= strtoupper(substr($userData['fullName'], strpos($userData['fullName'], ' ') + 1, 1));

$originalOid = isset($_GET['original_oid']) ? htmlspecialchars($_GET['original_oid']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $originalOid ? 'Revise Order — ATIRE' : 'Place Order — ATIRE'; ?></title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ─── SF UI DISPLAY FONT FACES ───────────────────────────────────────────── */
@font-face { font-family:'SF UI Display'; font-weight:500; font-style:normal; src:url('font/sf-ui-display-medium-58646be638f96.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:600; font-style:normal; src:url('font/sf-ui-display-semibold-58646eddcae92.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:700; font-style:normal; src:url('font/sf-ui-display-bold-58646a511e3d9.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:800; font-style:normal; src:url('font/sf-ui-display-heavy-586470160b9e5.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:900; font-style:normal; src:url('font/sf-ui-display-black-58646a6b80d5a.otf') format('opentype'); }

/* ─── CSS VARIABLES ──────────────────────────────────────────────────────── */
:root {
    --orange:      #f28018;
    --orange-dk:   #d06e10;
    --orange-lt:   rgba(242,128,24,0.10);
    --orange-glow: rgba(242,128,24,0.18);
    --gray-50:     #f9f9f9;
    --gray-100:    #f2f2f2;
    --gray-200:    #e4e4e4;
    --gray-300:    #d0d0d0;
    --gray-400:    #b0b0b0;
    --gray-500:    #888888;
    --gray-700:    #444444;
    --gray-900:    #1a1a1a;
    --white:       #ffffff;
    --bg:          #f3f4f6;
    --success:     #16a34a;
    --success-lt:  rgba(22,163,74,0.08);
    --error:       #dc2626;
    --error-lt:    rgba(220,38,38,0.08);
    --font:       'SF UI Display', -apple-system, BlinkMacSystemFont, sans-serif;
    --radius-xs:   4px;
    --radius-sm:   8px;
    --radius-md:   12px;
    --radius-lg:   16px;
    --shadow-sm:   0 1px 6px rgba(0,0,0,0.06);
    --shadow:      0 2px 14px rgba(0,0,0,0.08);
    --shadow-md:   0 6px 28px rgba(0,0,0,0.12);
    --shadow-lg:   0 12px 48px rgba(0,0,0,0.14);
    --trans:       0.18s cubic-bezier(0.4,0,0.2,1);
    --hdr-h:       60px;
}

*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
html { scroll-behavior:smooth; }
body {
    font-family: var(--font);
    background: var(--bg);
    color: var(--gray-700);
    min-height: 100vh;
    font-size: 13.5px;
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
    overflow-x: hidden;
}

::-webkit-scrollbar { width:5px; height:5px; }
::-webkit-scrollbar-track { background:transparent; }
::-webkit-scrollbar-thumb { background:var(--gray-300); border-radius:99px; }
::-webkit-scrollbar-thumb:hover { background:var(--orange); }

/* ─── HEADER ─────────────────────────────────────────────────────────────── */
.hdr {
    position:sticky; top:0; z-index:400;
    background:var(--white);
    border-bottom:2.5px solid var(--orange);
    box-shadow:0 2px 20px rgba(0,0,0,0.08);
    height:var(--hdr-h);
}
.hdr-inner {
    max-width:1800px; margin:0 auto;
    padding:0 1.8rem; height:100%;
    display:flex; align-items:center; justify-content:space-between; gap:1rem;
}
.brand { display:flex; align-items:center; gap:12px; text-decoration:none; }
.brand-logo { height:30px; width:auto; }
.hdr-right { display:flex; align-items:center; gap:8px; }
/* Currency badge */
.hdr-currency {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 11px; border-radius:var(--radius-sm);
    background:var(--orange-lt); border:1.5px solid rgba(242,128,24,0.25);
    font-size:11px; font-weight:800; color:var(--orange);
    letter-spacing:.06em;
}
.hdr-currency i { font-size:9px; }
.hdr-btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 15px; border-radius:var(--radius-sm);
    font-family:var(--font); font-weight:700; font-size:12px; letter-spacing:.03em;
    text-decoration:none; border:1.5px solid var(--gray-200);
    background:var(--white); color:var(--gray-500);
    cursor:pointer; transition:var(--trans);
}
.hdr-btn:hover { border-color:var(--orange); color:var(--orange); background:var(--orange-lt); }
.avatar {
    width:34px; height:34px; border-radius:50%;
    background:var(--orange); color:var(--white);
    display:flex; align-items:center; justify-content:center;
    font-weight:900; font-size:12px;
    box-shadow:0 2px 8px rgba(242,128,24,0.35);
}

/* ─── PAGE SHELL ─────────────────────────────────────────────────────────── */
.page-shell {
    display:flex; max-width:1800px; margin:0 auto;
    min-height:calc(100vh - var(--hdr-h));
}

/* ─── LEFT SIDEBAR ───────────────────────────────────────────────────────── */
.left-sidebar {
    width:270px; flex-shrink:0;
    background:var(--white);
    border-right:1.5px solid var(--gray-200);
    position:sticky; top:var(--hdr-h);
    height:calc(100vh - var(--hdr-h));
    overflow-y:auto; overflow-x:hidden;
    display:flex; flex-direction:column;
}
.sidebar-filters { padding:1.1rem 1.2rem; flex:1; }
.sidebar-hd {
    font-size:10px; font-weight:800; color:var(--gray-700);
    letter-spacing:.12em; text-transform:uppercase;
    margin-bottom:10px;
    display:flex; align-items:center; justify-content:space-between;
}
.sidebar-hd i { color:var(--orange); margin-right:4px; font-size:9.5px; }
.spin-ico { color:var(--orange); display:none; font-size:10px; }

.active-filter-chips { display:flex; flex-wrap:wrap; gap:4px; margin-bottom:8px; }
.filter-chip {
    background:var(--orange); color:var(--white);
    border-radius:20px; padding:3px 9px 3px 8px;
    font-size:10px; font-weight:700;
    display:flex; align-items:center; gap:4px;
    cursor:pointer; transition:var(--trans);
}
.filter-chip:hover { background:var(--orange-dk); }
.filter-chip i { font-size:8px; opacity:.8; }

.sb-divider { height:1px; background:var(--gray-100); margin:10px 0; }

.fg { margin-bottom:9px; }
.fg label {
    display:flex; align-items:center; gap:4px;
    margin-bottom:4px;
    font-size:9.5px; font-weight:700; color:var(--gray-500);
    text-transform:uppercase; letter-spacing:.09em;
}
.fg label i { color:var(--orange); font-size:8.5px; }
.fg input[type="text"],
.fg select {
    width:100%; padding:7px 10px;
    border:1.5px solid var(--gray-200); border-radius:var(--radius-sm);
    font-family:var(--font); font-size:12.5px; font-weight:600;
    color:var(--gray-700); background:var(--white);
    transition:var(--trans); outline:none;
    appearance:none; -webkit-appearance:none;
}
.fg select {
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='11' height='6' viewBox='0 0 11 6'%3E%3Cpath d='M1 1l4.5 4 4.5-4' stroke='%23aaa' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 10px center; padding-right:28px;
}
.fg input[type="text"]:focus,
.fg select:focus { border-color:var(--orange); box-shadow:0 0 0 3px var(--orange-glow); }
.fg input.active-filter,
.fg select.active-filter { border-color:var(--orange); background:rgba(242,128,24,0.03); }

.sort-row { display:flex; flex-wrap:wrap; gap:4px; }
.sort-chip {
    padding:4px 9px; border-radius:20px;
    font-size:10px; font-weight:700;
    background:var(--white); border:1.5px solid var(--gray-200);
    color:var(--gray-500); cursor:pointer; transition:var(--trans);
}
.sort-chip:hover { border-color:var(--orange); color:var(--orange); }
.sort-chip.active { background:var(--orange); border-color:var(--orange); color:var(--white); }

.btn-clear-all {
    width:100%; padding:8px;
    border:1.5px solid var(--gray-200); border-radius:var(--radius-sm);
    background:var(--white); color:var(--gray-500);
    font-family:var(--font); font-size:11.5px; font-weight:700;
    letter-spacing:.06em; text-transform:uppercase;
    cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;
    transition:var(--trans); margin-top:4px;
}
.btn-clear-all:hover { border-color:var(--orange); color:var(--orange); background:var(--orange-lt); }

/* ─── RIGHT CONTENT ──────────────────────────────────────────────────────── */
.right-content { flex:1; min-width:0; display:flex; flex-direction:column; }

/* ─── HERO BANNER ────────────────────────────────────────────────────────── */
.hero-banner {
    background:var(--white);
    border-bottom:1px solid var(--gray-100);
    padding:0.9rem 2rem 0.8rem;
    display:flex; align-items:center; justify-content:space-between; gap:1.5rem; flex-wrap:wrap;
}
.hero-eyebrow {
    font-size:9px; font-weight:800; color:var(--orange);
    letter-spacing:.22em; text-transform:uppercase;
    margin-bottom:5px; display:flex; align-items:center; gap:6px;
}
.hero-eyebrow::before { content:''; width:16px; height:2px; background:var(--orange); border-radius:2px; }
.hero-title {
    font-size:clamp(26px,3vw,40px); font-weight:900;
    color:var(--gray-900); letter-spacing:-.02em; line-height:1;
}
.hero-title span { color:var(--orange); }
.hero-sub { font-size:12px; font-weight:500; color:var(--gray-400); margin-top:5px; }

.revision-badge {
    display:inline-flex; align-items:center; gap:6px;
    background:var(--orange-lt); border:1.5px solid rgba(242,128,24,0.30);
    color:var(--orange); border-radius:20px;
    padding:4px 12px; font-size:10px; font-weight:800; letter-spacing:.06em; text-transform:uppercase;
}
.rate-badge {
    display:inline-flex; align-items:center; gap:6px;
    background:var(--gray-100); border:1.5px solid var(--gray-200);
    color:var(--gray-700); border-radius:20px;
    padding:4px 12px; font-size:10px; font-weight:700;
}
.rate-badge i { color:var(--orange); font-size:9px; }

/* ─── CONTAINER CARDS ────────────────────────────────────────────────────── */
.container-col { flex-shrink:0; }
.container-row { display:flex; gap:12px; flex-wrap:wrap; justify-content:flex-end; }
.ctr-card {
    background:var(--white); border:1.5px solid var(--gray-200);
    border-radius:var(--radius-md); padding:9px 12px 8px;
    min-width:260px; display:none; box-shadow:var(--shadow);
    transition:border-color var(--trans);
}
.ctr-card.show { display:block; animation:fadeUp .3s ease; }
.ctr-card.overloaded { border-color:var(--orange); box-shadow:0 0 0 3px var(--orange-glow); }
@keyframes fadeUp { from{opacity:0;transform:translateY(-8px);}to{opacity:1;transform:translateY(0);} }
.ctr-label {
    font-size:9px; font-weight:800; color:var(--gray-400);
    letter-spacing:.14em; text-transform:uppercase;
    display:flex; align-items:center; justify-content:space-between; margin-bottom:9px;
}
.overload-badge-pill {
    background:var(--orange); color:var(--white);
    padding:2px 8px; border-radius:20px; font-size:9px; font-weight:700;
    display:none; animation:pulseAnim 1s infinite;
}
.ctr-card.overloaded .overload-badge-pill { display:inline-block; }
@keyframes pulseAnim { 0%,100%{opacity:1;}50%{opacity:.5;} }
.ctr-svg-wrap { margin-bottom:9px; }
.ctr-svg { width:100%; height:auto; display:block; }
.cargo-fill { transition:width .6s cubic-bezier(.4,0,.2,1); }
.ctr-stats { display:flex; align-items:center; justify-content:space-between; }
.ctr-pct { font-size:2rem; font-weight:900; color:var(--orange); line-height:1; letter-spacing:-.04em; }
.ctr-card.overloaded .ctr-pct { animation:blinkAnim .9s infinite; }
@keyframes blinkAnim { 0%,100%{opacity:1;}50%{opacity:.4;} }
.ctr-info { text-align:right; }
.ctr-wt  { font-size:11px; font-weight:700; color:var(--gray-700); }
.ctr-cap { font-size:10px; font-weight:500; color:var(--gray-400); }

/* ─── MESSAGE ────────────────────────────────────────────────────────────── */
.msg-bar { padding:.7rem 2rem 0; }
.msg {
    padding:10px 14px; border-radius:var(--radius-sm);
    display:flex; align-items:center; gap:10px;
    font-weight:600; font-size:13px;
    border-left:3px solid var(--orange);
    background:rgba(242,128,24,0.07); color:#7a4400;
}
.msg.error { background:var(--error-lt); color:#7a1a1a; border-color:var(--error); }

/* ─── INVENTORY PANEL ────────────────────────────────────────────────────── */
.page-body { padding:0 0 9rem; flex:1; }
.inv-panel { background:var(--white); position:relative; }

.loading-veil {
    position:absolute; inset:0;
    background:rgba(255,255,255,0.90); backdrop-filter:blur(3px);
    display:none; align-items:center; justify-content:center; z-index:20;
}
.loading-veil.on { display:flex; }
.spinner {
    width:32px; height:32px;
    border:3px solid var(--gray-200); border-top-color:var(--orange);
    border-radius:50%; animation:spin .65s linear infinite;
}
@keyframes spin { to{transform:rotate(360deg);} }

.inv-header {
    padding:.8rem 1.3rem;
    border-bottom:1.5px solid var(--gray-100);
    display:flex; align-items:center; justify-content:space-between; gap:1rem;
    background:var(--white);
    position:sticky; top:var(--hdr-h); z-index:10;
}
.inv-title {
    font-size:13px; font-weight:800; color:var(--gray-700);
    letter-spacing:.05em; text-transform:uppercase;
    display:flex; align-items:center; gap:9px;
}
.inv-title-icon {
    width:27px; height:27px; border-radius:var(--radius-xs);
    background:var(--orange); color:var(--white);
    display:flex; align-items:center; justify-content:center; font-size:11px;
}
.count-pill {
    padding:4px 13px; border-radius:20px;
    font-size:11.5px; font-weight:700;
    background:var(--gray-100); color:var(--gray-500); transition:var(--trans);
}
.count-pill.pop { animation:popAnim .4s ease; }
@keyframes popAnim { 0%,100%{transform:scale(1);}50%{transform:scale(1.12);background:var(--orange);color:var(--white);} }

.filter-info-strip {
    background:rgba(242,128,24,0.06); border-bottom:1px solid rgba(242,128,24,0.15);
    padding:6px 1.3rem; font-size:11.5px; color:#8a5000; font-weight:600;
    display:none; align-items:center; gap:8px;
}
.filter-info-strip.show { display:flex; }

/* ─── TABLE ──────────────────────────────────────────────────────────────── */
.tbl-wrap { overflow-x:auto; }
.tbl-scroll {
    overflow-y:auto; overflow-x:auto;
    max-height:calc(100vh - var(--hdr-h) - 160px); min-height:300px;
}
table.inv-tbl { width:100%; border-collapse:collapse; table-layout:auto; min-width:700px; }
table.inv-tbl thead { background:#f7f7f7; position:sticky; top:0; z-index:9; }
table.inv-tbl thead::after { content:''; display:block; position:absolute; bottom:-2px; left:0; right:0; height:2px; background:var(--gray-200); }
table.inv-tbl th {
    padding:8px 10px; text-align:left;
    font-size:10px; font-weight:800; color:var(--gray-500);
    letter-spacing:.11em; text-transform:uppercase;
    white-space:nowrap; border-right:1px solid var(--gray-200);
    border-bottom:2px solid var(--gray-200); user-select:none;
}
table.inv-tbl th:last-child { border-right:none; }
table.inv-tbl th i { color:var(--orange); margin-right:4px; font-size:9px; }
table.inv-tbl tbody tr { transition:background var(--trans); border-bottom:1px solid var(--gray-100); }
table.inv-tbl tbody tr:last-child { border-bottom:none; }
table.inv-tbl tbody tr:nth-child(even) { background:#fafafa; }
table.inv-tbl tbody tr:hover { background:rgba(242,128,24,0.04); }
table.inv-tbl tbody tr.selected { background:rgba(242,128,24,0.06); }
table.inv-tbl td { padding:7px 10px; font-size:12.5px; color:var(--gray-700); font-weight:500; vertical-align:middle; }
td.code-cell { font-weight:800; font-size:13px; color:var(--orange); letter-spacing:.01em; white-space:nowrap; }
td.brand-cell { font-weight:700; color:var(--gray-900); font-size:12.5px; white-space:nowrap; }
td.num-cell { font-weight:700; font-size:12px; color:var(--gray-700); text-align:right; white-space:nowrap; }
td.price-cell { font-weight:700; font-size:12px; color:var(--gray-700); text-align:right; white-space:nowrap; }
td.tiresize-cell { font-size:11.5px; font-weight:600; color:var(--gray-500); white-space:nowrap; }
.color-badge, .rim-badge {
    display:inline-flex; align-items:center; padding:2px 7px; border-radius:20px;
    font-size:10px; font-weight:700; background:var(--gray-100); color:var(--gray-500);
    border:1px solid var(--gray-200); white-space:nowrap;
}

/* Price display */
.price-specific { display:inline-flex; align-items:center; gap:4px; color:#166534; }
.price-specific-badge {
    display:inline-block; background:#dcfce7; color:#15803d;
    border:1px solid #86efac; border-radius:20px; padding:1px 6px;
    font-size:9px; font-weight:800; letter-spacing:.06em; text-transform:uppercase;
}
.price-rate { color:var(--gray-500); font-size:11.5px; }
.price-approx { display:inline-flex; align-items:center; gap:4px; color:#92400e; }
.price-approx-badge {
    display:inline-block; background:#fef3c7; color:#b45309;
    border:1px solid #fcd34d; border-radius:20px; padding:1px 6px;
    font-size:9px; font-weight:800; letter-spacing:.06em; text-transform:uppercase;
}

.qty-inp {
    width:60px; text-align:center;
    border:2px solid var(--gray-200); border-radius:var(--radius-sm);
    padding:4px; font-family:var(--font); font-weight:800; font-size:13px; color:var(--gray-700);
    background:var(--white); transition:var(--trans); outline:none;
    -moz-appearance:textfield;
}
.qty-inp::-webkit-outer-spin-button,
.qty-inp::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
.qty-inp:focus { border-color:var(--orange); box-shadow:0 0 0 3px var(--orange-glow); }
.qty-inp.filled { border-color:var(--orange); background:rgba(242,128,24,0.06); color:var(--orange); }

.empty-state { text-align:center; padding:4rem 2rem; }
.empty-state i { font-size:3rem; color:var(--gray-300); margin-bottom:14px; display:block; }
.empty-state h3 { font-size:18px; font-weight:800; color:var(--gray-700); }
.empty-state p { font-size:13px; color:var(--gray-400); margin-top:5px; font-weight:500; }

/* ─── ORDER DOCK ─────────────────────────────────────────────────────────── */
.order-dock {
    position:fixed; bottom:0; left:0; right:0; z-index:500;
    transform:translateY(100%);
    transition:transform .36s cubic-bezier(.4,0,.2,1);
    box-shadow:0 -4px 32px rgba(0,0,0,.12);
    background:var(--white);
    border-top:3px solid var(--orange);
    max-height:80vh; overflow-y:auto;
}
.order-dock.open { transform:translateY(0); }
.dock-header {
    background:var(--orange);
    padding:11px 1.8rem;
    display:flex; align-items:center; justify-content:space-between;
    cursor:pointer; position:sticky; top:0; z-index:10;
}
.dock-title {
    font-size:13px; font-weight:900; color:var(--white);
    letter-spacing:.05em; text-transform:uppercase;
    display:flex; align-items:center; gap:8px;
}
.dock-sub { font-size:9.5px; color:rgba(255,255,255,.70); font-weight:500; }
.dock-stats { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
.stat-tile {
    background:rgba(255,255,255,.18); border:1px solid rgba(255,255,255,.25);
    border-radius:var(--radius-sm); padding:4px 12px; text-align:center; min-width:62px;
}
.stat-tile-lbl { font-size:8px; color:rgba(255,255,255,.70); font-weight:700; letter-spacing:.10em; text-transform:uppercase; }
.stat-tile-val { font-size:13.5px; font-weight:900; color:var(--white); line-height:1.15; }
.dock-toggle { background:none; border:none; color:rgba(255,255,255,.65); font-size:12px; cursor:pointer; padding:4px 6px; }
.dock-body { display:none; }
.dock-body.open { display:block; }
.disclaimer {
    margin:1rem 1.8rem 0; padding:9px 13px;
    background:rgba(242,128,24,0.07); border:1px solid rgba(242,128,24,0.20);
    border-left:3px solid var(--orange); border-radius:var(--radius-sm);
    font-size:11.5px; color:#7a4400; font-weight:500;
}
.disclaimer i { color:var(--orange); margin-right:5px; }
.dock-table-wrap {
    margin:1rem 1.8rem; border:1.5px solid var(--gray-200);
    border-radius:var(--radius-sm); max-height:260px; overflow-y:auto; overflow-x:auto;
}
table.dock-tbl { width:100%; border-collapse:collapse; min-width:800px; }
table.dock-tbl thead { background:#f5f5f5; position:sticky; top:0; z-index:5; }
table.dock-tbl th {
    padding:8px 10px; text-align:left; font-size:9.5px; font-weight:800; color:#555;
    letter-spacing:.10em; text-transform:uppercase; white-space:nowrap;
    border-right:1px solid var(--gray-200);
}
table.dock-tbl th:last-child { border-right:none; }
table.dock-tbl td {
    padding:7px 10px; border-bottom:1px solid var(--gray-100);
    font-size:12px; font-weight:500; color:#555; vertical-align:middle; white-space:nowrap;
}
table.dock-tbl tbody tr:hover { background:rgba(242,128,24,0.04); }
table.dock-tbl td.dc { font-weight:800; font-size:13px; color:var(--orange); }
table.dock-tbl td.dw { font-weight:700; color:var(--gray-900); }
table.dock-tbl td.dr { text-align:right; font-weight:700; }
table.dock-tbl td.dv { text-align:right; font-weight:700; color:#166534; }
.dock-qty {
    width:50px; text-align:center; border:1.5px solid var(--gray-200);
    border-radius:var(--radius-xs); padding:4px;
    font-family:var(--font); font-weight:800; font-size:13px; color:var(--gray-700);
    background:var(--white); outline:none; transition:var(--trans); -moz-appearance:textfield;
}
.dock-qty::-webkit-outer-spin-button,
.dock-qty::-webkit-inner-spin-button { -webkit-appearance:none; }
.dock-qty:focus { border-color:var(--orange); box-shadow:0 0 0 2px var(--orange-glow); }
.rm-btn {
    background:none; border:none; color:var(--gray-300); cursor:pointer;
    padding:4px 6px; border-radius:var(--radius-xs); font-size:11px; transition:var(--trans);
}
.rm-btn:hover { color:var(--orange); background:var(--orange-lt); }
.dock-actions {
    display:flex; gap:10px; padding:1rem 1.8rem 1.5rem;
    border-top:1.5px solid var(--gray-100);
}
.dock-actions form { display:contents; }
.dbtn {
    flex:1; padding:11px 18px; border:none; border-radius:var(--radius-sm);
    font-family:var(--font); font-size:13px; font-weight:800;
    letter-spacing:.05em; text-transform:uppercase;
    cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;
    transition:var(--trans);
}
.dbtn-submit { background:var(--orange); color:var(--white); }
.dbtn-submit:hover { background:var(--orange-dk); transform:translateY(-1px); box-shadow:0 5px 18px rgba(242,128,24,0.30); }
.dbtn-clear { background:var(--white); color:var(--gray-500); border:1.5px solid var(--gray-200); }
.dbtn-clear:hover { border-color:var(--orange); color:var(--orange); transform:translateY(-1px); }

/* ─── AUTOCOMPLETE ───────────────────────────────────────────────────────── */
.ui-autocomplete {
    max-height:200px; overflow-y:auto;
    background:var(--white) !important; border:2px solid var(--orange) !important;
    border-radius:var(--radius-sm) !important; box-shadow:var(--shadow-md) !important;
    z-index:9999 !important; font-family:var(--font) !important;
}
.ui-menu-item-wrapper {
    padding:8px 12px; font-family:var(--font) !important;
    font-weight:600; font-size:12.5px; color:#444;
    border-left:3px solid transparent; transition:var(--trans);
}
.ui-menu-item-wrapper:hover, .ui-state-active {
    background:rgba(242,128,24,0.08) !important;
    border-left-color:var(--orange) !important; color:var(--orange) !important;
}

/* ─── APPROX PRICE MODAL ─────────────────────────────────────────────────── */
.approx-modal-backdrop {
    position:fixed; inset:0; z-index:9000;
    background:rgba(0,0,0,0.48); backdrop-filter:blur(4px);
    display:flex; align-items:center; justify-content:center; padding:1rem;
    opacity:0; pointer-events:none; transition:opacity .22s ease;
}
.approx-modal-backdrop.visible { opacity:1; pointer-events:all; }
.approx-modal {
    background:var(--white); border-radius:var(--radius-lg);
    box-shadow:var(--shadow-lg); width:100%; max-width:420px; overflow:hidden;
    transform:translateY(18px) scale(0.97);
    transition:transform .25s cubic-bezier(.34,1.56,.64,1);
}
.approx-modal-backdrop.visible .approx-modal { transform:translateY(0) scale(1); }
.approx-modal-hdr {
    background:linear-gradient(135deg,#fff8ed 0%,#fff3d6 100%);
    border-bottom:2px solid #fcd34d; padding:1.2rem 1.4rem 1rem;
    display:flex; align-items:flex-start; gap:14px;
}
.approx-modal-icon {
    width:44px; height:44px; flex-shrink:0;
    background:linear-gradient(135deg,#f59e0b,#f28018); border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 4px 14px rgba(242,128,24,0.35);
}
.approx-modal-icon i { color:#fff; font-size:18px; }
.approx-modal-eyebrow { font-size:9px; font-weight:800; color:#b45309; letter-spacing:.18em; text-transform:uppercase; margin-bottom:3px; }
.approx-modal-title { font-size:16px; font-weight:900; color:var(--gray-900); letter-spacing:-.01em; line-height:1.2; }
.approx-modal-body { padding:1.2rem 1.4rem 0.6rem; }
.approx-modal-icode {
    display:inline-flex; align-items:center; gap:6px;
    background:rgba(242,128,24,0.08); border:1px solid rgba(242,128,24,0.25);
    border-radius:var(--radius-sm); padding:5px 12px;
    font-size:13px; font-weight:800; color:var(--orange); letter-spacing:.04em; margin-bottom:10px;
}
.approx-modal-icode i { font-size:10px; }
.approx-modal-msg { font-size:13px; font-weight:500; color:var(--gray-700); line-height:1.6; }
.approx-modal-msg strong { color:var(--gray-900); font-weight:800; }
.approx-modal-note {
    margin-top:10px; padding:9px 12px; background:#fef9ee;
    border:1px solid #fde68a; border-left:3px solid #f59e0b; border-radius:var(--radius-sm);
    font-size:11.5px; color:#78350f; font-weight:600;
    display:flex; align-items:flex-start; gap:7px; line-height:1.5;
}
.approx-modal-note i { color:#f59e0b; margin-top:1px; flex-shrink:0; font-size:11px; }
.approx-modal-ftr { padding:1rem 1.4rem 1.2rem; display:flex; gap:8px; border-top:1px solid var(--gray-100); }
.approx-modal-cancel {
    flex:1; padding:10px 14px; border:1.5px solid var(--gray-200); border-radius:var(--radius-sm);
    background:var(--white); color:var(--gray-500); font-family:var(--font);
    font-size:12.5px; font-weight:700; letter-spacing:.04em; text-transform:uppercase;
    cursor:pointer; transition:var(--trans); display:flex; align-items:center; justify-content:center; gap:6px;
}
.approx-modal-cancel:hover { border-color:var(--orange); color:var(--orange); background:var(--orange-lt); }
.approx-modal-ok {
    flex:2; padding:10px 14px; border:none; border-radius:var(--radius-sm);
    background:var(--orange); color:var(--white); font-family:var(--font);
    font-size:12.5px; font-weight:900; letter-spacing:.06em; text-transform:uppercase;
    cursor:pointer; transition:var(--trans); display:flex; align-items:center; justify-content:center; gap:7px;
    box-shadow:0 3px 12px rgba(242,128,24,0.30);
}
.approx-modal-ok:hover { background:var(--orange-dk); transform:translateY(-1px); box-shadow:0 5px 18px rgba(242,128,24,0.38); }
.approx-modal-ok i { font-size:11px; }

/* ─── RESPONSIVE ─────────────────────────────────────────────────────────── */
@media(max-width:960px) {
    .page-shell { flex-direction:column; }
    .left-sidebar { width:100%; position:static; height:auto; border-right:none; border-bottom:1.5px solid var(--gray-200); }
}
@media(max-width:600px) {
    .hdr-inner,.hero-banner { padding-left:1rem; padding-right:1rem; }
    .dock-header,.dock-actions { padding-left:1rem; padding-right:1rem; }
    .dock-table-wrap,.disclaimer { margin-left:1rem; margin-right:1rem; }
    .hero-banner { flex-direction:column; align-items:flex-start; }
}
</style>
</head>
<body>

<!-- ═══════════════════════════════ HEADER ════════════════════════════════ -->
<header class="hdr">
    <div class="hdr-inner">
        <a href="dashboard.php" class="brand">
            <img src="atire.png" alt="ATIRE" class="brand-logo">
        </a>
        <div class="hdr-right">
            <!-- Functional currency badge -->
            <div class="hdr-currency" title="Your account currency: <?php echo htmlspecialchars($functionalCurrency); ?>">
                <i class="fas fa-coins"></i>
                <?php echo htmlspecialchars($functionalCurrency); ?>
                &nbsp;<span style="opacity:.6;font-weight:600;">(<?php echo htmlspecialchars(rtrim($currencySymbol)); ?>)</span>
            </div>
            <a href="dashboard.php" class="hdr-btn"><i class="fas fa-arrow-left"></i> Dashboard</a>
            <div class="avatar"><?php echo htmlspecialchars($initials); ?></div>
        </div>
    </div>
</header>

<div class="page-shell">

<!-- ═══════════════════════════════ LEFT SIDEBAR ══════════════════════════ -->
<aside class="left-sidebar">
    <div class="sidebar-filters">
        <div class="sidebar-hd">
            <span><i class="fas fa-sliders-h"></i>Filters</span>
            <i class="fas fa-spinner fa-spin spin-ico" id="spinIco"></i>
        </div>
        <div class="active-filter-chips" id="chipRow"></div>

        <div class="fg">
            <label><i class="fas fa-sort-amount-down"></i>Sort By</label>
            <div class="sort-row" id="sortRow">
                <div class="sort-chip active" data-sort="">Default</div>
                <div class="sort-chip" data-sort="brand_asc">Brand ↑</div>
                <div class="sort-chip" data-sort="brand_desc">Brand ↓</div>
                <div class="sort-chip" data-sort="weight_asc">Wt ↑</div>
                <div class="sort-chip" data-sort="weight_desc">Wt ↓</div>
                <div class="sort-chip" data-sort="code_asc">Code ↑</div>
            </div>
        </div>

        <div class="sb-divider"></div>

        <div class="fg">
            <label><i class="fas fa-barcode"></i>Item Code</label>
            <input type="text" id="icode_select" class="rf" placeholder="e.g. TY-1234" autocomplete="off">
        </div>
        <div class="fg">
            <label><i class="fas fa-circle-notch"></i>Tire Size</label>
            <input type="text" id="tire_size_select" class="rf" placeholder="e.g. 205/55R16" autocomplete="off">
        </div>
        <div class="fg">
            <label><i class="fas fa-tag"></i>Brand</label>
            <select id="brand_select" class="rf">
                <option value="all">All Brands</option>
                <?php foreach ($brands as $b): ?>
                <option value="<?php echo htmlspecialchars($b); ?>"><?php echo htmlspecialchars($b); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fg">
            <label><i class="fas fa-palette"></i>Color</label>
            <select id="col_select" class="rf">
                <option value="all">All Colors</option>
                <?php foreach ($colors as $c): ?>
                <option value="<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars($c); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fg">
            <label><i class="fas fa-cog"></i>Rim Size</label>
            <select id="rim_select" class="rf">
                <option value="all">All Rims</option>
                <?php foreach ($rims as $r): ?>
                <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="sb-divider"></div>
        <button class="btn-clear-all" onclick="clearFilters()"><i class="fas fa-undo"></i> Clear All Filters</button>
    </div>
</aside>

<!-- ═══════════════════════════════ RIGHT CONTENT ═════════════════════════ -->
<div class="right-content">

    <!-- HERO BANNER + CONTAINER CARDS -->
    <div class="hero-banner">
        <div>
            <div class="hero-eyebrow"><?php echo $originalOid ? 'Revising Order' : 'Live Inventory'; ?></div>
            <div class="hero-title">
                <?php if ($originalOid): ?>
                    Revise <span>Order</span>
                <?php else: ?>
                    Place <span>New</span> Order
                <?php endif; ?>
            </div>
            <div class="hero-sub" style="display:flex;align-items:center;gap:8px;margin-top:6px;flex-wrap:wrap;">
                <span>Select quantities — your cart updates in real time.</span>
                <?php if ($originalOid): ?>
                <span class="revision-badge"><i class="fas fa-history"></i> Revising: <?php echo $originalOid; ?></span>
                <?php endif; ?>
                <span class="rate-badge"><i class="fas fa-money-bill-wave"></i> Rate: <?php echo htmlspecialchars(rtrim($currencySymbol)); ?><?php echo number_format($paymentRate, 2); ?>/kg</span>
            </div>
        </div>

        <div class="container-col">
            <div class="container-row">
                <!-- 20 FT Container Card -->
                <div class="ctr-card" id="card20">
                    <div class="ctr-label">
                        <span><i class="fas fa-truck" style="color:var(--orange);margin-right:4px;"></i>20 FT Container</span>
                        <span class="overload-badge-pill">⚠ OVERLOAD</span>
                    </div>
                    <div class="ctr-svg-wrap">
                        <svg class="ctr-svg" viewBox="0 0 340 88" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <ellipse cx="175" cy="86" rx="158" ry="3" fill="rgba(0,0,0,0.07)"/>
                            <rect x="4" y="20" width="55" height="54" rx="7" fill="#e8e8e8" stroke="#c4c4c4" stroke-width="1.5"/>
                            <rect x="10" y="24" width="36" height="26" rx="3" fill="#c6dff0" stroke="#a8c8de" stroke-width="1"/>
                            <rect x="55" y="54" width="8" height="14" rx="2" fill="#cccccc"/>
                            <rect x="60" y="66" width="276" height="7" rx="3" fill="#d0d0d0" stroke="#bbbbbb" stroke-width="1"/>
                            <rect x="60" y="16" width="276" height="52" rx="4" fill="#f4f4f4" stroke="#c8c8c8" stroke-width="1.5"/>
                            <rect x="60" y="16" width="276" height="5" rx="2" fill="#e2e2e2"/>
                            <rect x="60" y="63" width="276" height="5" fill="#e2e2e2"/>
                            <defs><clipPath id="clip20"><rect x="61" y="21" width="272" height="42" rx="2"/></clipPath></defs>
                            <rect id="fill20" x="61" y="21" width="0" height="42" fill="rgba(242,128,24,0.55)" clip-path="url(#clip20)" class="cargo-fill"/>
                            <line x1="124" y1="16" x2="124" y2="68" stroke="#ddd" stroke-width="1.2"/>
                            <line x1="178" y1="16" x2="178" y2="68" stroke="#ddd" stroke-width="1.2"/>
                            <line x1="232" y1="16" x2="232" y2="68" stroke="#ddd" stroke-width="1.2"/>
                            <circle cx="23" cy="74" r="9" fill="#c4c4c4" stroke="#a8a8a8" stroke-width="1.5"/>
                            <circle cx="23" cy="74" r="5" fill="#d8d8d8" stroke="#b8b8b8" stroke-width="1"/>
                            <circle cx="23" cy="74" r="1.8" fill="#aaa"/>
                            <circle cx="218" cy="74" r="9" fill="#c4c4c4" stroke="#a8a8a8" stroke-width="1.5"/>
                            <circle cx="218" cy="74" r="5" fill="#d8d8d8" stroke="#b8b8b8" stroke-width="1"/>
                            <circle cx="218" cy="74" r="1.8" fill="#aaa"/>
                            <circle cx="302" cy="74" r="9" fill="#c4c4c4" stroke="#a8a8a8" stroke-width="1.5"/>
                            <circle cx="302" cy="74" r="5" fill="#d8d8d8" stroke="#b8b8b8" stroke-width="1"/>
                            <circle cx="302" cy="74" r="1.8" fill="#aaa"/>
                        </svg>
                    </div>
                    <div class="ctr-stats">
                        <div class="ctr-pct" id="pct20">0%</div>
                        <div class="ctr-info">
                            <div class="ctr-wt" id="wt20">0 kg</div>
                            <div class="ctr-cap">Max 18,000 kg</div>
                        </div>
                    </div>
                </div>

                <!-- 40 FT Container Card -->
                <div class="ctr-card" id="card40">
                    <div class="ctr-label">
                        <span><i class="fas fa-truck" style="color:var(--orange);margin-right:4px;"></i>40 FT Container</span>
                        <span class="overload-badge-pill">⚠ OVERLOAD</span>
                    </div>
                    <div class="ctr-svg-wrap">
                        <svg class="ctr-svg" viewBox="0 0 400 88" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <ellipse cx="205" cy="86" rx="188" ry="3" fill="rgba(0,0,0,0.07)"/>
                            <rect x="4" y="20" width="55" height="54" rx="7" fill="#e8e8e8" stroke="#c4c4c4" stroke-width="1.5"/>
                            <rect x="10" y="24" width="36" height="26" rx="3" fill="#c6dff0" stroke="#a8c8de" stroke-width="1"/>
                            <rect x="55" y="54" width="8" height="14" rx="2" fill="#cccccc"/>
                            <rect x="60" y="66" width="336" height="7" rx="3" fill="#d0d0d0" stroke="#bbbbbb" stroke-width="1"/>
                            <rect x="60" y="16" width="336" height="52" rx="4" fill="#f4f4f4" stroke="#c8c8c8" stroke-width="1.5"/>
                            <rect x="60" y="16" width="336" height="5" rx="2" fill="#e2e2e2"/>
                            <rect x="60" y="63" width="336" height="5" fill="#e2e2e2"/>
                            <defs><clipPath id="clip40"><rect x="61" y="21" width="332" height="42" rx="2"/></clipPath></defs>
                            <rect id="fill40" x="61" y="21" width="0" height="42" fill="rgba(242,128,24,0.55)" clip-path="url(#clip40)" class="cargo-fill"/>
                            <line x1="126" y1="16" x2="126" y2="68" stroke="#ddd" stroke-width="1.2"/>
                            <line x1="192" y1="16" x2="192" y2="68" stroke="#ddd" stroke-width="1.2"/>
                            <line x1="258" y1="16" x2="258" y2="68" stroke="#ddd" stroke-width="1.2"/>
                            <line x1="324" y1="16" x2="324" y2="68" stroke="#ddd" stroke-width="1.2"/>
                            <circle cx="23" cy="74" r="9" fill="#c4c4c4" stroke="#a8a8a8" stroke-width="1.5"/>
                            <circle cx="23" cy="74" r="5" fill="#d8d8d8" stroke="#b8b8b8" stroke-width="1"/>
                            <circle cx="23" cy="74" r="1.8" fill="#aaa"/>
                            <circle cx="218" cy="74" r="9" fill="#c4c4c4" stroke="#a8a8a8" stroke-width="1.5"/>
                            <circle cx="218" cy="74" r="5" fill="#d8d8d8" stroke="#b8b8b8" stroke-width="1"/>
                            <circle cx="218" cy="74" r="1.8" fill="#aaa"/>
                            <circle cx="360" cy="74" r="9" fill="#c4c4c4" stroke="#a8a8a8" stroke-width="1.5"/>
                            <circle cx="360" cy="74" r="5" fill="#d8d8d8" stroke="#b8b8b8" stroke-width="1"/>
                            <circle cx="360" cy="74" r="1.8" fill="#aaa"/>
                        </svg>
                    </div>
                    <div class="ctr-stats">
                        <div class="ctr-pct" id="pct40">0%</div>
                        <div class="ctr-info">
                            <div class="ctr-wt" id="wt40">0 kg</div>
                            <div class="ctr-cap">Max 25,000 kg</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="msg-bar">
        <div class="msg <?php echo htmlspecialchars($messageType); ?>">
            <i class="fas fa-<?php echo $messageType==='success'?'check-circle':'exclamation-circle'; ?>"></i>
            <?php echo nl2br(htmlspecialchars($message)); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- INVENTORY -->
    <div class="page-body">
        <section class="inv-panel">
            <div class="loading-veil" id="loadVeil"><div class="spinner"></div></div>
            <div class="inv-header">
                <div class="inv-title">
                    <div class="inv-title-icon"><i class="fas fa-box-open"></i></div>
                    Available Inventory
                </div>
                <span class="count-pill" id="itemCount"><?php echo count($inventory); ?> Items</span>
            </div>
            <div class="filter-info-strip" id="filterStrip">
                <i class="fas fa-info-circle"></i> Showing filtered results — clear filters to see full inventory.
            </div>
            <div id="invContainer">
                <?php if (!empty($inventory)): ?>
                <div class="tbl-scroll"><div class="tbl-wrap">
                <table class="inv-tbl">
                    <thead>
                        <tr>
                            <th><i class="fas fa-barcode"></i>Item Code</th>
                            <th><i class="fas fa-ruler"></i>Description</th>
                            <th><i class="fas fa-tag"></i>Brand</th>
                            <th><i class="fas fa-circle-notch"></i>Tire Size</th>
                            <th><i class="fas fa-palette"></i>Color</th>
                            <th><i class="fas fa-cog"></i>Rim</th>
                            <th><i class="fas fa-weight"></i>Wt (kg)</th>
                            <th><i class="fas fa-cube"></i>CBM</th>
                            <th><i class="fas fa-coins"></i>Unit Price (<?php echo htmlspecialchars($functionalCurrency); ?>)</th>
                            <th><i class="fas fa-cart-plus"></i>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($inventory as $item):
                        $ik               = strtolower(trim($item['icode'] ?? ''));
                        $hasSpecificPrice = isset($itemPrices[$ik]);
                        $displayPrice     = $hasSpecificPrice ? $itemPrices[$ik] : null;
                        $br               = $item['tire_brand'] ?? $item['brand'] ?? '';
                        $brk              = strtolower(trim($br));
                        $rateForItem      = isset($brandRates[$brk]) ? $brandRates[$brk] : $paymentRate;
                        $needsApproxWarning = $cusHasItemPrices && !$hasSpecificPrice;
                        $approxPrice      = $rateForItem * (float)($item['fweight'] ?? 0);
                    ?>
                        <tr data-id="<?php echo htmlspecialchars($item['id']); ?>">
                            <td class="code-cell"><?php echo htmlspecialchars($item['icode'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($item['t_size'] ?? 'N/A'); ?></td>
                            <td class="brand-cell"><?php echo htmlspecialchars($br ?: 'N/A'); ?></td>
                            <td class="tiresize-cell"><?php echo htmlspecialchars($item['tire_size'] ?? 'N/A'); ?></td>
                            <td><span class="color-badge"><?php echo htmlspecialchars($item['col'] ?? '—'); ?></span></td>
                            <td><span class="rim-badge"><?php echo htmlspecialchars($item['rim'] ?? '—'); ?></span></td>
                            <td class="num-cell"><?php echo htmlspecialchars($item['fweight'] ?? '—'); ?></td>
                            <td class="num-cell"><?php echo htmlspecialchars($item['cbm'] ?? '—'); ?></td>
                            <td class="price-cell">
                                <?php if ($hasSpecificPrice): ?>
                                    <span class="price-specific">
                                        <?php echo htmlspecialchars($currencySymbol); ?><?php echo number_format($displayPrice, 2); ?>
                                        <span class="price-specific-badge">Fixed</span>
                                    </span>
                                <?php elseif ($needsApproxWarning): ?>
                                    <span class="price-approx"
                                          title="Approx: <?php echo htmlspecialchars($currencySymbol); ?><?php echo number_format($rateForItem, 4); ?>/kg × <?php echo number_format((float)($item['fweight'] ?? 0), 2); ?> kg">
                                        <?php echo htmlspecialchars($currencySymbol); ?><?php echo number_format($approxPrice, 2); ?>
                                        <span class="price-approx-badge">Approx</span>
                                    </span>
                                <?php else: ?>
                                    <span class="price-rate"
                                          title="Rate-based: <?php echo htmlspecialchars($currencySymbol); ?><?php echo number_format($rateForItem, 4); ?>/kg">
                                        <?php echo number_format($rateForItem, 4); ?>/kg
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <input type="number" class="qty-inp" min="0" value="0" placeholder="0"
                                    data-id="<?php echo htmlspecialchars($item['id']); ?>"
                                    data-icode="<?php echo htmlspecialchars($item['icode'] ?? ''); ?>"
                                    data-size="<?php echo htmlspecialchars($item['t_size'] ?? ''); ?>"
                                    data-tiresize="<?php echo htmlspecialchars($item['tire_size'] ?? ''); ?>"
                                    data-brand="<?php echo htmlspecialchars($br); ?>"
                                    data-color="<?php echo htmlspecialchars($item['col'] ?? ''); ?>"
                                    data-rim="<?php echo htmlspecialchars($item['rim'] ?? ''); ?>"
                                    data-fweight="<?php echo htmlspecialchars($item['fweight'] ?? 0); ?>"
                                    data-cbm="<?php echo htmlspecialchars($item['cbm'] ?? 0); ?>"
                                    data-customer-price="<?php echo $hasSpecificPrice ? htmlspecialchars($displayPrice) : ''; ?>"
                                    data-needs-approx-warning="<?php echo $needsApproxWarning ? '1' : '0'; ?>"
                                    onchange="updateOrder(this)" oninput="updateOrder(this)">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div></div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Items Found</h3>
                    <p>Try adjusting your filters or check back later.</p>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

</div><!-- /right-content -->
</div><!-- /page-shell -->

<!-- ═══════════════════════════════ ORDER DOCK ════════════════════════════ -->
<div class="order-dock" id="orderDock">
    <div class="dock-header" onclick="toggleDock()">
        <div class="dock-title">
            <i class="fas fa-shopping-cart"></i>
            <?php echo $originalOid ? 'Revised Order' : 'Your Order'; ?>
            <span class="dock-sub">&nbsp;— tap to expand</span>
        </div>
        <div class="dock-stats">
            <div class="stat-tile"><div class="stat-tile-lbl">Lines</div><div class="stat-tile-val" id="dItems">0</div></div>
            <div class="stat-tile"><div class="stat-tile-lbl">Units</div><div class="stat-tile-val" id="dQty">0</div></div>
            <div class="stat-tile"><div class="stat-tile-lbl">Weight</div><div class="stat-tile-val" id="dWt">0 kg</div></div>
            <div class="stat-tile"><div class="stat-tile-lbl">CBM</div><div class="stat-tile-val" id="dCBM">0</div></div>
            <div class="stat-tile"><div class="stat-tile-lbl">Est. Value</div><div class="stat-tile-val" id="dCost">0</div></div>
            <button class="dock-toggle" type="button"><i class="fas fa-chevron-up" id="dockIcon"></i></button>
        </div>
    </div>

    <div class="dock-body" id="dockBody">
        <div class="disclaimer">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Note:</strong> Weight, CBM and estimated value are indicative only. Final figures will appear on your Proforma Invoice.
            All prices shown in <strong><?php echo htmlspecialchars($functionalCurrency); ?></strong>.
            <?php if ($originalOid): ?>
            <strong> | Revising Order: <?php echo $originalOid; ?></strong>
            <?php endif; ?>
        </div>

        <div class="dock-table-wrap">
            <table class="dock-tbl">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Brand</th>
                        <th>Tire Size</th>
                        <th>Color</th>
                        <th>Rim</th>
                        <th>Qty</th>
                        <th>Unit Wt</th>
                        <th>Unit CBM</th>
                        <th>Total Wt</th>
                        <th>Total CBM</th>
                        <th>Unit Price (<?php echo htmlspecialchars($functionalCurrency); ?>)</th>
                        <th>Line Value (<?php echo htmlspecialchars($functionalCurrency); ?>)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="dockBody2"></tbody>
            </table>
        </div>

        <div class="dock-actions">
            <form id="orderForm" method="POST">
                <input type="hidden" name="action" value="place_order">
                <input type="hidden" name="order_data" id="orderData">
                <input type="hidden" name="original_oid" value="<?php echo $originalOid; ?>">
                <button type="submit" class="dbtn dbtn-submit" onclick="return submitOrder(event)">
                    <i class="fas fa-paper-plane"></i>
                    <?php echo $originalOid ? 'Place Revised Order' : 'Place Order'; ?>
                </button>
                <button type="button" class="dbtn dbtn-clear" onclick="clearOrder()">
                    <i class="fas fa-trash"></i> Clear All
                </button>
            </form>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════ APPROX PRICE MODAL ═══════════════════ -->
<div class="approx-modal-backdrop" id="approxModalBackdrop" role="dialog" aria-modal="true" aria-labelledby="approxModalTitle">
    <div class="approx-modal">
        <div class="approx-modal-hdr">
            <div class="approx-modal-icon"><i class="fas fa-tag"></i></div>
            <div class="approx-modal-hdr-txt">
                <div class="approx-modal-eyebrow">Pricing Notice</div>
                <div class="approx-modal-title" id="approxModalTitle">Approximate Price</div>
            </div>
        </div>
        <div class="approx-modal-body">
            <div class="approx-modal-icode" id="approxModalIcode">
                <i class="fas fa-barcode"></i>
                <span id="approxModalIcodeText">—</span>
            </div>
            <div class="approx-modal-msg">
                This tire code has an approximate price for your order purpose. A
                <strong>FIXED PRICE</strong> will be confirmed and provided in the
                <strong>PROFORMA INVOICE</strong>.
            </div>
            <div class="approx-modal-note">
                <i class="fas fa-info-circle"></i>
                You may proceed to add this item to your order. The final unit price will be set by our team
                before the invoice is issued.
            </div>
        </div>
        <div class="approx-modal-ftr">
            <button type="button" class="approx-modal-cancel" id="approxModalCancel">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button type="button" class="approx-modal-ok" id="approxModalOk">
                <i class="fas fa-check"></i> OK, Add to Order
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════ JS ═══════════════════════════════════ -->
<script>
/* ── PHP → JS constants ──────────────────────────────────────────────────── */
const icodeOptions      = <?php echo json_encode($icodes    ?? []); ?>;
const tireSizeOptions   = <?php echo json_encode($tireSizes ?? []); ?>;
const defaultRate       = <?php echo json_encode($paymentRate); ?>;
const brandRates        = <?php echo json_encode($brandRates); ?>;
const currencySymbol    = <?php echo json_encode($currencySymbol); ?>;
const currencyCode      = <?php echo json_encode($functionalCurrency); ?>;
let cusHasItemPrices    = <?php echo json_encode($cusHasItemPrices); ?>;
const allowedBrands     = <?php echo json_encode($allowedBrands); ?>;

const CAP20 = 18000, CAP40 = 25000, FILL20_MAX = 272, FILL40_MAX = 332;

let orderItems   = new Map();
let dockOpen     = false;
let searchTimer  = null;
let updateTimer  = null;
let currentSort  = '';

/* ── Approx-price modal state ─────────────────────────────────────────────── */
let approxModalPending = null;

/* ── Pricing helpers ─────────────────────────────────────────────────────── */
function calcUnitPrice(brand, fweight, customerPrice) {
    if (customerPrice !== null && customerPrice !== '' && parseFloat(customerPrice) > 0) {
        return parseFloat(customerPrice);
    }
    return brandRate(brand) * (parseFloat(fweight) || 0);
}

function brandRate(brand) {
    if (!brand) return defaultRate;
    const k = brand.toLowerCase().trim();
    return brandRates.hasOwnProperty(k) ? (parseFloat(brandRates[k]) || defaultRate) : defaultRate;
}

function fmtCurrency(amount, decimals = 2) {
    return currencySymbol + (+amount).toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });
}

/* ── INIT ────────────────────────────────────────────────────────────────── */
$(document).ready(function () {
    initAC('#icode_select',     icodeOptions);
    initAC('#tire_size_select', tireSizeOptions);
    $('.rf').on('input change', function () {
        updateFilterUI();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(doSearch, 320);
    });
    $('#sortRow').on('click', '.sort-chip', function () {
        $('.sort-chip').removeClass('active');
        $(this).addClass('active');
        currentSort = $(this).data('sort');
        doSearch();
    });
    updateFilterUI();
    loadState();
    autoFillOrderFromUrl();
    initApproxModal();
});

function initAC(sel, data) {
    $(sel).autocomplete({
        source: (req, resp) => resp(
            data.filter(v => v.toLowerCase().includes(req.term.toLowerCase())).slice(0, 20)
        ),
        minLength: 1, delay: 80,
        select: (e, ui) => {
            $(sel).val(ui.item.value).addClass('active-filter');
            updateFilterUI(); doSearch(); return false;
        },
    });
}

/* ── APPROX PRICE MODAL ──────────────────────────────────────────────────── */
function initApproxModal() {
    document.getElementById('approxModalOk').addEventListener('click', function () {
        if (approxModalPending) {
            const { input, qty } = approxModalPending;
            approxModalPending = null;
            closeApproxModal();
            input.dataset.approxAcknowledged = '1';
            _doUpdateOrder(input, qty);
        }
    });
    document.getElementById('approxModalCancel').addEventListener('click', function () {
        if (approxModalPending) {
            const { input } = approxModalPending;
            approxModalPending = null;
            input.value = input.dataset.prevValue || '0';
            input.classList.remove('filled');
        }
        closeApproxModal();
    });
    document.getElementById('approxModalBackdrop').addEventListener('click', function (e) {
        if (e.target === this) document.getElementById('approxModalCancel').click();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && document.getElementById('approxModalBackdrop').classList.contains('visible')) {
            document.getElementById('approxModalCancel').click();
        }
    });
}
function openApproxModal(icode) {
    document.getElementById('approxModalIcodeText').textContent = icode || '—';
    document.getElementById('approxModalBackdrop').classList.add('visible');
    setTimeout(() => document.getElementById('approxModalOk').focus(), 80);
}
function closeApproxModal() {
    document.getElementById('approxModalBackdrop').classList.remove('visible');
}

/* ── CONTAINER VISUALIZER ────────────────────────────────────────────────── */
function updateContainers(wt) {
    const c20 = document.getElementById('card20'), c40 = document.getElementById('card40');
    if (wt <= 0) { [c20,c40].forEach(c=>c.classList.remove('show','overloaded')); setFill('fill20',0,FILL20_MAX); setFill('fill40',0,FILL40_MAX); return; }
    if (wt <= CAP20) {
        show(c20); hide(c40);
        const p = (wt/CAP20)*100;
        setTxt('pct20', Math.round(p)+'%'); setTxt('wt20', fmtN(wt)+' kg');
        setFill('fill20', p, FILL20_MAX); c20.classList.remove('overloaded');
    } else if (wt <= CAP40) {
        show(c40); hide(c20);
        const p = (wt/CAP40)*100;
        setTxt('pct40', Math.round(p)+'%'); setTxt('wt40', fmtN(wt)+' kg');
        setFill('fill40', p, FILL40_MAX); c40.classList.remove('overloaded');
    } else {
        show(c40); hide(c20); c40.classList.add('overloaded');
        setTxt('pct40','100%+'); setTxt('wt40', fmtN(wt)+' kg (+'+fmtN(wt-CAP40)+')');
        setFill('fill40',100,FILL40_MAX);
    }
}
function show(el){el.classList.add('show');}
function hide(el){el.classList.remove('show','overloaded');}
function setTxt(id,v){const e=document.getElementById(id);if(e)e.textContent=v;}
function setFill(id,pct,maxW){const el=document.getElementById(id);if(el)el.setAttribute('width',((Math.min(pct,100)/100)*maxW).toFixed(1));}
function fmtN(n){return (+n).toLocaleString('en-US',{maximumFractionDigits:0});}
function fmtD(n,d=2){return (+n).toLocaleString('en-US',{minimumFractionDigits:d,maximumFractionDigits:d});}
function esc(t){return String(t).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));}

/* ── FILTER UI ───────────────────────────────────────────────────────────── */
function updateFilterUI() {
    const fields=[{id:'icode_select',label:'Code'},{id:'tire_size_select',label:'Size'},{id:'brand_select',label:'Brand'},{id:'col_select',label:'Color'},{id:'rim_select',label:'Rim'}];
    let count=0; const chips=[];
    fields.forEach(f=>{
        const el=document.getElementById(f.id), v=el.value.trim(), active=v&&v!=='all';
        if(active){el.classList.add('active-filter');count++;chips.push(`<div class="filter-chip" onclick="clearOne('${f.id}')"><i class="fas fa-times"></i>${esc(v)}</div>`);}
        else el.classList.remove('active-filter');
    });
    document.getElementById('chipRow').innerHTML=chips.join('');
    document.getElementById('filterStrip').classList.toggle('show',count>0);
}
function clearOne(id){const el=document.getElementById(id);el.value=el.tagName==='SELECT'?'all':'';updateFilterUI();doSearch();}

/* ── AJAX SEARCH ─────────────────────────────────────────────────────────── */
function doSearch() {
    document.getElementById('spinIco').style.display='inline-block';
    document.getElementById('loadVeil').classList.add('on');
    const p=new URLSearchParams({ajax:'1',sort:currentSort,icode_select:document.getElementById('icode_select').value,tire_size_select:document.getElementById('tire_size_select').value,brand_select:document.getElementById('brand_select').value,col_select:document.getElementById('col_select').value,rim_select:document.getElementById('rim_select').value});
    fetch('?'+p.toString())
        .then(r=>r.json())
        .then(d=>{
            if(d.success){
                if(typeof d.cusHasItemPrices!=='undefined') cusHasItemPrices=d.cusHasItemPrices;
                renderTable(d.data);
                const cb=document.getElementById('itemCount');
                if(cb){cb.textContent=d.count+' Items';cb.classList.add('pop');setTimeout(()=>cb.classList.remove('pop'),450);}
            }
        }).catch(console.error)
        .finally(()=>{document.getElementById('spinIco').style.display='none';document.getElementById('loadVeil').classList.remove('on');});
}

/* ── RENDER TABLE ────────────────────────────────────────────────────────── */
function renderTable(items) {
    const wrap=document.getElementById('invContainer');
    if(!items.length){wrap.innerHTML=`<div class="empty-state"><i class="fas fa-search"></i><h3>No Matching Items</h3><p>Try adjusting or clearing your filters.</p></div>`;return;}
    const saved={};
    document.querySelectorAll('.qty-inp').forEach(i=>{if(parseInt(i.value)>0)saved[i.dataset.id]=i.value;});
    const rows=items.map(item=>{
        const q=saved[item.id]||'0', hv=saved[item.id]?'filled':'', br=item.tire_brand||item.brand||'';
        const cp=(item.customer_price!==null&&item.customer_price!==undefined)?item.customer_price:'';
        const needsApprox=cusHasItemPrices&&(cp===''||cp===null);
        const bk=br.toLowerCase().trim();
        const rate=(brandRates.hasOwnProperty(bk)?brandRates[bk]:defaultRate)||0;
        const fw=parseFloat(item.fweight)||0;
        const approxDollar=rate*fw;
        let priceHtml;
        if(cp!==''&&parseFloat(cp)>0){
            priceHtml=`<span class="price-specific">${esc(currencySymbol)}${parseFloat(cp).toFixed(2)}<span class="price-specific-badge">Fixed</span></span>`;
        } else if(needsApprox){
            priceHtml=`<span class="price-approx" title="Approx: ${esc(currencySymbol)}${rate.toFixed(4)}/kg × ${fw.toFixed(2)} kg">${esc(currencySymbol)}${approxDollar.toFixed(2)}<span class="price-approx-badge">Approx</span></span>`;
        } else {
            priceHtml=`<span class="price-rate" title="Rate: ${esc(currencySymbol)}${rate.toFixed(4)}/kg">${rate.toFixed(4)}/kg</span>`;
        }
        return `<tr data-id="${esc(item.id)}" class="${saved[item.id]?'selected':''}">
            <td class="code-cell">${esc(item.icode||'N/A')}</td>
            <td>${esc(item.t_size||'N/A')}</td>
            <td class="brand-cell">${esc(br||'N/A')}</td>
            <td class="tiresize-cell">${esc(item.tire_size||'N/A')}</td>
            <td><span class="color-badge">${esc(item.col||'—')}</span></td>
            <td><span class="rim-badge">${esc(item.rim||'—')}</span></td>
            <td class="num-cell">${esc(item.fweight||'—')}</td>
            <td class="num-cell">${esc(item.cbm||'—')}</td>
            <td class="price-cell">${priceHtml}</td>
            <td><input type="number" class="qty-inp ${hv}" min="0" value="${q}" placeholder="0"
                data-id="${esc(item.id)}" data-icode="${esc(item.icode||'')}" data-size="${esc(item.t_size||'')}"
                data-tiresize="${esc(item.tire_size||'')}" data-brand="${esc(br)}"
                data-color="${esc(item.col||'')}" data-rim="${esc(item.rim||'')}"
                data-fweight="${esc(item.fweight||0)}" data-cbm="${esc(item.cbm||0)}"
                data-customer-price="${esc(cp)}"
                data-needs-approx-warning="${needsApprox?'1':'0'}"
                onchange="updateOrder(this)" oninput="updateOrder(this)"></td>
        </tr>`;
    }).join('');
    wrap.innerHTML=`<div class="tbl-scroll"><div class="tbl-wrap"><table class="inv-tbl">
        <thead><tr>
            <th><i class="fas fa-barcode"></i>Item Code</th>
            <th><i class="fas fa-ruler"></i>Description</th>
            <th><i class="fas fa-tag"></i>Brand</th>
            <th><i class="fas fa-circle-notch"></i>Tire Size</th>
            <th><i class="fas fa-palette"></i>Color</th>
            <th><i class="fas fa-cog"></i>Rim</th>
            <th><i class="fas fa-weight"></i>Wt (kg)</th>
            <th><i class="fas fa-cube"></i>CBM</th>
            <th><i class="fas fa-coins"></i>Unit Price (${esc(currencyCode)})</th>
            <th><i class="fas fa-cart-plus"></i>Qty</th>
        </tr></thead>
        <tbody>${rows}</tbody>
    </table></div></div>`;
}

/* ── CLEAR FILTERS ───────────────────────────────────────────────────────── */
function clearFilters() {
    ['icode_select','tire_size_select'].forEach(id=>document.getElementById(id).value='');
    ['brand_select','col_select','rim_select'].forEach(id=>document.getElementById(id).value='all');
    $('.sort-chip').removeClass('active'); $('.sort-chip[data-sort=""]').addClass('active');
    currentSort=''; updateFilterUI(); doSearch();
}

/* ── DOCK TOGGLE ─────────────────────────────────────────────────────────── */
function toggleDock() {
    dockOpen=!dockOpen;
    document.getElementById('dockBody').classList.toggle('open',dockOpen);
    document.getElementById('dockIcon').className=dockOpen?'fas fa-chevron-down':'fas fa-chevron-up';
}

/* ── ORDER UPDATE ────────────────────────────────────────────────────────── */
function updateOrder(input) {
    clearTimeout(updateTimer);
    updateTimer=setTimeout(()=>{
        const qty=parseInt(input.value)||0;
        if(qty<=0){
            input.classList.remove('filled');
            input.closest('tr')?.classList.remove('selected');
            orderItems.delete(input.dataset.id);
            refreshSummary(); saveState(); return;
        }
        const needsWarn=input.dataset.needsApproxWarning==='1';
        const acknowledged=input.dataset.approxAcknowledged==='1';
        if(needsWarn&&!acknowledged){
            input.dataset.prevValue='0';
            approxModalPending={input,qty};
            openApproxModal(input.dataset.icode||''); return;
        }
        _doUpdateOrder(input,qty);
    },140);
}

function _doUpdateOrder(input,qty){
    const row=input.closest('tr');
    input.classList.add('filled');
    if(row) row.classList.add('selected');
    orderItems.set(input.dataset.id,{
        id:input.dataset.id, icode:input.dataset.icode,
        size:input.dataset.size, tiresize:input.dataset.tiresize,
        brand:input.dataset.brand, color:input.dataset.color,
        rim:input.dataset.rim, fweight:parseFloat(input.dataset.fweight)||0,
        cbm:parseFloat(input.dataset.cbm)||0, quantity:qty,
        customerPrice:input.dataset.customerPrice||'',
    });
    refreshSummary(); saveState();
}

/* ── REFRESH SUMMARY ─────────────────────────────────────────────────────── */
function refreshSummary() {
    const items=Array.from(orderItems.values());
    const qty=items.reduce((s,i)=>s+i.quantity,0);
    const wt=items.reduce((s,i)=>s+i.quantity*i.fweight,0);
    const cbm=items.reduce((s,i)=>s+i.quantity*i.cbm,0);
    const cost=items.reduce((s,i)=>s+i.quantity*calcUnitPrice(i.brand,i.fweight,i.customerPrice),0);
    setTxt('dItems',items.length.toLocaleString()); setTxt('dQty',qty.toLocaleString());
    document.getElementById('dWt').textContent=fmtD(wt)+' kg';
    document.getElementById('dCBM').textContent=fmtD(cbm,3);
    document.getElementById('dCost').textContent=fmtCurrency(cost);
    updateContainers(wt);

    const tbody=document.getElementById('dockBody2');
    tbody.innerHTML=items.length?items.map(item=>{
        const unitPrice=calcUnitPrice(item.brand,item.fweight,item.customerPrice);
        const lineValue=item.quantity*unitPrice;
        const isFixed=item.customerPrice!==''&&parseFloat(item.customerPrice)>0;
        const isApprox=!isFixed&&cusHasItemPrices;
        let priceLabel;
        if(isFixed){
            priceLabel=`<span style="color:#166534;font-weight:800;">${esc(currencySymbol)}${fmtD(unitPrice)}</span>
                <span style="font-size:9px;background:#dcfce7;color:#15803d;border-radius:10px;padding:1px 5px;font-weight:800;">Fixed</span>`;
        } else if(isApprox){
            priceLabel=`<span style="color:#92400e;font-weight:800;">${esc(currencySymbol)}${fmtD(unitPrice)}</span>
                <span style="font-size:9px;background:#fef3c7;color:#b45309;border:1px solid #fcd34d;border-radius:10px;padding:1px 5px;font-weight:800;">Approx</span>`;
        } else {
            priceLabel=`<span style="color:#888;font-size:11px;">${esc(currencySymbol)}${fmtD(unitPrice)}</span>`;
        }
        return `<tr>
            <td class="dc">${esc(item.icode)}</td>
            <td>${esc(item.size)}</td>
            <td>${esc(item.brand)}</td>
            <td>${esc(item.tiresize||'N/A')}</td>
            <td>${esc(item.color||'—')}</td>
            <td>${esc(item.rim||'—')}</td>
            <td><input type="number" class="dock-qty" min="1" value="${item.quantity}"
                onchange="updateQty(this,'${item.id}')" oninput="updateQty(this,'${item.id}')"></td>
            <td class="dw dr">${fmtD(item.fweight)} kg</td>
            <td class="dr">${fmtD(item.cbm,3)}</td>
            <td class="dw dr">${fmtD(item.quantity*item.fweight)} kg</td>
            <td class="dr">${fmtD(item.quantity*item.cbm,3)}</td>
            <td class="dr">${priceLabel}</td>
            <td class="dv">${fmtCurrency(lineValue)}</td>
            <td><button type="button" class="rm-btn" onclick="removeItem('${item.id}')"><i class="fas fa-times"></i></button></td>
        </tr>`;
    }).join(''):`<tr><td colspan="14" style="text-align:center;padding:2rem;color:#ccc;font-weight:600;font-size:13px;"><i class="fas fa-shopping-cart" style="font-size:2rem;display:block;margin-bottom:8px;opacity:.3;"></i>No items added yet</td></tr>`;
    document.getElementById('orderDock').classList.toggle('open',items.length>0);
}

function updateQty(input,id){
    const q=parseInt(input.value)||0;
    if(q<=0){removeItem(id);return;}
    const item=orderItems.get(id);
    if(item){item.quantity=q;orderItems.set(id,item);const m=document.querySelector(`.qty-inp[data-id="${id}"]`);if(m)m.value=q;refreshSummary();saveState();}
}
function removeItem(id){
    orderItems.delete(id);
    const inp=document.querySelector(`.qty-inp[data-id="${id}"]`);
    if(inp){inp.value=0;inp.classList.remove('filled');inp.closest('tr')?.classList.remove('selected');inp.dataset.approxAcknowledged='0';}
    refreshSummary();saveState();
}
function clearOrder(){
    if(!confirm('Clear all items from your order?'))return;
    document.querySelectorAll('.qty-inp').forEach(i=>{i.value=0;i.classList.remove('filled');i.closest('tr')?.classList.remove('selected');i.dataset.approxAcknowledged='0';});
    orderItems.clear();refreshSummary();saveState();
}

/* ── SUBMIT ──────────────────────────────────────────────────────────────── */
function submitOrder(event){
    event.preventDefault();
    if(!orderItems.size){alert('Please add at least one item to your order.');return false;}
    const items=Array.from(orderItems.values());
    const qty=items.reduce((s,i)=>s+i.quantity,0);
    const wt=items.reduce((s,i)=>s+i.quantity*i.fweight,0);
    const cbm=items.reduce((s,i)=>s+i.quantity*i.cbm,0);
    const cost=items.reduce((s,i)=>s+i.quantity*calcUnitPrice(i.brand,i.fweight,i.customerPrice),0);
    const ctype=wt<=CAP20?'20 FT':(wt<=CAP40?'40 FT':'40 FT — OVERLOADED');
    const warn=wt>CAP40?`\n\n⚠ OVERLOAD: exceeds 40 FT capacity by ${fmtD(wt-CAP40)} kg!`:'';
    const msg=`Confirm Order:\n\n• ${items.length} product line(s)\n• ${qty.toLocaleString()} total units\n• ${fmtD(wt)} kg total weight\n• ${fmtD(cbm,3)} total CBM\n• Est. Value: ${fmtCurrency(cost)} (${currencyCode})\n• Container: ${ctype}${warn}`;
    if(confirm(msg)){document.getElementById('orderData').value=JSON.stringify(items);sessionStorage.removeItem('orderItems');document.getElementById('orderForm').submit();return true;}
    return false;
}

/* ── PERSISTENCE ─────────────────────────────────────────────────────────── */
function saveState(){sessionStorage.setItem('orderItems',JSON.stringify(Array.from(orderItems.values())));}
function loadState(){
    const s=sessionStorage.getItem('orderItems');if(!s)return;
    try{JSON.parse(s).forEach(item=>{orderItems.set(item.id,item);const inp=document.querySelector(`.qty-inp[data-id="${item.id}"]`);if(inp){inp.value=item.quantity;inp.classList.add('filled');inp.closest('tr')?.classList.add('selected');inp.dataset.approxAcknowledged='1';}});refreshSummary();}
    catch(ex){sessionStorage.removeItem('orderItems');}
}

/* ── AUTO-FILL FROM URL ──────────────────────────────────────────────────── */
function autoFillOrderFromUrl(){
    const urlParams=new URLSearchParams(window.location.search);
    const itemsParam=urlParams.get('items');if(!itemsParam)return;
    let items;try{items=JSON.parse(decodeURIComponent(itemsParam));}catch(e){return;}
    if(!Array.isArray(items))return;
    let filled=0;
    items.forEach(item=>{
        if(!item.icode||!item.qty||item.qty<=0)return;
        const qty=parseInt(item.qty);if(isNaN(qty)||qty<1)return;
        const input=Array.from(document.querySelectorAll('.qty-inp')).find(inp=>inp.dataset.icode&&inp.dataset.icode.trim().toUpperCase()===String(item.icode).trim().toUpperCase());
        if(input){input.value=qty;input.classList.add('filled');input.closest('tr')?.classList.add('selected');orderItems.set(input.dataset.id,{id:input.dataset.id,icode:input.dataset.icode,size:input.dataset.size,tiresize:input.dataset.tiresize,brand:input.dataset.brand,color:input.dataset.color||'',rim:input.dataset.rim||'',fweight:parseFloat(input.dataset.fweight)||0,cbm:parseFloat(input.dataset.cbm)||0,quantity:qty,customerPrice:input.dataset.customerPrice||''});filled++;}
    });
    if(filled>0){refreshSummary();if(!dockOpen)toggleDock();}
    if(window.history&&window.history.replaceState&&urlParams.has('items')){urlParams.delete('items');window.history.replaceState({},'',window.location.pathname+(urlParams.toString()?'?'+urlParams.toString():''));}
}
</script>
</body>
</html>
<?php $pdo = null; ?>