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

// Handle batch PDF generation
$batch_serials = [];
$should_generate_batch = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_batch_pdfs'])) {
    $should_generate_batch = true;
    
    $batch_sql = "SELECT 
                    gs.serial_number,
                    se.tyre_code,
                    se.description,
                    td.brand,
                    td.maxload
                  FROM get_serial gs
                  LEFT JOIN stock_erp se ON gs.serial_number = se.serial_number
                  LEFT JOIN tire_details td ON se.tyre_code = td.icode
                  ORDER BY gs.id";
    
    $batch_result = $conn->query($batch_sql);
    
    if ($batch_result && $batch_result->num_rows > 0) {
        while ($batch_row = $batch_result->fetch_assoc()) {
            $batch_serials[] = [
                'serial_number' => $batch_row['serial_number'],
                'tyre_code' => $batch_row['tyre_code'],
                'brand' => $batch_row['brand'],
                'description' => $batch_row['description'],
                'maxload' => $batch_row['maxload']
            ];
        }
    }
}

// Count items in get_serial table
$get_serial_count_sql = "SELECT COUNT(*) as total FROM get_serial";
$get_serial_count_result = $conn->query($get_serial_count_sql);
$get_serial_count_row = $get_serial_count_result->fetch_assoc();
$get_serial_total = $get_serial_count_row['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management - PDF Generation</title>
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
        .modal-content {
            border-radius: 15px;
            border: 2px solid #F28018;
        }
        .modal-header {
            background-color: #343a40;
            color: white;
            border-bottom: 2px solid #F28018;
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
            <p class="mb-0">Generate batch PDFs from get_serial and stock_erp database</p>
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
                        <span><i class="fas fa-database mr-2"></i>Total Serial Numbers (get_serial):</span>
                        <span class="batch-badge"><?php echo number_format($get_serial_total); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batch PDF Generation Section -->
        <div class="batch-section">
            <h5 class="mb-3"><i class="fas fa-file-pdf mr-2"></i>Batch PDF Generation</h5>
            <p class="text-muted">Generate a single PDF file with all serial numbers from get_serial table. All other information (tyre_code, description) will be retrieved from stock_erp table.</p>
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

            const shouldGenerateBatch = <?php echo $should_generate_batch ? 'true' : 'false'; ?>;
            const batchData = <?php echo json_encode($batch_serials); ?>;

            if (shouldGenerateBatch && batchData && batchData.length > 0) {
                console.log('Starting batch generation with', batchData.length, 'records');
                $('#batchProgressModal').modal('show');
                generateBatchPdfs(batchData);
            } else if (shouldGenerateBatch && (!batchData || batchData.length === 0)) {
                alert('No serial numbers found in get_serial table to generate PDFs.');
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
                        $('#batchProgressText').html('<i class="fas fa-check-circle text-success mr-2"></i>PDF generated successfully!');
                        $('#batchCurrentItem').text(`Completed ${completed} pages`);
                        
                        $('#batchProgressBar').removeClass('progress-bar-animated');
                        $('#batchCloseBtn').prop('disabled', false);
                        $('#downloadAllBtn').show();
                        
                        $('#downloadAllBtn').off('click').on('click', function() {
                            doc.save('batch_qr_codes_' + new Date().getTime() + '.pdf');
                            setTimeout(function() {
                                window.location.href = window.location.pathname + '?success=' + encodeURIComponent('PDFs generated successfully!');
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

                    // QR code contains InventoryID, TB, and LotSerialNbr
                    const qrText = JSON.stringify({
                        'InventoryID': qrData.tyre_code || '',
                        'LotSerialNbr': qrData.serial_number || '',
                        'TB': qrData.description || ''
                    });

                    const qrCode = new QRCode(tempDiv, {
                        text: qrText,
                        width: 180,
                        height: 180,
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
                            
                            // Add QR code - smaller size (18mm instead of 22mm)
                            doc.addImage(qrImgData, 'PNG', 1.8, 1.2, 17, 13.8);
                            
                            // Display "Tire Code" label at the top center of the box
                            doc.setFont('helvetica');
                            doc.setFontSize(7);
                            doc.setTextColor(0, 0, 0);
                            
                            const labelText = 'Tire Code';
                            const labelWidth = doc.getTextWidth(labelText);
                            const boxCenterX = 2 + (11.9 / 2);
                            const labelX = boxCenterX - (labelWidth / 2);
                            
                            doc.text(labelText, labelX, 18);
                            
                            // Display tyre_code value centered below the label
                            doc.setFont('helvetica', 'bold');
                            doc.setFontSize(9.8);
                            
                            const icValue = qrData.tyre_code || '';
                            const boxWidth = 16;
                            const maxValueWidth = boxWidth - 1;
                            let displayValue = icValue;
                            
                            // Truncate text if it exceeds max width
                            let valueWidth = doc.getTextWidth(displayValue);
                            while (valueWidth > maxValueWidth && displayValue.length > 0) {
                                displayValue = displayValue.slice(0, -1);
                                valueWidth = doc.getTextWidth(displayValue);
                            }
                            
                            // Center the tyre_code value in the box
                            const valueX = boxCenterX - (doc.getTextWidth(displayValue) / 2);
                            
                            doc.text(displayValue, valueX, 21.6);
                            
                            // Reset font for other fields
                            doc.setFont('helvetica', 'normal');
                            doc.setFontSize(6.5);
                            
                            // Right side content - Serial Number and other details
                            let yPosition = 3.7;
                            const lineHeight = 2.2;
                            const maxWidth = 28;

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
                                doc.text(qrData.serial_number || '', 20, yPosition);
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
                                    { label: 'ML:', value: maxloadDisplay, valueOnNewLine: false, fontSize: 6 },
                                    { label: '', value: 'MADE IN SRI LANKA', valueOnNewLine: false, fontSize: 6.5 }
                                ];

                                rightFields.forEach((field) => {
                                    if (field.label !== undefined) {
                                        const heightUsed = wrapTextWithBoldLabel(
                                            field.label, 
                                            field.value, 
                                            20, 
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