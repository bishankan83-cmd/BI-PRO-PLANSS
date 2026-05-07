<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Tire Details Entry">
    <meta name="author" content="Enterprise Development">
    <link rel="shortcut icon" href="assets/custom/images/shortcut.png">
    <title>UnPlanned Tire Details Entry</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cantarell:wght@400;700&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Custom Variables Based on Provided Color Scheme */
        :root {
            --primary: #F28018;
            --dark: #000000;
            --light-gray: #f0f0f0;
            --border-color: #CCCCCC;
            --hover-dark: #333333;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: var(--light-gray);
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
        }
        
        /* Header Styling */
        .page-header {
            background-color: var(--primary);
            color: var(--dark);
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .logo-container {
            display: flex;
            align-items: center;
        }
        
        .fables-logo {
            height: 50px;
            margin-right: 15px;
        }
        
        .company-name {
            font-family: 'Cantarell', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
            color: var(--dark);
        }
        
        /* Breadcrumb Styling */
        .breadcrumb-wrapper {
            background-color: white;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        
        .breadcrumb-item {
            font-family: 'Cantarell', sans-serif;
        }
        
        .breadcrumb-item a {
            color: var(--primary);
            text-decoration: none;
        }
        
        /* Card Styling */
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 2rem;
            background-color: white;
        }
        
        .card-header {
            background-color: var(--primary);
            color: var(--dark);
            border-bottom: none;
            padding: 1.25rem 1.5rem;
            font-family: 'Cantarell', sans-serif;
            font-weight: 700;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Button Styling */
        .btn-type {
            background-color: var(--dark);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-family: 'Cantarell', sans-serif;
            margin: 0 0.5rem;
        }
        
        .btn-type:hover {
            background-color: var(--hover-dark);
        }
        
        .btn-submit {
            background-color: var(--dark);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-family: 'Cantarell', sans-serif;
            width: 100%;
            font-weight: 600;
            margin-top: 1rem;
        }
        
        .btn-submit:hover {
            background-color: var(--hover-dark);
        }
        
        /* Form Styling */
        .form-label {
            font-family: 'Cantarell', sans-serif;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-family: 'Open Sans', sans-serif;
            transition: border-color 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(242, 128, 24, 0.25);
        }
        
        .form-control[readonly] {
            background-color: #e9ecef;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        /* Table Styling */
        .stockr-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .stockr-table th {
            background-color: var(--primary);
            color: var(--dark);
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 15px;
            text-align: left;
        }
        
        .stockr-table td {
            border: 1px solid var(--dark);
            padding: 10px;
            text-align: left;
            font-family: 'Open Sans', sans-serif;
            padding-top: 30px;
        }
        
        /* Main Title */
        .main-title {
            font-family: 'Cantarell', sans-serif;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            position: relative;
            display: inline-block;
        }
        
        .main-title::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary);
        }
        
        /* Footer */
        footer {
            background-color: var(--dark);
            color:rgb(85, 46, 10);
            padding: 1.5rem 0;
            margin-top: 2rem;
            font-family: 'Open Sans', sans-serif;
        }
        
        /* Input Group Styling */
        .input-group-text {
            background-color: #f8f9fa;
            color: var(--primary);
            border-color: var(--border-color);
        }
        
        /* Optional field styling */
        .field-optional::after {
            content: " (Optional)";
            font-weight: normal;
            font-size: 0.85em;
            opacity: 0.7;
        }
    </style>
</head>

<body>
    <?php
    require_once 'db_connect.php';
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Fetch tire codes for dropdown
    $tireCodes = [];
    $query = "SELECT icode, Description FROM tire_details";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $tireCodes[] = $row;
        }
    }

    // Fetch press numbers for dropdown
    $pressNumbers = [];
    $query = "SELECT press_name FROM press WHERE is_available = 1";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $pressNumbers[] = $row['press_name'];
        }
    }
    ?>

    <!-- Header -->
    <header class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="logo-container">
                        <img src="assets/custom/images/ATIRE-logo.png" alt="ATIRE Logo" class="fables-logo">
                        <h1 class="company-name"></h1>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <h2 class="fs-4 fw-bold">Tire Production QR Process</h2>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Breadcrumb -->
    <div class="container breadcrumb-wrapper">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="#">Inventory</a></li>
                <li class="breadcrumb-item active" aria-current="page">UnPlanned Tire Details Entry</li>
            </ol>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h2 class="main-title">UnPlanned Tire Details Entry</h2>
                <p class="text-muted mb-4">Enter tire information for inventory management</p>
                
                <!-- Entry Type Card -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Entry Type</span>
                        <div class="button-container">
                            <button id="plannedBtn" class="btn-type">
                                <i class="fas fa-calendar-check me-2"></i>Planned
                            </button>
                            <button id="unplannedBtn" class="btn-type">
                                <i class="fas fa-exclamation-circle me-2"></i>Unplanned
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Tire Information Card -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-tire fa-fw me-2"></i>Tire Information
                    </div>
                    <div class="card-body">
                        <form id="tireDetailsForm" action="save_tire_data.php" method="POST">
                            <div class="form-group">
                                <label for="serialNumber" class="form-label">Serial Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-barcode"></i>
                                    </span>
                                    <input type="text" id="serialNumber" name="serialNumber" class="form-control" readonly required />
                                </div>
                                <small class="text-muted">Auto-generated unique identifier</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="tireCode" class="form-label">Tire Code</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-code"></i>
                                    </span>
                                    <select id="tireCode" name="tireCode" class="form-select" required>
                                        <option value="">Select Tire Code</option>
                                        <?php foreach ($tireCodes as $tire): ?>
                                            <option value="<?php echo htmlspecialchars($tire['icode']); ?>">
                                                <?php echo htmlspecialchars($tire['icode'] . ' - ' . $tire['Description']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="brand" class="form-label">Brand</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-tag"></i>
                                            </span>
                                            <input type="text" id="brand" name="brand" class="form-control" readonly required />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tireWeight" class="form-label">Tire Weight</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-weight-hanging"></i>
                                            </span>
                                            <input type="text" id="tireWeight" name="tireWeight" class="form-control" readonly required />
                                            <span class="input-group-text">kg</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="pressNumber" class="form-label" id="pressNumberLabel">Press Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-compress-alt"></i>
                                    </span>
                                    <select id="pressNumber" name="pressNumber" class="form-select" required>
                                        <option value="">Select Press Number</option>
                                        <?php foreach ($pressNumbers as $press): ?>
                                            <option value="<?php echo htmlspecialchars($press); ?>">
                                                <?php echo htmlspecialchars($press); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <small id="pressNumberHelp" class="form-text text-muted d-none">Press Number is optional for Tire Code 00000</small>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-save me-2"></i>Submit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2025 BI PRO PLAN S All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Tire Inventory System v2.5</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Planned and Unplanned buttons
        document.getElementById('plannedBtn').addEventListener('click', function () {
            window.location.href = 'about1.php';
        });
        
        document.getElementById('unplannedBtn').addEventListener('click', function () {
            window.location.href = 'abt1.php';
        });
        
        // Generate serial number on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Show loading state
            const serialNumberField = document.getElementById('serialNumber');
            serialNumberField.value = "Generating...";
            
            fetch('generate_serial_number.php')
                .then(response => response.json())
                .then(data => {
                    if (data.serialNumber) {
                        serialNumberField.value = data.serialNumber;
                    } else {
                        serialNumberField.value = "Failed to generate";
                        alert('Failed to generate serial number. Please refresh the page or contact support.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    serialNumberField.value = "Failed to generate";
                    alert('Failed to generate serial number. Please refresh the page or contact support.');
                });
        });

        // Fetch tire details when tire code is selected and handle Press Number requirement
        document.getElementById('tireCode').addEventListener('change', function() {
            const tireCode = this.value;
            const pressNumberField = document.getElementById('pressNumber');
            const pressNumberLabel = document.getElementById('pressNumberLabel');
            const pressNumberHelp = document.getElementById('pressNumberHelp');
            
            if (tireCode) {
                fetch(`get_tireDetails1.php?tireCode=${encodeURIComponent(tireCode)}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('brand').value = data.brand || '';
                        document.getElementById('tireWeight').value = data.tireWeight || '';
                        
                        // Check if tire code is 00000 and update Press Number field requirement
                        if (tireCode === '00000') {
                            pressNumberField.removeAttribute('required');
                            pressNumberLabel.classList.add('field-optional');
                            pressNumberHelp.classList.remove('d-none');
                        } else {
                            pressNumberField.setAttribute('required', 'required');
                            pressNumberLabel.classList.remove('field-optional');
                            pressNumberHelp.classList.add('d-none');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
        
        // Add form validation
        const form = document.getElementById('tireDetailsForm');
        form.addEventListener('submit', function(event) {
            const tireCode = document.getElementById('tireCode').value;
            const pressNumber = document.getElementById('pressNumber').value;
            
            if (!tireCode) {
                event.preventDefault();
                alert('Please select a Tire Code before submitting.');
                return;
            }
            
            // Only validate Press Number as required if tire code is not 00000
            if (tireCode !== '00000' && !pressNumber) {
                event.preventDefault();
                alert('Please select a Press Number before submitting.');
                return;
            }
        });
    </script>
</body>
</html>