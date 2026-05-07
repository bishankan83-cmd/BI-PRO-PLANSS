<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create generated_serials table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS generated_serials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    serial_number VARCHAR(255) NOT NULL,
    icode VARCHAR(100) NOT NULL,
    brand VARCHAR(255),
    description TEXT,
    date DATE,
    maxload VARCHAR(100),
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_serial (serial_number),
    INDEX idx_icode (icode),
    INDEX idx_date (date)
)";
$conn->query($create_table_sql);

// Function to parse HTML-based XLS files
function parseHtmlXls($file_path) {
    $data = [];
    $content = file_get_contents($file_path);
    
    // Remove XML declarations and get table content
    $content = preg_replace('/<\?xml[^>]*>/', '', $content);
    
    // Load as HTML
    $dom = new DOMDocument();
    @$dom->loadHTML($content);
    
    $rows = $dom->getElementsByTagName('tr');
    $row_number = 0;
    
    foreach ($rows as $row) {
        $row_number++;
        if ($row_number == 1) continue; // Skip header
        
        $cells = $row->getElementsByTagName('td');
        if ($cells->length >= 4) {
            $date = trim($cells->item(0)->textContent);
            $icode = trim($cells->item(1)->textContent);
            // Skip column C (index 2)
            $serial = trim($cells->item(3)->textContent);
            
            if (!empty($serial) && !empty($icode) && !empty($date)) {
                $data[] = [
                    'serial_number' => $serial,
                    'icode' => $icode,
                    'date' => $date
                ];
            }
        }
    }
    
    return $data;
}

// Enhanced function to parse XLSX files
function parseXlsx($file_path) {
    $data = [];
    
    try {
        $zip = new ZipArchive;
        if ($zip->open($file_path) !== TRUE) {
            throw new Exception("Could not open XLSX file");
        }
        
        // Get shared strings
        $shared_strings = [];
        $xml_content = $zip->getFromName("xl/sharedStrings.xml");
        if ($xml_content) {
            $xml = @simplexml_load_string($xml_content);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $shared_strings[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        // Handle rich text
                        $text = '';
                        foreach ($si->r as $r) {
                            if (isset($r->t)) {
                                $text .= (string)$r->t;
                            }
                        }
                        $shared_strings[] = $text;
                    }
                }
            }
        }
        
        // Get worksheet data
        $sheet_content = $zip->getFromName("xl/worksheets/sheet1.xml");
        $zip->close();
        
        if (!$sheet_content) {
            throw new Exception("Could not read worksheet data");
        }
        
        $xml = @simplexml_load_string($sheet_content);
        if (!$xml || !isset($xml->sheetData) || !isset($xml->sheetData->row)) {
            throw new Exception("Invalid worksheet structure");
        }
        
        $row_number = 0;
        
        foreach ($xml->sheetData->row as $row) {
            $row_number++;
            
            // Skip header row
            if ($row_number == 1) continue;
            
            $cells = $row->c;
            if (!$cells || count($cells) == 0) continue;
            
            // Extract data from cells - NEW ORDER: Date (A), Item Code (B), Skip C, Serial Number (D)
            $row_data = ['', '', '', ''];
            
            foreach ($cells as $cell) {
                $cell_ref = (string)$cell['r'];
                $col_letter = preg_replace('/[0-9]+/', '', $cell_ref);
                
                $value = '';
                
                // Check if cell has a value
                if (isset($cell->v)) {
                    $cell_value = (string)$cell->v;
                    
                    // Check if it's a shared string
                    if (isset($cell['t']) && (string)$cell['t'] == 's') {
                        $index = (int)$cell_value;
                        if (isset($shared_strings[$index])) {
                            $value = $shared_strings[$index];
                        }
                    } 
                    // Check if it's inline string
                    elseif (isset($cell['t']) && (string)$cell['t'] == 'inlineStr') {
                        if (isset($cell->is) && isset($cell->is->t)) {
                            $value = (string)$cell->is->t;
                        }
                    }
                    // Check if it's a date (number format)
                    elseif (isset($cell['s']) && is_numeric($cell_value)) {
                        // Excel date handling - dates are stored as numbers
                        $value = $cell_value;
                        
                        // Try to convert Excel date serial number to date
                        if ($cell_value > 40000 && $cell_value < 50000) {
                            // Likely a date serial number (Excel epoch starts 1900-01-01)
                            $unix_date = ($cell_value - 25569) * 86400;
                            $converted_date = gmdate("Y-m-d", $unix_date);
                            if ($converted_date) {
                                $value = $converted_date;
                            }
                        }
                    }
                    // Regular value (string or number)
                    else {
                        $value = $cell_value;
                    }
                }
                // Check for inline string without v element
                elseif (isset($cell->is) && isset($cell->is->t)) {
                    $value = (string)$cell->is->t;
                }
                
                // Map to array positions based on column - NEW ORDER: A=Date, B=ICode, C=Skip, D=Serial
                if ($col_letter == 'A') {
                    $row_data[0] = trim($value); // Date
                } elseif ($col_letter == 'B') {
                    $row_data[1] = trim($value); // Item Code
                } elseif ($col_letter == 'C') {
                    $row_data[2] = trim($value); // Skip this column
                } elseif ($col_letter == 'D') {
                    $row_data[3] = trim($value); // Serial Number
                }
            }
            
            // Only add row if Date (A), Item Code (B), and Serial Number (D) have data
            if (!empty($row_data[0]) && !empty($row_data[1]) && !empty($row_data[3])) {
                $data[] = [
                    'date' => $row_data[0],
                    'icode' => $row_data[1],
                    'serial_number' => $row_data[3]
                ];
            }
        }
        
    } catch (Exception $e) {
        throw new Exception("Error parsing XLSX file: " . $e->getMessage());
    }
    
    return $data;
}

// Handle Excel File Upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];
    $allowed_extensions = ['xls', 'xlsx', 'csv'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . urlencode("Invalid file format. Please upload XLS, XLSX, or CSV file."));
        exit;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . urlencode("File upload error. Please try again."));
        exit;
    }
    
    $uploaded_file_path = $file['tmp_name'];
    
    // Parse the file based on extension
    $data = [];
    
    try {
        if ($file_extension == 'csv') {
            // Parse CSV - NEW ORDER: Date (A), Item Code (B), Skip C, Serial Number (D)
            if (($handle = fopen($uploaded_file_path, "r")) !== FALSE) {
                $row_number = 0;
                while (($row = fgetcsv($handle)) !== FALSE) {
                    $row_number++;
                    if ($row_number == 1) continue; // Skip header row
                    
                    // Check if we have at least 4 columns and required fields are filled
                    if (count($row) >= 4 && !empty($row[0]) && !empty($row[1]) && !empty($row[3])) {
                        $data[] = [
                            'date' => trim($row[0]),
                            'icode' => trim($row[1]),
                            'serial_number' => trim($row[3])
                        ];
                    }
                }
                fclose($handle);
            }
        } elseif ($file_extension == 'xlsx') {
            // Parse XLSX using enhanced function
            $data = parseXlsx($uploaded_file_path);
        } else {
            // Parse XLS (HTML-based format from template)
            $data = parseHtmlXls($uploaded_file_path);
        }
    } catch (Exception $e) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . urlencode($e->getMessage()));
        exit;
    }
    
    if (empty($data)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . urlencode("No valid data found in the uploaded file. Please ensure: 1) File has data rows below the header, 2) Columns A (Date), B (Item Code), and D (Serial Number) are filled, 3) Column C can be blank, 4) File format matches the template."));
        exit;
    }
    
    // CLEAR get_serial2 table before importing new data
    $clear_sql = "TRUNCATE TABLE get_serial2";
    if (!$conn->query($clear_sql)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . urlencode("Failed to clear existing data: " . $conn->error));
        exit;
    }
    
    // Insert data into database
    $success_count = 0;
    $error_count = 0;
    $errors = [];
    
    foreach ($data as $row) {
        // Validate date format
        $date_obj = DateTime::createFromFormat('Y-m-d', $row['date']);
        if (!$date_obj || $date_obj->format('Y-m-d') !== $row['date']) {
            $errors[] = "Invalid date format for serial: " . $row['serial_number'];
            $error_count++;
            continue;
        }
        
        // Check if icode exists in tire_details
        $check_sql = "SELECT icode FROM tire_details WHERE icode = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $row['icode']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            $errors[] = "Item code not found in tire_details: " . $row['icode'];
            $error_count++;
            $check_stmt->close();
            continue;
        }
        $check_stmt->close();
        
        // Insert into get_serial2
        $insert_sql = "INSERT INTO get_serial2 (serial_number, icode, date) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sss", $row['serial_number'], $row['icode'], $row['date']);
        
        if ($insert_stmt->execute()) {
            $success_count++;
        } else {
            $error_count++;
            $errors[] = "Failed to insert serial: " . $row['serial_number'] . " - " . $conn->error;
        }
        $insert_stmt->close();
    }
    
    $message = "Previous data cleared. Successfully imported: $success_count new records.";
    if ($error_count > 0) {
        $message .= " Failed: $error_count records.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(", ", array_slice($errors, 0, 5));
        }
    }
    
    header("Location: " . $_SERVER['PHP_SELF'] . "?success=" . urlencode($message));
    exit;
}

// Handle batch PDF generation
$batch_serials = [];
$should_generate_batch = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_batch_pdfs'])) {
    $should_generate_batch = true;
    
    $batch_sql = "SELECT 
                    gs.serial_number,
                    gs.icode as tyre_code,
                    td.brand,
                    td.description,
                    gs.date,
                    td.maxload
                  FROM get_serial2 gs
                  LEFT JOIN tire_details td ON gs.icode = td.icode
                  ORDER BY gs.id";
    
    $batch_result = $conn->query($batch_sql);
    
    if ($batch_result && $batch_result->num_rows > 0) {
        while ($batch_row = $batch_result->fetch_assoc()) {
            $batch_serials[] = [
                'serial_number' => $batch_row['serial_number'],
                'tyre_code' => $batch_row['tyre_code'],
                'brand' => $batch_row['brand'],
                'description' => $batch_row['description'],
                'date' => $batch_row['date'],
                'maxload' => $batch_row['maxload']
            ];
        }
    }
}

// Count items in get_serial2 table
$get_serial_count_sql = "SELECT COUNT(*) as total FROM get_serial2";
$get_serial_count_result = $conn->query($get_serial_count_sql);
$get_serial_count_row = $get_serial_count_result->fetch_assoc();
$get_serial_total = $get_serial_count_row['total'];

// Count items in generated_serials table
$generated_count_sql = "SELECT COUNT(*) as total FROM generated_serials";
$generated_count_result = $conn->query($generated_count_sql);
$generated_count_row = $generated_count_result->fetch_assoc();
$generated_total = $generated_count_row['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management - Excel Upload</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        body {
            background-color: #f0f0f0;
            font-family: 'Open Sans', sans-serif;
        }
        .container-fluid {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        .header {
            background-color: #343a40;
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 2px solid #F28018;
        }
        .upload-section {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            border: 2px solid #28a745;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .batch-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            border: 2px solid #6610f2;
        }
        .stats-section {
            background-color: #fff3cd;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            border: 2px solid #ffc107;
        }
        .btn {
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 40px;
            transition: all 0.3s;
        }
        .btn-upload {
            background-color: #28a745;
            border-color: #28a745;
            color: #ffffff;
        }
        .btn-upload:hover {
            background-color: #218838;
            border-color: #1e7e34;
            color: #ffffff;
        }
        .btn-batch {
            background-color: #6610f2;
            border-color: #6610f2;
            color: #ffffff;
        }
        .btn-batch:hover {
            background-color: #520dc2;
            border-color: #520dc2;
            color: #ffffff;
        }
        .batch-badge {
            background-color: #6610f2;
            color: #ffffff;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: bold;
        }
        .archive-badge {
            background-color: #17a2b8;
            color: #ffffff;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: bold;
        }
        .warning-notice {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .custom-file-label::after {
            content: "Browse";
            background-color: #6c757d;
            color: white;
            border-radius: 0 40px 40px 0;
        }
        .custom-file-label {
            border-radius: 40px;
            overflow: hidden;
        }
        .modal-content {
            border-radius: 15px;
            border: 2px solid #F28018;
        }
        .modal-header {
            background-color: #343a40;
            color: white;
            border-bottom: 2px solid #F28018;
        }
        .instructions {
            background-color: #e7f3ff;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .instructions ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        .column-order-highlight {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .column-order-highlight strong {
            color: #856404;
        }
        .column-c-note {
            background-color: #d1ecf1;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .column-c-note strong {
            color: #0c5460;
        }
        @media (max-width: 768px) {
            .container-fluid {
                padding: 10px;
            }
            .btn {
                padding: 8px 16px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="header">
            <h1 class="mb-0">Stock Management System</h1>
            <p class="mb-0">Upload serial numbers via Excel and generate batch PDFs</p>
        </div>

        <div id="alertArea">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($_GET['success']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Statistics Section -->
        <div class="stats-section">
            <h5 class="mb-3"><i class="fas fa-chart-bar mr-2"></i>Database Statistics</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span><i class="fas fa-hourglass-half mr-2"></i>Pending Queue:</span>
                        <span class="batch-badge"><?php echo number_format($get_serial_total); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Excel Upload Section -->
        <div class="upload-section">
            <h5 class="mb-3"><i class="fas fa-file-excel mr-2"></i>Upload Serial Numbers from Excel</h5>
            
            <div class="warning-notice">
                <strong><i class="fas fa-exclamation-triangle mr-2"></i>WARNING:</strong>
                Uploading a new file will <strong>DELETE ALL EXISTING DATA</strong> in the pending queue and replace it with the new data.
            </div>
            
            <div class="column-order-highlight">
                <strong><i class="fas fa-exclamation-circle mr-2"></i>IMPORTANT - Column Order:</strong>
                <p class="mb-1 mt-2">Your spreadsheet columns MUST be in this exact order:</p>
                <ol class="mb-0 mt-2">
                    <li><strong>Column A:</strong> Date (YYYY-MM-DD format, e.g., 2025-01-15)</li>
                    <li><strong>Column B:</strong> Item Code (icode)</li>
                    <li><strong>Column C:</strong> Leave BLANK (this column will be ignored)</li>
                    <li><strong>Column D:</strong> Serial Number</li>
                </ol>
            </div>
            
            <div class="column-c-note">
                <strong><i class="fas fa-info-circle mr-2"></i>NOTE:</strong>
                Column C can be left empty or contain any data - it will be completely ignored during import. Only columns A, B, and D are required.
            </div>
            
            <form method="post" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="excel_file"><strong>Select Excel File:</strong></label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="excel_file" name="excel_file" 
                               accept=".xls,.xlsx,.csv" required>
                        <label class="custom-file-label" for="excel_file">Choose file...</label>
                    </div>
                    <small class="form-text text-muted">
                        All Excel formats (XLS, XLSX, CSV) are supported. Upload any format you prefer!
                    </small>
                </div>
                <button type="submit" class="btn btn-upload">
                    <i class="fas fa-upload mr-2"></i>Clear & Import New Data
                </button>
            </form>
        </div>

        <!-- Batch PDF Generation Section -->
        <div class="batch-section">
            <h5 class="mb-3"><i class="fas fa-file-pdf mr-2"></i>Batch PDF Generation</h5>
            <p class="text-muted">Generate a single PDF file with all serial numbers from the pending queue. Generated items will be automatically moved to the archive.</p>
            <div class="d-flex align-items-center justify-content-between flex-wrap">
                <div class="mb-2">
                    <span class="batch-badge">
                        <i class="fas fa-list mr-2"></i><?php echo number_format($get_serial_total); ?> Serial Numbers Ready
                    </span>
                </div>
                <form method="post" action="" style="margin: 0;">
                    <button type="submit" name="generate_batch_pdfs" class="btn btn-batch" 
                            <?php echo ($get_serial_total == 0) ? 'disabled' : ''; ?>>
                        <i class="fas fa-magic mr-2"></i>Generate All PDFs
                    </button>
                </form>
            </div>
        </div>

        <!-- Batch Progress Modal -->
        <div class="modal fade" id="batchProgressModal" tabindex="-1" role="dialog" 
             aria-labelledby="batchProgressModalLabel" aria-hidden="true" 
             data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="batchProgressModalLabel">Generating Batch PDFs</h5>
                    </div>
                    <div class="modal-body">
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" id="batchProgressBar" style="width: 0%">0%</div>
                        </div>
                        <div id="batchProgressText" class="text-center">Initializing batch generation...</div>
                        <div id="batchCurrentItem" class="text-muted text-center mt-2"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="batchCloseBtn" 
                                data-dismiss="modal" disabled>Close</button>
                        <button type="button" class="btn btn-primary" id="downloadAllBtn" style="display: none;">
                            <i class="fas fa-download mr-2"></i>Download Combined PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $(".alert").alert('close');
            }, 5000);

            $('.custom-file-input').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName);
            });

            const shouldGenerateBatch = <?php echo $should_generate_batch ? 'true' : 'false'; ?>;
            const batchData = <?php echo json_encode($batch_serials); ?>;

            if (shouldGenerateBatch && batchData && batchData.length > 0) {
                console.log('Starting batch generation with', batchData.length, 'records');
                $('#batchProgressModal').modal('show');
                generateBatchPdfs(batchData);
            } else if (shouldGenerateBatch && (!batchData || batchData.length === 0)) {
                alert('No serial numbers found in pending queue to generate PDFs.');
            }

            function saveToGeneratedSerials(serialData) {
                // Send data to server to save in generated_serials table
                fetch('save_generated.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(serialData)
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Saved to archive:', data);
                })
                .catch(error => {
                    console.error('Error saving to archive:', error);
                });
            }

            function generateBatchPdfs(serialsData) {
                const total = serialsData.length;
                let completed = 0;
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({
                    orientation: 'landscape',
                    unit: 'mm',
                    format: [50, 25]
                });

                $('#batchProgressText').text(`Generating PDF with ${total} pages...`);

                function processNext(index) {
                    if (index >= total) {
                        $('#batchProgressText').html('<i class="fas fa-check-circle text-success mr-2"></i>PDF generated successfully! Saving to archive...');
                        $('#batchCurrentItem').text(`Completed ${completed} pages`);
                        
                        // Save all generated serials to the archive
                        saveToGeneratedSerials(serialsData);
                        
                        $('#batchProgressBar').removeClass('progress-bar-animated');
                        $('#batchCloseBtn').prop('disabled', false);
                        $('#downloadAllBtn').show();
                        
                        $('#downloadAllBtn').off('click').on('click', function() {
                            doc.save('batch_qr_codes_' + new Date().getTime() + '.pdf');
                            // Reload page after download to show updated statistics
                            setTimeout(function() {
                                window.location.href = window.location.pathname + '?success=' + encodeURIComponent('PDFs generated and saved to archive successfully!');
                            }, 500);
                        });
                        return;
                    }

                    const qrData = serialsData[index];
                    $('#batchCurrentItem').text(`Processing: ${qrData.serial_number || 'Unknown'}`);

                    generateSinglePdf(qrData, function() {
                        if (index < total - 1) {
                            doc.addPage([50, 25], 'landscape');
                        }

                        completed++;
                        const progress = Math.round((completed / total) * 100);
                        $('#batchProgressBar').css('width', progress + '%').text(progress + '%');
                        setTimeout(() => processNext(index + 1), 300);
                    }, doc);
                }

                processNext(0);
            }
        
            function generateSinglePdf(qrData, callback, doc) {
                try {
                    const tempDiv = document.createElement('div');
                    tempDiv.style.position = 'absolute';
                    tempDiv.style.left = '-9999px';
                    document.body.appendChild(tempDiv);

                    const qrText = JSON.stringify({
                        'IC': qrData.tyre_code || '',
                        SN: qrData.serial_number || '',
                        'TB': qrData.description || '',
                        D: qrData.date || '',
                        C: 'Made in Sri Lanka'
                    });

                    const qrCode = new QRCode(tempDiv, {
                        text: qrText,
                        width: 200,
                        height: 200,
                        colorDark: '#000000',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.M
                    });

                    const barcodeCanvas = document.createElement('canvas');
                    JsBarcode(barcodeCanvas, qrData.serial_number || '', {
                        format: 'CODE128',
                        width: 2.5,
                        height: 60,
                        displayValue: true,
                        fontSize: 10,
                        margin: 5,
                        marginTop: 2,
                        marginBottom: 2,
                        textMargin: 2
                    });

                    setTimeout(() => {
                        const qrCanvas = tempDiv.querySelector('canvas');
                        if (qrCanvas) {
                            const qrImgData = qrCanvas.toDataURL('image/png');
                            const barcodeImgData = barcodeCanvas.toDataURL('image/png');
                            
                            // Add QR code
                            doc.addImage(qrImgData, 'PNG', 1.8, 1.4, 22, 16);
                            
                            // Draw border around IC box
                            doc.setDrawColor(60, 60, 60);
                            doc.setLineWidth(0.1);
                            doc.rect(1.8, 18, 12.9, 6);
                            
                            // Display "IC:" label at the top center of the box
                            doc.setFont('helvetica');
                            doc.setFontSize(6);
                            doc.setTextColor(0, 0, 0);
                            
                            const labelText = 'Tire Code';
                            const labelWidth = doc.getTextWidth(labelText);
                            const boxCenterX = 1 + (14 / 2);
                            const labelX = boxCenterX - (labelWidth / 2);
                            
                            doc.text(labelText, labelX, 20);
                            
                            // Display IC value centered below the label
                            doc.setFont('helvetica', 'bold');
                            doc.setFontSize(10.8);
                            
                            const icValue = qrData.tyre_code || '';
                            const boxWidth = 13;
                            const maxValueWidth = boxWidth;
                            let displayValue = icValue;
                            
                            // Truncate text if it exceeds max width
                            let valueWidth = doc.getTextWidth(displayValue);
                            while (valueWidth > maxValueWidth && displayValue.length > 0) {
                                displayValue = displayValue.slice(0, -1);
                                valueWidth = doc.getTextWidth(displayValue);
                            }
                            
                            // Center the IC value in the box
                            const valueX = boxCenterX - (doc.getTextWidth(displayValue) / 2);
                            
                            doc.text(displayValue, valueX, 23);
                            
                            // Reset font for other fields
                            doc.setFont('helvetica', 'normal');
                            doc.setFontSize(6.5);
                            
                            // Right side content - Serial Number and other details
                            let yPosition = 3.7;
                            const lineHeight = 2.2;
                            const maxWidth = 24;

                            function wrapTextWithBoldLabel(label, value, x, y, maxWidth, lineHeight, valueOnNewLine = false, customFontSize = null) {
                                const lines = [];
                                let totalHeight = 0;

                                // Store original font size and set custom if provided
                                const originalFontSize = doc.internal.getFontSize();
                                if (customFontSize) {
                                    doc.setFontSize(customFontSize);
                                }

                                if (valueOnNewLine && value) {
                                    lines.push({ text: label, isBold: true });
                                    totalHeight += lineHeight;

                                    const words = value.split(' ');
                                    let line = '';

                                    for (let i = 0; i < words.length; i++) {
                                        const testLine = line + words[i] + ' ';
                                        const metrics = doc.getTextWidth(testLine);
                                        if (metrics > maxWidth && line !== '') {
                                            lines.push({ text: line.trim(), isBold: false });
                                            totalHeight += lineHeight;
                                            line = words[i] + ' ';
                                        } else {
                                            line = testLine;
                                        }
                                    }
                                    if (line.trim()) {
                                        lines.push({ text: line.trim(), isBold: false });
                                        totalHeight += lineHeight;
                                    }
                                } else {
                                    const combinedText = value ? `${label} ${value}` : label;
                                    const words = combinedText.split(' ');
                                    let line = '';

                                    for (let i = 0; i < words.length; i++) {
                                        const testLine = line + words[i] + ' ';
                                        const metrics = doc.getTextWidth(testLine);
                                        if (metrics > maxWidth && line !== '') {
                                            lines.push({ text: line.trim(), needsStyling: true, label: label });
                                            totalHeight += lineHeight;
                                            line = words[i] + ' ';
                                        } else {
                                            line = testLine;
                                        }
                                    }
                                    if (line.trim()) {
                                        lines.push({ text: line.trim(), needsStyling: true, label: label });
                                        totalHeight += lineHeight;
                                    }
                                }

                                lines.forEach((lineObj, index) => {
                                    if (lineObj.text) {
                                        if (valueOnNewLine) {
                                            doc.setFont('helvetica', lineObj.isBold ? 'bold' : 'normal');
                                            doc.text(lineObj.text, x, y + index * lineHeight);
                                        } else {
                                            if (lineObj.needsStyling && lineObj.text.includes(lineObj.label)) {
                                                const labelEndIndex = lineObj.text.indexOf(lineObj.label) + lineObj.label.length;
                                                const labelPart = lineObj.text.substring(0, labelEndIndex);
                                                const valuePart = lineObj.text.substring(labelEndIndex).trim();

                                                let xOffset = x;

                                                if (labelPart) {
                                                    doc.setFont('helvetica', 'bold');
                                                    doc.text(labelPart, xOffset, y + index * lineHeight);
                                                    xOffset += doc.getTextWidth(labelPart);

                                                    if (valuePart) {
                                                        xOffset += 1;
                                                    }
                                                }

                                                if (valuePart) {
                                                    doc.setFont('helvetica', 'normal');
                                                    doc.text(valuePart, xOffset, y + index * lineHeight);
                                                }
                                            } else {
                                                doc.setFont('helvetica', 'normal');
                                                doc.text(lineObj.text, x, y + index * lineHeight);
                                            }
                                        }
                                    }
                                });

                                // Restore original font size
                                doc.setFontSize(originalFontSize);

                                return totalHeight;
                            }

                            if (qrData) {
                                // Display Serial Number without "SN:" label - BOLD and LARGE
                                doc.setFont('helvetica', 'bold');
                                doc.setFontSize(11.1);
                                doc.setTextColor(0, 0, 0);
                                doc.text(qrData.serial_number || '', 24, yPosition);
                                yPosition += 2.6;
                                
                                // Reset font for other fields
                                doc.setFont('helvetica', 'normal');
                                doc.setFontSize(6.5);
                                
                                // Format maxload with "kgs" suffix
                                let maxloadDisplay = '';
                                if (qrData.maxload) {
                                    maxloadDisplay = qrData.maxload + ' kgs';
                                }
                                
                                const rightFields = [
                                    { label: 'Tire Size & Brand:', value: qrData.description || '', valueOnNewLine: true, fontSize: null },
                                    { label: 'DOM:', value: qrData.date || '', valueOnNewLine: false, fontSize: null },
                                    { label: 'ML:', value: maxloadDisplay, valueOnNewLine: false, fontSize: 6 },
                                ];

                                rightFields.forEach((field) => {
                                    if (field.label) {
                                        const heightUsed = wrapTextWithBoldLabel(
                                            field.label, 
                                            field.value, 
                                            24.5, 
                                            yPosition, 
                                            maxWidth, 
                                            lineHeight, 
                                            field.valueOnNewLine,
                                            field.fontSize
                                        );
                                        yPosition += heightUsed;
                                    }
                                });

                                // Add barcode at bottom
                                doc.addImage(barcodeImgData, 'PNG', 15, 17.8, 34, 9);
                            }
                            
                            document.body.removeChild(tempDiv);
                            
                            if (callback) {
                                callback();
                            }
                        } else {
                            document.body.removeChild(tempDiv);
                            if (callback) {
                                callback();
                            }
                        }
                    }, 400);

                } catch (error) {
                    console.error('Error generating PDF page:', error);
                    if (callback) {
                        callback();
                    }
                }
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>