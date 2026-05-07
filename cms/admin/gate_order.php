<?php
// confirm_pi.php
session_start();
include('include/config.php');

$conn = $con;
if (!$conn) die("Database connection failed. Please check your config.php file.");

// ---------------------------------------------------------------
// Currency symbol helper – call AFTER $functional_currency is set
// ---------------------------------------------------------------
function getCurrencySymbol($currency) {
    $map = [
        'us dollar'         => '$',
        'usd'               => '$',
        'euro'              => '€',
        'eur'               => '€',
        'british pound'     => '£',
        'gbp'               => '£',
        'japanese yen'      => '¥',
        'jpy'               => '¥',
        'chinese yuan'      => '¥',
        'cny'               => '¥',
        'indian rupee'      => '₹',
        'inr'               => '₹',
        'sri lankan rupee'  => 'Rs.',
        'lkr'               => 'Rs.',
        'australian dollar' => 'A$',
        'aud'               => 'A$',
        'canadian dollar'   => 'C$',
        'cad'               => 'C$',
        'swiss franc'       => 'CHF',
        'chf'               => 'CHF',
        'singapore dollar'  => 'S$',
        'sgd'               => 'S$',
        'uae dirham'        => 'AED',
        'aed'               => 'AED',
        'saudi riyal'       => 'SAR',
        'sar'               => 'SAR',
    ];
    $key = strtolower(trim($currency));
    return $map[$key] ?? '$'; // default to $ if unknown
}

// ---------------------------------------------------------------
// Amount-in-words – currency label driven by functional_currency
// ---------------------------------------------------------------
function numberToWords($number, $currency_label = 'US Dollars') {
    $ones = [0=>'',1=>'One',2=>'Two',3=>'Three',4=>'Four',5=>'Five',6=>'Six',
        7=>'Seven',8=>'Eight',9=>'Nine',10=>'Ten',11=>'Eleven',12=>'Twelve',
        13=>'Thirteen',14=>'Fourteen',15=>'Fifteen',16=>'Sixteen',
        17=>'Seventeen',18=>'Eighteen',19=>'Nineteen'];
    $tens = [0=>'',2=>'Twenty',3=>'Thirty',4=>'Forty',5=>'Fifty',
        6=>'Sixty',7=>'Seventy',8=>'Eighty',9=>'Ninety'];
    $thousands = ['','Thousand','Million','Billion','Trillion'];

    $parts   = explode('.', number_format($number, 2, '.', ''));
    $dollars = (int)$parts[0];
    $cents   = (int)$parts[1];

    if ($dollars == 0) {
        $dollarWords = 'Zero';
    } else {
        $dollarWords = ''; $chunk = 0;
        while ($dollars > 0) {
            $n = $dollars % 1000;
            if ($n != 0) {
                $str = ''; $h = (int)($n/100); $r = $n%100;
                if ($h > 0) $str .= $ones[$h].' Hundred ';
                if ($r < 20) { $str .= $ones[$r]; }
                else { $str .= $tens[(int)($r/10)]; if ($r%10>0) $str .= ' '.$ones[$r%10]; }
                $str = trim($str);
                if ($chunk > 0) $str .= ' '.$thousands[$chunk];
                $dollarWords = $str.' '.$dollarWords;
            }
            $dollars = (int)($dollars/1000); $chunk++;
        }
        $dollarWords = trim($dollarWords);
    }

    $centWords = '';
    if ($cents > 0) {
        if ($cents < 20) { $centWords = $ones[$cents]; }
        else { $centWords = $tens[(int)($cents/10)]; if ($cents%10>0) $centWords .= ' '.$ones[$cents%10]; }
        $centWords = trim($centWords);
    }
    $result = $currency_label.' '.$dollarWords;
    if ($cents > 0) $result .= ' & Cents '.$centWords;
    return $result.' Only';
}

// AJAX: Confirm order
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='confirm_order') {
    header('Content-Type: application/json');
    $order_id = isset($_POST['order_id']) ? mysqli_real_escape_string($conn,$_POST['order_id']) : '';
    if (!empty($order_id)) {
        $q = "UPDATE tire_orders SET status='pi_confirm',
              order_notes=CONCAT(COALESCE(order_notes,''),'\nProforma Invoice confirmed at ".date('Y-m-d H:i:s')."')
              WHERE order_id=?";
        $stmt = $conn->prepare($q);
        if ($stmt) {
            $stmt->bind_param("s",$order_id);
            if ($stmt->execute())
                echo json_encode(['success'=>true,'message'=>'Order status updated to PI Confirmed',
                    'redirect_url'=>'sent_mail7.php?id='.urlencode($order_id)]);
            else echo json_encode(['success'=>false,'message'=>'Failed to update order status']);
            $stmt->close();
        } else echo json_encode(['success'=>false,'message'=>'Database error']);
    } else echo json_encode(['success'=>false,'message'=>'Invalid order ID']);
    exit;
}

// Load page
$order_id = isset($_GET['id']) ? mysqli_real_escape_string($conn,$_GET['id']) : '';
if (empty($order_id)) { header('location:view_order.php'); exit(); }

$stmt = $conn->prepare("SELECT * FROM tire_orders tor
                        LEFT JOIN users u ON tor.customer_id=u.id
                        WHERE tor.order_id=?");
if (!$stmt) die("Prepare failed: ".$conn->error);
$stmt->bind_param("s",$order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order) { header('location:view_order.php'); exit(); }

$order_payment_rate = isset($order['payment_rate']) ? floatval($order['payment_rate']) : 0;

// Determine customer ID for lookups
$cus_id_for_rate = !empty($order['cus_id']) ? $order['cus_id'] : $order['customer_id'];

// Load customer-specific prices per icode from customer_items
$customer_item_prices = [];
if (!empty($cus_id_for_rate)) {
    $ci_stmt = $conn->prepare("SELECT icode, price FROM customer_items WHERE cus_id = ?");
    if ($ci_stmt) {
        $ci_stmt->bind_param("s", $cus_id_for_rate);
        $ci_stmt->execute();
        $ci_result = $ci_stmt->get_result();
        while ($ci_row = $ci_result->fetch_assoc()) {
            $customer_item_prices[strtolower(trim($ci_row['icode']))] = floatval($ci_row['price']);
        }
        $ci_stmt->close();
    }
}

// Load customer brand-level rates
$customer_brand_rates = [];
if (!empty($cus_id_for_rate)) {
    $cr_stmt = $conn->prepare("SELECT brand, payment_rate FROM customer_rate WHERE cus_id = ?");
    if ($cr_stmt) {
        $cr_stmt->bind_param("s", $cus_id_for_rate);
        $cr_stmt->execute();
        $cr_result = $cr_stmt->get_result();
        while ($cr_row = $cr_result->fetch_assoc()) {
            $customer_brand_rates[strtolower(trim($cr_row['brand']))] = floatval($cr_row['payment_rate']);
        }
        $cr_stmt->close();
    }
}

$payment_term = !empty($order['standard_payment_term'])
    ? $order['standard_payment_term']
    : ($order['payment_term'] ?? '35% Adv.65%');

// ---------------------------------------------------------------
// functional_currency: Priority:
//   1. users.functional_currency matched by cus_id
//   2. order-level functional_currency (if explicitly set)
//   3. fallback: 'US Dollar'
// ---------------------------------------------------------------
$functional_currency = 'US Dollar'; // default fallback
if (!empty($cus_id_for_rate)) {
    $fc_stmt = $conn->prepare("SELECT functional_currency FROM users WHERE cus_id = ? LIMIT 1");
    if ($fc_stmt) {
        $fc_stmt->bind_param("s", $cus_id_for_rate);
        $fc_stmt->execute();
        $fc_row = $fc_stmt->get_result()->fetch_assoc();
        if ($fc_row && !empty($fc_row['functional_currency'])) {
            $functional_currency = $fc_row['functional_currency'];
        }
        $fc_stmt->close();
    }
}
// Order-level value takes final precedence if explicitly set
if (!empty($order['functional_currency'])) {
    $functional_currency = $order['functional_currency'];
}

// Derive currency symbol and display label from functional_currency
$curr_sym   = getCurrencySymbol($functional_currency);   // e.g. $, €, £, Rs., AED
$curr_label = htmlspecialchars($functional_currency);    // e.g. "US Dollar", "Euro"

$delivery_term = $order['inco_term_delivery'] ?? 'DDP';
if (!empty($order['inco_term_id'])) {
    $ist = $conn->prepare("SELECT term_name FROM inco_terms WHERE id = ? LIMIT 1");
    if ($ist) {
        $ist->bind_param("i", $order['inco_term_id']);
        $ist->execute();
        $irow = $ist->get_result()->fetch_assoc();
        if ($irow) $delivery_term = $irow['term_name'];
        $ist->close();
    }
}

$charges = [];
for ($i = 1; $i <= 4; $i++) {
    $name_key  = 'charge'.$i.'_name';
    $value_key = 'charge'.$i.'_value';
    $cname  = isset($order[$name_key])  ? trim($order[$name_key])  : '';
    $cvalue = isset($order[$value_key]) ? trim($order[$value_key]) : '';
    if (!empty($cname) && $cvalue !== '' && floatval($cvalue) != 0) {
        $charges[] = ['name' => $cname, 'value' => floatval($cvalue)];
    }
}

$stmt = $conn->prepare("SELECT
    toi.item_id, toi.order_id, toi.product_id, toi.icode,
    toi.quantity, toi.discount,
    toi.unit_weight, toi.total_weight,
    toi.payment_amount, toi.total_payment,
    toi.total_cbm, toi.unit_cbm,
    toi.rate_value, toi.ordered_date,
    tp.description,
    tp.Brand AS item_brand
FROM tire_order_items toi
LEFT JOIN tire_details tp ON toi.icode = tp.icode
WHERE toi.order_id = ?
ORDER BY toi.item_id");
if (!$stmt) die("Prepare failed: ".$conn->error);
$stmt->bind_param("s",$order_id);
$stmt->execute();
$items_result = $stmt->get_result();

$items=[];
$subtotal=$grand_before_discount=$total_quantity=$total_weight=$total_discount=0;
$effective_rates_used = [];

while ($item = $items_result->fetch_assoc()) {
    $qty         = floatval(preg_replace('/[^0-9.]/','',$item['quantity']    ?? '0'));
    $unit_w      = floatval(preg_replace('/[^0-9.]/','',$item['unit_weight'] ?? '0'));
    $db_tw       = floatval(preg_replace('/[^0-9.]/','',$item['total_weight']?? '0'));
    $disc_pct    = floatval(preg_replace('/[^0-9.]/','',$item['discount']    ?? '0'));
    $stored_rate = floatval($item['rate_value'] ?? 0);

    $item_icode  = strtolower(trim($item['icode'] ?? ''));
    $item_brand  = strtolower(trim($item['item_brand'] ?? ''));

    $price_source      = 'order';
    $actual_unit_price = 0;
    $payment_rate_used = $order_payment_rate;
    $is_icode_price    = false;

    if (!empty($item_icode) && isset($customer_item_prices[$item_icode])) {
        $actual_unit_price = $customer_item_prices[$item_icode];
        $payment_rate_used = ($unit_w > 0) ? ($actual_unit_price / $unit_w) : 0;
        $price_source      = 'icode';
        $is_icode_price    = true;
    } elseif (!empty($item_brand) && isset($customer_brand_rates[$item_brand])) {
        $payment_rate_used = $customer_brand_rates[$item_brand];
        $actual_unit_price = $unit_w * $payment_rate_used;
        $price_source      = 'brand:'.ucfirst($item_brand);
    } else {
        $payment_rate_used = $order_payment_rate;
        $actual_unit_price = $unit_w * $payment_rate_used;
        $price_source      = 'order';
    }

    $effective_rates_used[$price_source] = $payment_rate_used;

    if ($disc_pct > 0) {
        $disc_per_unit         = $actual_unit_price * ($disc_pct / 100);
        $discounted_unit_price = $actual_unit_price - $disc_per_unit;
    } else {
        $discounted_unit_price = ($stored_rate > 0 && !$is_icode_price) ? $stored_rate : $actual_unit_price;
        $disc_per_unit         = max(0, $actual_unit_price - $discounted_unit_price);
    }

    $calc_tw    = ($db_tw > 0) ? $db_tw : ($qty * $unit_w);
    $net_amount = $qty * $discounted_unit_price;
    $amt_before = $qty * $actual_unit_price;
    $item_disc  = $qty * $disc_per_unit;

    $item['qty_n']             = $qty;
    $item['unit_w_n']          = $unit_w;
    $item['tw_n']              = $calc_tw;
    $item['actual_unit_price'] = $actual_unit_price;
    $item['disc_pct']          = $disc_pct;
    $item['disc_n']            = $disc_per_unit;
    $item['discounted_price']  = $discounted_unit_price;
    $item['net_amount']        = $net_amount;
    $item['item_disc']         = $item_disc;
    $item['payment_rate_used'] = $payment_rate_used;
    $item['rate_source']       = $price_source;
    $item['is_icode_price']    = $is_icode_price;

    $subtotal              += $net_amount;
    $grand_before_discount += $amt_before;
    $total_quantity        += $qty;
    $total_weight          += $calc_tw;
    $total_discount        += $item_disc;

    $items[] = $item;
}
$stmt->close();

$invoice_no      = str_pad($order['order_id'],6,'0',STR_PAD_LEFT);
$invoice_date    = date('d/m/Y',strtotime($order['order_date']));
$tax_amount      = 0;
$carriage        = 0.00;

$total_charges = 0;
foreach ($charges as $ch) $total_charges += $ch['value'];

$total           = $subtotal + $tax_amount + $carriage + $total_charges;
$amount_in_words = numberToWords($total, $functional_currency);

$display_payment_rate = $order_payment_rate;
$rate_is_mixed = count($effective_rates_used) > 1;

$logged_in_admin_id = null;
if      (isset($_SESSION['id'])       && !empty($_SESSION['id']))       $logged_in_admin_id = intval($_SESSION['id']);
elseif  (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) $logged_in_admin_id = intval($_SESSION['admin_id']);
elseif  (isset($_SESSION['adminid'])  && !empty($_SESSION['adminid']))  $logged_in_admin_id = intval($_SESSION['adminid']);
elseif  (isset($_SESSION['alogin'])   && !empty($_SESSION['alogin'])) {
    $u=$_SESSION['alogin'];
    $cs=$conn->prepare("SELECT id FROM admin WHERE username=? OR email=? LIMIT 1");
    $cs->bind_param("ss",$u,$u); $cs->execute();
    if ($crow=$cs->get_result()->fetch_assoc()) $logged_in_admin_id=intval($crow['id']);
    $cs->close();
}

$admin_signatures = [];
if ($logged_in_admin_id) {
    $ls=$conn->prepare("SELECT id,fullname,role,digital_signature,signature_date
                        FROM admin WHERE id=? AND digital_signature IS NOT NULL AND role='acm' LIMIT 1");
    $ls->bind_param("i",$logged_in_admin_id); $ls->execute();
    if ($lrow=$ls->get_result()->fetch_assoc()) $admin_signatures[]=$lrow;
    $ls->close();
}
if (empty($admin_signatures)) {
    $fr=$conn->query("SELECT id,fullname,role,digital_signature,signature_date
                      FROM admin WHERE digital_signature IS NOT NULL AND role='acm'
                      ORDER BY signature_date DESC LIMIT 1");
    if ($fr) while ($frow=$fr->fetch_assoc()) $admin_signatures[]=$frow;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Proforma Invoice - <?php echo htmlspecialchars($invoice_no); ?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Arial,sans-serif;padding:20px;padding-top:80px;background:#f5f5f5}
.invoice-container{max-width:1300px;margin:0 auto;background:#fff;box-shadow:0 0 10px rgba(0,0,0,.1)}

.main-header{display:flex;align-items:flex-start;justify-content:space-between;padding:20px 30px;border-bottom:2px dotted #ccc}
.logo-section{flex:0 0 300px}
.logo-section img{width:400px;height:auto}
.company-contact{flex:1;font-size:11px;color:#333;line-height:1.8;padding:0 20px;text-align:right}
.contact-row{margin-bottom:4px}
.contact-label{font-weight:bold;color:#666}

.invoice-banner{background:linear-gradient(135deg,#ff8c00,#ff6b00);padding:15px;text-align:center;border-bottom:2px dotted #ccc}
.invoice-banner h1{color:#fff;font-size:32px;font-weight:bold;letter-spacing:4px;text-transform:uppercase;margin:0;text-shadow:2px 2px 4px rgba(0,0,0,.2)}

.invoice-content{padding:30px}
.status-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:15px;border-bottom:2px solid #333}
.invoice-details{display:grid;grid-template-columns:1fr 1fr;gap:30px;margin:30px 0}
.detail-box{border:1px solid #ddd;padding:15px}
.detail-box h3{background:#f0f0f0;padding:8px;margin:-15px -15px 15px;font-size:14px;font-weight:bold}
.detail-row{display:flex;margin:8px 0;font-size:13px}
.detail-label{font-weight:bold;width:150px;color:#333}
.detail-value{flex:1;color:#666}

.shipping-details-box{border:1px solid #ddd;padding:15px;margin:20px 0;background:#f9f9f9}
.shipping-details-box h3{background:#ff8c00;color:#fff;padding:8px;margin:-15px -15px 15px;font-size:14px;font-weight:bold}
.shipping-grid{display:grid;grid-template-columns:1fr 1fr;gap:15px}
.packing-section{grid-column:1/-1;margin-top:10px;padding-top:10px;border-top:1px solid #ddd}
.packing-content{background:#fff;padding:10px;border-left:3px solid #ff8c00;font-size:13px;color:#666;line-height:1.6;white-space:pre-wrap}

.summary-box{background:#f9f9f9;border:1px solid #ddd;padding:15px;margin:20px 0}
.summary-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:15px}
.summary-item{text-align:center}
.summary-label{font-size:11px;color:#666;text-transform:uppercase;margin-bottom:5px}
.summary-value{font-size:18px;font-weight:bold;color:#333}
.summary-value.discount{color:#d32f2f}
.summary-value.mixed-rate{font-size:13px;color:#333;font-weight:700}

.items-table{width:100%;border-collapse:collapse;margin:20px 0;font-size:10.5px}
.items-table thead tr.thead-top{background:#2c2c2c;color:#fff}
.items-table thead tr.thead-sub{background:#444;color:#fff}
.items-table th{padding:8px 5px;text-align:left;font-weight:bold;border:1px solid #555;white-space:nowrap}
.items-table td{padding:7px 5px;border-bottom:1px solid #e0e0e0;border-right:1px solid #eeeeee;vertical-align:middle}
.items-table tbody tr:nth-child(even){background:#fafafa}
.items-table tbody tr:hover{background:#f0f7ff}
.text-right{text-align:right}
.text-center{text-align:center}

th.col-rate-used{background:#2c2c2c!important;color:#fff!important}
td.col-rate-used{background:#fff;color:#333;font-weight:600;text-align:right;font-size:10px}
td.col-rate-used.brand-rate{background:#fff;color:#333}
td.col-rate-used.order-rate{background:#fff;color:#333}
td.col-rate-used.icode-price{background:#fff8e1;color:#e65100;font-weight:700}
th.col-actual{background:#2c2c2c!important;color:#fff!important}
td.col-actual{background:#fff;color:#333;font-weight:700;text-align:right}
th.col-disc{background:#2c2c2c!important;color:#fff!important}
td.col-disc{background:#fff;color:#333;font-weight:600;text-align:right}
th.col-rate{background:#2c2c2c!important;color:#fff!important}
td.col-rate{background:#fff;color:#333;font-weight:800;text-align:right;font-size:11.5px}
th.col-net{background:#2c2c2c!important;color:#fff!important}
td.col-net{background:#fff;color:#333;font-weight:800;text-align:right;font-size:11.5px}

.amount-in-words{background:#f0f8ff;border:2px solid #4CAF50;border-radius:5px;padding:14px 20px;margin:20px 0;text-align:center;box-shadow:0 2px 5px rgba(0,0,0,.1)}
.amount-in-words .amount-text{font-size:15px;font-weight:bold;color:#1565c0}

.totals-section{margin-top:30px;display:flex;justify-content:space-between;gap:30px}
.notes{flex:1}
.notes h4{font-size:14px;margin-bottom:10px}
.notes p{font-size:12px;color:#666;line-height:1.7}
.totals{width:370px;border:1px solid #ddd;padding:15px}
.total-row{display:flex;justify-content:space-between;padding:8px 0;font-size:13px;border-bottom:1px dotted #e0e0e0}
.total-row.subtotal{border-top:1px solid #ccc;padding-top:10px}
.total-row.discount-row{color:#c62828;font-weight:600}
.total-row.charge-row{color:#6a1b9a;font-weight:600}
.total-row.grand-total{border-top:2px solid #333;margin-top:8px;padding-top:10px;font-weight:bold;font-size:16px;color:#d32f2f;border-bottom:none}

.bank-details{margin-top:30px;border:1px solid #ddd;padding:15px;background:#f9f9f9}
.bank-details h4{margin-bottom:15px;font-size:14px;border-bottom:2px solid #333;padding-bottom:8px}
.bank-section h5{font-size:13px;color:#ff6b00;margin-bottom:10px}
.bank-info{display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:12px}

.signature-section{margin-top:40px;padding-top:20px;border-top:1px solid #ddd}
.signature-box{margin-top:20px;display:flex;justify-content:space-between;align-items:flex-end;gap:30px}
.signature-line{flex:1;text-align:center;font-size:12px}
.signature-line.company-signature{text-align:left;width:20%;flex:0 0 20%}
.signature-line.customer-signature{width:20%;flex:0 0 20%}
.signature-placeholder{min-height:80px;max-height:120px;border-bottom:2px solid #333;margin-bottom:5px;display:flex;align-items:flex-end;justify-content:flex-start;padding-bottom:2px}
.signature-image{max-width:100%;max-height:100px;height:auto;object-fit:contain}
.signature-title{font-weight:bold;margin-bottom:5px;margin-top:10px}
.signature-subtitle{color:#666;font-size:11px;margin-bottom:3px}
.signature-meta{font-size:10px;color:#999;font-style:italic;margin-top:5px}
.no-signature-msg{color:#d32f2f;font-size:11px;padding:10px;background:#fff3cd;border:1px solid #ffc107;border-radius:4px;text-align:center}

.invoice-footer{background:linear-gradient(135deg,#ff8c00,#ff6b00);padding:20px 30px;margin-top:30px;border-top:2px dotted #ccc}
.footer-content{display:flex;justify-content:space-between;align-items:center;color:#fff}
.footer-company{flex:1}
.footer-company h2{font-size:24px;font-weight:bold;margin-bottom:8px;color:#fff;text-shadow:1px 1px 2px rgba(0,0,0,.2)}
.footer-address{font-size:11px;line-height:1.6;color:rgba(255,255,255,.95)}
.footer-contact{flex:0 0 auto;text-align:right;font-size:11px;line-height:1.8;color:rgba(255,255,255,.95)}
.footer-contact-row{margin-bottom:3px}
.footer-label{font-weight:bold;text-transform:uppercase;color:#fff}
.footer-generated{text-align:center;padding:15px;background:#333;color:#fff;font-size:11px;font-style:italic}

.action-buttons{position:fixed;top:20px;right:20px;display:flex;gap:10px;z-index:1000;background:#fff;padding:10px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.2)}
.btn{padding:12px 24px;border:none;border-radius:5px;cursor:pointer;font-size:14px;font-weight:bold;box-shadow:0 2px 5px rgba(0,0,0,.2);transition:all .3s ease}
.btn:hover{transform:translateY(-2px);box-shadow:0 4px 8px rgba(0,0,0,.3)}
.btn:disabled{opacity:.6;cursor:not-allowed;transform:none}
.print-button{background:#d32f2f;color:#fff}.print-button:hover{background:#b71c1c}
.confirm-button{background:#4CAF50;color:#fff}.confirm-button:hover{background:#45a049}
.confirm-button.confirmed{background:#2196F3}
.back-button{background:#757575;color:#fff}.back-button:hover{background:#616161}
.status-badge{display:inline-block;padding:5px 15px;border-radius:20px;font-size:12px;font-weight:bold;text-transform:uppercase}
.status-pending{background:#fff3cd;color:#856404}
.status-confirmed{background:#d4edda;color:#155724}
.status-cus_confirmed,.status-acm_confirm{background:#d1ecf1;color:#0c5460}
.status-pi_confirm{background:#cce5ff;color:#004085}
.alert{padding:15px;border:1px solid transparent;border-radius:4px;position:fixed;top:80px;right:20px;min-width:300px;z-index:1001;animation:slideIn .3s ease}
.alert-success{color:#155724;background:#d4edda;border-color:#c3e6cb}
.alert-error{color:#721c24;background:#f8d7da;border-color:#f5c6cb}
@keyframes slideIn{from{transform:translateX(400px);opacity:0}to{transform:translateX(0);opacity:1}}

@media(max-width:768px){
  body{padding:10px;padding-top:150px}
  .main-header{flex-direction:column;gap:15px}
  .company-contact{padding:0;text-align:left}
  .invoice-details,.summary-grid,.shipping-grid{grid-template-columns:1fr}
  .totals-section{flex-direction:column}.totals{width:100%}
  .action-buttons{position:static;margin-bottom:20px;flex-wrap:wrap}
  .bank-info{grid-template-columns:1fr}
  .signature-box{flex-direction:column;gap:30px}.signature-line{width:100%}
  .footer-content{flex-direction:column;gap:15px;text-align:center}.footer-contact{text-align:center}
}
@media print{
  body{padding:0;background:#fff}
  .invoice-container{box-shadow:none}
  .action-buttons,.alert{display:none!important}
  @page{margin:1cm}
}
</style>
</head>
<body>

<div class="action-buttons">
    <a href="order-details.php?oid=<?php echo urlencode($order_id); ?>" class="btn back-button">&#8592; Back to Order</a>
    <button class="btn print-button" onclick="window.print()">&#128424; Print Invoice</button>
    <?php if ($order['status'] !== 'pi_confirm'): ?>
    <button class="btn confirm-button" id="confirmBtn" onclick="confirmOrder()">&#10003; Confirm PI</button>
    <?php else: ?>
    <button class="btn confirm-button confirmed" disabled>&#10003; PI Confirmed</button>
    <?php endif; ?>
</div>

<div class="invoice-container">

    <div class="main-header">
        <div class="logo-section"><img src="atire.png" alt="Atire Logo"></div>
        <div class="company-contact">
            <div class="contact-row"><span class="contact-label">sales:</span> +94 (345)760-746</div>
            <div class="contact-row"><span class="contact-label">support:</span> support@atire.com</div>
            <div class="contact-row"><span class="contact-label">website:</span> www.atire.com</div>
            <div class="contact-row"><span class="contact-label">addr:</span>
                Atire (Pvt) Ltd,<br>No 02, Udugalle Estate,<br>Bandaragama,<br>Sri Lanka</div>
            <div class="contact-row"><span class="contact-label">Reg:</span> PV20795</div>
        </div>
    </div>

    <div class="invoice-banner"><h1>PROFORMA INVOICE</h1></div>

    <div class="invoice-content">

        <div class="status-header">
            <h3>Invoice Details</h3>
            <span class="status-badge status-<?php echo strtolower(str_replace(' ','_',$order['status'])); ?>" id="statusBadge">
                <?php echo htmlspecialchars($order['status']); ?>
            </span>
        </div>

        <div class="invoice-details">
            <div class="detail-box">
                <h3>Invoice To</h3>
                <div class="detail-row">
                    <span class="detail-label">Company:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['fullName'] ?: 'N/A'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['registerd_Address'] ?: 'N/A'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Country:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['Country'] ?: 'N/A'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['userEmail'] ?: 'N/A'); ?></span>
                </div>
                <?php if ($order['contact_person1_name']): ?>
                <div class="detail-row">
                    <span class="detail-label">Contact Person:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['contact_person1_name']); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="detail-box">
                <h3>Invoice Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Invoice No:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($invoice_no); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Invoice Date:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($invoice_date); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Order ID:</span>
                    <span class="detail-value">#<?php echo htmlspecialchars($order['order_id']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Customer Code:</span>
                    <span class="detail-value">
                        <?php echo htmlspecialchars(
                            !empty($order['customer_code'])
                                ? $order['customer_code']
                                : ($order['cus_id'] ?: $order['customer_id'])
                        ); ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Currency:</span>
                    <span class="detail-value"><?php echo $curr_label; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Terms:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($payment_term); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Inco Terms:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($delivery_term); ?></span>
                </div>
            </div>
        </div>

        <div class="shipping-details-box">
            <h3>&#128230; Shipping &amp; Logistics Details</h3>
            <div class="shipping-grid">
                <?php if (!empty($order['hs_code'])): ?>
                <div class="detail-row">
                    <span class="detail-label">HS Code:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['hs_code']); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['destination_port'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Destination Port:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['destination_port']); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['shipping_method'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Shipping Method:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['shipping_method']); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['container_size'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Container Size:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['container_size']); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['shipment'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Shipment Details:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['shipment']); ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($order['packing'])): ?>
            <div class="packing-section">
                <div class="detail-row">
                    <span class="detail-label" style="width:100%;margin-bottom:8px">Packing Instructions:</span>
                </div>
                <div class="packing-content"><?php echo nl2br(htmlspecialchars($order['packing'])); ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Summary bar ── -->
        <div class="summary-box">
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Total Items</div>
                    <div class="summary-value"><?php echo htmlspecialchars($order['total_items']); ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Quantity</div>
                    <div class="summary-value"><?php echo number_format($total_quantity,0); ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Weight</div>
                    <div class="summary-value"><?php echo number_format($total_weight,2); ?> kgs</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Discount</div>
                    <div class="summary-value discount">
                        <?php echo $curr_sym.number_format($total_discount,2); ?>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Payment Rate</div>
                    <?php if ($rate_is_mixed): ?>
                        <div class="summary-value mixed-rate">Mixed<br><small style="font-size:10px">(per item / brand)</small></div>
                    <?php else: ?>
                        <div class="summary-value"><?php echo $curr_sym.number_format($display_payment_rate,4); ?>/kg</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ── Line-items table ── -->
        <table class="items-table">
            <thead>
                <tr class="thead-top">
                    <th rowspan="2" style="vertical-align:middle;width:3%">#</th>
                    <th rowspan="2" style="vertical-align:middle;width:8%">Item Code</th>
                    <th rowspan="2" style="vertical-align:middle;width:15%">Description</th>
                    <th rowspan="2" style="vertical-align:middle;width:5%">Product ID</th>
                    <th rowspan="2" style="vertical-align:middle;width:4%">Brand</th>
                    <th rowspan="2" class="text-center" style="vertical-align:middle;width:4%">Qty</th>
                    <th colspan="2" class="text-center" style="background:#444;width:12%">Weight (kgs)</th>
                    <th class="text-right col-actual" rowspan="2" style="vertical-align:middle;width:9%">
                        Actual Unit Price (<?php echo $curr_sym; ?>)<br>
                        <small style="font-weight:normal;font-size:9px">(item price or Wt&times;Rate)</small>
                    </th>
                    <th class="text-right col-disc" rowspan="2" style="vertical-align:middle;width:9%">
                        Discount<br>
                        <small style="font-weight:normal;font-size:9px">(% &amp; <?php echo $curr_sym; ?> / unit)</small>
                    </th>
                    <th class="text-right col-rate" rowspan="2" style="vertical-align:middle;width:9%">
                        Discounted Unit Price (<?php echo $curr_sym; ?>)
                    </th>
                    <th class="text-right col-net" rowspan="2" style="vertical-align:middle;width:9%">
                        Net Amount (<?php echo $curr_sym; ?>)
                    </th>
                </tr>
                <tr class="thead-sub">
                    <th class="text-right" style="background:#555;width:6%">Unit</th>
                    <th class="text-right" style="background:#555;width:6%">Total</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="12" class="text-center" style="padding:20px">No items found for this order</td></tr>
            <?php else: $c=1; foreach($items as $item): ?>
                <?php
                    $is_icode_price = $item['is_icode_price'];
                    $is_brand_rate  = (!$is_icode_price && $item['rate_source'] !== 'order');
                    if ($is_icode_price) {
                        $rate_cell_class = 'col-rate-used icode-price';
                    } elseif ($is_brand_rate) {
                        $rate_cell_class = 'col-rate-used brand-rate';
                    } else {
                        $rate_cell_class = 'col-rate-used order-rate';
                    }
                ?>
                <tr>
                    <td><?php echo $c++; ?></td>
                    <td><?php echo htmlspecialchars($item['icode']); ?></td>
                    <td><?php echo htmlspecialchars($item['description'] ?: 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($item['product_id']); ?></td>
                    <td style="text-align:center;font-size:10px">
                        <?php if (!empty($item['item_brand'])): ?>
                            <span style="background:#f5f5f5;color:#555;padding:2px 5px;border-radius:3px;font-weight:600">
                                <?php echo htmlspecialchars($item['item_brand']); ?>
                            </span>
                        <?php else: ?>
                            <span style="color:#bbb">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?php echo number_format($item['qty_n'],0); ?></td>
                    <td class="text-right"><?php echo number_format($item['unit_w_n'],2); ?></td>
                    <td class="text-right"><?php echo number_format($item['tw_n'],2); ?></td>
                    <td class="col-actual">
                        <?php echo $curr_sym.number_format($item['actual_unit_price'],2); ?>
                    </td>
                    <td class="col-disc">
                        <?php if ($item['disc_pct'] > 0): ?>
                            <span style="display:block;font-size:10px;color:#333;margin-bottom:2px;font-weight:700">
                                <?php echo number_format($item['disc_pct'],2); ?>%
                            </span>
                        <?php endif; ?>
                        <?php if ($item['disc_n'] > 0): ?>
                            - <?php echo $curr_sym.number_format($item['disc_n'],2); ?>
                        <?php else: ?>
                            <span style="color:#bbb">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="col-rate">
                        <?php echo $curr_sym.number_format($item['discounted_price'],2); ?>
                    </td>
                    <td class="col-net">
                        <?php echo $curr_sym.number_format($item['net_amount'],2); ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>

        <!-- ── Amount in words ── -->
        <div class="amount-in-words">
            <div class="amount-text"><?php echo htmlspecialchars($amount_in_words); ?></div>
        </div>

        <!-- ── Notes + Totals ── -->
        <div class="totals-section">
            <div class="notes">
                <h4>Note:</h4>
                <p><strong>Sales Tax is the customer's responsibility</strong>, please provide a valid sales tax exemption certificate at the time of purchase.</p>
                <p style="margin-top:10px">*** Please note we accept bank transfers / Check Deposit</p>
                <?php if ($order['order_notes']): ?>
                <div style="margin-top:15px;padding:10px;background:#fff3cd;border-left:3px solid #ffc107">
                    <strong>Order Notes:</strong><br>
                    <?php echo nl2br(htmlspecialchars($order['order_notes'])); ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="totals">
                <div class="total-row">
                    <span>Sub Total <small>(before discount)</small>:</span>
                    <span><?php echo $curr_sym.number_format($grand_before_discount,2); ?></span>
                </div>
                <div class="total-row discount-row">
                    <span>Total Discount:</span>
                    <span>- <?php echo $curr_sym.number_format($total_discount,2); ?></span>
                </div>
                <div class="total-row subtotal">
                    <span>Sub Total <small>(after discount)</small>:</span>
                    <span><?php echo $curr_sym.number_format($subtotal,2); ?></span>
                </div>
                <div class="total-row">
                    <span>Total Tax Amount (0.00%):</span>
                    <span><?php echo $curr_sym.number_format($tax_amount,2); ?></span>
                </div>
                <div class="total-row">
                    <span>Carriage Amount:</span>
                    <span><?php echo $curr_sym.number_format($carriage,2); ?></span>
                </div>
                <?php if (!empty($charges)): ?>
                    <?php foreach ($charges as $ch): ?>
                    <div class="total-row charge-row">
                        <span><?php echo htmlspecialchars($ch['name']); ?>:</span>
                        <span><?php echo $curr_sym.number_format($ch['value'],2); ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="total-row grand-total">
                    <span>Payment Due:</span>
                    <span><?php echo $curr_sym.number_format($total,2); ?></span>
                </div>
            </div>
        </div>

        <div class="bank-details">
            <h4>Bank Transfer Details:</h4>
            <div class="bank-section">
                <h5>Seylan Bank PLC (Sri Lanka Account)</h5>
                <div class="bank-info">
                    <div><strong>Account Name:</strong> Atire (Pvt) Ltd</div>
                    <div><strong>Account Number:</strong> 9911-00090856-001</div>
                    <div><strong>Bank Name:</strong> Seylan Bank PLC</div>
                    <div><strong>Branch:</strong> Corporate Branch</div>
                    <div><strong>Bank Address:</strong> No. 90, Galle Road, Colombo 03, Sri Lanka</div>
                    <div><strong>Swift Code:</strong> SEYBLKLX</div>
                </div>
            </div>
        </div>

        <div class="signature-section">
            <h4>Authorized Signatures</h4>
            <div class="signature-box">
                <div class="signature-line company-signature">
                    <?php if (!empty($admin_signatures) && isset($admin_signatures[0])): ?>
                        <div class="signature-placeholder">
                            <img src="<?php echo htmlspecialchars($admin_signatures[0]['digital_signature']); ?>"
                                 alt="Digital Signature" class="signature-image">
                        </div>
                        <div class="signature-title"><?php echo htmlspecialchars($admin_signatures[0]['fullname']); ?></div>
                        <div class="signature-subtitle"><?php echo htmlspecialchars($admin_signatures[0]['role']); ?></div>
                        <div class="signature-subtitle">FOR ATIRE (PVT) LIMITED</div>
                        <div class="signature-meta">Digitally signed on <?php echo date('d M Y',strtotime($admin_signatures[0]['signature_date'])); ?></div>
                    <?php else: ?>
                        <div class="signature-placeholder"></div>
                        <div class="summary-title">Authorized Signatory</div>
                        <div class="signature-subtitle">Account Manager</div>
                        <div class="signature-subtitle">FOR ATIRE (PVT) LIMITED</div>
                        <div class="no-signature-msg">
                            No digital signature available.
                            <?php if ($logged_in_admin_id): ?>
                                <a href="digital_signature.php" style="color:#d32f2f;text-decoration:underline">Click here to create your signature</a>
                            <?php else: ?>Please contact admin to add signature.<?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="signature-line customer-signature">
                    <div class="signature-placeholder"></div>
                    <div class="signature-title">Customer Signature</div>
                    <div class="signature-subtitle">Date &amp; Company Seal</div>
                </div>
            </div>
        </div>

    </div><!-- /.invoice-content -->

    <div class="invoice-footer">
        <div class="footer-content">
            <div class="footer-company">
                <h2>ATIRE (PVT) LTD.</h2>
                <div class="footer-address">OFFICE &amp; FACTORY: No. 02 Udugalla Estate, Paragasthota (Bandaragama) KT 12444, Sri Lanka</div>
            </div>
            <div class="footer-contact">
                <div class="footer-contact-row"><span class="footer-label">TEL:</span> 0094 (0)34-5 760 740</div>
                <div class="footer-contact-row"><span class="footer-label">WEB:</span> www.atirecom.com</div>
                <div class="footer-contact-row"><span class="footer-label">EMAIL:</span> yeshan.w@atire.com</div>
                <div class="footer-contact-row"><span class="footer-label">REG:</span> PV20795</div>
            </div>
        </div>
    </div>
    <div class="footer-generated">** THIS IS SYSTEM GENERATED INVOICE **</div>

</div><!-- /.invoice-container -->

<script>
function showAlert(message, type) {
    document.querySelectorAll('.alert').forEach(a => a.remove());
    const el = document.createElement('div');
    el.className = 'alert alert-' + type;
    el.textContent = message;
    document.body.appendChild(el);
    setTimeout(() => { el.style.animation='slideIn 0.3s ease reverse'; setTimeout(()=>el.remove(),300); }, 5000);
}

function confirmOrder() {
    const orderId    = '<?php echo addslashes($order_id); ?>';
    const confirmBtn = document.getElementById('confirmBtn');
    if (!confirm('Are you sure you want to confirm this Proforma Invoice?\nYou will be redirected to send an email confirmation.')) return;
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processing...';

    fetch(window.location.pathname + window.location.search, {
        method : 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body   : 'action=confirm_order&order_id=' + encodeURIComponent(orderId)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('statusBadge');
            if (badge) { badge.className='status-badge status-pi_confirm'; badge.textContent='pi_confirm'; }
            showAlert(data.message + ' - Redirecting to email confirmation...', 'success');
            confirmBtn.className   = 'btn confirm-button confirmed';
            confirmBtn.textContent = 'PI Confirmed';
            setTimeout(() => { window.location.href = data.redirect_url; }, 2000);
        } else {
            showAlert(data.message, 'error');
            confirmBtn.disabled    = false;
            confirmBtn.textContent = 'Confirm PI';
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showAlert('An error occurred. Please try again.', 'error');
        confirmBtn.disabled    = false;
        confirmBtn.textContent = 'Confirm PI';
    });
}

console.log('Invoice loaded - Order: <?php echo addslashes($order_id); ?>');
console.log('Functional Currency: <?php echo addslashes($functional_currency); ?>');
console.log('Currency Symbol: <?php echo addslashes($curr_sym); ?>');
console.log('Order-level Payment Rate: <?php echo $curr_sym; ?><?php echo number_format($order_payment_rate,4); ?>/kg');
console.log('Grand Total: <?php echo $curr_sym; ?><?php echo number_format($total,2); ?>');
</script>
</body>
</html>