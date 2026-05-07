<?php
// customer_items.php
session_start();
include('include/config.php');

// ── Auth ─────────────────────────────────────────────────────────────────────
if (!isset($_SESSION["aid"])) {
    header("Location: index.php");
    exit();
}
$adminId = intval($_SESSION["aid"]);

$stmt = mysqli_prepare($con, "SELECT * FROM admin WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $adminId);
mysqli_stmt_execute($stmt);
$adminData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// ── Account-manager scope ────────────────────────────────────────────────────
$isAccountManager = false;
$acmRef           = '';
$acmCustomerIds   = [];

$stmt = mysqli_prepare($con, "SELECT acm_ref FROM account_managers WHERE id = ? AND status = 'active'");
mysqli_stmt_bind_param($stmt, "i", $adminId);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($r)) {
    $isAccountManager = true;
    $acmRef = $row['acm_ref'];
    $s2 = mysqli_prepare($con, "SELECT cus_id FROM users WHERE acm_ref = ?");
    mysqli_stmt_bind_param($s2, "s", $acmRef);
    mysqli_stmt_execute($s2);
    $r2 = mysqli_stmt_get_result($s2);
    while ($c = mysqli_fetch_assoc($r2)) { $acmCustomerIds[] = $c['cus_id']; }
    mysqli_stmt_close($s2);
}
mysqli_stmt_close($stmt);

// ── Helper: insert a revision record ────────────────────────────────────────
function insertRevision($con, $cus_id, $icode, $price, $adminId) {
    $stmt = mysqli_prepare($con, "SELECT COALESCE(MAX(revision_no), 0) + 1 FROM customer_item_revisions WHERE cus_id = ? AND icode = ?");
    mysqli_stmt_bind_param($stmt, "ss", $cus_id, $icode);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $nextRev);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($con, "INSERT INTO customer_item_revisions (cus_id, icode, revision_no, price, saved_by, saved_at) VALUES (?, ?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "ssiid", $cus_id, $icode, $nextRev, $price, $adminId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $nextRev;
}

// ── Helper: check if all order items have prices → confirm order ──────────────
// Returns true if the order was just confirmed, false otherwise.
function checkAndConfirmOrder($con, $orderId) {
    if (empty($orderId)) return false;

    $escOid = mysqli_real_escape_string($con, $orderId);

    // Get the order's customer_id and current status
    $rOrd = mysqli_query($con,
        "SELECT o.status, u.cus_id
         FROM tire_orders o
         LEFT JOIN users u ON u.id = o.customer_id
         WHERE o.order_id = '$escOid'
         LIMIT 1");
    if (!$rOrd || !($ord = mysqli_fetch_assoc($rOrd))) return false;
    if ($ord['status'] !== 'price_pending') return false;

    $cus_id = mysqli_real_escape_string($con, $ord['cus_id']);

    // Fetch all icodes for this order
    $rItems = mysqli_query($con, "SELECT icode FROM tire_order_items WHERE order_id = '$escOid'");
    $icodes = [];
    while ($ir = mysqli_fetch_assoc($rItems)) {
        if (!empty($ir['icode'])) $icodes[] = mysqli_real_escape_string($con, $ir['icode']);
    }
    if (empty($icodes)) return false;

    // Check every icode has a price set for this customer
    $inList = "'" . implode("','", $icodes) . "'";
    $rPriced = mysqli_query($con,
        "SELECT COUNT(*) AS cnt
         FROM customer_items
         WHERE cus_id = '$cus_id'
           AND icode IN ($inList)
           AND price IS NOT NULL
           AND price > 0");
    $pricedCount = 0;
    if ($rPriced && ($pr = mysqli_fetch_assoc($rPriced))) {
        $pricedCount = intval($pr['cnt']);
    }

    if ($pricedCount >= count($icodes)) {
        // All items priced — update order status
        mysqli_query($con,
            "UPDATE tire_orders SET status = 'cus_confirmed' WHERE order_id = '$escOid'");
        return true;
    }
    return false;
}

// ── SAVE handler (AJAX POST) ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_price') {
    header('Content-Type: application/json');
    $cus_id  = mysqli_real_escape_string($con, trim($_POST['cus_id']    ?? ''));
    $icode   = mysqli_real_escape_string($con, trim($_POST['icode']     ?? ''));
    $price   = trim($_POST['price'] ?? '');
    $orderId = trim($_POST['order_id'] ?? '');

    if ($cus_id === '' || $icode === '' || $price === '') {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }
    if (!is_numeric($price) || floatval($price) < 0) {
        echo json_encode(['success' => false, 'message' => 'Price must be a valid non-negative number.']);
        exit;
    }
    $priceF = floatval($price);

    $stmtCheck = mysqli_prepare($con, "SELECT id FROM customer_items WHERE cus_id = ? AND icode = ?");
    mysqli_stmt_bind_param($stmtCheck, "ss", $cus_id, $icode);
    mysqli_stmt_execute($stmtCheck);
    mysqli_stmt_store_result($stmtCheck);
    $exists = mysqli_stmt_num_rows($stmtCheck) > 0;
    mysqli_stmt_close($stmtCheck);

    if ($exists) {
        // Update existing price
        $stmtUpd = mysqli_prepare($con, "UPDATE customer_items SET price = ? WHERE cus_id = ? AND icode = ?");
        if ($stmtUpd) {
            mysqli_stmt_bind_param($stmtUpd, "dss", $priceF, $cus_id, $icode);
            if (!mysqli_stmt_execute($stmtUpd)) {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_stmt_error($stmtUpd)]);
                mysqli_stmt_close($stmtUpd);
                exit;
            }
            mysqli_stmt_close($stmtUpd);
        }
    } else {
        $stmtIns = mysqli_prepare($con, "INSERT INTO customer_items (cus_id, icode, price) VALUES (?, ?, ?)");
        if ($stmtIns) {
            mysqli_stmt_bind_param($stmtIns, "ssd", $cus_id, $icode, $priceF);
            if (!mysqli_stmt_execute($stmtIns)) {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_stmt_error($stmtIns)]);
                mysqli_stmt_close($stmtIns);
                exit;
            }
            mysqli_stmt_close($stmtIns);
        } else {
            echo json_encode(['success' => false, 'message' => 'Query prepare failed.']);
            exit;
        }
    }

    $revNo         = insertRevision($con, $cus_id, $icode, $priceF, $adminId);
    $orderConfirmed = false;
    if ($orderId !== '') {
        $orderConfirmed = checkAndConfirmOrder($con, $orderId);
    }

    echo json_encode([
        'success'         => true,
        'message'         => 'Price saved.',
        'revision_no'     => $revNo,
        'order_confirmed' => $orderConfirmed,
    ]);
    exit;
}

// ── BULK SAVE handler ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_save') {
    header('Content-Type: application/json');
    $cus_id  = mysqli_real_escape_string($con, trim($_POST['cus_id'] ?? ''));
    $prices  = $_POST['prices'] ?? [];
    $orderId = trim($_POST['order_id'] ?? '');
    $saved   = 0; $errors = 0;

    foreach ($prices as $icode => $price) {
        $icode  = mysqli_real_escape_string($con, trim($icode));
        $price  = trim($price);
        if ($icode === '' || $price === '' || !is_numeric($price)) { $errors++; continue; }
        $priceF = floatval($price);

        $stmtCheck = mysqli_prepare($con, "SELECT id FROM customer_items WHERE cus_id = ? AND icode = ?");
        mysqli_stmt_bind_param($stmtCheck, "ss", $cus_id, $icode);
        mysqli_stmt_execute($stmtCheck);
        mysqli_stmt_store_result($stmtCheck);
        $exists = mysqli_stmt_num_rows($stmtCheck) > 0;
        mysqli_stmt_close($stmtCheck);

        if ($exists) {
            $stmtUpd = mysqli_prepare($con, "UPDATE customer_items SET price = ? WHERE cus_id = ? AND icode = ?");
            if ($stmtUpd) {
                mysqli_stmt_bind_param($stmtUpd, "dss", $priceF, $cus_id, $icode);
                if (!mysqli_stmt_execute($stmtUpd)) { $errors++; mysqli_stmt_close($stmtUpd); continue; }
                mysqli_stmt_close($stmtUpd);
            } else { $errors++; continue; }
        } else {
            $stmtIns = mysqli_prepare($con, "INSERT INTO customer_items (cus_id, icode, price) VALUES (?, ?, ?)");
            if ($stmtIns) {
                mysqli_stmt_bind_param($stmtIns, "ssd", $cus_id, $icode, $priceF);
                if (!mysqli_stmt_execute($stmtIns)) { $errors++; mysqli_stmt_close($stmtIns); continue; }
                mysqli_stmt_close($stmtIns);
            } else { $errors++; continue; }
        }

        insertRevision($con, $cus_id, $icode, $priceF, $adminId);
        $saved++;
    }

    $orderConfirmed = false;
    if ($orderId !== '' && $saved > 0) {
        $orderConfirmed = checkAndConfirmOrder($con, $orderId);
    }

    echo json_encode([
        'success'         => true,
        'saved'           => $saved,
        'errors'          => $errors,
        'order_confirmed' => $orderConfirmed,
    ]);
    exit;
}

// ── FETCH REVISIONS handler (AJAX GET) ──────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'get_revisions') {
    header('Content-Type: application/json');
    $cus_id = trim($_GET['cus_id'] ?? '');
    $icode  = trim($_GET['icode']  ?? '');
    if ($cus_id === '' || $icode === '') {
        echo json_encode(['success' => false, 'revisions' => []]);
        exit;
    }
    $stmt = mysqli_prepare($con,
        "SELECT r.revision_no, r.price, r.saved_at, a.fullname AS saved_by
         FROM customer_item_revisions r
         LEFT JOIN admin a ON a.id = r.saved_by
         WHERE r.cus_id = ? AND r.icode = ?
         ORDER BY r.revision_no DESC");
    mysqli_stmt_bind_param($stmt, "ss", $cus_id, $icode);
    mysqli_stmt_execute($stmt);
    $res  = mysqli_stmt_get_result($stmt);
    $revs = [];
    while ($row = mysqli_fetch_assoc($res)) { $revs[] = $row; }
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => true, 'revisions' => $revs]);
    exit;
}

// ── Build customer list ──────────────────────────────────────────────────────
$customers = [];
if ($isAccountManager && !empty($acmCustomerIds)) {
    $placeholders = implode(',', array_fill(0, count($acmCustomerIds), '?'));
    $types        = str_repeat('s', count($acmCustomerIds));
    $stmt = mysqli_prepare($con, "SELECT id, cus_id, fullName, company_rn, Country
                                   FROM users WHERE cus_id IN ($placeholders) AND status = 1
                                   ORDER BY fullName");
    mysqli_stmt_bind_param($stmt, $types, ...$acmCustomerIds);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($r)) { $customers[] = $row; }
    mysqli_stmt_close($stmt);
} else {
    $r = mysqli_query($con, "SELECT id, cus_id, fullName, company_rn, Country
                              FROM users WHERE status = 1 ORDER BY fullName");
    while ($row = mysqli_fetch_assoc($r)) { $customers[] = $row; }
}

// ── ORDER-MODE: auto-filter from a price_pending order ──────────────────────
$orderMode      = false;
$orderData      = null;
$orderIcodes    = [];

$incomingOrderId = isset($_GET['order_id']) ? trim($_GET['order_id']) : '';

if ($incomingOrderId !== '') {
    $escOid = mysqli_real_escape_string($con, $incomingOrderId);

    $rOrd = mysqli_query($con, "SELECT tord.order_id, tord.order_reference, tord.order_date, tord.status,
                                       u.cus_id, u.fullname AS customer_name
                                FROM tire_orders tord
                                LEFT JOIN users u ON u.id = tord.customer_id
                                WHERE tord.order_id = '$escOid'
                                LIMIT 1");
    if ($rOrd && $ordRow = mysqli_fetch_assoc($rOrd)) {
        $orderData = $ordRow;
        $orderMode = true;

        if (!isset($_GET['cus_id']) || trim($_GET['cus_id']) === '') {
            $_GET['cus_id'] = $ordRow['cus_id'];
        }

        $rItems = mysqli_query($con, "SELECT icode FROM tire_order_items WHERE order_id = '$escOid'");
        if ($rItems) {
            while ($ir = mysqli_fetch_assoc($rItems)) {
                if (!empty($ir['icode'])) $orderIcodes[] = $ir['icode'];
            }
        }
    }
}

// ── Selected customer & filters ──────────────────────────────────────────────
$selectedCusId  = isset($_GET['cus_id']) ? trim($_GET['cus_id']) : '';
$searchIcode    = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterHasPrice = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';
$filterBrand    = isset($_GET['brand']) ? trim($_GET['brand']) : 'all';
$filterColor    = isset($_GET['colour']) ? trim($_GET['colour']) : 'all';
$filterRimSize  = isset($_GET['rim']) ? trim($_GET['rim']) : 'all';
$filterTireSize = isset($_GET['tire_size']) ? trim($_GET['tire_size']) : '';
$filterItemCode = isset($_GET['icode']) ? trim($_GET['icode']) : '';

// ── Fetch unique filter options from tire_details ───────────────────────────
$brands = $colors = $rims = [];
$r = mysqli_query($con, "SELECT DISTINCT Brand FROM tire_details WHERE Brand IS NOT NULL AND Brand != '' ORDER BY Brand");
while ($row = mysqli_fetch_assoc($r)) { $brands[] = $row['Brand']; }

$r = mysqli_query($con, "SELECT DISTINCT colour FROM tire_details WHERE colour IS NOT NULL AND colour != '' ORDER BY colour");
while ($row = mysqli_fetch_assoc($r)) { $colors[] = $row['colour']; }

$r = mysqli_query($con, "SELECT DISTINCT rim FROM tire_details WHERE rim IS NOT NULL AND rim != '' ORDER BY rim");
while ($row = mysqli_fetch_assoc($r)) { $rims[] = $row['rim']; }

// ── Fetch tire_details joined with customer_items & latest revision ──────────
$tireRows         = [];
$selectedCustomer = null;

if ($selectedCusId !== '') {
    foreach ($customers as $c) {
        if ($c['cus_id'] === $selectedCusId) { $selectedCustomer = $c; break; }
    }

    if ($selectedCustomer) {
        $escapedCusId = mysqli_real_escape_string($con, $selectedCusId);

        $whereSearch = '';
        if ($searchIcode !== '') {
            $esc         = mysqli_real_escape_string($con, $searchIcode);
            $whereSearch = " AND (td.icode LIKE '%$esc%' OR td.Description LIKE '%$esc%' OR td.Brand LIKE '%$esc%')";
        }

        $whereFilters = '';
        if ($filterItemCode !== '') {
            $esc = mysqli_real_escape_string($con, $filterItemCode);
            $whereFilters .= " AND td.icode LIKE '%$esc%'";
        }
        if ($filterTireSize !== '') {
            $esc = mysqli_real_escape_string($con, $filterTireSize);
            $whereFilters .= " AND td.tire_size LIKE '%$esc%'";
        }
        if ($filterBrand !== 'all') {
            $esc = mysqli_real_escape_string($con, $filterBrand);
            $whereFilters .= " AND td.Brand = '$esc'";
        }
        if ($filterColor !== 'all') {
            $esc = mysqli_real_escape_string($con, $filterColor);
            $whereFilters .= " AND td.colour = '$esc'";
        }
        if ($filterRimSize !== 'all') {
            $esc = mysqli_real_escape_string($con, $filterRimSize);
            $whereFilters .= " AND td.rim = '$esc'";
        }

        $whereOrderIcodes = '';
        if ($orderMode && !empty($orderIcodes)) {
            $escapedIcodes = array_map(function($ic) use ($con) {
                return "'" . mysqli_real_escape_string($con, $ic) . "'";
            }, $orderIcodes);
            $whereOrderIcodes = " AND td.icode IN (" . implode(',', $escapedIcodes) . ")";
        }

        $havingFilter = '';
        if ($filterHasPrice === 'with_price')    { $havingFilter = " HAVING pl_price IS NOT NULL AND pl_price != ''"; }
        if ($filterHasPrice === 'without_price') { $havingFilter = " HAVING pl_price IS NULL OR pl_price = ''"; }

        $sql = "SELECT td.icode,
                       td.Description  AS description,
                       td.Brand        AS brand,
                       td.tire_size    AS size,
                       td.Type         AS pattern,
                       td.Spec         AS category,
                       td.colour        AS colour,
                       td.rim     AS rim,
                       pl.price        AS pl_price,
                       lr.revision_no  AS latest_rev,
                       lr.saved_at     AS pl_updated
                FROM tire_details td
                LEFT JOIN customer_items pl
                       ON pl.icode = td.icode AND pl.cus_id = '$escapedCusId'
                LEFT JOIN (
                    SELECT icode, MAX(revision_no) AS revision_no, MAX(saved_at) AS saved_at
                    FROM customer_item_revisions
                    WHERE cus_id = '$escapedCusId'
                    GROUP BY icode
                ) lr ON lr.icode = td.icode
                WHERE 1=1 $whereSearch $whereFilters $whereOrderIcodes
                $havingFilter
                ORDER BY td.Brand, td.icode";

        $r = mysqli_query($con, $sql);
        if ($r) { while ($row = mysqli_fetch_assoc($r)) { $tireRows[] = $row; } }
    }
}

// ── Stats ────────────────────────────────────────────────────────────────────
$totalTires   = count($tireRows);
$withPrice    = 0;
$withoutPrice = 0;
foreach ($tireRows as $t) {
    if (!empty($t['pl_price'])) { $withPrice++; } else { $withoutPrice++; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATire Customer Service</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --orange:        #F28018;
            --orange-dark:   #d96d0c;
            --orange-xlight: #FFF4E8;
            --orange-mid:    rgba(242,128,24,.13);
            --orange-glow:   rgba(242,128,24,.28);
            --white:         #ffffff;
            --off-white:     #F8F8F8;
            --gray-50:       #F5F5F5;
            --gray-100:      #EEEEEE;
            --gray-200:      #E0E0E0;
            --gray-300:      #BDBDBD;
            --gray-400:      #9E9E9E;
            --gray-600:      #616161;
            --gray-800:      #2C2C2C;
            --blue:          #1565C0;
            --blue-light:    rgba(21,101,192,.10);
            --green:         #2E7D32;
            --green-light:   rgba(46,125,50,.12);
            --red:           #C62828;
            --red-light:     rgba(198,40,40,.10);
            --purple:        #6A1B9A;
            --purple-light:  rgba(106,27,154,.10);
            --shadow-xs:     0 1px 3px rgba(0,0,0,.07);
            --shadow-sm:     0 2px 8px rgba(0,0,0,.08);
            --shadow-md:     0 4px 18px rgba(0,0,0,.10);
            --shadow-orange: 0 6px 24px rgba(242,128,24,.22);
            --radius-sm:     8px;
            --radius-md:     14px;
            --radius-lg:     20px;
        }
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        html { scroll-behavior:smooth; }
        body {
            font-family:'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background:var(--off-white); color:var(--gray-800);
            min-height:100vh; display:flex; flex-direction:column;
            -webkit-font-smoothing:antialiased;
        }
        .topbar {
            position:sticky; top:0; z-index:200; height:64px; background:var(--white);
            border-bottom:1px solid var(--gray-200); box-shadow:var(--shadow-xs);
            display:flex; align-items:center; padding:0 2rem; gap:1rem;
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
        .nav-link i { font-size:.78rem; }
        .nav-link:hover { background:var(--gray-50); color:var(--gray-800); }
        .nav-link.active { background:var(--orange-mid); color:var(--orange); font-weight:700; }
        .user-chip {
            display:flex; align-items:center; gap:.6rem; padding:.3rem .85rem .3rem .3rem;
            border:1.5px solid var(--gray-200); border-radius:999px; background:var(--white); flex-shrink:0;
        }
        .user-avatar {
            width:30px; height:30px; background:var(--orange); border-radius:50%;
            display:flex; align-items:center; justify-content:center; font-size:.72rem; font-weight:800; color:#fff;
        }
        .user-name { font-size:.8rem; font-weight:600; color:var(--gray-800); max-width:120px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

        .page { flex:1; padding:2.5rem 2rem; max-width:1440px; margin:0 auto; width:100%; }
        .page-header { display:flex; align-items:flex-end; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1.75rem; }
        .page-header h1 { font-size:1.75rem; font-weight:800; color:var(--gray-800); letter-spacing:-.03em; line-height:1.2; }
        .page-header h1 em { font-style:normal; color:var(--orange); }
        .page-header p { font-size:.88rem; color:var(--gray-600); margin-top:.25rem; }
        .odivider { height:3px; background:linear-gradient(90deg, var(--orange) 0%, rgba(242,128,24,.3) 55%, transparent 100%); border-radius:2px; margin-bottom:2rem; }

        /* ── ORDER MODE CONTEXT BANNER ── */
        .order-context-banner {
            background:linear-gradient(90deg,#FFF8E1,#FFF3CD);
            border:1.5px solid #FFCC80;
            border-left:5px solid var(--orange);
            border-radius:var(--radius-md);
            padding:1.1rem 1.5rem;
            display:flex; align-items:center; gap:1.25rem;
            flex-wrap:wrap; margin-bottom:1.5rem;
            box-shadow:var(--shadow-xs);
            animation:riseUp .4s ease both;
        }
        @keyframes riseUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
        .ocb-icon {
            width:46px; height:46px; border-radius:12px;
            background:var(--orange); color:#fff;
            display:flex; align-items:center; justify-content:center;
            font-size:1.15rem; flex-shrink:0;
            box-shadow:0 4px 12px var(--orange-glow);
        }
        .ocb-info { flex:1; min-width:0; }
        .ocb-info h3 { font-size:.93rem; font-weight:800; color:var(--gray-800); margin-bottom:3px; }
        .ocb-info p { font-size:.8rem; color:var(--gray-600); line-height:1.55; }
        .ocb-icodes { display:flex; flex-wrap:wrap; gap:.35rem; margin-top:.55rem; }
        .ocb-icode {
            display:inline-flex; align-items:center; gap:.3rem; padding:.22rem .65rem;
            background:var(--blue-light); color:var(--blue); border-radius:6px;
            font-size:.74rem; font-weight:700; font-family:monospace; border:1px solid rgba(21,101,192,.18);
        }
        .ocb-stats { display:flex; gap:1.25rem; flex-shrink:0; flex-wrap:wrap; }
        .ocb-stat { text-align:center; padding:.5rem .9rem; background:rgba(255,255,255,.7); border-radius:10px; border:1px solid rgba(255,204,128,.5); }
        .ocb-stat-val { font-size:1.35rem; font-weight:900; line-height:1; }
        .ocb-stat-val.orange { color:var(--orange); } .ocb-stat-val.green { color:var(--green); } .ocb-stat-val.gray { color:var(--gray-400); }
        .ocb-stat-lbl { font-size:.65rem; font-weight:700; color:var(--gray-400); text-transform:uppercase; letter-spacing:.05em; margin-top:2px; }
        .ocb-actions { display:flex; gap:.5rem; flex-wrap:wrap; }
        .ocb-btn {
            display:inline-flex; align-items:center; gap:.4rem; padding:.4rem .9rem;
            border-radius:var(--radius-sm); font-size:.77rem; font-weight:600;
            text-decoration:none; white-space:nowrap; transition:all .18s;
        }
        .ocb-btn-back { background:var(--white); color:var(--gray-600); border:1.5px solid var(--gray-200); }
        .ocb-btn-back:hover { border-color:var(--gray-400); color:var(--gray-800); }
        .ocb-btn-clear { background:var(--orange-mid); color:var(--orange); border:1.5px solid rgba(242,128,24,.25); }
        .ocb-btn-clear:hover { background:var(--orange); color:#fff; }

        /* ORDER CONFIRMED BANNER */
        .order-confirmed-banner {
            background:linear-gradient(90deg,#E8F5E9,#F1F8E9);
            border:1.5px solid #A5D6A7;
            border-left:5px solid var(--green);
            border-radius:var(--radius-md);
            padding:1.1rem 1.5rem;
            display:flex; align-items:center; gap:1.25rem;
            flex-wrap:wrap; margin-bottom:1.5rem;
            box-shadow:var(--shadow-xs);
            animation:riseUp .4s ease both;
        }
        .ocb-confirmed-icon {
            width:46px; height:46px; border-radius:12px;
            background:var(--green); color:#fff;
            display:flex; align-items:center; justify-content:center;
            font-size:1.15rem; flex-shrink:0;
            box-shadow:0 4px 12px rgba(46,125,50,.3);
        }
        .ocb-confirmed-info h3 { font-size:.93rem; font-weight:800; color:var(--green); margin-bottom:3px; }
        .ocb-confirmed-info p  { font-size:.8rem; color:var(--gray-600); line-height:1.55; }

        /* MISSING ICODES WARNING */
        .missing-warn {
            background:#FFF3E0; border:1px solid #FFCC80; border-left:4px solid #FF9800;
            border-radius:var(--radius-sm); padding:.75rem 1.1rem;
            font-size:.8rem; color:#E65100; margin-top:.6rem;
            display:flex; align-items:flex-start; gap:.6rem;
        }
        .missing-warn i { flex-shrink:0; margin-top:1px; }
        .missing-chips { display:inline-flex; flex-wrap:wrap; gap:.3rem; margin-top:.35rem; }
        .missing-chip {
            display:inline-flex; align-items:center; padding:.15rem .5rem;
            background:rgba(255,152,0,.15); color:#E65100; border-radius:5px;
            font-size:.72rem; font-weight:700; font-family:monospace;
        }

        .filter-card {
            background:var(--white); border:1px solid var(--gray-200); border-radius:var(--radius-lg);
            padding:1.5rem 1.75rem; box-shadow:var(--shadow-xs); margin-bottom:1.5rem;
            animation:riseUp .4s ease both;
        }
        .filter-title { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.09em; color:var(--gray-400); margin-bottom:1.1rem; display:flex; align-items:center; gap:.5rem; }
        .filter-title i { color:var(--orange); }
        .filter-row { display:flex; align-items:flex-end; gap:1rem; flex-wrap:wrap; }
        .filter-group { display:flex; flex-direction:column; gap:.4rem; flex:1; min-width:150px; }
        .filter-group label { font-size:.78rem; font-weight:600; color:var(--gray-600); }
        .filter-select, .filter-input {
            height:42px; border:1.5px solid var(--gray-200); border-radius:var(--radius-sm);
            padding:0 .9rem; font-size:.85rem; font-family:inherit; color:var(--gray-800);
            background:var(--white); transition:border-color .2s, box-shadow .2s; width:100%;
        }
        .filter-select:focus, .filter-input:focus { outline:none; border-color:var(--orange); box-shadow:0 0 0 3px var(--orange-mid); }
        .radio-group { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; margin-top:.4rem; }
        .radio-btn {
            display:flex; align-items:center; gap:.4rem; padding:.38rem .85rem;
            border:1.5px solid var(--gray-200); border-radius:999px; font-size:.78rem;
            font-weight:600; cursor:pointer; color:var(--gray-600); background:var(--white);
            transition:all .18s; white-space:nowrap;
        }
        .radio-btn input { display:none; }
        .radio-btn:has(input:checked), .radio-btn.selected { background:var(--orange-mid); border-color:var(--orange); color:var(--orange); }
        .btn {
            display:inline-flex; align-items:center; gap:.5rem; padding:0 1.4rem; height:42px;
            border:none; border-radius:var(--radius-sm); font-size:.85rem; font-weight:600;
            font-family:inherit; cursor:pointer; text-decoration:none; white-space:nowrap; transition:all .2s;
        }
        .btn-orange { background:var(--orange); color:#fff; box-shadow:0 3px 10px var(--orange-glow); }
        .btn-orange:hover { background:var(--orange-dark); box-shadow:var(--shadow-orange); transform:translateY(-1px); }
        .btn-outline { background:var(--white); color:var(--orange); border:1.5px solid var(--orange); }
        .btn-outline:hover { background:var(--orange-xlight); }
        .btn-ghost { background:var(--gray-50); color:var(--gray-600); border:1.5px solid var(--gray-200); }
        .btn-ghost:hover { border-color:var(--gray-400); color:var(--gray-800); }

        .cus-bar {
            background:var(--white); border:1px solid var(--gray-200); border-left:4px solid var(--orange);
            border-radius:var(--radius-md); padding:1.1rem 1.5rem; display:flex; align-items:center;
            gap:1.5rem; flex-wrap:wrap; margin-bottom:1.5rem; box-shadow:var(--shadow-xs);
            animation:riseUp .4s .05s ease both;
        }
        .cus-bar-avatar {
            width:44px; height:44px; background:var(--orange); border-radius:12px;
            display:flex; align-items:center; justify-content:center; font-size:1.1rem; font-weight:800; color:#fff; flex-shrink:0;
        }
        .cus-bar-info { flex:1; min-width:0; }
        .cus-bar-info h3 { font-size:.95rem; font-weight:700; color:var(--gray-800); }
        .cus-bar-info p { font-size:.78rem; color:var(--gray-400); margin-top:2px; }
        .cus-bar-stats { display:flex; gap:1.5rem; flex-wrap:wrap; }
        .cus-stat { text-align:center; }
        .cus-stat-val { font-size:1.5rem; font-weight:900; line-height:1; }
        .cus-stat-val.orange { color:var(--orange); } .cus-stat-val.green { color:var(--green); } .cus-stat-val.gray { color:var(--gray-400); }
        .cus-stat-lbl { font-size:.7rem; font-weight:600; color:var(--gray-400); text-transform:uppercase; letter-spacing:.05em; margin-top:3px; }
        .bulk-bar {
            background:linear-gradient(90deg,#FFF4E8,#FDE8CC); border:1px solid #F5CFA0;
            border-radius:var(--radius-md); padding:.85rem 1.5rem; display:flex; align-items:center;
            gap:1rem; margin-bottom:1.5rem; animation:riseUp .4s .08s ease both;
        }
        .bulk-bar i { color:var(--orange); font-size:1.1rem; }
        .bulk-bar span { flex:1; font-size:.85rem; color:var(--gray-600); }
        .bulk-bar span strong { color:var(--gray-800); }
        .table-wrap {
            background:var(--white); border:1px solid var(--gray-200); border-radius:var(--radius-lg);
            box-shadow:var(--shadow-xs); overflow:hidden; animation:riseUp .4s .1s ease both;
        }
        .table-header {
            padding:1rem 1.5rem; border-bottom:1px solid var(--gray-100);
            display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;
        }
        .table-header h3 { font-size:.95rem; font-weight:700; color:var(--gray-800); }
        .table-count { padding:.28rem .75rem; background:var(--orange-mid); color:var(--orange); border-radius:999px; font-size:.72rem; font-weight:700; }
        .order-mode-tag {
            display:inline-flex; align-items:center; gap:.35rem; padding:.28rem .8rem;
            background:rgba(255,152,0,.12); color:#E65100; border-radius:999px;
            font-size:.72rem; font-weight:700; border:1px solid rgba(255,152,0,.25);
        }
        .tbl { width:100%; border-collapse:collapse; }
        .tbl thead { background:var(--gray-50); }
        .tbl th {
            padding:.75rem 1rem; text-align:left; font-size:.72rem; font-weight:700;
            text-transform:uppercase; letter-spacing:.07em; color:var(--gray-400);
            border-bottom:2px solid var(--gray-200); white-space:nowrap;
        }
        .tbl th.center { text-align:center; }
        .tbl tbody tr.main-row { border-bottom:1px solid var(--gray-100); transition:background .15s; }
        .tbl tbody tr.main-row:hover { background:var(--orange-xlight); }

        .tbl tbody tr.main-row.order-item { background:linear-gradient(90deg,rgba(255,200,100,.06),transparent); }
        .tbl tbody tr.main-row.order-item:hover { background:var(--orange-xlight); }
        .order-item-dot {
            display:inline-flex; align-items:center; justify-content:center;
            width:18px; height:18px; border-radius:50%; background:var(--orange);
            color:#fff; font-size:.58rem; flex-shrink:0;
        }

        .tbl td { padding:.7rem 1rem; font-size:.845rem; color:var(--gray-800); vertical-align:middle; }
        .tbl td.center { text-align:center; }
        .revision-row { display:none; }
        .revision-row.open { display:table-row; }
        .revision-row td { padding:0; background:#FAFAFA; border-bottom:2px solid var(--orange-mid); }
        .revision-inner { padding:.85rem 1.25rem .85rem 3.5rem; }
        .revision-title { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--purple); margin-bottom:.65rem; display:flex; align-items:center; gap:.45rem; }
        .revision-list { display:flex; flex-wrap:wrap; gap:.5rem; }
        .rev-chip {
            display:inline-flex; align-items:center; gap:.5rem; padding:.32rem .8rem;
            border-radius:999px; font-size:.75rem; font-weight:600;
            background:var(--purple-light); color:var(--purple);
            border:1px solid rgba(106,27,154,.18); white-space:nowrap;
        }
        .rev-chip.latest { background:var(--green-light); color:var(--green); border-color:rgba(46,125,50,.2); }
        .rev-chip-no { font-size:.65rem; font-weight:800; opacity:.75; }
        .rev-empty { font-size:.78rem; color:var(--gray-400); font-style:italic; }
        .rev-loading { font-size:.78rem; color:var(--gray-400); }
        .icode-badge {
            display:inline-block; padding:.2rem .6rem; background:var(--blue-light); color:var(--blue);
            border-radius:6px; font-size:.75rem; font-weight:700; font-family:monospace; letter-spacing:.02em;
        }
        .price-cell { display:flex; align-items:center; gap:.5rem; }
        .price-symbol { font-size:.85rem; font-weight:700; color:var(--gray-400); flex-shrink:0; }
        .price-input {
            width:110px; height:36px; border:1.5px solid var(--gray-200); border-radius:var(--radius-sm);
            padding:0 .7rem; font-size:.85rem; font-family:inherit; color:var(--gray-800);
            background:var(--white); transition:border-color .2s, box-shadow .2s;
        }
        .price-input:focus { outline:none; border-color:var(--orange); box-shadow:0 0 0 3px var(--orange-mid); }
        .price-input.has-price { border-color:#A5D6A7; background:#F1F8F1; }
        .price-input.changed   { border-color:var(--orange); background:var(--orange-xlight); }
        .save-row-btn {
            width:32px; height:32px; border-radius:8px; border:none; cursor:pointer;
            background:var(--green-light); color:var(--green); font-size:.8rem;
            display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:all .2s; opacity:.4;
        }
        .save-row-btn.active { opacity:1; }
        .save-row-btn.active:hover { background:var(--green); color:#fff; }
        .save-row-btn.saving { opacity:.6; pointer-events:none; }
        .save-row-btn.saved  { background:var(--green); color:#fff; opacity:1; }
        .hist-btn {
            width:28px; height:28px; border-radius:7px; border:none; cursor:pointer;
            background:var(--purple-light); color:var(--purple); font-size:.72rem;
            display:inline-flex; align-items:center; justify-content:center;
            flex-shrink:0; transition:all .2s; position:relative;
        }
        .hist-btn:hover { background:var(--purple); color:#fff; }
        .rev-badge {
            position:absolute; top:-5px; right:-5px; background:var(--purple); color:#fff;
            font-size:.55rem; font-weight:800; width:14px; height:14px; border-radius:50%;
            display:flex; align-items:center; justify-content:center; line-height:1;
        }
        .status-dot { display:inline-flex; align-items:center; gap:.35rem; font-size:.74rem; font-weight:600; }
        .status-dot::before { content:''; width:7px; height:7px; border-radius:50%; flex-shrink:0; }
        .status-dot.set::before   { background:var(--green); } .status-dot.unset::before { background:var(--gray-300); }
        .status-dot.set   { color:var(--green); } .status-dot.unset { color:var(--gray-400); }
        .updated-tag { font-size:.72rem; color:var(--gray-400); }
        .empty-state { padding:4rem 2rem; text-align:center; }
        .empty-icon { width:70px; height:70px; background:var(--orange-xlight); border-radius:50%; margin:0 auto 1.25rem; display:flex; align-items:center; justify-content:center; font-size:1.75rem; color:var(--orange); }
        .empty-state h3 { font-size:1.1rem; font-weight:700; color:var(--gray-800); margin-bottom:.4rem; }
        .empty-state p { font-size:.85rem; color:var(--gray-400); }
        #toast-container { position:fixed; bottom:1.5rem; right:1.5rem; z-index:9999; display:flex; flex-direction:column; gap:.6rem; pointer-events:none; }
        .toast {
            display:flex; align-items:center; gap:.75rem; padding:.75rem 1.25rem;
            background:var(--gray-800); color:#fff; border-radius:var(--radius-md);
            box-shadow:var(--shadow-md); font-size:.84rem; font-weight:500;
            pointer-events:auto; animation:toastIn .25s ease; min-width:240px;
        }
        .toast.success { background:var(--green); } .toast.error { background:var(--red); }
        .toast.confirm { background:#1B5E20; border:2px solid #A5D6A7; }
        .toast i { font-size:.9rem; }
        @keyframes toastIn { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
        .page-footer { border-top:1px solid var(--gray-200); padding:1rem 2rem; text-align:center; font-size:.73rem; color:var(--gray-400); }
        @media (max-width:960px) { .topnav { display:none; } .topbar { padding:0 1rem; } }
        @media (max-width:640px) { .page { padding:1.5rem 1rem; } .filter-row { flex-direction:column; } .price-input { width:90px; } .filter-group { min-width:100%; } }
    </style>
</head>
<body>



<main class="page">
    <div class="page-header">
        <div>
            <h1>Price List <em>Management</em></h1>
            <p>
                <?php if ($orderMode && $orderData): ?>
                    Showing items from order <strong><?php echo htmlspecialchars($orderData['order_reference'] ?: $orderData['order_id']); ?></strong> — enter prices below to resolve price_pending status.
                <?php else: ?>
                    Select a customer, enter prices, and track every change as a numbered revision.
                <?php endif; ?>
            </p>
        </div>
        <?php if ($isAccountManager): ?>
            <div style="display:inline-flex;align-items:center;gap:.45rem;padding:.42rem 1rem;background:#FFF8E1;color:#E65100;border:1px solid #FFCC80;border-radius:999px;font-size:.76rem;font-weight:600;">
                <i class="fas fa-user-shield"></i>
                Account Manager &mdash; <?php echo count($acmCustomerIds); ?> customer<?php echo count($acmCustomerIds) != 1 ? 's' : ''; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="odivider"></div>

    <?php
    if ($orderMode && $orderData):
        $missingIcodes = [];
        if (!empty($orderIcodes)) {
            $foundIcodes = array_column($tireRows, 'icode');
            $missingIcodes = array_diff($orderIcodes, $foundIcodes);
        }

        // Check if this order is already confirmed (all prices were set in a previous session)
        $alreadyConfirmed = ($orderData['status'] === 'cus_confirmed');
    ?>

    <?php if ($alreadyConfirmed): ?>
    <!-- Already confirmed banner -->
    <div class="order-confirmed-banner">
        <div class="ocb-confirmed-icon"><i class="fas fa-check-double"></i></div>
        <div class="ocb-confirmed-info">
            <h3><i class="fas fa-check-circle" style="margin-right:.35rem;"></i>Order Already Confirmed</h3>
            <p>
                Order <strong><?php echo htmlspecialchars($orderData['order_reference'] ?: $orderData['order_id']); ?></strong>
                has already been marked as <strong>cus_confirmed</strong>. All items had prices set.
                You can still update prices below — each save creates a new revision.
            </p>
        </div>
        <div class="ocb-actions">
            <a href="finace.php" class="ocb-btn ocb-btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
    <?php else: ?>
    <!-- Price-pending order context banner -->
    <div class="order-context-banner" id="orderContextBanner">
        <div class="ocb-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <div class="ocb-info" style="flex:1;min-width:200px;">
            <h3>
                <i class="fas fa-filter" style="font-size:.8rem;color:var(--orange);margin-right:.3rem;"></i>
                Filtered for Order: <?php echo htmlspecialchars($orderData['order_reference'] ?: $orderData['order_id']); ?>
            </h3>
            <p>
                Customer: <strong><?php echo htmlspecialchars($orderData['customer_name'] ?? $orderData['cus_id']); ?></strong>
                &nbsp;&bull;&nbsp; ID: <strong><?php echo htmlspecialchars($orderData['cus_id']); ?></strong>
                &nbsp;&bull;&nbsp; Date: <?php echo date('d M Y', strtotime($orderData['order_date'])); ?>
                &nbsp;&bull;&nbsp; Status: <span style="color:var(--red);font-weight:700;" id="orderStatusLabel">price_pending</span>
            </p>
            <?php if (!empty($orderIcodes)): ?>
            <div class="ocb-icodes">
                <?php foreach ($orderIcodes as $ic): ?>
                    <span class="ocb-icode"><i class="fas fa-tag" style="font-size:.6rem;"></i><?php echo htmlspecialchars($ic); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($missingIcodes)): ?>
            <div class="missing-warn">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    The following item codes from this order were not found in your tire catalog:
                    <div class="missing-chips">
                        <?php foreach ($missingIcodes as $mic): ?>
                            <span class="missing-chip"><?php echo htmlspecialchars($mic); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="ocb-stats">
            <div class="ocb-stat"><div class="ocb-stat-val orange" id="ocbTotal"><?php echo $totalTires; ?></div><div class="ocb-stat-lbl">Items</div></div>
            <div class="ocb-stat"><div class="ocb-stat-val green" id="ocbPriced"><?php echo $withPrice; ?></div><div class="ocb-stat-lbl">Priced</div></div>
            <div class="ocb-stat"><div class="ocb-stat-val gray" id="ocbPending"><?php echo $withoutPrice; ?></div><div class="ocb-stat-lbl">Pending</div></div>
        </div>
        <div class="ocb-actions">
            <a href="finace.php#pendingSection" class="ocb-btn ocb-btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <a href="customer_items.php?cus_id=<?php echo urlencode($selectedCusId); ?>" class="ocb-btn ocb-btn-clear">
                <i class="fas fa-expand-arrows-alt"></i> Show All Items
            </a>
        </div>
    </div>
    <?php endif; // alreadyConfirmed ?>

    <?php endif; // orderMode ?>

    <!-- FILTER CARD -->
    <div class="filter-card">
        <div class="filter-title"><i class="fas fa-filter"></i> Filter Options</div>
        <form method="GET" id="filterForm">
            <?php if ($orderMode && $incomingOrderId !== ''): ?>
                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($incomingOrderId); ?>">
            <?php endif; ?>
            <div class="filter-row">
                <div class="filter-group">
                    <label for="cusSelect"><i class="fas fa-user"></i> &nbsp;Customer</label>
                    <select name="cus_id" id="cusSelect" class="filter-select" onchange="this.form.submit()">
                        <option value="">— Select a customer —</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?php echo htmlspecialchars($c['cus_id']); ?>"
                                <?php echo $c['cus_id'] === $selectedCusId ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['fullName'] ?: $c['cus_id']); ?>
                                (<?php echo htmlspecialchars($c['cus_id']); ?>)
                                <?php if ($c['company_rn']): ?> — <?php echo htmlspecialchars($c['company_rn']); ?><?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="itemCodeInput"><i class="fas fa-barcode"></i> &nbsp;Item Code</label>
                    <input type="text" name="icode" id="itemCodeInput" class="filter-input"
                           placeholder="e.g. TY1234"
                           value="<?php echo htmlspecialchars($filterItemCode); ?>">
                </div>

                <div class="filter-group">
                    <label for="tireSizeInput"><i class="fas fa-circle-notch"></i> &nbsp;Tire Size</label>
                    <input type="text" name="tire_size" id="tireSizeInput" class="filter-input"
                           placeholder="e.g. 205/55R16"
                           value="<?php echo htmlspecialchars($filterTireSize); ?>">
                </div>

                <div class="filter-group">
                    <label for="brandSelect"><i class="fas fa-tag"></i> &nbsp;Brand</label>
                    <select name="brand" id="brandSelect" class="filter-select">
                        <option value="all">All Brands</option>
                        <?php foreach($brands as $b): ?>
                        <option value="<?php echo htmlspecialchars($b); ?>" <?php echo $filterBrand === $b ? 'selected' : ''; ?>><?php echo htmlspecialchars($b); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="colorSelect"><i class="fas fa-palette"></i> &nbsp;Color</label>
                    <select name="colour" id="colorSelect" class="filter-select">
                        <option value="all">All Colors</option>
                        <?php foreach($colors as $c): ?>
                        <option value="<?php echo htmlspecialchars($c); ?>" <?php echo $filterColor === $c ? 'selected' : ''; ?>><?php echo htmlspecialchars($c); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="rimSelect"><i class="fas fa-cog"></i> &nbsp;Rim Size</label>
                    <select name="rim" id="rimSelect" class="filter-select">
                        <option value="all">All Rims</option>
                        <?php foreach($rims as $r): ?>
                        <option value="<?php echo htmlspecialchars($r); ?>" <?php echo $filterRimSize === $r ? 'selected' : ''; ?>><?php echo htmlspecialchars($r); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="filter-row" style="margin-top:1rem;">
                <div class="filter-group">
                    <label for="searchInput"><i class="fas fa-search"></i> &nbsp;Search Description</label>
                    <input type="text" name="search" id="searchInput" class="filter-input"
                           placeholder="e.g. Bridgestone, Michelin..."
                           value="<?php echo htmlspecialchars($searchIcode); ?>">
                </div>

                <div class="filter-group">
                    <label><i class="fas fa-dollar-sign"></i> &nbsp;Price Status</label>
                    <div class="radio-group">
                        <label class="radio-btn <?php echo $filterHasPrice === 'all' ? 'selected' : ''; ?>">
                            <input type="radio" name="filter" value="all" <?php echo $filterHasPrice === 'all' ? 'checked' : ''; ?>> All
                        </label>
                        <label class="radio-btn <?php echo $filterHasPrice === 'with_price' ? 'selected' : ''; ?>">
                            <input type="radio" name="filter" value="with_price" <?php echo $filterHasPrice === 'with_price' ? 'checked' : ''; ?>>
                            <i class="fas fa-check" style="font-size:.65rem;"></i> Priced
                        </label>
                        <label class="radio-btn <?php echo $filterHasPrice === 'without_price' ? 'selected' : ''; ?>">
                            <input type="radio" name="filter" value="without_price" <?php echo $filterHasPrice === 'without_price' ? 'checked' : ''; ?>>
                            <i class="fas fa-minus" style="font-size:.65rem;"></i> Unpriced
                        </label>
                    </div>
                </div>

                <div class="filter-group" style="flex:0;min-width:auto;flex-direction:row;align-items:flex-end;gap:.5rem;">
                    <button type="submit" class="btn btn-orange"><i class="fas fa-search"></i> Search</button>
                    <a href="customer_items.php<?php echo $selectedCusId ? '?cus_id='.urlencode($selectedCusId) : ''; ?>" class="btn btn-ghost">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <?php if ($selectedCustomer): ?>
    <div class="cus-bar">
        <div class="cus-bar-avatar"><?php echo strtoupper(substr($selectedCustomer['fullName'] ?: $selectedCustomer['cus_id'], 0, 1)); ?></div>
        <div class="cus-bar-info">
            <h3><?php echo htmlspecialchars($selectedCustomer['fullName'] ?: 'N/A'); ?></h3>
            <p>
                Customer ID: <strong><?php echo htmlspecialchars($selectedCustomer['cus_id']); ?></strong>
                <?php if ($selectedCustomer['company_rn']): ?> &nbsp;&bull;&nbsp; <?php echo htmlspecialchars($selectedCustomer['company_rn']); ?><?php endif; ?>
                <?php if ($selectedCustomer['Country']): ?> &nbsp;&bull;&nbsp; <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($selectedCustomer['Country']); ?><?php endif; ?>
            </p>
        </div>
        <div class="cus-bar-stats">
            <div class="cus-stat"><div class="cus-stat-val orange"><?php echo number_format($totalTires); ?></div><div class="cus-stat-lbl">Total Items</div></div>
            <div class="cus-stat"><div class="cus-stat-val green"><?php echo number_format($withPrice); ?></div><div class="cus-stat-lbl">Priced</div></div>
            <div class="cus-stat"><div class="cus-stat-val gray"><?php echo number_format($withoutPrice); ?></div><div class="cus-stat-lbl">Unpriced</div></div>
        </div>
    </div>

    <?php if (!empty($tireRows)): ?>
    <div class="bulk-bar">
        <i class="fas fa-layer-group"></i>
        <span>
            Each save creates a new revision.
            <?php if ($orderMode): ?>
                <strong>Order-mode:</strong> only showing items from this order.
                <?php if (!($alreadyConfirmed ?? false)): ?>
                    Set prices for all items to automatically confirm this order.
                <?php endif; ?>
            <?php endif; ?>
            Click <strong><i class="fas fa-clock" style="font-size:.7rem;"></i></strong> on any row to view its full revision history.
        </span>
        <button class="btn btn-orange" id="bulkSaveBtn" onclick="bulkSave()">
            <i class="fas fa-save"></i> Save All Changes
        </button>
        <button class="btn btn-outline" onclick="exportCSV()">
            <i class="fas fa-download"></i> Export CSV
        </button>
    </div>

    <div class="table-wrap">
        <div class="table-header">
            <div style="display:flex;align-items:center;gap:.75rem;">
                <h3>Tyre Items — Price Entry</h3>
                <span class="table-count"><?php echo number_format($totalTires); ?> item<?php echo $totalTires != 1 ? 's' : ''; ?></span>
                <?php if ($orderMode): ?>
                    <span class="order-mode-tag"><i class="fas fa-filter"></i> Order Filter Active</span>
                <?php endif; ?>
            </div>
            <div style="font-size:.78rem;color:var(--gray-400);">
                Customer: <strong style="color:var(--gray-800);"><?php echo htmlspecialchars($selectedCusId); ?></strong>
                <?php if ($orderMode && $orderData): ?>
                    &nbsp;&bull;&nbsp; Order: <strong style="color:var(--gray-800);"><?php echo htmlspecialchars($orderData['order_reference'] ?: $orderData['order_id']); ?></strong>
                <?php endif; ?>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="tbl" id="priceTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <?php if ($orderMode): ?><th class="center" title="From order">Ord</th><?php endif; ?>
                        <th>iCode</th>
                        <th>Brand</th>
                        <th>Description</th>
                        <th>Size</th>
                        <th>Color</th>
                        <th>Rim</th>
                        <th>Type</th>
                        <th>Spec</th>
                        <th>Status</th>
                        <th>Price (USD)</th>
                        <th class="center">Save</th>
                        <th class="center">History</th>
                        <th>Latest Revision</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($tireRows as $idx => $t):
                    $icode       = htmlspecialchars($t['icode']);
                    $isOrderItem = $orderMode && in_array($t['icode'], $orderIcodes);
                ?>
                <tr class="main-row <?php echo $isOrderItem ? 'order-item' : ''; ?>" data-icode="<?php echo $icode; ?>">
                    <td style="color:var(--gray-400);font-size:.75rem;"><?php echo $idx + 1; ?></td>
                    <?php if ($orderMode): ?>
                    <td class="center">
                        <?php if ($isOrderItem): ?>
                            <span class="order-item-dot" title="Part of this order"><i class="fas fa-circle" style="font-size:.4rem;"></i></span>
                        <?php else: ?>
                            <span style="color:var(--gray-300);font-size:.7rem;">—</span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                    <td><span class="icode-badge"><?php echo $icode; ?></span></td>
                    <td style="font-weight:600;"><?php echo htmlspecialchars($t['brand'] ?? '—'); ?></td>
                    <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                        title="<?php echo htmlspecialchars($t['description'] ?? ''); ?>">
                        <?php echo htmlspecialchars($t['description'] ?? '—'); ?>
                    </td>
                    <td><?php echo htmlspecialchars($t['size']    ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($t['colour']   ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($t['rim']     ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($t['pattern'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($t['category']?? '—'); ?></td>
                    <td id="status_<?php echo $icode; ?>">
                        <?php if (!empty($t['pl_price'])): ?>
                            <span class="status-dot set"><i class="fas fa-check"></i> Set</span>
                        <?php else: ?>
                            <span class="status-dot unset"><i class="fas fa-minus"></i> Not set</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="price-cell">
                            <span class="price-symbol">$</span>
                            <input type="number"
                                   class="price-input <?php echo !empty($t['pl_price']) ? 'has-price' : ''; ?>"
                                   data-original="<?php echo htmlspecialchars($t['pl_price'] ?? ''); ?>"
                                   data-icode="<?php echo $icode; ?>"
                                   value="<?php echo htmlspecialchars($t['pl_price'] ?? ''); ?>"
                                   placeholder="0.00" min="0" step="0.01"
                                   oninput="onPriceChange(this)">
                        </div>
                    </td>
                    <td class="center">
                        <button class="save-row-btn" data-icode="<?php echo $icode; ?>" title="Save this row" onclick="saveRow(this)">
                            <i class="fas fa-check"></i>
                        </button>
                    </td>
                    <td class="center">
                        <button class="hist-btn" data-icode="<?php echo $icode; ?>" title="View revision history" onclick="toggleHistory(this)">
                            <i class="fas fa-clock"></i>
                            <?php if (!empty($t['latest_rev'])): ?>
                                <span class="rev-badge"><?php echo intval($t['latest_rev']); ?></span>
                            <?php endif; ?>
                        </button>
                    </td>
                    <td class="updated-tag" id="rev_<?php echo $icode; ?>">
                        <?php if (!empty($t['latest_rev'])): ?>
                            <span style="color:var(--purple);font-weight:600;">Rev <?php echo intval($t['latest_rev']); ?></span>
                            <?php if (!empty($t['pl_updated'])): ?>
                                <br><?php echo date('M j, Y H:i', strtotime($t['pl_updated'])); ?>
                            <?php endif; ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
                <tr class="revision-row" id="histrow_<?php echo $icode; ?>">
                    <td colspan="<?php echo $orderMode ? '15' : '14'; ?>">
                        <div class="revision-inner">
                            <div class="revision-title"><i class="fas fa-history"></i> Revision History — <?php echo $icode; ?></div>
                            <div class="revision-list" id="revlist_<?php echo $icode; ?>">
                                <span class="rev-loading"><i class="fas fa-spinner fa-spin"></i> Loading…</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php else: ?>
    <div class="table-wrap">
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-search"></i></div>
            <h3>No items found</h3>
            <p>
                <?php if ($orderMode): ?>
                    None of the item codes from this order were found in the tire catalog.
                    <br><a href="customer_items.php?cus_id=<?php echo urlencode($selectedCusId); ?>" style="color:var(--orange);font-weight:600;">Browse all items for this customer &rarr;</a>
                <?php else: ?>
                    No tyre items match your current search or filter criteria.
                <?php endif; ?>
            </p>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="table-wrap">
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-user-plus"></i></div>
            <h3>Select a Customer to Begin</h3>
            <p>Choose a customer from the dropdown above to load their tyre price list.</p>
        </div>
    </div>
    <?php endif; ?>
</main>

<footer class="page-footer">
    &copy; <?php echo date('Y'); ?> A-Tire Customer Service &mdash; Price List Management
</footer>
<div id="toast-container"></div>

<script>
const CUS_ID   = <?php echo json_encode($selectedCusId); ?>;
const ORDER_ID = <?php echo json_encode($incomingOrderId); ?>;
const ORDER_MODE = <?php echo json_encode($orderMode); ?>;

// Track how many order items are priced (for live banner counter)
let pricedCount  = <?php echo json_encode($withPrice); ?>;
let pendingCount = <?php echo json_encode($withoutPrice); ?>;

function toast(msg, type = 'success', duration = 3000) {
    const ct = document.getElementById('toast-container');
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `<i class="fas fa-${type==='success'?'check-circle':type==='error'?'exclamation-circle':type==='confirm'?'check-double':'info-circle'}"></i> ${msg}`;
    ct.appendChild(el);
    setTimeout(() => { el.style.opacity='0'; el.style.transform='translateY(10px)'; el.style.transition='.3s'; setTimeout(()=>el.remove(),320); }, duration);
}

function onPriceChange(input) {
    const isChanged = input.value.trim() !== input.dataset.original;
    const icode = input.dataset.icode;
    input.classList.toggle('changed', isChanged);
    input.classList.toggle('has-price', !isChanged && input.value.trim() !== '');
    const btn = document.querySelector(`.save-row-btn[data-icode="${CSS.escape(icode)}"]`);
    if (btn) btn.classList.toggle('active', isChanged);
}

function updateRevUI(icode, revNo) {
    const histBtn = document.querySelector(`.hist-btn[data-icode="${CSS.escape(icode)}"]`);
    if (histBtn) {
        let badge = histBtn.querySelector('.rev-badge');
        if (!badge) { badge = document.createElement('span'); badge.className = 'rev-badge'; histBtn.appendChild(badge); }
        badge.textContent = revNo;
    }
    const revCell = document.getElementById(`rev_${icode}`);
    if (revCell) {
        const now = new Date();
        const dateStr = now.toLocaleString('en-GB', {day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
        revCell.innerHTML = `<span style="color:var(--purple);font-weight:600;">Rev ${revNo}</span><br>${dateStr}`;
    }
    const revList = document.getElementById(`revlist_${icode}`);
    if (revList) revList.dataset.loaded = '';
}

// Update the banner stats counters live
function updateBannerStats(wasAlreadyPriced) {
    if (!ORDER_MODE) return;
    if (!wasAlreadyPriced) {
        pricedCount++;
        pendingCount = Math.max(0, pendingCount - 1);
    }
    const el1 = document.getElementById('ocbPriced');
    const el2 = document.getElementById('ocbPending');
    if (el1) el1.textContent = pricedCount;
    if (el2) el2.textContent = pendingCount;
}

// Transform the order banner to "confirmed" state
function markOrderConfirmedUI(orderRef) {
    const banner = document.getElementById('orderContextBanner');
    if (!banner) return;
    banner.style.transition = 'all .5s ease';
    banner.className = 'order-confirmed-banner';
    banner.innerHTML = `
        <div class="ocb-confirmed-icon"><i class="fas fa-check-double"></i></div>
        <div class="ocb-confirmed-info">
            <h3><i class="fas fa-check-circle" style="margin-right:.35rem;"></i>Order Confirmed!</h3>
            <p>
                Order <strong>${escHtml(orderRef)}</strong> has been automatically updated to
                <strong style="color:var(--green);">cus_confirmed</strong> — all items now have prices set.
            </p>
        </div>
        <div class="ocb-actions">
            <a href="finace.php" class="ocb-btn ocb-btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    `;
    const statusLabel = document.getElementById('orderStatusLabel');
    if (statusLabel) {
        statusLabel.textContent = 'cus_confirmed';
        statusLabel.style.color = 'var(--green)';
    }
}

async function saveRow(btn) {
    if (!btn.classList.contains('active')) return;
    const icode = btn.dataset.icode;
    const input = document.querySelector(`.price-input[data-icode="${CSS.escape(icode)}"]`);
    const price = input ? input.value.trim() : '';
    if (price === '' || isNaN(price) || parseFloat(price) < 0) { toast('Please enter a valid price.', 'error'); return; }

    const wasAlreadyPriced = input && input.dataset.original !== '';

    btn.classList.add('saving');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    try {
        const fd = new FormData();
        fd.append('action','save_price');
        fd.append('cus_id', CUS_ID);
        fd.append('icode', icode);
        fd.append('price', price);
        if (ORDER_ID) fd.append('order_id', ORDER_ID);

        const res  = await fetch(window.location.pathname, {method:'POST',body:fd});
        const data = await res.json();
        if (data.success) {
            btn.classList.remove('saving'); btn.classList.add('saved'); btn.classList.remove('active');
            btn.innerHTML = '<i class="fas fa-check"></i>';
            if (input) { input.dataset.original=price; input.classList.remove('changed'); input.classList.add('has-price'); }
            const statusTd = document.getElementById(`status_${icode}`);
            if (statusTd) statusTd.innerHTML = '<span class="status-dot set"><i class="fas fa-check"></i> Set</span>';
            updateRevUI(icode, data.revision_no);
            updateBannerStats(wasAlreadyPriced);

            if (data.order_confirmed) {
                const orderRef = <?php echo json_encode($orderData['order_reference'] ?? ($orderData['order_id'] ?? '')); ?>;
                toast(`✅ Order <strong>${escHtml(orderRef)}</strong> confirmed — all items priced!`, 'confirm', 6000);
                markOrderConfirmedUI(orderRef);
            } else {
                toast(`Saved — <strong>Rev ${data.revision_no}</strong> created for ${icode}`);
            }
            setTimeout(() => { btn.classList.remove('saved'); btn.innerHTML='<i class="fas fa-check"></i>'; }, 2000);
        } else {
            btn.classList.remove('saving'); btn.classList.add('active');
            btn.innerHTML='<i class="fas fa-check"></i>';
            toast(data.message || 'Save failed.', 'error');
        }
    } catch(e) {
        btn.classList.remove('saving'); btn.classList.add('active');
        btn.innerHTML='<i class="fas fa-check"></i>';
        toast('Network error. Please try again.', 'error');
    }
}

async function bulkSave() {
    const inputs = document.querySelectorAll('.price-input.changed');
    if (inputs.length === 0) { toast('No changes to save.', 'info'); return; }
    const bulkBtn = document.getElementById('bulkSaveBtn');
    bulkBtn.disabled = true; bulkBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';

    // Track which were already priced before this bulk save
    const wasAlreadyPricedCount = [...inputs].filter(inp => inp.dataset.original !== '').length;
    const newlyPricedCount = inputs.length - wasAlreadyPricedCount;

    const fd = new FormData();
    fd.append('action','bulk_save');
    fd.append('cus_id', CUS_ID);
    if (ORDER_ID) fd.append('order_id', ORDER_ID);
    inputs.forEach(inp => fd.append(`prices[${inp.dataset.icode}]`, inp.value.trim()));
    try {
        const res  = await fetch(window.location.pathname, {method:'POST',body:fd});
        const data = await res.json();
        if (data.success) {
            inputs.forEach(inp => {
                inp.dataset.original = inp.value.trim();
                inp.classList.remove('changed'); inp.classList.add('has-price');
                const icode = inp.dataset.icode;
                const sb = document.querySelector(`.save-row-btn[data-icode="${CSS.escape(icode)}"]`);
                if (sb) { sb.classList.remove('active','saved'); sb.innerHTML='<i class="fas fa-check"></i>'; }
                const statusTd = document.getElementById(`status_${icode}`);
                if (statusTd) statusTd.innerHTML = '<span class="status-dot set"><i class="fas fa-check"></i> Set</span>';
                const rl = document.getElementById(`revlist_${icode}`);
                if (rl) rl.dataset.loaded = '';
            });

            // Update banner counters
            if (ORDER_MODE && newlyPricedCount > 0) {
                pricedCount  += newlyPricedCount;
                pendingCount  = Math.max(0, pendingCount - newlyPricedCount);
                const el1 = document.getElementById('ocbPriced');
                const el2 = document.getElementById('ocbPending');
                if (el1) el1.textContent = pricedCount;
                if (el2) el2.textContent = pendingCount;
            }

            if (data.order_confirmed) {
                const orderRef = <?php echo json_encode($orderData['order_reference'] ?? ($orderData['order_id'] ?? '')); ?>;
                toast(`✅ Order <strong>${escHtml(orderRef)}</strong> confirmed — all items priced!`, 'confirm', 6000);
                markOrderConfirmedUI(orderRef);
            } else {
                toast(`${data.saved} price${data.saved!=1?'s':''} saved — new revisions recorded.${data.errors?` (${data.errors} error${data.errors!=1?'s':''})`:''}` );
            }
        } else { toast('Bulk save encountered errors.', 'error'); }
    } catch(e) { toast('Network error. Please try again.', 'error'); }
    finally { bulkBtn.disabled=false; bulkBtn.innerHTML='<i class="fas fa-save"></i> Save All Changes'; }
}

async function toggleHistory(btn) {
    const icode   = btn.dataset.icode;
    const histRow = document.getElementById(`histrow_${icode}`);
    const revList = document.getElementById(`revlist_${icode}`);
    if (!histRow) return;
    const isOpen = histRow.classList.contains('open');
    document.querySelectorAll('.revision-row.open').forEach(r => r.classList.remove('open'));
    if (isOpen) return;
    histRow.classList.add('open');
    if (revList.dataset.loaded === '1') return;
    revList.innerHTML = '<span class="rev-loading"><i class="fas fa-spinner fa-spin"></i> Loading…</span>';
    try {
        const url  = `${window.location.pathname}?action=get_revisions&cus_id=${encodeURIComponent(CUS_ID)}&icode=${encodeURIComponent(icode)}`;
        const res  = await fetch(url);
        const data = await res.json();
        if (data.success && data.revisions.length > 0) {
            revList.innerHTML = data.revisions.map((r, i) => {
                const isLatest = i === 0;
                const date = new Date(r.saved_at).toLocaleString('en-GB',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
                const by   = r.saved_by ? ` &bull; ${escHtml(r.saved_by)}` : '';
                return `<span class="rev-chip ${isLatest?'latest':''}">
                    <span class="rev-chip-no">Rev ${r.revision_no}</span>
                    $${parseFloat(r.price).toFixed(2)} &nbsp;&middot;&nbsp; ${date}${by}
                    ${isLatest?' <i class="fas fa-star" style="font-size:.6rem;opacity:.7;"></i>':''}
                </span>`;
            }).join('');
            revList.dataset.loaded = '1';
        } else {
            revList.innerHTML = '<span class="rev-empty">No revisions recorded yet.</span>';
        }
    } catch(e) { revList.innerHTML = '<span class="rev-empty">Failed to load history.</span>'; }
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function exportCSV() {
    const table = document.getElementById('priceTable');
    if (!table) return;
    const rows = [...table.querySelectorAll('tr')];
    const csvRows = [];
    const headers = [...rows[0].querySelectorAll('th')].map(th => `"${th.textContent.trim()}"`);
    csvRows.push(headers.join(','));
    rows.slice(1).forEach(row => {
        if (!row.classList.contains('main-row')) return;
        const cells = [...row.querySelectorAll('td')].map((td,i) => {
            if (td.querySelector('.price-input')) { const inp=td.querySelector('input'); return `"${inp?inp.value.trim():''}"` ; }
            return `"${td.textContent.trim().replace(/"/g,'""')}"`;
        });
        csvRows.push(cells.join(','));
    });
    const blob = new Blob([csvRows.join('\n')],{type:'text/csv'});
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href=url; a.download=`price_list_<?php echo date('Ymd'); ?>.csv`;
    a.click(); URL.revokeObjectURL(url);
    toast('CSV exported successfully.');
}

document.addEventListener('DOMContentLoaded', () => {
    const selects = document.querySelectorAll('#brandSelect, #colorSelect, #rimSelect');
    selects.forEach(select => {
        select.addEventListener('change', () => {
            document.getElementById('filterForm').submit();
        });
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key==='Enter' && e.target.classList.contains('price-input')) {
        e.preventDefault();
        const inputs=[...document.querySelectorAll('.price-input')];
        const idx=inputs.indexOf(e.target);
        if (idx!==-1 && inputs[idx+1]) inputs[idx+1].focus();
    }
});
</script>
</body>
</html>