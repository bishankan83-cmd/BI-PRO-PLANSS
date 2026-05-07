<?php
session_start();
define('DB_SERVER', 'localhost');
define('DB_USER', 'planatir_task_managemen');
define('DB_PASS', 'Bishan@1919');
define('DB_NAME', 'planatir_cms');

if (!isset($_SESSION['id']) || strlen($_SESSION['id']) == 0) {
    header('location:index2.php'); exit;
}
$inventory = []; $message = ''; $messageType = 'info'; $userId = $_SESSION['id'];
define('CONTAINER_20FT_CAPACITY', 18000); define('CONTAINER_40FT_CAPACITY', 25000);

function getCurrencySymbol(string $code): string {
    $map = [
        'USD'=>'$','EUR'=>'€','GBP'=>'£','JPY'=>'¥','AUD'=>'A$','CAD'=>'C$','CHF'=>'CHF ',
        'CNY'=>'¥','LKR'=>'Rs ','INR'=>'₹','SGD'=>'S$','AED'=>'AED ','SAR'=>'SAR ','MYR'=>'RM ',
        'THB'=>'฿','HKD'=>'HK$','NZD'=>'NZ$','NOK'=>'NOK ','SEK'=>'SEK ','DKK'=>'DKK ',
        'ZAR'=>'R','BRL'=>'R$','MXN'=>'MX$','PHP'=>'₱','IDR'=>'Rp ','VND'=>'₫','KRW'=>'₩',
        'TRY'=>'₺','PKR'=>'Rs ','BDT'=>'৳',
    ];
    return $map[strtoupper(trim($code))] ?? (trim($code) . ' ');
}

$pdo = null;
try {
    $pdo = new PDO(
        "mysql:host=".DB_SERVER.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    $message = "Database connection failed: " . $e->getMessage(); $messageType = 'error';
}

/* ══ AJAX ══════════════════════════════════════════════════════════════════ */
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    try {
        $useOldTable  = (isset($_GET['table']) && $_GET['table'] === 'old');
        $ajaxUserId   = $_SESSION['id'];
        $ajaxCusRow   = $pdo->prepare("SELECT cus_id FROM users WHERE id=?");
        $ajaxCusRow->execute([$ajaxUserId]);
        $ajaxCusData  = $ajaxCusRow->fetch();
        $ajaxCusId    = $ajaxCusData['cus_id'] ?? $ajaxUserId;
        $ajaxAllowedBrands = []; $ajaxHasBrandRestriction = false;
        if (!empty($ajaxCusId)) {
            $ajaxBrandStmt = $pdo->prepare("SELECT DISTINCT brand FROM customers_brand WHERE cus_id=?");
            $ajaxBrandStmt->execute([$ajaxCusId]);
            $ajaxBrandRows = $ajaxBrandStmt->fetchAll();
            if (!empty($ajaxBrandRows)) {
                $ajaxAllowedBrands = array_column($ajaxBrandRows,'brand');
                $ajaxHasBrandRestriction = true;
            }
        }
        $whereConditions=[]; $params=[];
        $brandCol = $useOldTable ? 't.Brand' : 't.brand';
        $tireSizeCol = 't.tire_size';
        if ($ajaxHasBrandRestriction && !empty($ajaxAllowedBrands)) {
            $phs = implode(',',array_fill(0,count($ajaxAllowedBrands),'?'));
            $whereConditions[] = "$brandCol IN ($phs)";
            foreach ($ajaxAllowedBrands as $b) $params[] = $b;
        }
        $hasActiveFilters = !empty($_GET['icode_select'])||!empty($_GET['tire_size_select'])||!empty($_GET['brand_select'])&&$_GET['brand_select']!=='all'||(!empty($_GET['col_select'])&&$_GET['col_select']!=='all')||(!empty($_GET['rim_select'])&&$_GET['rim_select']!=='all');
        if (!$hasActiveFilters) { $whereConditions[]="(t.fweight IS NOT NULL AND t.fweight>0)"; }
        if (!empty($_GET['brand_select']) && $_GET['brand_select']!=='all') {
            $brandSelectRaw = $_GET['brand_select'];
            $selectedBrandsFilter = json_decode($brandSelectRaw,true);
            if (!is_array($selectedBrandsFilter)) $selectedBrandsFilter=[$brandSelectRaw];
            $selectedBrandsFilter = array_filter(array_map('trim',$selectedBrandsFilter));
            if (!empty($selectedBrandsFilter)) {
                $phs2=implode(',',array_fill(0,count($selectedBrandsFilter),'?'));
                $whereConditions[]="$brandCol IN ($phs2)";
                foreach ($selectedBrandsFilter as $sb) $params[]=$sb;
            }
        }
        $filterMap=['icode_select'=>'r.icode','tire_size_select'=>$tireSizeCol,'col_select'=>'r.col','rim_select'=>'r.rim'];
        foreach ($filterMap as $key=>$column) {
            if (!empty($_GET[$key])&&$_GET[$key]!=='all') {
                $whereConditions[]="$column LIKE ?"; $params[]='%'.$_GET[$key].'%';
            }
        }
        $sortOptions=['code_asc'=>'r.icode ASC','code_desc'=>'r.icode DESC','brand_asc'=>"$brandCol ASC",'brand_desc'=>"$brandCol DESC",'size_asc'=>'r.t_size ASC','size_desc'=>'r.t_size DESC','tire_size_asc'=>"$tireSizeCol ASC",'tire_size_desc'=>"$tireSizeCol DESC",'weight_asc'=>'t.fweight ASC','weight_desc'=>'t.fweight DESC'];
        $sortBy=(isset($_GET['sort'])&&isset($sortOptions[$_GET['sort']]))?$sortOptions[$_GET['sort']]:"$brandCol ASC, r.t_size ASC";
        $whereStr = empty($whereConditions)?'':' WHERE '.implode(' AND ',$whereConditions);
        if ($useOldTable) {
            $sql="SELECT r.id,r.icode,r.t_size,r.brand,r.col,r.rim,t.fweight,t.tire_size,t.cbm,t.Brand AS tire_brand,t.Description AS t_description,t.Colour AS t_colour,t.Type AS t_type FROM realstock r LEFT JOIN tire_details_old t ON r.icode=t.icode{$whereStr} ORDER BY {$sortBy}";
        } else {
            $sql="SELECT r.id,r.icode,r.t_size,r.brand,r.col,r.rim,t.fweight,t.tire_size,t.cbm,t.brand AS tire_brand FROM realstock r LEFT JOIN tire_details t ON r.icode=t.icode{$whereStr} ORDER BY {$sortBy}";
        }
        $stmt=$pdo->prepare($sql); $stmt->execute($params); $inventory=$stmt->fetchAll();
        $ajaxItemPrices=[]; $ajaxCusHasItemPrices=false;
        if (!empty($ajaxCusId)) {
            $stmt2=$pdo->prepare("SELECT icode,price FROM customer_items WHERE cus_id=?"); $stmt2->execute([$ajaxCusId]);
            $rows2=$stmt2->fetchAll(); $ajaxCusHasItemPrices=count($rows2)>0;
            foreach ($rows2 as $ci) $ajaxItemPrices[strtolower(trim($ci['icode']))]=(float)$ci['price'];
        }
        foreach ($inventory as &$row) {
            $ik=strtolower(trim($row['icode']??''));
            $row['customer_price']=isset($ajaxItemPrices[$ik])?$ajaxItemPrices[$ik]:null;
            if ($useOldTable&&empty($row['tire_brand'])) $row['tire_brand']=$row['brand']??'';
        }
        unset($row);
        echo json_encode(['success'=>true,'data'=>$inventory,'count'=>count($inventory),'cusHasItemPrices'=>$ajaxCusHasItemPrices,'usingOldTable'=>$useOldTable]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success'=>false,'error'=>$e->getMessage()]); exit;
    }
}

/* ══ ORDER PLACEMENT ═══════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['action'])&&$_POST['action']==='place_order') {
    $orderItems=json_decode($_POST['order_data']??'',true);
    if (!empty($orderItems)) {
        try {
            $pdo->beginTransaction();
            $totalQuantity=array_sum(array_column($orderItems,'quantity'));
            $totalWeight=array_sum(array_map(fn($i)=>$i['quantity']*$i['fweight'],$orderItems));
            $totalCBM=array_sum(array_map(fn($i)=>$i['quantity']*($i['cbm']??0),$orderItems));
            $stmt=$pdo->query("SELECT MAX(order_id) as max_id FROM tire_orders");
            $result=$stmt->fetch(); $newOrderId=($result['max_id']??0)+1;
            $orderNotes=$_POST['order_notes']??'';
            $stmt=$pdo->prepare("INSERT INTO tire_orders(order_id,customer_id,status,total_items,total_quantity,total_weight,total_cbm,order_notes,created_at) VALUES(?,?,'pending',?,?,?,?,?,NOW())");
            $stmt->execute([$newOrderId,$userId,count($orderItems),$totalQuantity,$totalWeight,$totalCBM,$orderNotes]);
            $orderSummary=[];
            foreach ($orderItems as $item) {
                if (!isset($item['id'],$item['icode'],$item['quantity'])||!is_numeric($item['quantity'])||$item['quantity']<=0) throw new Exception("Invalid order item data");
                $unitWeight=$item['fweight']??0; $totalItemWeight=$item['quantity']*$unitWeight;
                $unitCBM=$item['cbm']??0; $totalItemCBM=$item['quantity']*$unitCBM;
                $stmt=$pdo->prepare("INSERT INTO tire_order_items(order_id,product_id,icode,quantity,unit_weight,total_weight,unit_cbm,total_cbm) VALUES(?,?,?,?,?,?,?,?)");
                $stmt->execute([$newOrderId,$item['id'],$item['icode'],(int)$item['quantity'],$unitWeight,$totalItemWeight,$unitCBM,$totalItemCBM]);
                $orderSummary[]="{$item['icode']} ({$item['brand']} - {$item['size']}): {$item['quantity']} units, Weight: ".number_format($totalItemWeight,2)." kg, CBM: ".number_format($totalItemCBM,3);
            }
            $summaryDetails=implode("\n",$orderSummary);
            $stmt=$pdo->prepare("INSERT INTO order_summaries(order_id,user_id,total_items,total_quantity,total_weight,total_cbm,summary_details,created_at) VALUES(?,?,?,?,?,?,?,NOW())");
            $stmt->execute([$newOrderId,$userId,count($orderItems),$totalQuantity,$totalWeight,$totalCBM,$summaryDetails]);
            $pdo->commit();
            $_SESSION['order_success']=true; $_SESSION['order_id']=$newOrderId;
            $_SESSION['order_details']=['total_items'=>count($orderItems),'total_quantity'=>$totalQuantity,'total_weight'=>$totalWeight,'total_cbm'=>$totalCBM,'summary'=>$summaryDetails];
            header('Location: order_review.php?order_id='.$newOrderId); exit;
        } catch (Exception $e) {
            $pdo->rollBack(); $message="Order failed: ".$e->getMessage(); $messageType='error';
        }
    }
}

/* ══ MAIN PAGE DATA ════════════════════════════════════════════════════════ */
$functionalCurrency='USD'; $currencySymbol='$';
try {
    $stmt=$pdo->prepare("SELECT fullName,userEmail,functional_currency FROM users WHERE id=?"); $stmt->execute([$userId]);
    $userData=$stmt->fetch();
    if (!$userData) { header('location:index2.php'); exit; }
    $functionalCurrency=$userData['functional_currency']??'USD'; $currencySymbol=getCurrencySymbol($functionalCurrency);
    $stmt=$pdo->prepare("SELECT payment_rate FROM users WHERE id=?"); $stmt->execute([$userId]);
    $rateData=$stmt->fetch(); $paymentRate=$rateData?(float)$rateData['payment_rate']:0;
    $stmt=$pdo->prepare("SELECT cus_id FROM users WHERE id=?"); $stmt->execute([$userId]);
    $cusRow=$stmt->fetch(); $cusId=$cusRow['cus_id']??$userId;
    $allowedBrands=[]; $hasBrandRestriction=false;
    if (!empty($cusId)) {
        $brandStmt=$pdo->prepare("SELECT DISTINCT brand FROM customers_brand WHERE cus_id=?"); $brandStmt->execute([$cusId]);
        $brandRows=$brandStmt->fetchAll();
        if (!empty($brandRows)) { $allowedBrands=array_column($brandRows,'brand'); $hasBrandRestriction=true; }
    }
    $brandRates=[];
    if (!empty($cusId)) {
        $stmt=$pdo->prepare("SELECT brand,payment_rate FROM customer_rate WHERE cus_id=?"); $stmt->execute([$cusId]);
        foreach ($stmt->fetchAll() as $cr) $brandRates[strtolower(trim($cr['brand']))]=(float)$cr['payment_rate'];
    }
    $itemPrices=[]; $cusHasItemPrices=false;
    if (!empty($cusId)) {
        $stmt=$pdo->prepare("SELECT icode,price FROM customer_items WHERE cus_id=?"); $stmt->execute([$cusId]);
        $ciRows=$stmt->fetchAll(); $cusHasItemPrices=count($ciRows)>0;
        foreach ($ciRows as $ci) $itemPrices[strtolower(trim($ci['icode']))]=(float)$ci['price'];
    }
    if ($hasBrandRestriction&&!empty($allowedBrands)) {
        $phs=implode(',',array_fill(0,count($allowedBrands),'?'));
        $sql="SELECT r.id,r.icode,r.t_size,r.brand,r.col,r.rim,t.fweight,t.tire_size,t.cbm,t.brand AS tire_brand FROM realstock r LEFT JOIN tire_details t ON r.icode=t.icode WHERE t.fweight IS NOT NULL AND t.fweight>0 AND t.brand IN ($phs) ORDER BY t.brand ASC,r.t_size ASC";
        $stmt=$pdo->prepare($sql); $stmt->execute($allowedBrands);
    } else {
        $sql="SELECT r.id,r.icode,r.t_size,r.brand,r.col,r.rim,t.fweight,t.tire_size,t.cbm,t.brand AS tire_brand FROM realstock r LEFT JOIN tire_details t ON r.icode=t.icode WHERE t.fweight IS NOT NULL AND t.fweight>0 ORDER BY t.brand ASC,r.t_size ASC";
        $stmt=$pdo->query($sql);
    }
    $inventory=$stmt->fetchAll();
    foreach ($inventory as &$row) { $ik=strtolower(trim($row['icode']??'')); $row['customer_price']=isset($itemPrices[$ik])?$itemPrices[$ik]:null; }
    unset($row);
    $icodes=array_column($pdo->query("SELECT DISTINCT r.icode FROM realstock r WHERE r.icode IS NOT NULL AND r.icode!='' ORDER BY r.icode")->fetchAll(),'icode');
    $tireSizes=array_column($pdo->query("SELECT DISTINCT t.tire_size FROM tire_details t WHERE t.tire_size IS NOT NULL AND t.tire_size!='' ORDER BY t.tire_size")->fetchAll(),'tire_size');
    if ($hasBrandRestriction&&!empty($allowedBrands)) {
        $phs=implode(',',array_fill(0,count($allowedBrands),'?'));
        $bStmt=$pdo->prepare("SELECT DISTINCT t.brand FROM tire_details t WHERE t.brand IN ($phs) AND t.brand IS NOT NULL AND t.brand!='' ORDER BY t.brand"); $bStmt->execute($allowedBrands);
    } else {
        $bStmt=$pdo->query("SELECT DISTINCT t.brand FROM tire_details t WHERE t.brand IS NOT NULL AND t.brand!='' ORDER BY t.brand");
    }
    $brands=array_column($bStmt->fetchAll(),'brand');
    $colors=array_column($pdo->query("SELECT DISTINCT col FROM realstock WHERE col IS NOT NULL AND col!='' ORDER BY col")->fetchAll(),'col');
    $rims=array_column($pdo->query("SELECT DISTINCT rim FROM realstock WHERE rim IS NOT NULL AND rim!='' ORDER BY rim")->fetchAll(),'rim');
} catch (PDOException $e) {
    $message="Database error: ".$e->getMessage(); $messageType='error';
    $inventory=[]; $itemPrices=[]; $cusHasItemPrices=false;
    $functionalCurrency='USD'; $currencySymbol='$';
    $brands=$colors=$rims=$icodes=$tireSizes=[];
}
$initials=strtoupper(substr($userData['fullName'],0,1));
if (strpos($userData['fullName'],' ')!==false) $initials.=strtoupper(substr($userData['fullName'],strpos($userData['fullName'],' ')+1,1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<title>Place Order — ATIRE</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@font-face{font-family:'SF UI Display';font-weight:500;font-style:normal;src:url('font/sf-ui-display-medium-58646be638f96.otf') format('opentype')}
@font-face{font-family:'SF UI Display';font-weight:600;font-style:normal;src:url('font/sf-ui-display-semibold-58646eddcae92.otf') format('opentype')}
@font-face{font-family:'SF UI Display';font-weight:700;font-style:normal;src:url('font/sf-ui-display-bold-58646a511e3d9.otf') format('opentype')}
@font-face{font-family:'SF UI Display';font-weight:800;font-style:normal;src:url('font/sf-ui-display-heavy-586470160b9e5.otf') format('opentype')}
@font-face{font-family:'SF UI Display';font-weight:900;font-style:normal;src:url('font/sf-ui-display-black-58646a6b80d5a.otf') format('opentype')}

:root{
    --orange:#f28018;--orange-dk:#d06e10;--orange-lt:rgba(242,128,24,0.10);
    --orange-glow:rgba(242,128,24,0.18);--gray-50:#f9f9f9;--gray-100:#f2f2f2;
    --gray-200:#e4e4e4;--gray-300:#d0d0d0;--gray-400:#b0b0b0;--gray-500:#888888;
    --gray-700:#444444;--gray-900:#1a1a1a;--white:#ffffff;--bg:#f3f4f6;
    --sidebar-w:270px;--font:'SF UI Display',-apple-system,BlinkMacSystemFont,sans-serif;
    --radius-xs:4px;--radius-sm:8px;--radius-md:12px;--radius-lg:16px;
    --shadow-sm:0 1px 6px rgba(0,0,0,0.06);--shadow:0 2px 14px rgba(0,0,0,0.08);
    --shadow-md:0 6px 28px rgba(0,0,0,0.12);--shadow-lg:0 12px 48px rgba(0,0,0,0.14);
    --trans:0.18s cubic-bezier(0.4,0,0.2,1);--hdr-h:56px;
    --dock-h:0px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:var(--font);background:var(--bg);color:var(--gray-700);min-height:100vh;overflow-x:hidden;font-size:13.5px;line-height:1.5;-webkit-font-smoothing:antialiased;-webkit-tap-highlight-color:transparent}
::-webkit-scrollbar{width:4px;height:4px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--gray-300);border-radius:99px}
::-webkit-scrollbar-thumb:hover{background:var(--orange)}

/* ═══ HEADER ═══════════════════════════════════════════════════════════════ */
.hdr{position:sticky;top:0;z-index:500;background:var(--white);border-bottom:2.5px solid var(--orange);box-shadow:0 2px 20px rgba(0,0,0,0.08);height:var(--hdr-h)}
.hdr-inner{max-width:1800px;margin:0 auto;padding:0 1rem;height:100%;display:flex;align-items:center;justify-content:space-between;gap:.75rem}
.brand{display:flex;align-items:center;gap:10px;text-decoration:none;flex-shrink:0}
.brand-logo{height:28px;width:auto}
.hdr-currency{display:inline-flex;align-items:center;gap:4px;padding:4px 9px;border-radius:var(--radius-sm);background:var(--orange-lt);border:1.5px solid rgba(242,128,24,0.25);font-size:10px;font-weight:800;color:var(--orange);letter-spacing:.06em;white-space:nowrap}
.hdr-currency i{font-size:8px}
.hdr-right{display:flex;align-items:center;gap:6px}
.hdr-btn{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:var(--radius-sm);font-weight:700;font-size:11.5px;letter-spacing:.03em;text-decoration:none;border:1.5px solid var(--gray-200);background:var(--white);color:var(--gray-500);cursor:pointer;transition:var(--trans);white-space:nowrap}
.hdr-btn:hover{border-color:var(--orange);color:var(--orange);background:var(--orange-lt)}
.hdr-btn span.btn-text{display:inline}
.avatar{width:32px;height:32px;border-radius:50%;background:var(--orange);color:var(--white);display:flex;align-items:center;justify-content:center;font-weight:900;font-size:11px;box-shadow:0 2px 8px rgba(242,128,24,0.35);flex-shrink:0}

/* ═══ MOBILE FILTER TOGGLE ══════════════════════════════════════════════════ */
.mobile-filter-bar{display:none;align-items:center;justify-content:space-between;padding:.6rem 1rem;background:var(--white);border-bottom:1px solid var(--gray-200);gap:.5rem;position:sticky;top:var(--hdr-h);z-index:400}
.mobile-filter-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:var(--radius-sm);font-family:var(--font);font-size:12px;font-weight:700;border:1.5px solid var(--gray-200);background:var(--white);color:var(--gray-700);cursor:pointer;transition:var(--trans)}
.mobile-filter-btn.active{background:var(--orange);border-color:var(--orange-dk);color:var(--white)}
.mobile-filter-btn .filter-count-badge{background:rgba(255,255,255,0.3);border-radius:20px;padding:1px 6px;font-size:9px;font-weight:900}
.mobile-tbl-toggle{display:inline-flex;align-items:center;gap:6px;padding:7px 12px;border-radius:var(--radius-sm);font-family:var(--font);font-size:11.5px;font-weight:700;border:1.5px solid var(--gray-200);background:var(--white);color:var(--gray-500);cursor:pointer;transition:var(--trans)}
.mobile-tbl-toggle.active-old{background:var(--orange);border-color:var(--orange-dk);color:var(--white)}

/* ═══ PAGE SHELL ════════════════════════════════════════════════════════════ */
.page-shell{display:flex;max-width:1800px;margin:0 auto;min-height:calc(100vh - var(--hdr-h))}

/* ═══ LEFT SIDEBAR ══════════════════════════════════════════════════════════ */
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:450;backdrop-filter:blur(2px)}
.sidebar-overlay.open{display:block}
.left-sidebar{width:var(--sidebar-w);flex-shrink:0;background:var(--white);border-right:1.5px solid var(--gray-200);position:sticky;top:var(--hdr-h);height:calc(100vh - var(--hdr-h));overflow-y:auto;overflow-x:hidden;display:flex;flex-direction:column;transition:transform var(--trans)}
.sidebar-close-btn{display:none;position:absolute;top:12px;right:12px;background:none;border:none;font-size:18px;color:var(--gray-500);cursor:pointer;padding:4px;border-radius:var(--radius-xs);z-index:2}
.sidebar-close-btn:hover{color:var(--orange)}
.sidebar-filters{padding:1rem 1.1rem;flex:1;position:relative}
.sidebar-hd{font-size:10px;font-weight:800;color:var(--gray-700);letter-spacing:.12em;text-transform:uppercase;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between}
.sidebar-hd i{color:var(--orange);margin-right:4px;font-size:9.5px}
.spin-ico{color:var(--orange);display:none;font-size:10px}
.active-filter-chips{display:flex;flex-wrap:wrap;gap:4px;margin-bottom:8px}
.filter-chip{background:var(--orange);color:var(--white);border-radius:20px;padding:3px 9px 3px 8px;font-size:10px;font-weight:700;display:flex;align-items:center;gap:4px;cursor:pointer;transition:var(--trans)}
.filter-chip:hover{background:var(--orange-dk)}
.filter-chip i{font-size:8px;opacity:.8}
.sb-divider{height:1px;background:var(--gray-100);margin:10px 0}
.fg{margin-bottom:9px}
.fg label{display:flex;align-items:center;gap:4px;margin-bottom:4px;font-size:9.5px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.09em}
.fg label i{color:var(--orange);font-size:8.5px}
.fg input[type="text"],.fg select{width:100%;padding:8px 10px;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);font-family:var(--font);font-size:13px;font-weight:600;color:var(--gray-700);background:var(--white);transition:var(--trans);outline:none;appearance:none;-webkit-appearance:none;touch-action:manipulation}
.fg select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='11' height='6' viewBox='0 0 11 6'%3E%3Cpath d='M1 1l4.5 4 4.5-4' stroke='%23aaa' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;padding-right:28px}
.fg input[type="text"]:focus,.fg select:focus{border-color:var(--orange);box-shadow:0 0 0 3px var(--orange-glow)}
.fg input.active-filter,.fg select.active-filter{border-color:var(--orange);background:rgba(242,128,24,0.03)}

/* Multi-brand selector */
.brand-multi-wrap{position:relative}
.brand-multi-display{width:100%;padding:8px 10px;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);font-family:var(--font);font-size:13px;font-weight:600;color:var(--gray-700);background:var(--white);transition:var(--trans);outline:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:6px;user-select:none;min-height:38px;-webkit-tap-highlight-color:transparent}
.brand-multi-display:hover,.brand-multi-display.open{border-color:var(--orange);box-shadow:0 0 0 3px var(--orange-glow)}
.brand-multi-display.active-filter{border-color:var(--orange);background:rgba(242,128,24,0.03)}
.brand-multi-display-text{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px}
.brand-multi-display-arrow{color:var(--gray-400);font-size:9px;flex-shrink:0;transition:transform var(--trans)}
.brand-multi-display.open .brand-multi-display-arrow{transform:rotate(180deg)}
.brand-multi-selected-pills{display:flex;flex-wrap:wrap;gap:3px;margin-top:5px}
.brand-pill{display:inline-flex;align-items:center;gap:4px;background:var(--orange);color:var(--white);border-radius:20px;padding:2px 8px 2px 7px;font-size:10px;font-weight:700;cursor:pointer;transition:var(--trans)}
.brand-pill:hover{background:var(--orange-dk)}
.brand-pill i{font-size:7.5px;opacity:.8}
.brand-multi-dropdown{position:absolute;top:calc(100% + 4px);left:0;right:0;z-index:9999;background:var(--white);border:2px solid var(--orange);border-radius:var(--radius-sm);box-shadow:var(--shadow-md);max-height:220px;overflow-y:auto;display:none}
.brand-multi-dropdown.open{display:block;animation:fadeDown .18s ease}
@keyframes fadeDown{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
.brand-multi-search{padding:8px 10px;border-bottom:1px solid var(--gray-200);position:sticky;top:0;background:var(--white);z-index:2}
.brand-multi-search input{width:100%;border:1.5px solid var(--gray-200);border-radius:var(--radius-xs);padding:6px 8px;font-family:var(--font);font-size:13px;font-weight:600;outline:none;transition:var(--trans)}
.brand-multi-search input:focus{border-color:var(--orange)}
.brand-multi-option{padding:10px 12px;cursor:pointer;transition:background var(--trans);font-size:13px;font-weight:600;color:var(--gray-700);display:flex;align-items:center;gap:8px;border-left:3px solid transparent;min-height:44px}
.brand-multi-option:hover{background:rgba(242,128,24,0.06);border-left-color:var(--orange);color:var(--orange)}
.brand-multi-option.selected{background:rgba(242,128,24,0.08);border-left-color:var(--orange);color:var(--orange)}
.brand-multi-option .bm-check{width:16px;height:16px;border-radius:3px;border:1.5px solid var(--gray-300);flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:var(--trans);font-size:9px;color:var(--white)}
.brand-multi-option.selected .bm-check{background:var(--orange);border-color:var(--orange)}
.brand-multi-option.selected .bm-check::after{content:'✓'}
.brand-multi-footer{padding:6px 10px;border-top:1px solid var(--gray-200);display:flex;align-items:center;justify-content:space-between;position:sticky;bottom:0;background:var(--white);font-size:10px;font-weight:700;color:var(--gray-500)}
.brand-multi-footer-clear{color:var(--orange);cursor:pointer;font-weight:700;transition:opacity var(--trans)}
.brand-multi-footer-clear:hover{opacity:.7}

.sort-row{display:flex;flex-wrap:wrap;gap:4px}
.sort-chip{padding:5px 10px;border-radius:20px;font-size:10.5px;font-weight:700;background:var(--white);border:1.5px solid var(--gray-200);color:var(--gray-500);cursor:pointer;transition:var(--trans);touch-action:manipulation}
.sort-chip:hover{border-color:var(--orange);color:var(--orange)}
.sort-chip.active{background:var(--orange);border-color:var(--orange);color:var(--white)}
.btn-clear-all{width:100%;padding:9px;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);background:var(--white);color:var(--gray-500);font-family:var(--font);font-size:11.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;transition:var(--trans);margin-top:4px;min-height:40px}
.btn-clear-all:hover{border-color:var(--orange);color:var(--orange);background:var(--orange-lt)}

/* ═══ RIGHT CONTENT ══════════════════════════════════════════════════════════ */
.right-content{flex:1;min-width:0;display:flex;flex-direction:column}

/* ═══ HERO BANNER ═══════════════════════════════════════════════════════════ */
.hero-banner{background:var(--white);border-bottom:1px solid var(--gray-100);padding:.8rem 1.2rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.hero-center{flex:0 0 auto}
.hero-banner-eyebrow{font-size:9px;font-weight:800;color:var(--orange);letter-spacing:.22em;text-transform:uppercase;margin-bottom:4px;display:flex;align-items:center;gap:6px}
.hero-banner-eyebrow::before{content:'';width:14px;height:2px;background:var(--orange);border-radius:2px}
.hero-banner-title{font-size:clamp(24px,4vw,40px);font-weight:900;color:var(--gray-900);letter-spacing:-.02em;line-height:1}
.hero-banner-title span{color:var(--orange)}
.hero-banner-sub{font-size:11px;font-weight:500;color:var(--gray-400);margin-top:4px}
.container-col{flex-shrink:0;max-width:100%}
.container-row{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}

/* ═══ CONTAINER CARD ════════════════════════════════════════════════════════ */
.ctr-card{background:var(--white);border:1.5px solid var(--gray-200);border-radius:var(--radius-md);padding:8px 10px 7px;min-width:220px;max-width:100%;display:none;box-shadow:var(--shadow);transition:border-color var(--trans)}
.ctr-card.show{display:block;animation:fadeUp .3s ease}
.ctr-card.overloaded{border-color:var(--orange);box-shadow:0 0 0 3px var(--orange-glow)}
@keyframes fadeUp{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
.ctr-label{font-size:9px;font-weight:800;color:var(--gray-400);letter-spacing:.14em;text-transform:uppercase;display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.overload-badge{background:var(--orange);color:var(--white);padding:2px 8px;border-radius:20px;font-size:9px;font-weight:700;display:none;animation:pulse 1s infinite}
.ctr-card.overloaded .overload-badge{display:inline-block}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.55}}
.ctr-svg-wrap{margin-bottom:8px}
.ctr-svg{width:100%;height:auto;display:block}
.cargo-fill{transition:width .6s cubic-bezier(.4,0,.2,1)}
.ctr-stats{display:flex;align-items:center;justify-content:space-between}
.ctr-pct{font-size:1.8rem;font-weight:900;color:var(--orange);line-height:1;letter-spacing:-.04em}
.ctr-card.overloaded .ctr-pct{animation:blink .9s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.4}}
.ctr-info{text-align:right}
.ctr-wt{font-size:11px;font-weight:700;color:var(--gray-700)}
.ctr-cap{font-size:10px;font-weight:500;color:var(--gray-400)}

/* ═══ MESSAGE ═══════════════════════════════════════════════════════════════ */
.msg-bar{padding:.7rem 1rem 0}
.msg{padding:10px 14px;border-radius:var(--radius-sm);display:flex;align-items:center;gap:10px;font-weight:600;font-size:13px;border-left:3px solid var(--orange);background:rgba(242,128,24,0.07);color:#7a4400}
.msg.error{background:rgba(200,50,50,0.06);color:#7a1a1a;border-color:#e05555}

/* ═══ PAGE BODY ══════════════════════════════════════════════════════════════ */
.page-body{padding:0 0 10rem;flex:1}

/* ═══ INVENTORY PANEL ═══════════════════════════════════════════════════════ */
.inv-panel{background:var(--white);border:none;border-radius:0;box-shadow:none;position:relative}
.loading-veil{position:absolute;inset:0;background:rgba(255,255,255,0.90);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:20}
.loading-veil.on{display:flex}
.spinner{width:32px;height:32px;border:3px solid var(--gray-200);border-top-color:var(--orange);border-radius:50%;animation:spin .65s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.inv-header{padding:.7rem 1rem;border-bottom:1.5px solid var(--gray-100);display:flex;align-items:center;justify-content:space-between;gap:.75rem;background:var(--white);position:sticky;top:var(--hdr-h);z-index:10;flex-wrap:wrap}
.inv-header-left{display:flex;align-items:center;gap:8px}
.inv-title{font-size:12px;font-weight:800;color:var(--gray-700);letter-spacing:.05em;text-transform:uppercase;display:flex;align-items:center;gap:7px}
.inv-title-icon{width:26px;height:26px;border-radius:var(--radius-xs);background:var(--orange);color:var(--white);display:flex;align-items:center;justify-content:center;font-size:11px}
.count-pill{padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;background:var(--gray-100);color:var(--gray-500);transition:var(--trans)}
.count-pill.pop{animation:popAnim .4s ease}
@keyframes popAnim{0%,100%{transform:scale(1)}50%{transform:scale(1.12);background:var(--orange);color:var(--white)}}
.tbl-toggle-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 12px;border-radius:var(--radius-sm);font-family:var(--font);font-size:11.5px;font-weight:700;letter-spacing:.03em;cursor:pointer;transition:var(--trans);white-space:nowrap;border:1.5px solid var(--gray-200);background:var(--white);color:var(--gray-500)}
.tbl-toggle-btn:hover{border-color:var(--orange);color:var(--orange);background:var(--orange-lt)}
.tbl-toggle-btn.active-old{background:var(--orange);border-color:var(--orange-dk);color:var(--white);box-shadow:0 3px 12px rgba(242,128,24,0.30)}
.tbl-toggle-btn i{font-size:10px}
.old-table-banner{display:none;align-items:center;gap:10px;padding:7px 1rem;background:linear-gradient(90deg,rgba(242,128,24,0.08) 0%,rgba(242,128,24,0.03) 100%);border-bottom:1.5px solid rgba(242,128,24,0.20);font-size:12px;font-weight:600;color:#7a4400;flex-wrap:wrap}
.old-table-banner.show{display:flex}
.old-table-banner i{color:var(--orange);font-size:11px}
.old-table-dot{width:8px;height:8px;border-radius:50%;background:var(--orange);animation:pulse 1.2s infinite;flex-shrink:0}
.filter-info-strip{background:rgba(242,128,24,0.06);border-bottom:1px solid rgba(242,128,24,0.15);padding:6px 1rem;font-size:11.5px;color:#8a5000;font-weight:600;display:none;align-items:center;gap:8px}
.filter-info-strip.show{display:flex}

/* Brand group headers */
.brand-group-header td{background:linear-gradient(90deg,rgba(242,128,24,0.10) 0%,rgba(242,128,24,0.04) 100%) !important;border-top:2px solid rgba(242,128,24,0.35) !important;border-bottom:1px solid rgba(242,128,24,0.20) !important;padding:5px 10px !important}
.brand-group-label{font-size:10.5px;font-weight:900;color:var(--orange);letter-spacing:.12em;text-transform:uppercase;display:flex;align-items:center;gap:7px}
.brand-group-label i{font-size:9px}
.brand-group-count{background:rgba(242,128,24,0.15);color:var(--orange-dk);border:1px solid rgba(242,128,24,0.30);border-radius:20px;padding:1px 8px;font-size:9px;font-weight:800;letter-spacing:.06em}

/* Table */
.tbl-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
.tbl-scroll{overflow-y:auto;overflow-x:auto;-webkit-overflow-scrolling:touch;max-height:calc(100vh - var(--hdr-h) - 150px);min-height:300px}
table.inv-tbl{width:100%;border-collapse:collapse;table-layout:auto;min-width:700px}
table.inv-tbl thead{background:#f7f7f7;position:sticky;top:0;z-index:9}
table.inv-tbl thead::after{content:'';display:block;position:absolute;bottom:-2px;left:0;right:0;height:2px;background:var(--gray-200)}
table.inv-tbl th{padding:8px 10px;text-align:left;font-size:10px;font-weight:800;color:var(--gray-500);letter-spacing:.11em;text-transform:uppercase;white-space:nowrap;border-right:1px solid var(--gray-200);border-bottom:2px solid var(--gray-200);overflow:hidden;text-overflow:ellipsis;user-select:none}
table.inv-tbl th:last-child{border-right:none}
table.inv-tbl th i{color:var(--orange);margin-right:4px;font-size:9px}
table.inv-tbl tbody tr{transition:background var(--trans);border-bottom:1px solid var(--gray-100)}
table.inv-tbl tbody tr:nth-child(even):not(.brand-group-header){background:#fafafa}
table.inv-tbl tbody tr:hover:not(.brand-group-header){background:rgba(242,128,24,0.04)}
table.inv-tbl tbody tr.selected{background:rgba(242,128,24,0.06)}
table.inv-tbl td{padding:7px 10px;font-size:12.5px;color:var(--gray-700);font-weight:500;vertical-align:middle}
td.code-cell{font-weight:800;font-size:13px;color:var(--orange);letter-spacing:.01em;white-space:nowrap}
td.desc-cell{font-weight:600;font-size:12px;color:var(--gray-700);white-space:normal;word-break:break-word;min-width:120px}
td.brand-cell{font-weight:700;color:var(--gray-900);font-size:12.5px;white-space:nowrap}
td.tiresize-cell{font-size:11.5px;font-weight:600;color:var(--gray-500);white-space:nowrap}
td.num-cell{font-weight:700;font-size:12px;color:var(--gray-700);text-align:right;white-space:nowrap}
td.price-cell{font-weight:700;font-size:12px;color:var(--gray-700);text-align:right;white-space:nowrap}
td.type-cell{font-size:11px;font-weight:600;color:var(--gray-400);white-space:nowrap}
.type-badge{display:inline-flex;align-items:center;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;background:#e8f4ff;color:#2563eb;border:1px solid #bfdbfe;white-space:nowrap}
.price-specific{display:inline-flex;align-items:center;gap:4px;color:#166534}
.price-specific-badge{display:inline-block;background:#dcfce7;color:#15803d;border:1px solid #86efac;border-radius:20px;padding:1px 6px;font-size:9px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
.price-rate{color:var(--gray-500);font-size:11.5px}
.price-approx{display:inline-flex;align-items:center;gap:4px;color:#92400e}
.price-approx-badge{display:inline-block;background:#fef3c7;color:#b45309;border:1px solid #fcd34d;border-radius:20px;padding:1px 6px;font-size:9px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
.color-badge,.rim-badge{display:inline-flex;align-items:center;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;background:var(--gray-100);color:var(--gray-500);border:1px solid var(--gray-200);white-space:nowrap}
.qty-inp{width:62px;text-align:center;border:2px solid var(--gray-200);border-radius:var(--radius-sm);padding:6px 4px;font-family:var(--font);font-weight:800;font-size:14px;color:var(--gray-700);background:var(--white);transition:var(--trans);outline:none;-moz-appearance:textfield;touch-action:manipulation}
.qty-inp::-webkit-outer-spin-button,.qty-inp::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
.qty-inp:focus{border-color:var(--orange);box-shadow:0 0 0 3px var(--orange-glow)}
.qty-inp.filled{border-color:var(--orange);background:rgba(242,128,24,0.06);color:var(--orange)}
.empty-state{text-align:center;padding:4rem 2rem}
.empty-state i{font-size:3rem;color:var(--gray-300);margin-bottom:14px;display:block}
.empty-state h3{font-size:18px;font-weight:800;color:var(--gray-700)}
.empty-state p{font-size:13px;color:var(--gray-400);margin-top:5px;font-weight:500}

/* ═══ MOBILE CARD VIEW ══════════════════════════════════════════════════════ */
.mobile-cards{display:none;padding:.5rem}
.m-card{background:var(--white);border:1.5px solid var(--gray-200);border-radius:var(--radius-md);margin-bottom:.5rem;overflow:hidden;transition:border-color var(--trans)}
.m-card.selected{border-color:var(--orange);background:rgba(242,128,24,0.02)}
.m-card-header{padding:.65rem .75rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem;border-bottom:1px solid var(--gray-100)}
.m-card-code{font-weight:900;font-size:13.5px;color:var(--orange);letter-spacing:.02em}
.m-card-brand{font-weight:700;font-size:12px;color:var(--gray-700)}
.m-card-body{padding:.6rem .75rem;display:grid;grid-template-columns:1fr 1fr;gap:.35rem .75rem}
.m-card-row{display:flex;flex-direction:column;gap:1px}
.m-card-lbl{font-size:9px;font-weight:800;color:var(--gray-400);letter-spacing:.10em;text-transform:uppercase}
.m-card-val{font-size:12px;font-weight:600;color:var(--gray-700)}
.m-card-footer{padding:.6rem .75rem;border-top:1px solid var(--gray-100);display:flex;align-items:center;justify-content:space-between;gap:.75rem;background:#fafafa}
.m-card-price{flex:1}
.m-card-price-lbl{font-size:9px;font-weight:800;color:var(--gray-400);letter-spacing:.10em;text-transform:uppercase;margin-bottom:2px}
.m-qty-wrap{display:flex;align-items:center;gap:6px}
.m-qty-btn{width:32px;height:32px;border-radius:var(--radius-sm);background:var(--white);border:1.5px solid var(--gray-200);color:var(--gray-500);font-size:14px;font-weight:700;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:var(--trans);flex-shrink:0;touch-action:manipulation}
.m-qty-btn:hover,.m-qty-btn:active{background:var(--orange);border-color:var(--orange);color:var(--white)}
.m-qty-inp{width:52px;text-align:center;border:2px solid var(--gray-200);border-radius:var(--radius-sm);padding:5px 2px;font-family:var(--font);font-weight:800;font-size:15px;color:var(--gray-700);background:var(--white);transition:var(--trans);outline:none;-moz-appearance:textfield;touch-action:manipulation}
.m-qty-inp::-webkit-outer-spin-button,.m-qty-inp::-webkit-inner-spin-button{-webkit-appearance:none}
.m-qty-inp:focus{border-color:var(--orange);box-shadow:0 0 0 3px var(--orange-glow)}
.m-qty-inp.filled{border-color:var(--orange);background:rgba(242,128,24,0.06);color:var(--orange)}
.mobile-empty{text-align:center;padding:3rem 1rem}
.mobile-empty i{font-size:2.5rem;color:var(--gray-300);margin-bottom:12px;display:block}
.mobile-empty h3{font-size:16px;font-weight:800;color:var(--gray-700)}
.mobile-empty p{font-size:12px;color:var(--gray-400);margin-top:4px}

/* ═══ ORDER DOCK ════════════════════════════════════════════════════════════ */
.order-dock{position:fixed;bottom:0;left:0;right:0;z-index:500;transform:translateY(100%);transition:transform .36s cubic-bezier(.4,0,.2,1);box-shadow:0 -4px 32px rgba(0,0,0,.12);background:var(--white);border-top:3px solid var(--orange);max-height:85vh;overflow-y:auto;-webkit-overflow-scrolling:touch}
.order-dock.open{transform:translateY(0)}
.dock-header{background:var(--orange);padding:10px 1rem;display:flex;align-items:center;justify-content:space-between;cursor:pointer;position:sticky;top:0;z-index:10;-webkit-tap-highlight-color:transparent}
.dock-title{font-size:12px;font-weight:900;color:var(--white);letter-spacing:.05em;text-transform:uppercase;display:flex;align-items:center;gap:7px}
.dock-sub{font-size:9px;color:rgba(255,255,255,.70);font-weight:500}
.dock-stats{display:flex;gap:5px;align-items:center;flex-wrap:nowrap;overflow-x:auto;-webkit-overflow-scrolling:touch}
.stat-tile{background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.25);border-radius:var(--radius-sm);padding:4px 10px;text-align:center;min-width:55px;flex-shrink:0}
.stat-tile-lbl{font-size:7.5px;color:rgba(255,255,255,.70);font-weight:700;letter-spacing:.10em;text-transform:uppercase}
.stat-tile-val{font-size:12.5px;font-weight:900;color:var(--white);line-height:1.2}
.dock-toggle{background:none;border:none;color:rgba(255,255,255,.65);font-size:12px;cursor:pointer;padding:6px;flex-shrink:0}
.dock-body{display:none}
.dock-body.open{display:block}
.disclaimer{margin:.75rem 1rem 0;padding:8px 12px;background:rgba(242,128,24,0.07);border:1px solid rgba(242,128,24,0.20);border-left:3px solid var(--orange);border-radius:var(--radius-sm);font-size:11px;color:#7a4400;font-weight:500}
.disclaimer i{color:var(--orange);margin-right:5px}

/* Mobile dock items */
.dock-items-list{margin:.75rem 1rem;display:flex;flex-direction:column;gap:.4rem}
.dock-item-card{background:var(--white);border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);overflow:hidden}
.dock-item-top{padding:.5rem .65rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem;border-bottom:1px solid var(--gray-100)}
.dock-item-code{font-weight:900;font-size:13px;color:var(--orange)}
.dock-item-brand{font-size:11.5px;font-weight:600;color:var(--gray-500)}
.dock-item-mid{padding:.4rem .65rem;display:grid;grid-template-columns:1fr 1fr 1fr;gap:.25rem;border-bottom:1px solid var(--gray-100);background:#fafafa}
.dock-item-mid-cell{display:flex;flex-direction:column;gap:1px}
.dock-item-mid-lbl{font-size:8.5px;font-weight:800;color:var(--gray-400);letter-spacing:.09em;text-transform:uppercase}
.dock-item-mid-val{font-size:11.5px;font-weight:700;color:var(--gray-700)}
.dock-item-bottom{padding:.5rem .65rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem}
.dock-item-qty-wrap{display:flex;align-items:center;gap:5px}
.dock-item-qty-btn{width:28px;height:28px;border-radius:var(--radius-xs);background:var(--white);border:1.5px solid var(--gray-200);color:var(--gray-500);font-size:13px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:var(--trans);touch-action:manipulation}
.dock-item-qty-btn:active{background:var(--orange);border-color:var(--orange);color:var(--white)}
.dock-qty{width:46px;text-align:center;border:1.5px solid var(--gray-200);border-radius:var(--radius-xs);padding:4px;font-family:var(--font);font-weight:800;font-size:13px;color:var(--gray-700);background:var(--white);outline:none;transition:var(--trans);-moz-appearance:textfield;touch-action:manipulation}
.dock-qty::-webkit-outer-spin-button,.dock-qty::-webkit-inner-spin-button{-webkit-appearance:none}
.dock-qty:focus{border-color:var(--orange);box-shadow:0 0 0 2px var(--orange-glow)}
.dock-item-price{font-size:12px;font-weight:700;color:var(--gray-700);text-align:right}
.rm-btn{background:none;border:none;color:var(--gray-300);cursor:pointer;padding:5px;border-radius:var(--radius-xs);font-size:12px;transition:var(--trans);touch-action:manipulation}
.rm-btn:hover,.rm-btn:active{color:var(--orange);background:var(--orange-lt)}

/* Desktop dock table */
.dock-table-wrap{margin:.75rem 1rem;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);max-height:240px;overflow-y:auto;overflow-x:auto;-webkit-overflow-scrolling:touch;display:none}
table.dock-tbl{width:100%;border-collapse:collapse;min-width:700px}
table.dock-tbl thead{background:#f5f5f5;position:sticky;top:0;z-index:5}
table.dock-tbl th{padding:8px 10px;text-align:left;font-size:9.5px;font-weight:800;color:#555;letter-spacing:.10em;text-transform:uppercase;white-space:nowrap;border-right:1px solid var(--gray-200)}
table.dock-tbl th:last-child{border-right:none}
table.dock-tbl td{padding:7px 10px;border-bottom:1px solid var(--gray-100);font-size:12px;font-weight:500;color:#555;vertical-align:middle;white-space:nowrap}
table.dock-tbl tbody tr:hover{background:rgba(242,128,24,0.04)}
table.dock-tbl td.dc{font-weight:800;font-size:13px;color:var(--orange)}
table.dock-tbl td.dw{font-weight:700;color:var(--gray-900)}
table.dock-tbl td.dr{text-align:right;font-weight:700}
table.dock-tbl td.dv{text-align:right;font-weight:700;color:#166534}

.dock-actions{display:flex;gap:8px;padding:.75rem 1rem 1rem;border-top:1.5px solid var(--gray-100)}
.dock-actions form{display:contents}
.dbtn{flex:1;padding:12px 14px;border:none;border-radius:var(--radius-sm);font-family:var(--font);font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:var(--trans);touch-action:manipulation;min-height:46px}
.dbtn-submit{background:var(--orange);color:var(--white)}
.dbtn-submit:hover{background:var(--orange-dk)}
.dbtn-submit:active{transform:scale(.98)}
.dbtn-clear{background:var(--white);color:var(--gray-500);border:1.5px solid var(--gray-200)}
.dbtn-clear:hover{border-color:var(--orange);color:var(--orange)}

/* ═══ AUTOCOMPLETE ══════════════════════════════════════════════════════════ */
.ui-autocomplete{max-height:200px;overflow-y:auto;background:var(--white) !important;border:2px solid var(--orange) !important;border-radius:var(--radius-sm) !important;box-shadow:var(--shadow-md) !important;z-index:9999 !important;font-family:var(--font) !important}
.ui-menu-item-wrapper{padding:10px 12px;font-family:var(--font) !important;font-weight:600;font-size:13px;color:#444;border-left:3px solid transparent;transition:var(--trans);min-height:44px;display:flex;align-items:center}
.ui-menu-item-wrapper:hover,.ui-state-active{background:rgba(242,128,24,0.08) !important;border-left-color:var(--orange) !important;color:var(--orange) !important}

/* ═══ APPROX MODAL ══════════════════════════════════════════════════════════ */
.approx-modal-backdrop{position:fixed;inset:0;z-index:9000;background:rgba(0,0,0,0.48);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;padding:1rem;opacity:0;pointer-events:none;transition:opacity .22s ease}
.approx-modal-backdrop.visible{opacity:1;pointer-events:all}
.approx-modal{background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);width:100%;max-width:420px;overflow:hidden;transform:translateY(18px) scale(0.97);transition:transform .25s cubic-bezier(.34,1.56,.64,1)}
.approx-modal-backdrop.visible .approx-modal{transform:translateY(0) scale(1)}
.approx-modal-hdr{background:linear-gradient(135deg,#fff8ed 0%,#fff3d6 100%);border-bottom:2px solid #fcd34d;padding:1.1rem 1.3rem .9rem;display:flex;align-items:flex-start;gap:12px}
.approx-modal-icon{width:42px;height:42px;flex-shrink:0;background:linear-gradient(135deg,#f59e0b,#f28018);border-radius:12px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(242,128,24,0.35)}
.approx-modal-icon i{color:#fff;font-size:17px}
.approx-modal-eyebrow{font-size:9px;font-weight:800;color:#b45309;letter-spacing:.18em;text-transform:uppercase;margin-bottom:3px}
.approx-modal-title{font-size:15px;font-weight:900;color:var(--gray-900);letter-spacing:-.01em;line-height:1.2}
.approx-modal-body{padding:1rem 1.3rem .5rem}
.approx-modal-icode{display:inline-flex;align-items:center;gap:6px;background:rgba(242,128,24,0.08);border:1px solid rgba(242,128,24,0.25);border-radius:var(--radius-sm);padding:5px 12px;font-size:13px;font-weight:800;color:var(--orange);letter-spacing:.04em;margin-bottom:9px}
.approx-modal-msg{font-size:13px;font-weight:500;color:var(--gray-700);line-height:1.6}
.approx-modal-msg strong{color:var(--gray-900);font-weight:800}
.approx-modal-note{margin-top:10px;padding:9px 12px;background:#fef9ee;border:1px solid #fde68a;border-left:3px solid #f59e0b;border-radius:var(--radius-sm);font-size:11.5px;color:#78350f;font-weight:600;display:flex;align-items:flex-start;gap:7px;line-height:1.5}
.approx-modal-note i{color:#f59e0b;margin-top:1px;flex-shrink:0;font-size:11px}
.approx-modal-ftr{padding:.9rem 1.3rem 1.1rem;display:flex;gap:8px;border-top:1px solid var(--gray-100)}
.approx-modal-cancel{flex:1;padding:11px 12px;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);background:var(--white);color:var(--gray-500);font-family:var(--font);font-size:12.5px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;cursor:pointer;transition:var(--trans);display:flex;align-items:center;justify-content:center;gap:6px;min-height:44px;touch-action:manipulation}
.approx-modal-cancel:hover{border-color:var(--orange);color:var(--orange);background:var(--orange-lt)}
.approx-modal-ok{flex:2;padding:11px 12px;border:none;border-radius:var(--radius-sm);background:var(--orange);color:var(--white);font-family:var(--font);font-size:12.5px;font-weight:900;letter-spacing:.06em;text-transform:uppercase;cursor:pointer;transition:var(--trans);display:flex;align-items:center;justify-content:center;gap:7px;box-shadow:0 3px 12px rgba(242,128,24,0.30);min-height:44px;touch-action:manipulation}
.approx-modal-ok:hover{background:var(--orange-dk)}

/* ═══ RESPONSIVE BREAKPOINTS ════════════════════════════════════════════════ */

/* Large desktop: show sidebar inline */
@media(min-width:961px){
    .mobile-filter-bar{display:none !important}
    .left-sidebar{display:flex}
    .sidebar-close-btn{display:none !important}
    .sidebar-overlay{display:none !important}
    .inv-header .tbl-toggle-btn{display:inline-flex}
    .tbl-scroll{display:block}
    .mobile-cards{display:none !important}
    .dock-table-wrap{display:block}
    .dock-items-list{display:none !important}
    .hero-banner{flex-direction:row}
    .hero-banner .container-col{display:block}
}

/* Tablet + Mobile: sidebar as drawer */
@media(max-width:960px){
    .left-sidebar{
        position:fixed;
        top:0;
        left:0;
        height:100%;
        z-index:460;
        transform:translateX(-100%);
        width:min(var(--sidebar-w), 85vw);
        box-shadow:var(--shadow-lg);
        padding-top:var(--hdr-h);
    }
    .left-sidebar.open{transform:translateX(0)}
    .sidebar-close-btn{display:flex !important}
    .mobile-filter-bar{display:flex}
    .page-shell{flex-direction:column}
    .hero-banner{flex-direction:column;align-items:flex-start;gap:.75rem}
    .hero-banner .container-col{width:100%}
    .container-row{justify-content:flex-start}
    .ctr-card{min-width:0;flex:1}
    .inv-header .tbl-toggle-btn{display:none}
    .inv-header{top:calc(var(--hdr-h) + 42px)}
    .tbl-scroll{max-height:calc(100vh - var(--hdr-h) - 42px - 120px)}
}

/* Mobile only: card view instead of table */
@media(max-width:640px){
    .tbl-scroll{display:none !important}
    .mobile-cards{display:block}
    .dock-table-wrap{display:none !important}
    .dock-items-list{display:flex}
    .hdr-btn .btn-text{display:none}
    .hdr-btn{padding:6px 10px}
    .hero-banner-title{font-size:clamp(22px,6vw,32px)}
    .stat-tile{min-width:46px;padding:3px 6px}
    .stat-tile-lbl{font-size:7px}
    .stat-tile-val{font-size:11px}
    .dock-header{padding:9px .75rem}
    .dock-actions{padding:.65rem .75rem .9rem}
    .dock-items-list{margin:.65rem .75rem}
    .disclaimer{margin:.65rem .75rem 0}
}

/* Very small screens */
@media(max-width:360px){
    .hdr-currency{display:none}
    .stat-tile:nth-child(4),.stat-tile:nth-child(5){display:none}
}
</style>
</head>
<body>

<!-- ═══ HEADER ═══════════════════════════════════════════════════════════ -->
<header class="hdr">
    <div class="hdr-inner">
        <a href="dashboard.php" class="brand">
            <img src="atire.png" alt="ATIRE" class="brand-logo">
        </a>
        <div class="hdr-right">
            <div class="hdr-currency">
                <i class="fas fa-coins"></i>
                <?php echo htmlspecialchars($functionalCurrency); ?>
                <span style="opacity:.6;font-weight:600;">(<?php echo htmlspecialchars(rtrim($currencySymbol)); ?>)</span>
            </div>
            <a href="dashboard.php" class="hdr-btn"><i class="fas fa-arrow-left"></i><span class="btn-text"> Dashboard</span></a>
            <div class="avatar"><?php echo htmlspecialchars($initials); ?></div>
        </div>
    </div>
</header>

<!-- ═══ MOBILE FILTER BAR ════════════════════════════════════════════════ -->
<div class="mobile-filter-bar" id="mobileFilterBar">
    <button type="button" class="mobile-filter-btn" id="mobileFilterBtn" onclick="openSidebar()">
        <i class="fas fa-sliders-h"></i> Filters
        <span class="filter-count-badge" id="mobileFilterCount" style="display:none">0</span>
    </button>
    <button type="button" class="mobile-tbl-toggle" id="mobileTblToggle" onclick="toggleTable()">
        <i class="fas fa-layer-group"></i>
        <span id="mobileTblLabel">New Tires</span>
    </button>
    <span class="count-pill" id="mobileItemCount" style="font-size:11px;padding:4px 10px;"><?php echo count($inventory); ?></span>
</div>

<!-- ═══ SIDEBAR OVERLAY ══════════════════════════════════════════════════ -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="page-shell">

<!-- ═══ LEFT SIDEBAR ════════════════════════════════════════════════════ -->
<aside class="left-sidebar" id="leftSidebar">
    <button type="button" class="sidebar-close-btn" onclick="closeSidebar()">
        <i class="fas fa-times"></i>
    </button>
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
            <label><i class="fas fa-tag"></i>Brand <span id="brandSelCount" style="display:none;background:var(--orange);color:#fff;border-radius:20px;padding:0 6px;font-size:9px;font-weight:900;margin-left:3px;"></span></label>
            <div class="brand-multi-wrap" id="brandMultiWrap">
                <div class="brand-multi-display" id="brandMultiDisplay" onclick="toggleBrandDropdown(event)">
                    <span class="brand-multi-display-text" id="brandMultiText">All Brands</span>
                    <i class="fas fa-chevron-down brand-multi-display-arrow"></i>
                </div>
                <div class="brand-multi-selected-pills" id="brandMultiPills"></div>
                <div class="brand-multi-dropdown" id="brandMultiDropdown">
                    <div class="brand-multi-search">
                        <input type="text" placeholder="Search brands…" id="brandMultiSearch" oninput="filterBrandOptions(this.value)" autocomplete="off">
                    </div>
                    <div id="brandMultiOptions">
                        <?php foreach ($brands as $b): ?>
                        <div class="brand-multi-option" data-value="<?php echo htmlspecialchars($b); ?>" onclick="toggleBrand(this)">
                            <span class="bm-check"></span><?php echo htmlspecialchars($b); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="brand-multi-footer">
                        <span id="brandMultiCountLabel">0 selected</span>
                        <span class="brand-multi-footer-clear" onclick="clearBrandSelection()">Clear all</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="fg">
            <label><i class="fas fa-palette"></i>Color</label>
            <select id="col_select" class="rf">
                <option value="all">All Colors</option>
                <?php foreach ($colors as $c): ?><option value="<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars($c); ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="fg">
            <label><i class="fas fa-cog"></i>Rim Size</label>
            <select id="rim_select" class="rf">
                <option value="all">All Rims</option>
                <?php foreach ($rims as $r): ?><option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="sb-divider"></div>
        <button class="btn-clear-all" onclick="clearFilters()"><i class="fas fa-undo"></i> Clear All Filters</button>
    </div>
</aside>

<!-- ═══ RIGHT CONTENT ════════════════════════════════════════════════════ -->
<div class="right-content">

    <!-- HERO BANNER -->
    <div class="hero-banner">
        <div class="hero-center">
            <div class="hero-banner-eyebrow">Live Inventory</div>
            <div class="hero-banner-title">Place <span>New</span> Order</div>
            <div class="hero-banner-sub">Select quantities — your cart updates in real time.</div>
        </div>
        <div class="container-col">
            <div class="container-row">
                <!-- 20 FT CONTAINER CARD -->
                <div class="ctr-card" id="card20">
                    <div class="ctr-label">
                        <span><i class="fas fa-truck" style="color:var(--orange);margin-right:4px;"></i>20 FT Container</span>
                        <span class="overload-badge">⚠ OVERLOAD</span>
                    </div>
                    <div class="ctr-svg-wrap">
                        <svg class="ctr-svg" viewBox="0 0 340 88" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <ellipse cx="175" cy="86" rx="158" ry="3" fill="rgba(0,0,0,0.07)"/>
                            <rect x="4" y="20" width="55" height="54" rx="7" fill="#e8e8e8" stroke="#c4c4c4" stroke-width="1.5"/>
                            <rect x="10" y="24" width="36" height="26" rx="3" fill="#c6dff0" stroke="#a8c8de" stroke-width="1"/>
                            <rect x="12" y="25" width="8" height="14" rx="2" fill="rgba(255,255,255,0.45)"/>
                            <line x1="10" y1="52" x2="46" y2="52" stroke="#c0c0c0" stroke-width="1"/>
                            <rect x="10" y="54" width="28" height="13" rx="2" fill="#c6dff0" stroke="#a8c8de" stroke-width="1"/>
                            <rect x="35" y="59" width="6" height="2" rx="1" fill="#aaa"/>
                            <circle cx="12" cy="56" r="1.5" fill="#c0c0c0"/><circle cx="12" cy="64" r="1.5" fill="#c0c0c0"/>
                            <rect x="4" y="56" width="11" height="18" rx="2" fill="#d8d8d8" stroke="#c0c0c0" stroke-width="1"/>
                            <rect x="5" y="58" width="8" height="6" rx="2" fill="#fff8c0" stroke="#e8c840" stroke-width="1"/>
                            <rect x="5" y="66" width="8" height="1.5" rx="0.5" fill="#bbb"/><rect x="5" y="68.5" width="8" height="1.5" rx="0.5" fill="#bbb"/><rect x="5" y="71" width="8" height="1.5" rx="0.5" fill="#bbb"/>
                            <rect x="4" y="67" width="14" height="7" rx="2" fill="#c0c0c0" stroke="#aaa" stroke-width="1"/>
                            <rect x="14" y="70" width="12" height="4" rx="1" fill="#d4d4d4"/>
                            <rect x="50" y="6" width="5" height="22" rx="2" fill="#c8c8c8" stroke="#b8b8b8" stroke-width="1"/>
                            <ellipse cx="52.5" cy="6" rx="3.5" ry="2" fill="#b8b8b8"/>
                            <rect x="18" y="15" width="36" height="7" rx="3" fill="#d8d8d8" stroke="#c4c4c4" stroke-width="1"/>
                            <rect x="55" y="54" width="8" height="14" rx="2" fill="#cccccc"/>
                            <rect x="60" y="66" width="276" height="7" rx="3" fill="#d0d0d0" stroke="#bbbbbb" stroke-width="1"/>
                            <rect x="60" y="16" width="276" height="52" rx="4" fill="#f4f4f4" stroke="#c8c8c8" stroke-width="1.5"/>
                            <rect x="60" y="16" width="276" height="5" rx="2" fill="#e2e2e2"/>
                            <rect x="60" y="63" width="276" height="5" rx="0" fill="#e2e2e2"/>
                            <defs><clipPath id="clip20"><rect x="61" y="21" width="272" height="42" rx="2"/></clipPath></defs>
                            <rect id="fill20" x="61" y="21" width="0" height="42" fill="rgba(242,128,24,0.55)" clip-path="url(#clip20)" class="cargo-fill"/>
                            <rect id="fill20stripe" x="61" y="21" width="0" height="14" fill="rgba(242,128,24,0.18)" clip-path="url(#clip20)" class="cargo-fill"/>
                            <line x1="124" y1="16" x2="124" y2="68" stroke="#ddd" stroke-width="1.2"/><line x1="178" y1="16" x2="178" y2="68" stroke="#ddd" stroke-width="1.2"/><line x1="232" y1="16" x2="232" y2="68" stroke="#ddd" stroke-width="1.2"/><line x1="282" y1="16" x2="282" y2="68" stroke="#ddd" stroke-width="1.2"/>
                            <rect x="316" y="17" width="18" height="50" rx="2" fill="#ececec" stroke="#c4c4c4" stroke-width="1"/>
                            <line x1="325" y1="17" x2="325" y2="67" stroke="#c4c4c4" stroke-width="1"/>
                            <rect x="317" y="24" width="7" height="3" rx="1" fill="#c0c0c0"/><rect x="317" y="40" width="7" height="3" rx="1" fill="#c0c0c0"/><rect x="317" y="56" width="7" height="3" rx="1" fill="#c0c0c0"/>
                            <circle cx="23" cy="74" r="9" fill="#c4c4c4" stroke="#a8a8a8" stroke-width="1.5"/><circle cx="23" cy="74" r="5" fill="#d8d8d8" stroke="#b8b8b8" stroke-width="1"/><circle cx="23" cy="74" r="1.8" fill="#aaa"/>
                            <circle cx="23" cy="68.5" r="1" fill="#aaa"/><circle cx="27.5" cy="71" r="1" fill="#aaa"/><circle cx="27.5" cy="77" r="1" fill="#aaa"/><circle cx="23" cy="79.5" r="1" fill="#aaa"/><circle cx="18.5" cy="77" r="1" fill="#aaa"/><circle cx="18.5" cy="71" r="1" fill="#aaa"/>
                            <circle cx="218" cy="74" r="9" fill="#c4c4c4" stroke="#a8a8a8" stroke-width="1.5"/><circle cx="218" cy="74" r="5" fill="#d8d8d8" stroke="#b8b8b8" stroke-width="1"/><circle cx="218" cy="74" r="1.8" fill="#aaa"/>
                            <circle cx="236" cy="74" r="9" fill="#c4c4c4" stroke="#a8a8a8" stroke-width="1.5"/><circle cx="236" cy="74" r="5" fill="#d8d8d8" stroke="#b8b8b8" stroke-width="1"/><circle cx="236" cy="74" r="1.8" fill="#aaa"/>
                            <circle cx="302" cy="74" r="9" fill="#c4c4c4" stroke="#a8a8a8" stroke-width="1.5"/><circle cx="302" cy="74" r="5" fill="#d8d8d8" stroke="#b8b8b8" stroke-width="1"/><circle cx="302" cy="74" r="1.8" fill="#aaa"/>
                            <circle cx="320" cy="74" r="9" fill="#c4c4c4" stroke="#a8a8a8" stroke-width="1.5"/><circle cx="320" cy="74" r="5" fill="#d8d8d8" stroke="#b8b8b8" stroke-width="1"/><circle cx="320" cy="74" r="1.8" fill="#aaa"/>
                        </svg>
                    </div>
                    <div class="ctr-stats">
                        <div class="ctr-pct" id="pct20">0%</div>
                        <div class="ctr-info"><div class="ctr-wt" id="wt20">0 kg</div><div class="ctr-cap">Max 18,000 kg</div></div>
                    </div>
                </div>
                <!-- 40 FT CONTAINER CARD -->
                <div class="ctr-card" id="card40">
                    <div class="ctr-label">
                        <span><i class="fas fa-truck" style="color:var(--orange);margin-right:4px;"></i>40 FT Container</span>
                        <span class="overload-badge">⚠ OVERLOAD</span>
                    </div>
                    <div class="ctr-svg-wrap">
                        <svg class="ctr-svg" viewBox="0 0 400 88" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <ellipse cx="205" cy="86" rx="188" ry="3" fill="rgba(0,0,0,0.07)"/>
                            <rect x="4" y="20" width="55" height="54" rx="7" fill="#e8e8e8" stroke="#c4c4c4" stroke-width="1.5"/>
                            <rect x="10" y="24" width="36" height="26" rx="3" fill="#c6dff0" stroke="#a8c8de" stroke-width="1"/>
                            <rect x="12" y="25" width="8" height="14" rx="2" fill="rgba(255,255,255,0.45)"/>
                            <line x1="10" y1="52" x2="46" y2="52" stroke="#c0c0c0" stroke-width="1"/>
                            <rect x="10" y="54" width="28" height="13" rx="2" fill="#c6dff0" stroke="#a8c8de" stroke-width="1"/>
                            <rect x="35" y="59" width="6" height="2" rx="1" fill="#aaa"/>
                            <circle cx="12" cy="56" r="1.5" fill="#c0c0c0"/><circle cx="12" cy="64" r="1.5" fill="#c0c0c0"/>
                            <rect x="4" y="56" width="11" height="18" rx="2" fill="#d8d8d8" stroke="#c0c0c0" stroke-width="1"/>
                            <rect x="5" y="58" width="8" height="6" rx="2" fill="#fff8c0" stroke="#e8c840" stroke-width="1"/>
                            <rect x="5" y="66" width="8" height="1.5" rx="0.5" fill="#bbb"/><rect x="5" y="68.5" width="8" height="1.5" rx="0.5" fill="#bbb"/><rect x="5" y="71" width="8" height="1.5" rx="0.5" fill="#bbb"/>
                            <rect x="4" y="67" width="14" height="7" rx="2" fill="#c0c0c0" stroke="#aaa" stroke-width="1"/>
                            <rect x="14" y="70" width="12" height="4" rx="1" fill="#d4d4d4"/>
                            <rect x="50" y="6" width="5" height="22" rx="2" fill="#c8c8c8" stroke="#b8b8b8" stroke-width="1"/>
                            <ellipse cx="52.5" cy="6" rx="3.5" ry="2" fill="#b8b8b8"/>
                            <rect x="18" y="15" width="36" height="7" rx="3" fill="#d8d8d8" stroke="#c4c4c4" stroke-width="1"/>
                            <rect x="55" y="54" width="8" height="14" rx="2" fill="#cccccc"/>
                            <rect x="60" y="66" width="336" height="7" rx="3" fill="#d0d0d0" stroke="#bbbbbb" stroke-width="1"/>
                            <rect x="60" y="16" width="336" height="52" rx="4" fill="#f4f4f4" stroke="#c8c8c8" stroke-width="1.5"/>
                            <rect x="60" y="16" width="336" height="5" rx="2" fill="#e2e2e2"/>
                            <rect x="60" y="63" width="336" height="5" rx="0" fill="#e2e2e2"/>
                            <defs><clipPath id="clip40"><rect x="61" y="21" width="332" height="42" rx="2"/></clipPath></defs>
                            <rect id="fill40" x="61" y="21" width="0" height="42" fill="rgba(242,128,24,0.55)" clip-path="url(#clip40)" class="cargo-fill"/>
                            <rect id="fill40stripe" x="61" y="21" width="0" height="14" fill="rgba(242,128,24,0.18)" clip-path="url(#clip40)" class="cargo-fill"/>
                            <line x1="126" y1="16" x2="126" y2="68" stroke="#ddd" stroke-width="1.2"/><line x1="192" y1="16" x2="192" y2="68" stroke="#ddd" stroke-width="1.2"/><line x1="258" y1="16" x2="258" y2="68" stroke="#ddd" stroke-width="1.2"/><line x1="324" y1="16" x2="324" y2="68" stroke="#ddd" stroke-width="1.2"/><line x1="358" y1="16" x2="358" y2="68" stroke="#ddd" stroke-width="1.2"/>
                            <rect x="374" y="17" width="20" height="50" rx="2" fill="#ececec" stroke="#c4c4c4" stroke-width="1"/>
                            <line x1="384" y1="17" x2="384" y2="67" stroke="#c4c4c4" stroke-width="1"/>
                            <rect x="375" y="24" width="7" height="3" rx="1" fill="#c0c0c0"/><rect x="375" y="40" width="7" height="3" rx="1" fill="#c0c0c0"/><rect x="375" y="56" width="7" height="3" rx="1" fill="#c0c0c0"/>
                            <circle cx="23" cy="74" r="9" fill="#c4c4c4" stroke="#a8a8a8" stroke-width="1.5"/><circle cx="23" cy="74" r="5" fill="#d8d8d8" stroke="#b8b8b8" stroke-width="1"/><circle cx="23" cy="74" r="1.8" fill="#aaa"/>
                            <circle cx="23" cy="68.5" r="1" fill="#aaa"/><circle cx="27.5" cy="71" r="1" fill="#aaa"/><circle cx="27.5" cy="77" r="1" fill="#aaa"/><circle cx="23" cy="79.5" r="1" fill="#aaa"/><circle cx="18.5" cy="77" r="1" fill="#aaa"/><circle cx="18.5" cy="71" r="1" fill="#aaa"/>
                            <circle cx="218" cy="74" r="9" fill="#c4c4c4" stroke="#a8a8a8" stroke-width="1.5"/><circle cx="218" cy="74" r="5" fill="#d8d8d8" stroke="#b8b8b8" stroke-width="1"/><circle cx="218" cy="74" r="1.8" fill="#aaa"/>
                            <circle cx="236" cy="74" r="9" fill="#c4c4c4" stroke="#a8a8a8" stroke-width="1.5"/><circle cx="236" cy="74" r="5" fill="#d8d8d8" stroke="#b8b8b8" stroke-width="1"/><circle cx="236" cy="74" r="1.8" fill="#aaa"/>
                            <circle cx="360" cy="74" r="9" fill="#c4c4c4" stroke="#a8a8a8" stroke-width="1.5"/><circle cx="360" cy="74" r="5" fill="#d8d8d8" stroke="#b8b8b8" stroke-width="1"/><circle cx="360" cy="74" r="1.8" fill="#aaa"/>
                            <circle cx="378" cy="74" r="9" fill="#c4c4c4" stroke="#a8a8a8" stroke-width="1.5"/><circle cx="378" cy="74" r="5" fill="#d8d8d8" stroke="#b8b8b8" stroke-width="1"/><circle cx="378" cy="74" r="1.8" fill="#aaa"/>
                        </svg>
                    </div>
                    <div class="ctr-stats">
                        <div class="ctr-pct" id="pct40">0%</div>
                        <div class="ctr-info"><div class="ctr-wt" id="wt40">0 kg</div><div class="ctr-cap">Max 25,000 kg</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /hero-banner -->

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
                <div class="inv-header-left">
                    <div class="inv-title">
                        <div class="inv-title-icon"><i class="fas fa-boxes"></i></div>
                        Inventory
                    </div>
                    <span class="count-pill" id="itemCount"><?php echo count($inventory); ?> Items</span>
                </div>
                <button type="button" id="tblToggleBtn" class="tbl-toggle-btn" onclick="toggleTable()">
                    <i class="fas fa-layer-group"></i>
                    <span id="tblToggleLabel">New Tires</span>
                </button>
            </div>

            <div class="old-table-banner" id="oldTableBanner">
                <span class="old-table-dot"></span>
                <i class="fas fa-archive"></i>
                Viewing <strong>New Tires</strong> — click toggle to return to Existing Tires.
            </div>

            <div class="filter-info-strip" id="filterStrip">
                <i class="fas fa-info-circle"></i> Showing filtered results — clear filters to see full inventory.
            </div>

            <div id="invContainer">
                <?php
                $grouped=[];
                foreach ($inventory as $item) {
                    $br=$item['tire_brand']??$item['brand']??'Unknown';
                    $grouped[$br][]=$item;
                }
                ?>
                <?php if (!empty($inventory)): ?>
                <!-- DESKTOP TABLE -->
                <div class="tbl-scroll"><div class="tbl-wrap">
                    <table class="inv-tbl">
                        <thead><tr>
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
                        </tr></thead>
                        <tbody>
                        <?php foreach ($grouped as $brandName=>$items): $colCount=10; ?>
                            <tr class="brand-group-header">
                                <td colspan="<?php echo $colCount; ?>">
                                    <div class="brand-group-label">
                                        <i class="fas fa-tag"></i><?php echo htmlspecialchars($brandName); ?>
                                        <span class="brand-group-count"><?php echo count($items); ?> items</span>
                                    </div>
                                </td>
                            </tr>
                            <?php foreach ($items as $item):
                                $ik=strtolower(trim($item['icode']??''));
                                $hasSpecificPrice=isset($itemPrices[$ik]);
                                $displayPrice=$hasSpecificPrice?$itemPrices[$ik]:null;
                                $br=$item['tire_brand']??$item['brand']??'';
                                $needsApproxWarning=$cusHasItemPrices&&!$hasSpecificPrice;
                            ?>
                            <tr data-id="<?php echo htmlspecialchars($item['id']); ?>">
                                <td class="code-cell"><?php echo htmlspecialchars($item['icode']??'N/A'); ?></td>
                                <td class="desc-cell"><?php echo htmlspecialchars($item['t_size']??'N/A'); ?></td>
                                <td class="brand-cell"><?php echo htmlspecialchars($br?:'N/A'); ?></td>
                                <td class="tiresize-cell"><?php echo htmlspecialchars($item['tire_size']??'N/A'); ?></td>
                                <td><span class="color-badge"><?php echo htmlspecialchars($item['col']??'—'); ?></span></td>
                                <td><span class="rim-badge"><?php echo htmlspecialchars($item['rim']??'—'); ?></span></td>
                                <td class="num-cell"><?php echo htmlspecialchars($item['fweight']??'—'); ?></td>
                                <td class="num-cell"><?php echo htmlspecialchars($item['cbm']??'—'); ?></td>
                                <td class="price-cell">
                                    <?php if ($hasSpecificPrice): ?>
                                        <span class="price-specific"><?php echo htmlspecialchars($currencySymbol); ?><?php echo number_format($displayPrice,2); ?><span class="price-specific-badge">Fixed</span></span>
                                    <?php elseif ($needsApproxWarning): ?>
                                        <span class="price-approx">—<span class="price-approx-badge">Approx</span></span>
                                    <?php else: ?>
                                        <span class="price-rate"><?php echo number_format(isset($brandRates[strtolower(trim($br))])?$brandRates[strtolower(trim($br))]:$paymentRate,4); ?>/kg</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <input type="number" class="qty-inp" min="0" value="0" placeholder="0"
                                        data-id="<?php echo htmlspecialchars($item['id']); ?>"
                                        data-icode="<?php echo htmlspecialchars($item['icode']??''); ?>"
                                        data-size="<?php echo htmlspecialchars($item['t_size']??''); ?>"
                                        data-tiresize="<?php echo htmlspecialchars($item['tire_size']??''); ?>"
                                        data-brand="<?php echo htmlspecialchars($br); ?>"
                                        data-color="<?php echo htmlspecialchars($item['col']??''); ?>"
                                        data-rim="<?php echo htmlspecialchars($item['rim']??''); ?>"
                                        data-fweight="<?php echo htmlspecialchars($item['fweight']??0); ?>"
                                        data-cbm="<?php echo htmlspecialchars($item['cbm']??0); ?>"
                                        data-customer-price="<?php echo $hasSpecificPrice?htmlspecialchars($displayPrice):''; ?>"
                                        data-needs-approx-warning="<?php echo $needsApproxWarning?'1':'0'; ?>"
                                        onchange="updateOrder(this)" oninput="updateOrder(this)">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div></div>
                <!-- MOBILE CARD VIEW -->
                <div class="mobile-cards" id="mobileCards">
                    <?php foreach ($grouped as $brandName=>$items):?>
                    <div style="font-size:10px;font-weight:800;color:var(--orange);letter-spacing:.14em;text-transform:uppercase;padding:.4rem .5rem .2rem;display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-tag" style="font-size:9px;"></i><?php echo htmlspecialchars($brandName); ?>
                        <span style="background:rgba(242,128,24,0.15);color:var(--orange-dk);border-radius:20px;padding:1px 7px;font-size:9px;"><?php echo count($items); ?></span>
                    </div>
                    <?php foreach ($items as $item):
                        $ik=strtolower(trim($item['icode']??''));
                        $hasSpecificPrice=isset($itemPrices[$ik]);
                        $displayPrice=$hasSpecificPrice?$itemPrices[$ik]:null;
                        $br=$item['tire_brand']??$item['brand']??'';
                        $needsApproxWarning=$cusHasItemPrices&&!$hasSpecificPrice;
                    ?>
                    <div class="m-card" data-id="<?php echo htmlspecialchars($item['id']); ?>">
                        <div class="m-card-header">
                            <div>
                                <div class="m-card-code"><?php echo htmlspecialchars($item['icode']??'N/A'); ?></div>
                                <div class="m-card-brand"><?php echo htmlspecialchars($br?:'N/A'); ?></div>
                            </div>
                            <div style="text-align:right;">
                                <?php if ($item['tire_size']): ?>
                                <div style="font-size:11px;font-weight:700;color:var(--gray-500);"><?php echo htmlspecialchars($item['tire_size']); ?></div>
                                <?php endif; ?>
                                <div style="display:flex;gap:4px;justify-content:flex-end;flex-wrap:wrap;margin-top:3px;">
                                    <?php if ($item['col']): ?><span class="color-badge" style="font-size:9.5px;padding:1px 6px;"><?php echo htmlspecialchars($item['col']); ?></span><?php endif; ?>
                                    <?php if ($item['rim']): ?><span class="rim-badge" style="font-size:9.5px;padding:1px 6px;"><?php echo htmlspecialchars($item['rim']); ?></span><?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="m-card-body">
                            <div class="m-card-row">
                                <span class="m-card-lbl">Description</span>
                                <span class="m-card-val"><?php echo htmlspecialchars($item['t_size']??'N/A'); ?></span>
                            </div>
                            <div class="m-card-row">
                                <span class="m-card-lbl">Weight</span>
                                <span class="m-card-val"><?php echo htmlspecialchars($item['fweight']??'—'); ?> kg</span>
                            </div>
                            <div class="m-card-row">
                                <span class="m-card-lbl">CBM</span>
                                <span class="m-card-val"><?php echo htmlspecialchars($item['cbm']??'—'); ?></span>
                            </div>
                        </div>
                        <div class="m-card-footer">
                            <div class="m-card-price">
                                <div class="m-card-price-lbl">Unit Price</div>
                                <?php if ($hasSpecificPrice): ?>
                                    <span class="price-specific" style="font-size:12px;"><?php echo htmlspecialchars($currencySymbol); ?><?php echo number_format($displayPrice,2); ?><span class="price-specific-badge">Fixed</span></span>
                                <?php elseif ($needsApproxWarning): ?>
                                    <span class="price-approx" style="font-size:12px;">—<span class="price-approx-badge">Approx</span></span>
                                <?php else: ?>
                                    <span class="price-rate" style="font-size:11px;"><?php echo number_format(isset($brandRates[strtolower(trim($br))])?$brandRates[strtolower(trim($br))]:$paymentRate,4); ?>/kg</span>
                                <?php endif; ?>
                            </div>
                            <div class="m-qty-wrap">
                                <button type="button" class="m-qty-btn" onclick="mQtyAdj(this,-1)"><i class="fas fa-minus" style="font-size:10px;"></i></button>
                                <input type="number" class="m-qty-inp qty-inp" min="0" value="0" placeholder="0"
                                    data-id="<?php echo htmlspecialchars($item['id']); ?>"
                                    data-icode="<?php echo htmlspecialchars($item['icode']??''); ?>"
                                    data-size="<?php echo htmlspecialchars($item['t_size']??''); ?>"
                                    data-tiresize="<?php echo htmlspecialchars($item['tire_size']??''); ?>"
                                    data-brand="<?php echo htmlspecialchars($br); ?>"
                                    data-color="<?php echo htmlspecialchars($item['col']??''); ?>"
                                    data-rim="<?php echo htmlspecialchars($item['rim']??''); ?>"
                                    data-fweight="<?php echo htmlspecialchars($item['fweight']??0); ?>"
                                    data-cbm="<?php echo htmlspecialchars($item['cbm']??0); ?>"
                                    data-customer-price="<?php echo $hasSpecificPrice?htmlspecialchars($displayPrice):''; ?>"
                                    data-needs-approx-warning="<?php echo $needsApproxWarning?'1':'0'; ?>"
                                    onchange="updateOrder(this)" oninput="updateOrder(this)">
                                <button type="button" class="m-qty-btn" onclick="mQtyAdj(this,1)"><i class="fas fa-plus" style="font-size:10px;"></i></button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
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

<!-- ═══ ORDER DOCK ══════════════════════════════════════════════════════ -->
<div class="order-dock" id="orderDock">
    <div class="dock-header" onclick="toggleDock()">
        <div class="dock-title">
            <i class="fas fa-shopping-cart"></i>Order
            <span class="dock-sub">&nbsp;— tap to expand</span>
        </div>
        <div class="dock-stats">
            <div class="stat-tile"><div class="stat-tile-lbl">Lines</div><div class="stat-tile-val" id="dItems">0</div></div>
            <div class="stat-tile"><div class="stat-tile-lbl">Units</div><div class="stat-tile-val" id="dQty">0</div></div>
            <div class="stat-tile"><div class="stat-tile-lbl">Weight</div><div class="stat-tile-val" id="dWt">0 kg</div></div>
            <div class="stat-tile"><div class="stat-tile-lbl">CBM</div><div class="stat-tile-val" id="dCBM">0</div></div>
            <div class="stat-tile"><div class="stat-tile-lbl">Value</div><div class="stat-tile-val" id="dCost">0</div></div>
            <button class="dock-toggle" type="button"><i class="fas fa-chevron-up" id="dockIcon"></i></button>
        </div>
    </div>
    <div class="dock-body" id="dockBody">
        <div class="disclaimer">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Note:</strong> Weight, CBM and value are indicative only. Final figures on your Proforma Invoice.
            All prices in <strong><?php echo htmlspecialchars($functionalCurrency); ?></strong>.
        </div>
        <!-- Mobile: card list -->
        <div class="dock-items-list" id="dockItemsList"></div>
        <!-- Desktop: table -->
        <div class="dock-table-wrap">
            <table class="dock-tbl">
                <thead><tr>
                    <th>Code</th><th>Description</th><th>Brand</th><th>Tire Size</th>
                    <th>Color</th><th>Rim</th><th>Qty</th><th>Unit Wt</th><th>Unit CBM</th>
                    <th>Total Wt</th><th>Total CBM</th>
                    <th>Unit Price (<?php echo htmlspecialchars($functionalCurrency); ?>)</th>
                    <th>Line Value (<?php echo htmlspecialchars($functionalCurrency); ?>)</th>
                    <th></th>
                </tr></thead>
                <tbody id="dockBody2"></tbody>
            </table>
        </div>
        <div class="dock-actions">
            <form id="orderForm" method="POST">
                <input type="hidden" name="action" value="place_order">
                <input type="hidden" name="order_data" id="orderData">
                <button type="submit" class="dbtn dbtn-submit" onclick="return submitOrder(event)">
                    <i class="fas fa-paper-plane"></i> Place Order
                </button>
                <button type="button" class="dbtn dbtn-clear" onclick="clearOrder()">
                    <i class="fas fa-trash"></i> Clear
                </button>
            </form>
        </div>
    </div>
</div>

<!-- ═══ APPROX PRICE MODAL ══════════════════════════════════════════════ -->
<div class="approx-modal-backdrop" id="approxModalBackdrop" role="dialog" aria-modal="true" aria-labelledby="approxModalTitle">
    <div class="approx-modal">
        <div class="approx-modal-hdr">
            <div class="approx-modal-icon"><i class="fas fa-tag"></i></div>
            <div>
                <div class="approx-modal-eyebrow">Pricing Notice</div>
                <div class="approx-modal-title" id="approxModalTitle">Approximate Price</div>
            </div>
        </div>
        <div class="approx-modal-body">
            <div class="approx-modal-icode" id="approxModalIcode">
                <i class="fas fa-barcode"></i><span id="approxModalIcodeText">—</span>
            </div>
            <div class="approx-modal-msg">
                This tire code has an approximate price for your order purpose. A <strong>FIXED PRICE</strong> will be confirmed in the <strong>PROFORMA INVOICE</strong>.
            </div>
            <div class="approx-modal-note">
                <i class="fas fa-info-circle"></i>
                You may proceed to add this item. The final unit price will be set by our team before the invoice is issued.
            </div>
        </div>
        <div class="approx-modal-ftr">
            <button type="button" class="approx-modal-cancel" id="approxModalCancel"><i class="fas fa-times"></i> Cancel</button>
            <button type="button" class="approx-modal-ok" id="approxModalOk"><i class="fas fa-check"></i> OK, Add to Order</button>
        </div>
    </div>
</div>

<!-- ═══ JS ══════════════════════════════════════════════════════════════ -->
<script>
const icodeOptions    = <?php echo json_encode($icodes    ??[]); ?>;
const tireSizeOptions = <?php echo json_encode($tireSizes ??[]); ?>;
const defaultRate     = <?php echo json_encode($paymentRate); ?>;
const brandRates      = <?php echo json_encode($brandRates); ?>;
const currencySymbol  = <?php echo json_encode($currencySymbol); ?>;
const currencyCode    = <?php echo json_encode($functionalCurrency); ?>;
let cusHasItemPrices  = <?php echo json_encode($cusHasItemPrices); ?>;
const allowedBrands   = <?php echo json_encode($allowedBrands); ?>;

const CAP20 = 18000, CAP40 = 25000;
const FILL20_MAX = 272, FILL40_MAX = 332;

let orderItems = new Map();
let dockOpen = false, searchTimer = null, updateTimer = null, currentSort = '';
let usingOldTable = false;
let selectedBrands = new Set(), brandDropdownOpen = false;
let approxModalPending = null;

/* ── Sidebar (mobile drawer) ─────────────────────────────────────────── */
function openSidebar() {
    document.getElementById('leftSidebar').classList.add('open');
    document.getElementById('sidebarOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    document.getElementById('leftSidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

/* ── Mobile +/- buttons ──────────────────────────────────────────────── */
function mQtyAdj(btn, delta) {
    const wrap = btn.closest('.m-qty-wrap') || btn.closest('.dock-item-qty-wrap');
    const inp = wrap ? wrap.querySelector('.qty-inp, .dock-qty, .m-qty-inp') : null;
    if (!inp) return;
    const cur = parseInt(inp.value) || 0;
    const newVal = Math.max(0, cur + delta);
    inp.value = newVal;
    if (inp.classList.contains('dock-qty')) {
        const id = inp.dataset.id || inp.closest('[data-id]')?.dataset.id;
        if (id) updateQty(inp, id);
    } else {
        updateOrder(inp);
    }
}

/* ── Pricing ─────────────────────────────────────────────────────────── */
function calcUnitPrice(brand, fweight, customerPrice) {
    if (customerPrice !== null && customerPrice !== '' && parseFloat(customerPrice) > 0)
        return parseFloat(customerPrice);
    return brandRate(brand) * (parseFloat(fweight) || 0);
}
function brandRate(brand) {
    if (!brand) return defaultRate;
    const k = brand.toLowerCase().trim();
    return brandRates.hasOwnProperty(k) ? (parseFloat(brandRates[k]) || defaultRate) : defaultRate;
}
function fmtCurrency(amount, decimals = 2) {
    return currencySymbol + (+amount).toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
}

/* ── Multi-brand selector ────────────────────────────────────────────── */
function toggleBrandDropdown(e) {
    e.stopPropagation(); brandDropdownOpen = !brandDropdownOpen;
    document.getElementById('brandMultiDropdown').classList.toggle('open', brandDropdownOpen);
    document.getElementById('brandMultiDisplay').classList.toggle('open', brandDropdownOpen);
    if (brandDropdownOpen) document.getElementById('brandMultiSearch').focus();
}
function toggleBrand(el) {
    const val = el.dataset.value;
    if (selectedBrands.has(val)) { selectedBrands.delete(val); el.classList.remove('selected'); }
    else { selectedBrands.add(val); el.classList.add('selected'); }
    syncBrandUI(); clearTimeout(searchTimer); searchTimer = setTimeout(doSearch, 320);
}
function clearBrandSelection() {
    selectedBrands.clear();
    document.querySelectorAll('.brand-multi-option').forEach(o => o.classList.remove('selected'));
    syncBrandUI(); clearTimeout(searchTimer); searchTimer = setTimeout(doSearch, 320);
}
function filterBrandOptions(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.brand-multi-option').forEach(opt => {
        opt.style.display = opt.dataset.value.toLowerCase().includes(q) ? '' : 'none';
    });
}
function syncBrandUI() {
    const count = selectedBrands.size;
    const textEl = document.getElementById('brandMultiText');
    const pillsEl = document.getElementById('brandMultiPills');
    const badgeEl = document.getElementById('brandSelCount');
    const displayEl = document.getElementById('brandMultiDisplay');
    if (count === 0) { textEl.textContent = 'All Brands'; displayEl.classList.remove('active-filter'); badgeEl.style.display = 'none'; }
    else { textEl.textContent = count === 1 ? [...selectedBrands][0] : count + ' brands selected'; displayEl.classList.add('active-filter'); badgeEl.style.display = 'inline'; badgeEl.textContent = count; }
    document.getElementById('brandMultiCountLabel').textContent = count + ' selected';
    pillsEl.innerHTML = [...selectedBrands].map(b => `<div class="brand-pill" onclick="removeBrand('${esc(b)}')"><i class="fas fa-times"></i>${esc(b)}</div>`).join('');
    updateFilterUI();
}
function removeBrand(val) {
    selectedBrands.delete(val);
    const opt = document.querySelector(`.brand-multi-option[data-value="${val}"]`);
    if (opt) opt.classList.remove('selected');
    syncBrandUI(); clearTimeout(searchTimer); searchTimer = setTimeout(doSearch, 320);
}
document.addEventListener('click', function(e) {
    if (!document.getElementById('brandMultiWrap').contains(e.target)) {
        brandDropdownOpen = false;
        document.getElementById('brandMultiDropdown').classList.remove('open');
        document.getElementById('brandMultiDisplay').classList.remove('open');
    }
});

/* ── Init ────────────────────────────────────────────────────────────── */
$(document).ready(function() {
    initAC('#icode_select', icodeOptions);
    initAC('#tire_size_select', tireSizeOptions);
    $('.rf').on('input change', function() {
        if (this.id === 'brand_select') return;
        updateFilterUI(); clearTimeout(searchTimer); searchTimer = setTimeout(doSearch, 320);
    });
    $('#sortRow').on('click', '.sort-chip', function() {
        $('.sort-chip').removeClass('active'); $(this).addClass('active');
        currentSort = $(this).data('sort'); doSearch();
    });
    updateFilterUI(); loadState(); initApproxModal();
});

function initAC(sel, data) {
    $(sel).autocomplete({
        source: (req, resp) => resp(data.filter(v => v.toLowerCase().includes(req.term.toLowerCase())).slice(0, 20)),
        minLength: 1, delay: 80,
        select: (e, ui) => { $(sel).val(ui.item.value).addClass('active-filter'); updateFilterUI(); doSearch(); return false; }
    });
}

function toggleTable() {
    usingOldTable = !usingOldTable;
    const btn = document.getElementById('tblToggleBtn');
    const label = document.getElementById('tblToggleLabel');
    const mLabel = document.getElementById('mobileTblLabel');
    const mToggle = document.getElementById('mobileTblToggle');
    const banner = document.getElementById('oldTableBanner');
    if (usingOldTable) {
        btn.classList.add('active-old'); label.textContent = 'Existing Tires';
        if (mToggle) { mToggle.classList.add('active-old'); if (mLabel) mLabel.textContent = 'Existing'; }
        banner.classList.add('show');
    } else {
        btn.classList.remove('active-old'); label.textContent = 'New Tires';
        if (mToggle) { mToggle.classList.remove('active-old'); if (mLabel) mLabel.textContent = 'New Tires'; }
        banner.classList.remove('show');
    }
    doSearch();
}

/* ── Approx modal ────────────────────────────────────────────────────── */
function initApproxModal() {
    document.getElementById('approxModalOk').addEventListener('click', function() {
        if (approxModalPending) {
            const { input, qty } = approxModalPending;
            approxModalPending = null;
            closeApproxModal();
            input.dataset.approxAcknowledged = '1';
            _doUpdateOrder(input, qty);
        }
    });
    document.getElementById('approxModalCancel').addEventListener('click', function() {
        if (approxModalPending) {
            const { input } = approxModalPending;
            approxModalPending = null;
            input.value = input.dataset.prevValue || '0';
            input.classList.remove('filled');
        }
        closeApproxModal();
    });
    document.getElementById('approxModalBackdrop').addEventListener('click', function(e) {
        if (e.target === this) document.getElementById('approxModalCancel').click();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('approxModalBackdrop').classList.contains('visible'))
            document.getElementById('approxModalCancel').click();
    });
}
function openApproxModal(icode) {
    document.getElementById('approxModalIcodeText').textContent = icode || '—';
    document.getElementById('approxModalBackdrop').classList.add('visible');
    setTimeout(() => document.getElementById('approxModalOk').focus(), 80);
}
function closeApproxModal() { document.getElementById('approxModalBackdrop').classList.remove('visible'); }

/* ── Container visualizer ────────────────────────────────────────────── */
function updateContainers(wt) {
    const c20 = document.getElementById('card20'), c40 = document.getElementById('card40');
    if (wt <= 0) {
        [c20,c40].forEach(c => c.classList.remove('show','overloaded'));
        setFill('fill20',0,FILL20_MAX); setFill('fill40',0,FILL40_MAX); return;
    }
    if (wt <= CAP20) {
        show(c20); hide(c40);
        const p = (wt/CAP20)*100;
        setTxt('pct20', Math.round(p)+'%'); setTxt('wt20', fmtN(wt)+' kg');
        setFill('fill20',p,FILL20_MAX); c20.classList.remove('overloaded');
    } else if (wt <= CAP40) {
        show(c40); hide(c20);
        const p = (wt/CAP40)*100;
        setTxt('pct40', Math.round(p)+'%'); setTxt('wt40', fmtN(wt)+' kg');
        setFill('fill40',p,FILL40_MAX); c40.classList.remove('overloaded');
    } else {
        show(c40); hide(c20); c40.classList.add('overloaded');
        setTxt('pct40','100%+'); setTxt('wt40', fmtN(wt)+' kg (+'+fmtN(wt-CAP40)+')');
        setFill('fill40',100,FILL40_MAX);
    }
}
function show(el){el.classList.add('show')}
function hide(el){el.classList.remove('show','overloaded')}
function setTxt(id,v){const e=document.getElementById(id);if(e)e.textContent=v}
function setFill(id,pct,maxW){
    const el=document.getElementById(id);
    if(el)el.setAttribute('width',((Math.min(pct,100)/100)*maxW).toFixed(1));
    const stripe=document.getElementById(id+'stripe');
    if(stripe)stripe.setAttribute('width',((Math.min(pct,100)/100)*maxW).toFixed(1));
}
function fmtN(n){return(+n).toLocaleString('en-US',{maximumFractionDigits:0})}
function fmtD(n,d=2){return(+n).toLocaleString('en-US',{minimumFractionDigits:d,maximumFractionDigits:d})}
function esc(t){return String(t).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]))}

/* ── Filter UI ───────────────────────────────────────────────────────── */
function updateFilterUI() {
    const fields = [{id:'icode_select',label:'Code'},{id:'tire_size_select',label:'Size'},{id:'col_select',label:'Color'},{id:'rim_select',label:'Rim'}];
    let count = 0; const chips = [];
    fields.forEach(f => {
        const el = document.getElementById(f.id); if (!el) return;
        const v = el.value.trim(), active = v && v !== 'all';
        if (active) { el.classList.add('active-filter'); count++; chips.push(`<div class="filter-chip" onclick="clearOne('${f.id}')"><i class="fas fa-times"></i>${esc(v)}</div>`); }
        else el.classList.remove('active-filter');
    });
    if (selectedBrands.size > 0) count += selectedBrands.size;
    document.getElementById('chipRow').innerHTML = chips.join('');
    document.getElementById('filterStrip').classList.toggle('show', count > 0);
    // Update mobile filter badge
    const badge = document.getElementById('mobileFilterCount');
    const mBtn = document.getElementById('mobileFilterBtn');
    if (badge) { badge.textContent = count; badge.style.display = count > 0 ? 'inline' : 'none'; }
    if (mBtn) mBtn.classList.toggle('active', count > 0);
}
function clearOne(id) {
    const el = document.getElementById(id); if (!el) return;
    el.value = el.tagName==='SELECT' ? 'all' : ''; updateFilterUI(); doSearch();
}

/* ── AJAX search ─────────────────────────────────────────────────────── */
function doSearch() {
    document.getElementById('spinIco').style.display = 'inline-block';
    document.getElementById('loadVeil').classList.add('on');
    const brandParam = selectedBrands.size > 0 ? JSON.stringify([...selectedBrands]) : 'all';
    const p = new URLSearchParams({
        ajax:'1', table: usingOldTable?'old':'new', sort: currentSort,
        icode_select: document.getElementById('icode_select').value,
        tire_size_select: document.getElementById('tire_size_select').value,
        brand_select: brandParam,
        col_select: document.getElementById('col_select').value,
        rim_select: document.getElementById('rim_select').value
    });
    fetch('?'+p.toString()).then(r=>r.json()).then(d=>{
        if (d.success) {
            if (typeof d.cusHasItemPrices !== 'undefined') cusHasItemPrices = d.cusHasItemPrices;
            renderTable(d.data, d.usingOldTable);
            const cb = document.getElementById('itemCount');
            const mcb = document.getElementById('mobileItemCount');
            if (cb) { cb.textContent = d.count+' Items'; cb.classList.add('pop'); setTimeout(()=>cb.classList.remove('pop'),450); }
            if (mcb) mcb.textContent = d.count;
        }
    }).catch(console.error).finally(()=>{
        document.getElementById('spinIco').style.display = 'none';
        document.getElementById('loadVeil').classList.remove('on');
    });
}

/* ── Render table + mobile cards (AJAX) ──────────────────────────────── */
function renderTable(items, isOld) {
    const wrap = document.getElementById('invContainer');
    if (!items.length) {
        wrap.innerHTML = `<div class="empty-state"><i class="fas fa-search"></i><h3>No Matching Items</h3><p>Try adjusting or clearing your filters.</p></div>
        <div class="mobile-empty"><i class="fas fa-search"></i><h3>No Matching Items</h3><p>Adjust or clear your filters.</p></div>`;
        return;
    }
    const saved = {};
    document.querySelectorAll('.qty-inp').forEach(i=>{ if(parseInt(i.value)>0) saved[i.dataset.id]=i.value; });
    const brandGroups = new Map();
    items.forEach(item=>{
        const br = item.tire_brand||item.Brand||item.brand||'Unknown';
        if (!brandGroups.has(br)) brandGroups.set(br,[]);
        brandGroups.get(br).push(item);
    });

    const colCount = isOld ? 10 : 9;
    const extraHeaders = isOld ? `<th><i class="fas fa-layer-group"></i>Type</th>` : '';
    let tableRows = '', mobileCards = '';

    brandGroups.forEach((groupItems, brandName) => {
        // Table rows
        tableRows += `<tr class="brand-group-header"><td colspan="${colCount}"><div class="brand-group-label"><i class="fas fa-tag"></i>${esc(brandName)}<span class="brand-group-count">${groupItems.length} items</span></div></td></tr>`;
        // Mobile brand header
        mobileCards += `<div style="font-size:10px;font-weight:800;color:var(--orange);letter-spacing:.14em;text-transform:uppercase;padding:.4rem .5rem .2rem;display:flex;align-items:center;gap:6px;"><i class="fas fa-tag" style="font-size:9px;"></i>${esc(brandName)}<span style="background:rgba(242,128,24,0.15);color:var(--orange-dk);border-radius:20px;padding:1px 7px;font-size:9px;">${groupItems.length}</span></div>`;

        groupItems.forEach(item=>{
            const q = saved[item.id]||'0', hv = saved[item.id]?'filled':'';
            const br = item.tire_brand||item.Brand||item.brand||'';
            const cp = (item.customer_price!==null&&item.customer_price!==undefined)?item.customer_price:'';
            const needsApprox = cusHasItemPrices && (cp===''||cp===null);
            const descVal = isOld?(item.t_description||item.t_size||'N/A'):(item.t_size||'N/A');
            const colVal = isOld?(item.t_colour||item.col||'—'):(item.col||'—');
            const rimVal = item.rim||item.Rim||'—';

            let priceHtml, priceMobileHtml;
            if (cp!==''&&parseFloat(cp)>0) {
                priceHtml = `<span class="price-specific">${esc(currencySymbol)}${parseFloat(cp).toFixed(2)}<span class="price-specific-badge">Fixed</span></span>`;
                priceMobileHtml = priceHtml;
            } else if (needsApprox) {
                priceHtml = `<span class="price-approx">—<span class="price-approx-badge">Approx</span></span>`;
                priceMobileHtml = priceHtml;
            } else {
                const bk = br.toLowerCase().trim();
                const rate = (brandRates.hasOwnProperty(bk)?brandRates[bk]:defaultRate)||0;
                priceHtml = `<span class="price-rate">${rate.toFixed(4)}/kg</span>`;
                priceMobileHtml = priceHtml;
            }
            const extraCols = isOld ? `<td class="type-cell">${item.t_type?`<span class="type-badge">${esc(item.t_type)}</span>`:'—'}</td>` : '';
            const qtyAttr = `data-id="${esc(item.id)}" data-icode="${esc(item.icode||'')}" data-size="${esc(descVal)}" data-tiresize="${esc(item.tire_size||'')}" data-brand="${esc(br)}" data-color="${esc(colVal)}" data-rim="${esc(rimVal)}" data-fweight="${esc(item.fweight||0)}" data-cbm="${esc(item.cbm||0)}" data-customer-price="${esc(cp)}" data-needs-approx-warning="${needsApprox?'1':'0'}"`;

            // Desktop table row
            tableRows += `<tr data-id="${esc(item.id)}" class="${saved[item.id]?'selected':''}">
                <td class="code-cell">${esc(item.icode||'N/A')}</td>
                <td class="desc-cell">${esc(descVal)}</td>
                <td class="brand-cell">${esc(br||'N/A')}</td>
                <td class="tiresize-cell">${esc(item.tire_size||'N/A')}</td>
                <td><span class="color-badge">${esc(colVal)}</span></td>
                <td><span class="rim-badge">${esc(rimVal)}</span></td>
                <td class="num-cell">${esc(item.fweight||'—')}</td>
                <td class="num-cell">${esc(item.cbm||'—')}</td>
                ${extraCols}
                <td class="price-cell">${priceHtml}</td>
                <td><input type="number" class="qty-inp ${hv}" min="0" value="${q}" placeholder="0" ${qtyAttr}
                    onchange="updateOrder(this)" oninput="updateOrder(this)"></td>
            </tr>`;

            // Mobile card
            mobileCards += `<div class="m-card${saved[item.id]?' selected':''}" data-id="${esc(item.id)}">
                <div class="m-card-header">
                    <div>
                        <div class="m-card-code">${esc(item.icode||'N/A')}</div>
                        <div class="m-card-brand">${esc(br||'N/A')}</div>
                    </div>
                    <div style="text-align:right;">
                        ${item.tire_size?`<div style="font-size:11px;font-weight:700;color:var(--gray-500);">${esc(item.tire_size)}</div>`:''}
                        <div style="display:flex;gap:3px;justify-content:flex-end;flex-wrap:wrap;margin-top:2px;">
                            ${colVal!=='—'?`<span class="color-badge" style="font-size:9px;padding:1px 5px;">${esc(colVal)}</span>`:''}
                            ${rimVal!=='—'?`<span class="rim-badge" style="font-size:9px;padding:1px 5px;">${esc(rimVal)}</span>`:''}
                        </div>
                    </div>
                </div>
                <div class="m-card-body">
                    <div class="m-card-row"><span class="m-card-lbl">Description</span><span class="m-card-val">${esc(descVal)}</span></div>
                    <div class="m-card-row"><span class="m-card-lbl">Weight</span><span class="m-card-val">${esc(item.fweight||'—')} kg</span></div>
                    <div class="m-card-row"><span class="m-card-lbl">CBM</span><span class="m-card-val">${esc(item.cbm||'—')}</span></div>
                </div>
                <div class="m-card-footer">
                    <div class="m-card-price">
                        <div class="m-card-price-lbl">Unit Price</div>
                        ${priceMobileHtml}
                    </div>
                    <div class="m-qty-wrap">
                        <button type="button" class="m-qty-btn" onclick="mQtyAdj(this,-1)"><i class="fas fa-minus" style="font-size:10px;"></i></button>
                        <input type="number" class="m-qty-inp qty-inp ${hv}" min="0" value="${q}" placeholder="0" ${qtyAttr}
                            onchange="updateOrder(this)" oninput="updateOrder(this)">
                        <button type="button" class="m-qty-btn" onclick="mQtyAdj(this,1)"><i class="fas fa-plus" style="font-size:10px;"></i></button>
                    </div>
                </div>
            </div>`;
        });
    });

    wrap.innerHTML = `
        <div class="tbl-scroll"><div class="tbl-wrap">
            <table class="inv-tbl">
                <thead><tr>
                    <th><i class="fas fa-barcode"></i>Item Code</th>
                    <th><i class="fas fa-ruler"></i>Description</th>
                    <th><i class="fas fa-tag"></i>Brand</th>
                    <th><i class="fas fa-circle-notch"></i>Tire Size</th>
                    <th><i class="fas fa-palette"></i>Color</th>
                    <th><i class="fas fa-cog"></i>Rim</th>
                    <th><i class="fas fa-weight"></i>Wt (kg)</th>
                    <th><i class="fas fa-cube"></i>CBM</th>
                    ${extraHeaders}
                    <th><i class="fas fa-coins"></i>Unit Price (${esc(currencyCode)})</th>
                    <th><i class="fas fa-cart-plus"></i>Qty</th>
                </tr></thead>
                <tbody>${tableRows}</tbody>
            </table>
        </div></div>
        <div class="mobile-cards">${mobileCards}</div>`;
}

/* ── Clear filters ───────────────────────────────────────────────────── */
function clearFilters() {
    ['icode_select','tire_size_select'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
    ['col_select','rim_select'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='all';});
    clearBrandSelection();
    $('.sort-chip').removeClass('active'); $('.sort-chip[data-sort=""]').addClass('active');
    currentSort=''; updateFilterUI(); doSearch();
}

/* ── Dock toggle ─────────────────────────────────────────────────────── */
function toggleDock() {
    dockOpen = !dockOpen;
    document.getElementById('dockBody').classList.toggle('open', dockOpen);
    document.getElementById('dockIcon').className = dockOpen?'fas fa-chevron-down':'fas fa-chevron-up';
}

/* ── Order update ────────────────────────────────────────────────────── */
function updateOrder(input) {
    clearTimeout(updateTimer);
    updateTimer = setTimeout(()=>{
        const qty = parseInt(input.value)||0;
        if (qty<=0) {
            input.classList.remove('filled');
            input.closest('.m-card')?.classList.remove('selected');
            input.closest('tr')?.classList.remove('selected');
            // Sync companion inputs (desktop/mobile share same data-id)
            syncCompanionInput(input.dataset.id, 0);
            orderItems.delete(input.dataset.id);
            refreshSummary(); saveState(); return;
        }
        const needsWarn = input.dataset.needsApproxWarning==='1';
        const acknowledged = input.dataset.approxAcknowledged==='1';
        if (needsWarn && !acknowledged) {
            input.dataset.prevValue='0';
            approxModalPending={input,qty};
            openApproxModal(input.dataset.icode||'');
            return;
        }
        _doUpdateOrder(input,qty);
    },140);
}

function syncCompanionInput(id, val) {
    document.querySelectorAll(`.qty-inp[data-id="${id}"]`).forEach(inp=>{
        if (parseInt(inp.value)!==val) {
            inp.value=val;
            if(val>0){inp.classList.add('filled');}else{inp.classList.remove('filled');}
        }
    });
}

function _doUpdateOrder(input, qty) {
    const row = input.closest('tr');
    const mcard = input.closest('.m-card');
    input.classList.add('filled');
    if (row) row.classList.add('selected');
    if (mcard) mcard.classList.add('selected');
    // Sync companion
    syncCompanionInput(input.dataset.id, qty);
    orderItems.set(input.dataset.id,{
        id:input.dataset.id,
        icode:input.dataset.icode,
        size:input.dataset.size,
        tiresize:input.dataset.tiresize,
        brand:input.dataset.brand,
        color:input.dataset.color,
        rim:input.dataset.rim,
        fweight:parseFloat(input.dataset.fweight)||0,
        cbm:parseFloat(input.dataset.cbm)||0,
        quantity:qty,
        customerPrice:input.dataset.customerPrice||'',
        approxAcknowledged:input.dataset.approxAcknowledged==='1',
    });
    refreshSummary(); saveState();
}

/* ── Refresh summary ─────────────────────────────────────────────────── */
function refreshSummary() {
    const items = Array.from(orderItems.values());
    const qty = items.reduce((s,i)=>s+i.quantity,0);
    const wt = items.reduce((s,i)=>s+i.quantity*i.fweight,0);
    const cbm = items.reduce((s,i)=>s+i.quantity*i.cbm,0);
    const cost = items.reduce((s,i)=>s+i.quantity*calcUnitPrice(i.brand,i.fweight,i.customerPrice),0);
    setTxt('dItems',items.length.toLocaleString());
    setTxt('dQty',qty.toLocaleString());
    document.getElementById('dWt').textContent = fmtD(wt)+' kg';
    document.getElementById('dCBM').textContent = fmtD(cbm,3);
    document.getElementById('dCost').textContent = fmtCurrency(cost);
    updateContainers(wt);

    // Mobile dock cards
    const listEl = document.getElementById('dockItemsList');
    if (listEl) {
        listEl.innerHTML = items.length ? items.map(item=>{
            const unitPrice = calcUnitPrice(item.brand,item.fweight,item.customerPrice);
            const lineValue = item.quantity*unitPrice;
            const isFixed = item.customerPrice!==''&&parseFloat(item.customerPrice)>0;
            const isApprox = !isFixed && cusHasItemPrices;
            const ackd = item.approxAcknowledged===true||item.approxAcknowledged==='1';
            let priceLabel = isFixed
                ? `<span style="color:#166534;font-weight:800;">${esc(currencySymbol)}${fmtD(unitPrice)}</span>`
                : (isApprox && !ackd)
                    ? `<span style="color:#b45309;">—</span><span style="font-size:9px;background:#fef3c7;color:#b45309;border-radius:10px;padding:1px 5px;font-weight:800;margin-left:3px;">Approx</span>`
                    : `<span style="color:#92400e;font-weight:800;">${esc(currencySymbol)}${fmtD(unitPrice)}</span>`;
            const lineValueDisplay = (isApprox&&!ackd)?`<span style="color:#b45309;">—</span>`:fmtCurrency(lineValue);
            return `<div class="dock-item-card">
                <div class="dock-item-top">
                    <div><span class="dock-item-code">${esc(item.icode)}</span></div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span class="dock-item-brand">${esc(item.brand)}</span>
                        <button type="button" class="rm-btn" onclick="removeItem('${item.id}')"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <div class="dock-item-mid">
                    <div class="dock-item-mid-cell"><span class="dock-item-mid-lbl">Wt/unit</span><span class="dock-item-mid-val">${fmtD(item.fweight)} kg</span></div>
                    <div class="dock-item-mid-cell"><span class="dock-item-mid-lbl">Total Wt</span><span class="dock-item-mid-val">${fmtD(item.quantity*item.fweight)} kg</span></div>
                    <div class="dock-item-mid-cell"><span class="dock-item-mid-lbl">CBM</span><span class="dock-item-mid-val">${fmtD(item.quantity*item.cbm,3)}</span></div>
                </div>
                <div class="dock-item-bottom">
                    <div class="dock-item-price">
                        <div style="font-size:9px;color:var(--gray-400);font-weight:700;text-transform:uppercase;letter-spacing:.09em;margin-bottom:2px;">Unit Price</div>
                        ${priceLabel}
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:9px;color:var(--gray-400);font-weight:700;text-transform:uppercase;letter-spacing:.09em;margin-bottom:2px;">Line Value</div>
                        <div style="font-weight:800;font-size:13px;color:#166534;">${lineValueDisplay}</div>
                    </div>
                    <div class="dock-item-qty-wrap">
                        <button type="button" class="dock-item-qty-btn" onclick="dockQtyAdj('${item.id}',-1)"><i class="fas fa-minus" style="font-size:9px;"></i></button>
                        <input type="number" class="dock-qty" min="1" value="${item.quantity}" data-id="${item.id}"
                            onchange="updateQty(this,'${item.id}')" oninput="updateQty(this,'${item.id}')">
                        <button type="button" class="dock-item-qty-btn" onclick="dockQtyAdj('${item.id}',1)"><i class="fas fa-plus" style="font-size:9px;"></i></button>
                    </div>
                </div>
            </div>`;
        }).join('') : `<div style="text-align:center;padding:1.5rem;color:#ccc;font-weight:600;font-size:13px;"><i class="fas fa-shopping-cart" style="font-size:2rem;display:block;margin-bottom:8px;opacity:.3;"></i>No items yet</div>`;
    }

    // Desktop table
    const tbody = document.getElementById('dockBody2');
    if (tbody) {
        tbody.innerHTML = items.length ? items.map(item=>{
            const unitPrice = calcUnitPrice(item.brand,item.fweight,item.customerPrice);
            const lineValue = item.quantity*unitPrice;
            const isFixed = item.customerPrice!==''&&parseFloat(item.customerPrice)>0;
            const isApprox = !isFixed&&cusHasItemPrices;
            const ackd = item.approxAcknowledged===true||item.approxAcknowledged==='1';
            let priceLabel;
            if (isFixed) priceLabel=`<span style="color:#166534;font-weight:800;">${esc(currencySymbol)}${fmtD(unitPrice)}</span><span style="font-size:9px;background:#dcfce7;color:#15803d;border-radius:10px;padding:1px 5px;font-weight:800;">Fixed</span>`;
            else if (isApprox&&!ackd) priceLabel=`<span style="color:#b45309;font-weight:700;">—</span><span style="font-size:9px;background:#fef3c7;color:#b45309;border:1px solid #fcd34d;border-radius:10px;padding:1px 5px;font-weight:800;margin-left:4px;">Approx</span>`;
            else if (isApprox&&ackd) priceLabel=`<span style="color:#92400e;font-weight:800;">${esc(currencySymbol)}${fmtD(unitPrice)}</span><span style="font-size:9px;background:#fef3c7;color:#b45309;border:1px solid #fcd34d;border-radius:10px;padding:1px 5px;font-weight:800;margin-left:4px;">Approx</span>`;
            else priceLabel=`<span style="color:#888;font-size:11px;">${esc(currencySymbol)}${fmtD(unitPrice)}</span>`;
            const lineValueDisplay=(isApprox&&!ackd)?`<span style="color:#b45309;">—</span>`:fmtCurrency(lineValue);
            return `<tr>
                <td class="dc">${esc(item.icode)}</td>
                <td>${esc(item.size)}</td>
                <td>${esc(item.brand)}</td>
                <td>${esc(item.tiresize||'N/A')}</td>
                <td>${esc(item.color||'—')}</td>
                <td>${esc(item.rim||'—')}</td>
                <td><input type="number" class="dock-qty" min="1" value="${item.quantity}" data-id="${item.id}" onchange="updateQty(this,'${item.id}')" oninput="updateQty(this,'${item.id}')"></td>
                <td class="dw dr">${fmtD(item.fweight)} kg</td>
                <td class="dr">${fmtD(item.cbm,3)}</td>
                <td class="dw dr">${fmtD(item.quantity*item.fweight)} kg</td>
                <td class="dr">${fmtD(item.quantity*item.cbm,3)}</td>
                <td class="dr">${priceLabel}</td>
                <td class="dv">${lineValueDisplay}</td>
                <td><button type="button" class="rm-btn" onclick="removeItem('${item.id}')"><i class="fas fa-times"></i></button></td>
            </tr>`;
        }).join('') : `<tr><td colspan="14" style="text-align:center;padding:2rem;color:#ccc;font-weight:600;font-size:13px;"><i class="fas fa-shopping-cart" style="font-size:2rem;display:block;margin-bottom:8px;opacity:.3;"></i>No items added yet</td></tr>`;
    }

    document.getElementById('orderDock').classList.toggle('open', items.length>0);
}

function dockQtyAdj(id, delta) {
    const item = orderItems.get(id); if (!item) return;
    const newQ = Math.max(1, item.quantity + delta);
    item.quantity = newQ; orderItems.set(id, item);
    syncCompanionInput(id, newQ);
    refreshSummary(); saveState();
}

function updateQty(input, id) {
    const q = parseInt(input.value)||0;
    if (q<=0) { removeItem(id); return; }
    const item = orderItems.get(id);
    if (item) {
        item.quantity=q; orderItems.set(id,item);
        syncCompanionInput(id,q);
        refreshSummary(); saveState();
    }
}
function removeItem(id) {
    orderItems.delete(id);
    document.querySelectorAll(`.qty-inp[data-id="${id}"]`).forEach(inp=>{
        inp.value=0; inp.classList.remove('filled');
        inp.closest('tr')?.classList.remove('selected');
        inp.closest('.m-card')?.classList.remove('selected');
        inp.dataset.approxAcknowledged='0';
    });
    refreshSummary(); saveState();
}
function clearOrder() {
    if (!confirm('Clear all items from your order?')) return;
    document.querySelectorAll('.qty-inp').forEach(i=>{
        i.value=0; i.classList.remove('filled');
        i.closest('tr')?.classList.remove('selected');
        i.closest('.m-card')?.classList.remove('selected');
        i.dataset.approxAcknowledged='0';
    });
    orderItems.clear(); refreshSummary(); saveState();
}

/* ── Submit order ────────────────────────────────────────────────────── */
function submitOrder(event) {
    event.preventDefault();
    if (!orderItems.size) { alert('Please add at least one item to your order.'); return false; }
    const items = Array.from(orderItems.values());
    const qty = items.reduce((s,i)=>s+i.quantity,0);
    const wt = items.reduce((s,i)=>s+i.quantity*i.fweight,0);
    const cbm = items.reduce((s,i)=>s+i.quantity*i.cbm,0);
    const cost = items.reduce((s,i)=>s+i.quantity*calcUnitPrice(i.brand,i.fweight,i.customerPrice),0);
    const ctype = wt<=CAP20?'20 FT':(wt<=CAP40?'40 FT':'40 FT — OVERLOADED');
    const warn = wt>CAP40?`\n\n⚠ OVERLOAD: exceeds 40 FT capacity by ${fmtD(wt-CAP40)} kg!`:'';
    const msg = `Confirm Order:\n\n• ${items.length} product line(s)\n• ${qty.toLocaleString()} total units\n• ${fmtD(wt)} kg total weight\n• ${fmtD(cbm,3)} total CBM\n• Est. Value: ${fmtCurrency(cost)} (${currencyCode})\n• Container: ${ctype}${warn}`;
    if (confirm(msg)) {
        document.getElementById('orderData').value = JSON.stringify(items);
        sessionStorage.removeItem('orderItems');
        document.getElementById('orderForm').submit();
        return true;
    }
    return false;
}

/* ── Session persistence ─────────────────────────────────────────────── */
function saveState() { sessionStorage.setItem('orderItems', JSON.stringify(Array.from(orderItems.values()))); }
function loadState() {
    const s = sessionStorage.getItem('orderItems'); if (!s) return;
    try {
        JSON.parse(s).forEach(item=>{
            orderItems.set(item.id,item);
            document.querySelectorAll(`.qty-inp[data-id="${item.id}"]`).forEach(inp=>{
                inp.value=item.quantity; inp.classList.add('filled');
                inp.closest('tr')?.classList.add('selected');
                inp.closest('.m-card')?.classList.add('selected');
                inp.dataset.approxAcknowledged='1';
            });
        });
        refreshSummary();
    } catch(ex){ sessionStorage.removeItem('orderItems'); }
}
</script>
</body>
</html>
<?php $pdo = null; ?>