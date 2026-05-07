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

// Set default values for filter
$where_clause = "";
$serial_filter = "";
$serial_select = "";
$qr_data = null;
$batch_serials = [];

// Handle batch PDF generation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_batch_pdfs'])) {
    // Get all serial numbers from get_serial table
    $batch_sql = "SELECT DISTINCT gs.serial_number 
                  FROM get_serial gs
                  INNER JOIN stock_erp s ON gs.serial_number = s.serial_number
                  LEFT JOIN tire_details td ON s.tyre_code = td.icode
                  ORDER BY gs.id";
    $batch_result = $conn->query($batch_sql);
    
    if ($batch_result->num_rows > 0) {
        while ($batch_row = $batch_result->fetch_assoc()) {
            // Get detailed data for each serial number
            $detail_sql = "SELECT s.serial_number, s.tyre_code, td.brand, s.description, s.date, td.maxload 
                          FROM stock_erp s 
                          LEFT JOIN tire_details td ON s.tyre_code = td.icode 
                          WHERE s.serial_number = '" . $conn->real_escape_string($batch_row['serial_number']) . "'
                          LIMIT 1";
            $detail_result = $conn->query($detail_sql);
            
            if ($detail_result->num_rows > 0) {
                $batch_serials[] = $detail_result->fetch_assoc();
            }
        }
    }
}

// Handle serial number filter if submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['filter'])) {
    if (!empty($_POST['serial_select']) && $_POST['serial_select'] != "all") {
        $serial_select = $_POST['serial_select'];
        $where_clause .= " AND s.serial_number = '" . $conn->real_escape_string($serial_select) . "'";
    } elseif (!empty($_POST['serial_filter'])) {
        $serial_filter = $_POST['serial_filter'];
        $where_clause .= " AND s.serial_number LIKE '%" . $conn->real_escape_string($serial_filter) . "%'";
    }
    // Fetch data for QR code (first record matching the filter)
    $qr_sql = "SELECT s.serial_number, s.tyre_code, td.brand, s.description, s.date, td.maxload 
               FROM stock_erp s 
               LEFT JOIN tire_details td ON s.tyre_code = td.icode 
               WHERE 1=1" . $where_clause . " LIMIT 1";
    $qr_result = $conn->query($qr_sql);
    if ($qr_result->num_rows > 0) {
        $qr_data = $qr_result->fetch_assoc();
    }
} elseif (isset($_POST['reset'])) {
    $serial_filter = "";
    $serial_select = "";
    $where_clause = "";
    $qr_data = null;
}

// Get distinct serial numbers for dropdown (prioritize get_serial table)
$serial_sql = "SELECT DISTINCT s.serial_number 
               FROM stock_erp s
               WHERE s.serial_number IN (SELECT serial_number FROM get_serial)
               ORDER BY s.id DESC LIMIT 1000";
$serial_result = $conn->query($serial_sql);

// Count total items for pagination
$count_sql = "SELECT COUNT(*) as total FROM stock_erp s 
              LEFT JOIN tire_details td ON s.tyre_code = td.icode
              WHERE 1=1" . $where_clause;
$count_result = $conn->query($count_sql);
$count_row = $count_result->fetch_assoc();
$total_items = $count_row['total'];

// Count items in get_serial table
$get_serial_count_sql = "SELECT COUNT(*) as total FROM get_serial";
$get_serial_count_result = $conn->query($get_serial_count_sql);
$get_serial_count_row = $get_serial_count_result->fetch_assoc();
$get_serial_total = $get_serial_count_row['total'];

// Pagination
$items_per_page = 1000;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;
$total_pages = ceil($total_items / $items_per_page);

// Get data with pagination
$sql = "SELECT s.serial_number, s.tyre_code, td.brand, s.description, s.date, td.maxload 
        FROM stock_erp s 
        LEFT JOIN tire_details td ON s.tyre_code = td.icode
        WHERE 1=1" . $where_clause . " 
        ORDER BY s.id DESC LIMIT " . $offset . ", " . $items_per_page;
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock - Qty</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
        .card {
            border-radius: 15px;
            border: 2px solid #F28018;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            margin-bottom: 30px;
            overflow: hidden;
        }
        .card-header {
            background-color: #343a40;
            color: white;
            padding: 15px 20px;
            font-weight: bold;
            font-size: 18px;
            border-bottom: 2px solid #F28018;
        }
        .form-label {
            font-weight: 600;
            color: #343a40;
        }
        .form-control, .form-select {
            border-radius: 5px;
            padding: 10px 15px;
            border: 1px solid #CCCCCC;
            transition: all 0.3s;
            font-family: 'Cantarell', sans-serif;
        }
        .form-control:focus, .form-select:focus {
            border-color: #F28018;
            box-shadow: 0 0 0 0.25rem rgba(242, 128, 24, 0.25);
        }
        .btn {
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 40px;
            transition: all 0.3s;
        }
        .btn-primary {
            background-color: #000000;
            border-color: #000000;
            color: #FFFFFF;
        }
        .btn-primary:hover {
            background-color: #333333;
            border-color: #333333;
        }
        .btn-secondary {
            background-color: #343a40;
            border-color: #343a40;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #23272b;
            border-color: #23272b;
            color: white;
        }
        .btn-qr {
            background-color: #F28018;
            border-color: #F28018;
            color: #000000;
        }
        .btn-qr:hover {
            background-color: #d67015;
            border-color: #d67015;
            color: #000000;
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
        .btn-download-png {
            background-color: #28a745;
            border-color: #28a745;
            color: #FFFFFF;
        }
        .btn-download-png:hover {
            background-color: #218838;
            border-color: #218838;
        }
        .btn-download-pdf {
            background-color: #007bff;
            border-color: #007bff;
            color: #FFFFFF;
        }
        .btn-download-pdf:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .filter-section {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid #CCCCCC;
        }
        .batch-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            border: 2px solid #6610f2;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table thead {
            background-color: #F28018;
            color: #000000;
        }
        .table thead th {
            font-weight: bold;
            padding: 15px;
            border-bottom: none;
            font-family: 'Cantarell', sans-serif;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .table tbody tr:nth-child(even) {
            background-color: rgba(240, 240, 240, 0.5);
        }
        .table tbody tr:hover {
            background-color: rgba(242, 128, 24, 0.1);
        }
        .table td {
            padding: 15px;
            vertical-align: middle;
            border: 1px solid #000000;
            font-family: 'Open Sans', sans-serif;
        }
        .pagination .page-link {
            color: #000000;
            border-color: #F28018;
        }
        .pagination .page-item.active .page-link {
            background-color: #F28018;
            border-color: #F28018;
            color: #000000;
        }
        .pagination .page-link:hover {
            background-color: #343a40;
            color: white;
        }
        .alert {
            border-radius: 10px;
            border-left: 5px solid;
        }
        .alert-success {
            border-left-color: #28a745;
        }
        .alert-danger {
            border-left-color: #dc3545;
        }
        .filter-group {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #dee2e6;
        }
        .results-count {
            background-color: #343a40;
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            border: 2px solid #F28018;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .count-badge {
            background-color: #F28018;
            color: #000000;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: bold;
        }
        .batch-badge {
            background-color: #6610f2;
            color: #ffffff;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: bold;
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
        #qrCode {
            margin: 20px auto;
            text-align: center;
        }
        .progress-container {
            display: none;
            margin-top: 20px;
        }
        @media (max-width: 768px) {
            .container-fluid {
                padding: 10px;
            }
            .btn {
                padding: 8px 16px;
                font-size: 14px;
            }
            .card-header {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="header">
            <h1 class="mb-0">Stock With Serial Number</h1>
            <p class="mb-0">Manage and view your stock inventory</p>
        </div>

        <div id="alertArea">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Batch PDF Generation Section -->
        <div class="batch-section">
            <h5 class="mb-3"><i class="fas fa-file-pdf mr-2"></i>Batch PDF Generation</h5>
            <p class="text-muted">Generate a single PDF file with all serial numbers from the get_serial table.</p>
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="batch-badge">
                        <i class="fas fa-list mr-2"></i><?php echo number_format($get_serial_total); ?> Serial Numbers in Queue
                    </span>
                </div>
                <form method="post" action="" style="margin: 0;">
                    <button type="submit" name="generate_batch_pdfs" class="btn btn-batch" id="batchGenerateBtn">
                        <i class="fas fa-magic mr-2"></i>Generate All PDFs
                    </button>
                </form>
            </div>
            <div class="progress-container" id="progressContainer">
                <div class="progress mb-3">
                    <div class="progress-bar" role="progressbar" id="progressBar" style="width: 0%">0%</div>
                </div>
                <div id="progressText" class="text-center">Preparing to generate PDFs...</div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Stock Inventory</h5>
                    </div>
                    <div class="card-body">
                        <div class="filter-section">
                            <h5 class="mb-3">Filter by Serial Number</h5>
                            <form method="post" action="" id="filterForm">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <div class="filter-group">
                                            <label class="form-label font-weight-bold">Serial Number</label>
                                            <div class="mb-2">
                                                <label class="form-label">Select from list:</label>
                                                <select class="form-control" id="serial_select" name="serial_select">
                                                    <option value="all">All Serial Numbers</option>
                                                    <?php if ($serial_result->num_rows > 0): ?>
                                                        <?php while ($serial_row = $serial_result->fetch_assoc()): ?>
                                                            <option value="<?php echo htmlspecialchars($serial_row['serial_number'] ?? ''); ?>" 
                                                                    <?php echo ($serial_select == ($serial_row['serial_number'] ?? '')) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($serial_row['serial_number'] ?? ''); ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label">Or search:</label>
                                                <input type="text" class="form-control" id="serial_filter" name="serial_filter" 
                                                       placeholder="Enter serial number..." 
                                                       value="<?php echo htmlspecialchars($serial_filter ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <button type="submit" name="filter" class="btn btn-primary">
                                            <i class="fas fa-filter mr-2"></i>Apply Filter
                                        </button>
                                        <button type="submit" name="reset" class="btn btn-secondary ml-2">
                                            <i class="fas fa-undo mr-2"></i>Reset
                                        </button>
                                        <?php if ($qr_data): ?>
                                            <button type="button" class="btn btn-qr ml-2" data-toggle="modal" data-target="#qrModal">
                                                <i class="fas fa-qrcode mr-2"></i>Generate QR Code
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="results-count">
                            <span>Filtered Results</span>
                            <span class="count-badge">
                                <i class="fas fa-tags mr-2"></i> <?php echo number_format($total_items); ?> Items
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped mt-4" id="inventoryTable">
                                <thead>
                                    <tr>
                                        <th>Serial Number</th>
                                        <th>Tyre Code</th>
                                        <th>Brand</th>
                                        <th>Description</th>
                                        <th>DOM</th>
                                        <th>Max Load</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['serial_number'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($row['tyre_code'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($row['brand'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($row['date'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($row['maxload'] ?? ''); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No records found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo ($page - 1); ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo ($page + 1); ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Single QR Modal -->
        <div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="qrModalLabel">QR Code</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="qrCode"></div>
                        <div class="mt-3">
                            <p><strong>Icode:</strong> <?php echo htmlspecialchars($qr_data['tyre_code'] ?? ''); ?></p>
                            <p><strong>SN:</strong> <?php echo htmlspecialchars($qr_data['serial_number'] ?? ''); ?></p>
                            <p><strong>Tire Size & Brand:</strong> <?php echo htmlspecialchars($qr_data['description'] ?? ''); ?></p>
                            <p><strong>DOM:</strong> <?php echo htmlspecialchars($qr_data['date'] ?? ''); ?></p>
                            <p><strong>Max Load:</strong> <?php echo htmlspecialchars($qr_data['maxload'] ?? ''); ?></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-download-png" id="downloadQrCodePng">
                            <i class="fas fa-download mr-2"></i>Download as PNG
                        </button>
                        <button type="button" class="btn btn-download-pdf" id="downloadQrCodePdf">
                            <i class="fas fa-file-pdf mr-2"></i>Download as PDF
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batch Progress Modal -->
        <div class="modal fade" id="batchProgressModal" tabindex="-1" role="dialog" aria-labelledby="batchProgressModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="batchProgressModalLabel">Generating Batch PDFs</h5>
                    </div>
                    <div class="modal-body">
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" id="batchProgressBar" style="width: 0%">0%</div>
                        </div>
                        <div id="batchProgressText" class="text-center">Initializing batch generation...</div>
                        <div id="batchCurrentItem" class="text-muted text-center mt-2"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="batchCloseBtn" data-dismiss="modal" disabled>Close</button>
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

            $('#serial_select').change(function() {
                if ($(this).val() !== 'all') {
                    $('#serial_filter').val('');
                }
            });

            $('#serial_filter').keyup(function() {
                if ($(this).val() !== '') {
                    $('#serial_select').val('all');
                }
            });

            let qrCodeInstance = null;

            // Batch PDF generation data from PHP
            const batchData = <?php echo json_encode($batch_serials); ?>;

            // Handle batch PDF generation
            if (batchData && batchData.length > 0) {
                $('#batchProgressModal').modal('show');
                generateBatchPdfs(batchData);
            }

            function generateBatchPdfs(serialsData) {
                const total = serialsData.length;
                let completed = 0;
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({
                    orientation: 'landscape',
                    unit: 'mm',
                    format: [50, 25] // 50mm x 25mm sticker size
                });

                $('#batchProgressText').text(`Generating PDF with ${total} pages...`);

                function processNext(index) {
                    if (index >= total) {
                        // All pages completed
                        $('#batchProgressText').html('<i class="fas fa-check-circle text-success mr-2"></i>PDF generated successfully!');
                        $('#batchCurrentItem').text(`Completed ${completed} pages`);
                        $('#batchCloseBtn').prop('disabled', false);
                        $('#downloadAllBtn').show();
                        $('#batchProgressBar').removeClass('progress-bar-animated');
                        
                        // Download the combined PDF
                        $('#downloadAllBtn').off('click').on('click', function() {
                            doc.save('batch_qr_codes_' + new Date().getTime() + '.pdf');
                        });
                        return;
                    }

                    const qrData = serialsData[index];
                    $('#batchCurrentItem').text(`Processing: ${qrData.serial_number || 'Unknown'}`);

                    // Generate content for the current page
                    generateSinglePdf(qrData, function() {
                        // After generating the page, add a new page if not the last item
                        if (index < total - 1) {
                            doc.addPage([50, 25], 'landscape');
                        }

                        completed++;
                        const progress = Math.round((completed / total) * 100);
                        
                        $('#batchProgressBar').css('width', progress + '%').text(progress + '%');
                        
                        // Process next page after a short delay
                        setTimeout(() => processNext(index + 1), 300);
                    }, doc); // Pass the same doc to generateSinglePdf
                }

                // Start processing
                processNext(0);
            }

            function generateSinglePdf(qrData, callback, doc) {
                try {
                    // Create temporary QR code
                    const tempDiv = document.createElement('div');
                    tempDiv.style.position = 'absolute';
                    tempDiv.style.left = '-9999px';
                    document.body.appendChild(tempDiv);

                    const qrText = JSON.stringify({
                        'IC': qrData.tyre_code || '',
                        SN: qrData.serial_number || '',
                        'Tire Size & Brand': qrData.description || '',
                        DOM: qrData.date || '',
                        'Max Load': qrData.maxload || '',
                        COO: 'Made in Sri Lanka'
                    });

                    const qrCode = new QRCode(tempDiv, {
                        text: qrText,
                        width: 200,
                        height: 200,
                        colorDark: '#000000',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.H
                    });

                    // Wait for QR code generation
                    setTimeout(() => {
                        const canvas = tempDiv.querySelector('canvas');
                        if (canvas) {
                            const imgData = canvas.toDataURL('image/png');
                            
                            // Add QR code
                            doc.addImage(imgData, 'PNG', 4, 4, 17, 17); // QR code at top-left, 17mm x 17mm

                            // Set font and styling
                            doc.setFont('helvetica');
                            doc.setFontSize(6); // Reduced font size to fit more content
                            const maxWidth = 28; // Max width for text (50mm - 2mm margin - 17mm QR code - 3mm padding)
                            let yPosition = 5; // Starting Y position for text to the right of QR
                            const lineHeight = 2.4; // Line height for spacing

                            // Function to wrap text with bold label and regular value
                            function wrapTextWithBoldLabel(label, value, x, y, maxWidth, lineHeight, valueOnNewLine = false) {
                                const lines = [];
                                let totalHeight = 0;

                                if (valueOnNewLine && value) {
                                    // Special case: Label on first line, value on subsequent lines
                                    lines.push({ text: label, isBold: true });
                                    totalHeight += lineHeight;

                                    // Wrap the value text
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
                                    // Original behavior: combine label and value on same line
                                    const combinedText = value ? `${label} ${value}` : label;
                                    const words = combinedText.split(' ');
                                    let line = '';

                                    // Wrap text
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

                                // Draw each line with proper styling
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

                                                // Draw label in bold
                                                if (labelPart) {
                                                    doc.setFont('helvetica', 'bold');
                                                    doc.text(labelPart, xOffset, y + index * lineHeight);
                                                    xOffset += doc.getTextWidth(labelPart);

                                                    // Add small space after label if there's a value
                                                    if (valuePart) {
                                                        xOffset += 1;
                                                    }
                                                }

                                                // Draw value in normal font
                                                if (valuePart) {
                                                    doc.setFont('helvetica', 'normal');
                                                    doc.text(valuePart, xOffset, y + index * lineHeight);
                                                }
                                            } else {
                                                // This line doesn't contain the label (continuation line)
                                                doc.setFont('helvetica', 'normal');
                                                doc.text(lineObj.text, x, y + index * lineHeight);
                                            }
                                        }
                                    }
                                });

                                return totalHeight; // Return total height used
                            }

                            // Add text with wrapping
                            if (qrData) {
                                // Fields to display to the right of QR code
                                const rightFields = [
                                    { label: 'SN:', value: qrData.serial_number || '', valueOnNewLine: false },
                                    { label: 'Tire Size & Brand:', value: qrData.description || '', valueOnNewLine: true },
                                    { label: 'DOM:', value: qrData.date || '', valueOnNewLine: false },
                                    { label: 'Max Load:', value: qrData.maxload ? `${qrData.maxload}kgs` : 'N/A', valueOnNewLine: false },
                                    { label: 'Made in Sri Lanka', value: '', valueOnNewLine: false }
                                ];

                                // Add fields to the right of QR code
                                rightFields.forEach((field) => {
                                    if (field.label) {
                                        const heightUsed = wrapTextWithBoldLabel(
                                            field.label, 
                                            field.value, 
                                            22, 
                                            yPosition, 
                                            maxWidth, 
                                            lineHeight, 
                                            field.valueOnNewLine
                                        );
                                        yPosition += heightUsed + 0.5; // Add small gap between fields
                                        if (yPosition > 23) {
                                            console.warn('Text overflowed PDF height');
                                            return; // Stop adding text if it exceeds page height
                                        }
                                    }
                                });

                                // Add Tyre Code below the QR code
                                if (qrData.tyre_code) {
                                    const tyreCodeY = 23.3; // Position below QR code (QR is at y=4, height=17, so 4+17+1 = 22)
                                    const tyreCodeMaxWidth = 46; // Full width minus margins (50mm - 4mm margins)
                                    
                                    wrapTextWithBoldLabel(
                                        'IC:', 
                                        qrData.tyre_code, 
                                        6, // Align with left edge of QR code
                                        tyreCodeY, 
                                        tyreCodeMaxWidth, 
                                        lineHeight, 
                                        false
                                    );
                                }
                            }
                            
                            // Clean up temporary element
                            document.body.removeChild(tempDiv);
                            
                            // Call callback
                            if (callback) {
                                callback();
                            }
                        } else {
                            document.body.removeChild(tempDiv);
                            if (callback) {
                                callback();
                            }
                        }
                    }, 200); // Increased timeout to ensure QR code is fully generated

                } catch (error) {
                    console.error('Error generating PDF page:', error);
                    if (callback) {
                        callback();
                    }
                }
            }

            // Single QR code modal functionality
            $('#qrModal').on('shown.bs.modal', function() {
                $('#qrCode').empty();
                const qrData = <?php echo $qr_data ? json_encode($qr_data) : 'null'; ?>;
                if (qrData) {
                    const qrText = JSON.stringify({
                        'IC': qrData.tyre_code || '',
                        SN: qrData.serial_number || '',
                        'Tire Size & Brand': qrData.description || '',
                        DOM: qrData.date || '',
                        'Max Load': qrData.maxload || '',
                        COO: 'Made in Sri Lanka'
                    });
                    qrCodeInstance = new QRCode(document.getElementById('qrCode'), {
                        text: qrText,
                        width: 200,
                        height: 200,
                        colorDark: '#000000',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.H
                    });
                    setTimeout(function() {
                        const canvas = document.querySelector('#qrCode canvas');
                        if (!canvas) {
                            console.error('QR code canvas not found');
                        }
                    }, 100);
                } else {
                    console.error('No QR data available');
                }
            });

            $('#downloadQrCodePng').click(function() {
                const canvas = document.querySelector('#qrCode canvas');
                if (canvas) {
                    const qrData = <?php echo $qr_data ? json_encode($qr_data) : 'null'; ?>;
                    const link = document.createElement('a');
                    link.download = 'qrcode_' + (qrData ? qrData.serial_number.replace(/[^a-zA-Z0-9]/g, '_') : 'stock') + '.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                } else {
                    console.error('QR code canvas not found for PNG download');
                    alert('Error: Unable to generate PNG. Please try again.');
                }
            });

            $('#downloadQrCodePdf').click(function() {
                const canvas = document.querySelector('#qrCode canvas');
                if (canvas) {
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF({
                        orientation: 'landscape',
                        unit: 'mm',
                        format: [50, 25]
                    });
                    const qrData = <?php echo $qr_data ? json_encode($qr_data) : 'null'; ?>;
                    const imgData = canvas.toDataURL('image/png');

                    doc.addImage(imgData, 'PNG', 4, 4, 17, 17);

                    doc.setFont('helvetica');
                    doc.setFontSize(7.5);
                    const maxWidth = 28;
                    let yPosition = 5;
                    const lineHeight = 2.4;

                    function wrapTextWithBoldLabel(label, value, x, y, maxWidth, lineHeight, valueOnNewLine = false) {
                        const lines = [];
                        let totalHeight = 0;

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

                        return totalHeight;
                    }

                    if (qrData) {
                        const rightFields = [
                            { label: 'SN:', value: qrData.serial_number || '', valueOnNewLine: false },
                            { label: 'Tire Size & Brand:', value: qrData.description || '', valueOnNewLine: true },
                            { label: 'DOM:', value: qrData.date || '', valueOnNewLine: false },
                            { label: 'Max Load:', value: qrData.maxload ? `${qrData.maxload}kgs` : 'N/A', valueOnNewLine: false },
                            { label: 'Made in Sri Lanka', value: '', valueOnNewLine: false }
                        ];

                        rightFields.forEach((field) => {
                            if (field.label) {
                                const heightUsed = wrapTextWithBoldLabel(
                                    field.label, 
                                    field.value, 
                                    22, 
                                    yPosition, 
                                    maxWidth, 
                                    lineHeight, 
                                    field.valueOnNewLine
                                );
                                yPosition += heightUsed + 0.5;
                                if (yPosition > 23) {
                                    console.warn('Text overflowed PDF height');
                                    return;
                                }
                            }
                        });

                        if (qrData.tyre_code) {
                            const tyreCodeY = 23.3;
                            const tyreCodeMaxWidth = 46;
                            
                            wrapTextWithBoldLabel(
                                'IC:', 
                                qrData.tyre_code, 
                                6,
                                tyreCodeY, 
                                tyreCodeMaxWidth, 
                                lineHeight, 
                                false
                            );
                        }
                    }

                    doc.save('qrcode_' + (qrData ? qrData.serial_number.replace(/[^a-zA-Z0-9]/g, '_') : 'stock') + '.pdf');
                } else {
                    console.error('QR code canvas not found for PDF download');
                    alert('Error: Unable to generate PDF. Please try again.');
                }
            });

            $('#qrModal').on('hidden.bs.modal', function() {
                $('#qrCode').empty();
                qrCodeInstance = null;
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>