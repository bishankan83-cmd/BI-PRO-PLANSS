<?php
session_start(); // Required to store data between the two steps

/**
 * Tire Order Tool: Preview -> Confirm Insert
 * Step 1: Upload & Parse (Display CSV)
 * Step 2: Confirm & Insert to MySQL
 */

// ─── DATABASE CONFIGURATION ─────────────────────────────────────────────────────
 $dbHost = 'localhost';
 $dbName = 'planatir_cms';
 $dbUser = 'planatir_task_managemen';
 $dbPass = 'Bishan@1919';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('<div style="color:red; font-family:sans-serif; padding:20px;"><strong>Database Connection Failed:</strong> ' . $e->getMessage() . '<br>Please check the configuration at the top of this file.</div>');
}

// ─── LOGIC CONTROL ─────────────────────────────────────────────────────────────
 $error    = null;
 $success  = null;
 $csvData  = null;

// ACTION 2: USER CLICKED "INSERT TO DATABASE"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'insert_db') {
    if (isset($_SESSION['preview_order']) && isset($_SESSION['preview_items'])) {
        try {
            insertDataToDb($pdo, $_SESSION['preview_order'], $_SESSION['preview_items']);
            $success = "Data successfully inserted into the database for Order ID: " . htmlspecialchars($_SESSION['preview_order']['order_id']);
            
            // Clear session data after insert
            unset($_SESSION['preview_order']);
            unset($_SESSION['preview_items']);
            $csvData = null; // Clear preview
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
            // If error, keep preview data so user can see what failed
            $csvData = [
                'orders' => $_SESSION['preview_order'],
                'items'  => $_SESSION['preview_items'],
            ];
        }
    } else {
        $error = "Session expired or data lost. Please upload files again.";
    }
}
// ACTION 1: USER UPLOADED FILES
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['wo_file'], $_FILES['pi_file'])) {
    try {
        $woFile = $_FILES['wo_file']['tmp_name'];
        $piFile = $_FILES['pi_file']['tmp_name'];

        if ($_FILES['wo_file']['error'] !== UPLOAD_ERR_OK) throw new Exception('WO file upload failed.');
        if ($_FILES['pi_file']['error'] !== UPLOAD_ERR_OK) throw new Exception('PI file upload failed.');

        $orderId          = trim($_POST['order_id'] ?? '');
        $customerId       = (int)trim($_POST['customer_id'] ?? '0'); 
        $orderStatus      = trim($_POST['order_status'] ?? 'pending');
        
        // NEW: Capture the user-entered Original and Revised Order IDs
        $originalOrderId  = trim($_POST['original_order_id'] ?? '');
        $revisedOrderId   = trim($_POST['revised_order_id'] ?? '');

        if (empty($orderId)) throw new Exception('Order ID is required.');
        if ($customerId === 0) throw new Exception('Customer ID is required.');

        // Parse Excel files
        $woData = parseXlsx($woFile);
        $piData = parseXlsx($piFile);

        // Build Data (Passing the new IDs)
        [$orderRow, $itemsRows] = buildDbData($woData, $piData, $orderId, $customerId, $orderStatus, $originalOrderId, $revisedOrderId);

        // Store in Session for the next step (Insert)
        $_SESSION['preview_order'] = $orderRow;
        $_SESSION['preview_items'] = $itemsRows;

        // Prepare for Display
        $csvData = [
            'orders' => $orderRow,
            'items'  => $itemsRows,
        ];
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// ─── DATABASE INSERTION LOGIC ─────────────────────────────────────────────────
function insertDataToDb(PDO $pdo, array $order, array $items) {
    try {
        $pdo->beginTransaction();

        // Insert/Update Order
        $sqlOrder = "INSERT INTO tire_orders (
            order_id, customer_id, order_date, status, total_items, total_quantity,
            order_notes, customer_comment, is_revision, original_order_id, revised_order_id,
            order_reference, invoice_no, total_weight, total_payment, unit_weight, total_cbm,
            created_at, destination_port, shipping_method, container_size, hs_code,
            shipment, packing, updated_at, plate, request_status, accepted_at, accepted_by,
            confirmed_at, charge1_name, charge1_value, charge2_name, charge2_value,
            charge3_name, charge3_value, charge4_name, charge4_value, acm_comment
        ) VALUES (
            :order_id, :customer_id, :order_date, :status, :total_items, :total_quantity,
            :order_notes, :customer_comment, :is_revision, :original_order_id, :revised_order_id,
            :order_reference, :invoice_no, :total_weight, :total_payment, :unit_weight, :total_cbm,
            :created_at, :destination_port, :shipping_method, :container_size, :hs_code,
            :shipment, :packing, :updated_at, :plate, :request_status, :accepted_at, :accepted_by,
            :confirmed_at, :charge1_name, :charge1_value, :charge2_name, :charge2_value,
            :charge3_name, :charge3_value, :charge4_name, :charge4_value, :acm_comment
        ) ON DUPLICATE KEY UPDATE
            status = VALUES(status),
            total_items = VALUES(total_items),
            total_quantity = VALUES(total_quantity),
            total_weight = VALUES(total_weight),
            total_payment = VALUES(total_payment),
            total_cbm = VALUES(total_cbm),
            invoice_no = VALUES(invoice_no),
            updated_at = VALUES(updated_at)";

        $stmtOrder = $pdo->prepare($sqlOrder);
        $stmtOrder->execute($order);

        // Handle Items: Delete old, Insert new
        $stmtDelete = $pdo->prepare("DELETE FROM tire_order_items WHERE order_id = ?");
        $stmtDelete->execute([$order['order_id']]);

        $sqlItem = "INSERT INTO tire_order_items (
            order_id, revised, product_id, icode, quantity, unit_price, ordered_date,
            original_order_id, discount, unit_weight, total_weight, payment_amount,
            total_payment, total_cbm, unit_cbm, rate_value
        ) VALUES (
            :order_id, :revised, :product_id, :icode, :quantity, :unit_price, :ordered_date,
            :original_order_id, :discount, :unit_weight, :total_weight, :payment_amount,
            :total_payment, :total_cbm, :unit_cbm, :rate_value
        )";

        $stmtItem = $pdo->prepare($sqlItem);
        foreach ($items as $item) {
            $stmtItem->execute($item);
        }

        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollBack();
        throw new Exception("Database Error: " . $e->getMessage());
    }
}

// ─── DATA BUILDER (Matching SQL Schema & CSV Headers) ────────────────────────
// Updated function signature to include Original and Revised IDs
function buildDbData(array $woSheets, array $piSheets, string $orderId, int $customerId, string $orderStatus, string $originalOrderId, string $revisedOrderId): array {
    $woSheet = reset($woSheets);
    $piSheet = reset($piSheets);

    // 1. Extract Metadata
    $woMeta = [];
    foreach ($woSheet as $ri => $row) {
        $r = array_values($row);
        $flat = implode('|', array_map('strval', $r));
        if (strpos($flat, 'Date:') !== false && strpos($flat, 'Customer:') !== false) {
            $woMeta['date'] = trim($r[2] ?? '');
            $woMeta['customer'] = trim($r[10] ?? '');
        }
        if (strpos($flat, 'W.O. NO.') !== false) {
            $woMeta['wo_no'] = trim($r[2] ?? '');
            $woMeta['order_ref'] = trim($r[10] ?? '');
        }
        if (strpos($flat, 'PO #') !== false || strpos($flat, 'PO#') !== false) {
            $woMeta['po_no'] = trim($r[2] ?? '');
            $woMeta['erp_co'] = trim($r[10] ?? '');
        }
    }

    $piMeta = [];
    foreach ($piSheet as $ri => $row) {
        $r = array_values($row);
        $flat = implode('|', array_map('strval', $r));
        if (strpos($flat, 'Invoice No') !== false) {
            foreach ($r as $v) {
                if (is_string($v) && preg_match('/PI[\\/\-]\d{4}[\\/\-]\d+/i', $v, $m)) {
                    $piMeta['invoice_no'] = $m[0];
                }
            }
        }
        if (strpos($flat, 'Order Ref') !== false && !isset($piMeta['order_ref'])) {
            foreach ($r as $v) {
                if (is_string($v) && strlen(trim($v)) > 5 && trim($v) !== 'Order Ref') {
                    $piMeta['order_ref'] = trim($v);
                }
            }
        }
        if (strpos($flat, "Messer") !== false) {
            foreach ($r as $v) {
                if (is_string($v) && strlen(trim($v)) > 5 && strpos($v, 'Messer') === false && trim($v) !== ':') {
                    $piMeta['customer'] = trim($v);
                    break;
                }
            }
        }
        if (strpos($flat, 'HS Code') !== false) {
            foreach ($r as $v) {
                if (is_string($v) && preg_match('/\d{4}\.\d+/', $v, $m)) {
                    $piMeta['hs_code'] = $m[0];
                }
            }
        }
        if (strpos($flat, 'Delivery Address') !== false) {
            $piMeta['destination_port'] = 'Perth, Australia';
        }
    }

    // 2. Extract Prices & Items
    $piPrices = [];
    $inDataZone = false;
    foreach ($piSheet as $ri => $row) {
        $r = array_values($row);
        $flat = implode('|', array_map('strval', $r));
        if (strpos($flat, 'Item Code') !== false) { $inDataZone = true; continue; }
        if (!$inDataZone) continue;
        $icode = $r[2] ?? '';
        if (is_numeric($icode) && (int)$icode > 10000) {
            $unitPrice = $r[12] ?? 0;
            $piPrices[(int)$icode] = is_numeric($unitPrice) ? (float)$unitPrice : 0;
        }
        if (strpos($flat, 'TOTAL FOB') !== false) break;
    }

    $woItems = [];
    $totalQty = 0; $totalWeight = 0; $totalCbm = 0; $totalPayment = 0;
    foreach ($woSheet as $ri => $row) {
        $r = array_values($row);
        $icode = $r[1] ?? '';
        if (!is_numeric($icode) || (int)$icode <= 10000) continue;
        $icode = (int)$icode;

        $qty        = is_numeric($r[10] ?? '') ? (float)$r[10] : 0;
        $unitWeight = is_numeric($r[8] ?? '')  ? (float)$r[8]  : 0;
        $unitCbm    = is_numeric($r[9] ?? '')  ? (float)$r[9]  : 0;
        $unitPrice  = $piPrices[$icode] ?? 0;
        
        $totalWeightItem = round($unitWeight * $qty, 2);
        $totalCbmItem    = round($unitCbm * $qty, 4);
        $paymentAmt      = round($unitPrice * $qty, 2);

        $woItems[] = [
            'icode'        => $icode,
            'quantity'     => $qty,
            'unit_price'   => $unitPrice,
            'unit_weight'  => $unitWeight,
            'total_weight' => $totalWeightItem,
            'unit_cbm'     => $unitCbm,
            'total_cbm'    => $totalCbmItem,
            'payment_amount' => $paymentAmt,
        ];

        $totalQty     += $qty;
        $totalWeight  += $totalWeightItem;
        $totalCbm     += $totalCbmItem;
        $totalPayment += $paymentAmt;
    }

    // 3. Format Dates
    $rawDate = $woMeta['date'] ?? date('d.m.Y');
    if (preg_match('/(\d{2})\.(\d{2})\.(\d{4})/', $rawDate, $m)) {
        $orderDate = "$m[3]-$m[2]-$m[1]"; // Y-m-d for date column
    } else {
        $orderDate = date('Y-m-d');
    }
    $orderedDateTs = date('Y-m-d H:i:s'); // For timestamp column
    $nowTs         = date('Y-m-d H:i:s');

    // 4. Prepare Order Row (Matching Schema)
    $isRevision = ($orderStatus === 'revised') ? '1' : '0';
    
    $orderRow = [
        'order_id'          => $orderId,
        'customer_id'       => $customerId, // Int
        'order_date'        => $orderDate,  // Date Y-m-d
        'status'            => $orderStatus,
        'total_items'       => count($woItems),
        'total_quantity'    => $totalQty,
        'order_notes'       => '',
        'customer_comment'  => '',
        'is_revision'       => $isRevision,
        // UPDATED: Mapping user-entered values
        'original_order_id' => $originalOrderId,
        'revised_order_id'  => $revisedOrderId,
        // --------------------
        'order_reference'   => $woMeta['order_ref'] ?? ($piMeta['order_ref'] ?? ''),
        'invoice_no'        => $piMeta['invoice_no'] ?? '',
        'total_weight'      => (string)$totalWeight, // Schema is varchar, cast to string
        'total_payment'     => (string)$totalPayment,
        'unit_weight'       => '',
        'total_cbm'         => (string)$totalCbm,
        'created_at'        => $orderDate, // Schema expects 'date', not timestamp
        'destination_port'  => $piMeta['destination_port'] ?? 'Perth, Australia',
        'shipping_method'   => 'Sea Freight',
        'container_size'    => '20ft',
        'hs_code'           => $piMeta['hs_code'] ?? '',
        'shipment'          => '',
        'packing'           => '',
        'updated_at'        => $nowTs, // Timestamp
        'plate'             => '', // NOT NULL in schema
        'request_status'    => '', // NOT NULL in schema
        'accepted_at'       => '0000-00-00 00:00:00', // Datetime NOT NULL
        'accepted_by'       => '', // NOT NULL
        'confirmed_at'      => NULL,
        'charge1_name'      => '',
        'charge1_value'     => '',
        'charge2_name'      => '',
        'charge2_value'     => '',
        'charge3_name'      => '',
        'charge3_value'     => '',
        'charge4_name'      => '',
        'charge4_value'     => '',
        'acm_comment'       => '',
    ];

    // 5. Prepare Items Rows (Matching Schema)
    $itemRows = [];
    foreach ($woItems as $item) {
        $itemRows[] = [
            'order_id'          => $orderId,
            'revised'            => $isRevision,
            'product_id'        => '',
            'icode'             => (string)$item['icode'],
            'quantity'          => $item['quantity'],
            'unit_price'        => $item['unit_price'],
            'ordered_date'      => $orderedDateTs,
            'original_order_id' => '', // Item-level original ID usually blank unless tracked specifically
            'discount'          => '0',
            'unit_weight'       => (string)$item['unit_weight'],
            'total_weight'      => (string)$item['total_weight'],
            'payment_amount'    => (string)$item['payment_amount'],
            'total_payment'     => (string)$item['payment_amount'],
            'total_cbm'         => (string)$item['total_cbm'],
            'unit_cbm'          => (string)$item['unit_cbm'],
            'rate_value'        => '',
        ];
    }

    return [$orderRow, $itemRows];
}

// ─── XLSX PARSER (Unchanged) ─────────────────────────────────────────────────
function parseXlsx(string $file): array {
    $zip = new ZipArchive();
    if ($zip->open($file) !== true) throw new Exception('Cannot open xlsx file.');

    $sharedStrings = [];
    $ssXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($ssXml !== false) {
        $ss = new SimpleXMLElement($ssXml);
        foreach ($ss->si as $si) {
            $text = '';
            foreach ($si->r as $r) { $text .= (string)$r->t; }
            if (empty($text)) $text = (string)$si->t;
            $sharedStrings[] = $text;
        }
    }

    $sheetsXml = $zip->getFromName('xl/workbook.xml');
    $sheetNames = [];
    if ($sheetsXml !== false) {
        $wb = new SimpleXMLElement($sheetsXml);
        $wb->registerXPathNamespace('ns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        foreach ($wb->xpath('//ns:sheet') as $s) {
            $attrs = $s->attributes();
            $sheetNames[(string)$attrs['sheetId']] = (string)$attrs['name'];
        }
    }

    $sheets = [];
    $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
    $sheetFiles = [];
    if ($relsXml !== false) {
        $rels = new SimpleXMLElement($relsXml);
        foreach ($rels->Relationship as $rel) {
            $attrs = $rel->attributes();
            $target = (string)$attrs['Target'];
            $id = (string)$attrs['Id'];
            if (strpos($target, 'worksheets/') !== false) {
                $sheetFiles[$id] = 'xl/' . ltrim($target, '/');
            }
        }
    }

    $sheetIdToRId = [];
    if ($sheetsXml !== false) {
        $wb = new SimpleXMLElement($sheetsXml);
        $wb->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        foreach ($wb->sheets->sheet as $s) {
            $attrs = $s->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $sheetIdToRId[(string)$s['sheetId']] = (string)$attrs['id'];
        }
    }

    foreach ($sheetIdToRId as $sheetId => $rId) {
        $name = $sheetNames[$sheetId] ?? "Sheet$sheetId";
        $path = $sheetFiles[$rId] ?? null;
        if (!$path) continue;
        $sheetXml = $zip->getFromName($path);
        if ($sheetXml === false) continue;
        $sheets[$name] = parseSheet($sheetXml, $sharedStrings);
    }
    $zip->close();
    return $sheets;
}

function parseSheet(string $xml, array $ss): array {
    $ws = new SimpleXMLElement($xml);
    $ws->registerXPathNamespace('ns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    $rows = [];
    foreach ($ws->sheetData->row as $row) {
        $rowIndex = (int)$row['r'] - 1;
        foreach ($row->c as $cell) {
            $ref = (string)$cell['r'];
            $col = colToIndex(preg_replace('/\d/', '', $ref));
            $type = (string)$cell['t'];
            $v = isset($cell->v) ? (string)$cell->v : '';
            if ($type === 's') {
                $v = $ss[(int)$v] ?? '';
            } elseif ($type === 'b') {
                $v = $v ? 'TRUE' : 'FALSE';
            } elseif ($v !== '' && is_numeric($v)) {
                $v = $v + 0;
            }
            $rows[$rowIndex][$col] = $v;
        }
    }
    $maxRow = empty($rows) ? 0 : max(array_keys($rows));
    $maxCol = 0;
    foreach ($rows as $r) { $maxCol = max($maxCol, empty($r) ? 0 : max(array_keys($r))); }
    $grid = [];
    for ($r = 0; $r <= $maxRow; $r++) {
        $grid[$r] = [];
        for ($c = 0; $c <= $maxCol; $c++) {
            $grid[$r][$c] = $rows[$r][$c] ?? '';
        }
    }
    return $grid;
}

function colToIndex(string $col): int {
    $col = strtoupper($col);
    $index = 0;
    for ($i = 0; $i < strlen($col); $i++) {
        $index = $index * 26 + (ord($col[$i]) - ord('A') + 1);
    }
    return $index - 1;
}

// ─── CSV BUILDER (For Preview/Download) ─────────────────────────────────────
function arrayToCsv(array $headers, array $rows): string {
    $out = fopen('php://temp', 'r+');
    fputcsv($out, $headers);
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
    rewind($out);
    $content = stream_get_contents($out);
    fclose($out);
    return $content;
}

// ─── PREPARE CSV STRINGS FOR INLINE DISPLAY ─────────────────────────────────
 $ordersCsv = '';
 $itemsCsv  = '';
if ($csvData) {
    $orderHeaders = array_keys($csvData['orders']);
    $ordersCsv    = arrayToCsv($orderHeaders, [$csvData['orders']]);

    $itemHeaders  = array_keys($csvData['items'][0] ?? []);
    $itemsCsv     = arrayToCsv($itemHeaders, $csvData['items']);
}

// Encode for JS
 $ordersCsvJs = json_encode($ordersCsv);
 $itemsCsvJs  = json_encode($itemsCsv);

// Sticky values
 $postOrderId          = htmlspecialchars($_POST['order_id'] ?? '');
 $postCustomerId       = htmlspecialchars($_POST['customer_id'] ?? '');
 $postOrderStatus      = htmlspecialchars($_POST['order_status'] ?? 'pending');
 // Sticky values for new fields
 $postOriginalOrderId  = htmlspecialchars($_POST['original_order_id'] ?? '');
 $postRevisedOrderId   = htmlspecialchars($_POST['revised_order_id'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tire Order Preview & Import</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #0f1117;
    --surface: #1a1d27;
    --surface2: #22263a;
    --border: #2e3350;
    --accent: #4f7df3;
    --accent2: #38bdf8;
    --green: #22c55e;
    --red: #ef4444;
    --text: #e2e8f0;
    --muted: #64748b;
    --radius: 12px;
  }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'Segoe UI', system-ui, sans-serif;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 40px 16px 60px;
  }

  .logo {
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 8px;
  }

  h1 {
    font-size: clamp(22px, 4vw, 36px);
    font-weight: 800;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-align: center;
    margin-bottom: 6px;
  }

  .subtitle {
    color: var(--muted);
    font-size: 14px;
    text-align: center;
    margin-bottom: 40px;
  }

  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 32px;
    width: 100%;
    max-width: 900px;
    margin-bottom: 24px;
  }

  .card-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  /* UPDATED: Responsive Grid for 5 columns */
  .id-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
  }

  .id-group label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 6px;
  }
  .id-group input, .id-group select {
    width: 100%;
    padding: 10px 14px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text);
    font-size: 14px;
    font-family: inherit;
    outline: none;
  }
  .id-group input:focus, .id-group select:focus { border-color: var(--accent); }

  .upload-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
  }
  @media(max-width: 520px) { .upload-grid { grid-template-columns: 1fr; } }

  .upload-zone {
    border: 2px dashed var(--border);
    border-radius: 10px;
    padding: 28px 16px;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
    position: relative;
  }
  .upload-zone:hover { border-color: var(--accent); background: rgba(79,125,243,.06); }
  .upload-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }

  .btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    padding: 14px 28px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer;
    border: none; transition: all .2s; width: 100%; color: #fff;
  }
  .btn-primary {
    background: linear-gradient(135deg, var(--accent), #3b65d4);
  }
  .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 20px rgba(79,125,243,.4); }

  .btn-success {
    background: linear-gradient(135deg, var(--green), #16a34a);
  }
  .btn-success:hover { transform: translateY(-1px); box-shadow: 0 4px 20px rgba(34,197,94,.4); }

  .btn-download {
    background: var(--surface2); border: 1px solid var(--border); color: var(--text);
    flex: 1; justify-content: center; width: auto;
  }
  .btn-download:hover { border-color: var(--green); color: var(--green); }

  .alert { padding: 14px 18px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; display: flex; gap: 10px; }
  .alert-error { background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.3); color: #fca5a5; }
  .alert-success { background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.3); color: #86efac; }

  .results-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
  }
  .result-card { background: var(--surface2); border: 1px solid var(--border); border-radius: 10px; padding: 20px; }
  .result-card .rc-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; }
  .result-card .rc-value { font-size: 24px; font-weight: 800; color: var(--text); }
  .result-card .rc-sub { font-size: 12px; color: var(--muted); margin-top: 2px; }

  /* CSV Preview Styles */
  .tab-bar { display: flex; gap: 4px; margin-bottom: 16px; }
  .tab-btn {
    padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600;
    cursor: pointer; border: 1px solid var(--border); background: transparent; color: var(--muted);
  }
  .tab-btn.active { background: var(--accent); border-color: var(--accent); color: #fff; }

  .preview-table-wrap {
    overflow-x: auto; border-radius: 8px; border: 1px solid var(--border);
    margin-bottom: 24px; max-height: 400px; overflow-y: auto;
  }
  table { width: 100%; border-collapse: collapse; font-size: 12px; white-space: nowrap; }
  th {
    background: var(--surface2); color: var(--muted); font-size: 11px; font-weight: 700;
    padding: 10px 12px; border-bottom: 1px solid var(--border); position: sticky; top: 0; text-align: left;
  }
  td { padding: 8px 12px; border-bottom: 1px solid rgba(46,51,80,.5); color: var(--text); }
  tr:hover td { background: rgba(79,125,243,.04); }

  .dl-row { display: flex; gap: 12px; }
</style>
</head>
<body>

<div class="logo">ATIRE DB Tool</div>
<h1>Preview & Import</h1>
<p class="subtitle">Upload files to preview data, then confirm to insert into database.</p>

<?php if ($error): ?>
<div style="width:100%;max-width:900px">
  <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div style="width:100%;max-width:900px">
  <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
</div>
<?php endif; ?>

<!-- FORM 1: UPLOAD & PREVIEW -->
<?php if (!$csvData): ?>
<div class="card">
  <div class="card-title">📥 Step 1: Upload Files</div>
  <form method="POST" enctype="multipart/form-data" id="uploadForm">
    <div class="id-grid">
      <div class="id-group">
        <label>Order ID</label>
        <input type="text" name="order_id" placeholder="e.g. ORD-2024-001" value="<?= $postOrderId ?>" required>
      </div>
      
      <!-- NEW FIELDS -->
      <div class="id-group">
        <label>Original Order ID</label>
        <input type="text" name="original_order_id" placeholder="e.g. ORD-2023-001" value="<?= $postOriginalOrderId ?>">
      </div>
      <div class="id-group">
        <label>Revised Order ID</label>
        <input type="text" name="revised_order_id" placeholder="e.g. ORD-2024-002" value="<?= $postRevisedOrderId ?>">
      </div>
      <!-- END NEW FIELDS -->

      <div class="id-group">
        <label>Customer ID</label>
        <input type="number" name="customer_id" placeholder="e.g. 104" value="<?= $postCustomerId ?>" required>
      </div>
      <div class="id-group">
        <label>Status</label>
        <select name="order_status">
          <option value="pending" <?= $postOrderStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
          <option value="confirmed" <?= $postOrderStatus === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
          <option value="complete" <?= $postOrderStatus === 'complete' ? 'selected' : '' ?>>Complete</option>
          <option value="revised" <?= $postOrderStatus === 'revised' ? 'selected' : '' ?>>revised</option>
        </select>
      </div>
    </div>

    <div class="upload-grid">
      <div class="upload-zone">
        <input type="file" name="wo_file" accept=".xlsx,.xls" required>
        <div style="font-size:24px;margin-bottom:8px;">📄</div>
        <div style="font-weight:700">Work Order (WO)</div>
      </div>
      <div class="upload-zone">
        <input type="file" name="pi_file" accept=".xlsx,.xls" required>
        <div style="font-size:24px;margin-bottom:8px;">💰</div>
        <div style="font-weight:700">Proforma Invoice (PI)</div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">
      Generate Preview
    </button>
  </form>
</div>
<?php endif; ?>


<!-- PREVIEW & CONFIRM SECTION -->
<?php if ($csvData): ?>
<div class="card">
  <div class="card-title">👁️ Step 2: Review Data</div>

  <div class="alert alert-success" style="background: rgba(79,125,243,.1); border-color: rgba(79,125,243,.3); color: #bfdbfe;">
    Please review the data below. If correct, click <strong>Insert Data to Database</strong>.
  </div>

  <div class="results-grid">
    <div class="result-card">
      <div class="rc-label">Total Items</div>
      <div class="rc-value"><?= count($csvData['items']) ?></div>
      <div class="rc-sub">Line items</div>
    </div>
    <div class="result-card">
      <div class="rc-label">Total Qty</div>
      <div class="rc-value"><?= number_format($csvData['orders']['total_quantity']) ?></div>
      <div class="rc-sub">Pieces</div>
    </div>
    <div class="result-card">
      <div class="rc-label">Total Weight</div>
      <div class="rc-value"><?= number_format($csvData['orders']['total_weight'], 1) ?></div>
      <div class="rc-sub">kg</div>
    </div>
    <div class="result-card">
      <div class="rc-label">Total Payment</div>
      <div class="rc-value" style="font-size:18px">US$ <?= number_format($csvData['orders']['total_payment'], 2) ?></div>
      <div class="rc-sub">FOB</div>
    </div>
  </div>

  <!-- Preview Tabs -->
  <div class="tab-bar">
    <button class="tab-btn active" onclick="showTab('orders')">tire_orders.csv</button>
    <button class="tab-btn" onclick="showTab('items')">tire_order_items.csv</button>
  </div>

  <div id="tab-orders" class="tab-panel">
    <div class="preview-table-wrap">
      <?php
        $oh = array_keys($csvData['orders']);
        echo '<table><tr>';
        foreach ($oh as $h) echo '<th>'.htmlspecialchars($h).'</th>';
        echo '</tr><tr>';
        foreach ($csvData['orders'] as $v) echo '<td>'.htmlspecialchars((string)$v).'</td>';
        echo '</tr></table>';
      ?>
    </div>
  </div>

  <div id="tab-items" class="tab-panel" style="display:none">
    <div class="preview-table-wrap">
      <?php
        if ($csvData['items']) {
          $ih = array_keys($csvData['items'][0]);
          echo '<table><tr>';
          foreach ($ih as $h) echo '<th>'.htmlspecialchars($h).'</th>';
          echo '</tr>';
          foreach ($csvData['items'] as $row) {
            echo '<tr>';
            foreach ($row as $v) echo '<td>'.htmlspecialchars((string)$v).'</td>';
            echo '</tr>';
          }
          echo '</table>';
        }
      ?>
    </div>
  </div>

  <!-- ACTION BUTTONS -->
  <form method="POST" style="margin-top: 20px;">
    <input type="hidden" name="action" value="insert_db">
    <button type="submit" class="btn btn-success" style="font-size: 16px; padding: 16px;">
      ✅ Insert Data to Database
    </button>
  </form>
  
  <div style="margin-top: 16px; text-align: center;">
    <a href="?" style="color: var(--muted); text-decoration: none; font-size: 13px;">Cancel & Start Over</a>
  </div>
</div>
<?php endif; ?>

<script>
// ─── Tabs ────────────────────────────────────────────────────────────────────
function showTab(name) {
  document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + name).style.display = '';
  event.target.classList.add('active');
}

// ─── CSV Download (Optional helper if user wants to save locally) ────────────
const csvStore = {
  orders: <?= $ordersCsvJs ?>,
  items:  <?= $itemsCsvJs ?>
};

function downloadCsv(type) {
  const content = csvStore[type];
  if (!content) return;
  const filename = type === 'orders' ? 'tire_orders.csv' : 'tire_order_items.csv';
  const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement('a');
  a.href = url; a.download = filename;
  document.body.appendChild(a); a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}
</script>
</body>
</html>