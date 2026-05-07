<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Planned Tire Details Entry">
    <meta name="author" content="Enterprise Development">
    <link rel="shortcut icon" href="assets/custom/images/shortcut.png">
    <title>Planned Tire Details Entry</title>
    
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
            color: white;
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
    </style>
</head>

<body>
    <!-- Header -->
    <header class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="logo-container">
                        <img src="assets/custom/images/ATIRE-logo.png" alt="ATIRE Logo" class="fables-logo">
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
                <li class="breadcrumb-item active" aria-current="page">Planned Tire Details Entry</li>
            </ol>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h2 class="main-title">Planned Tire Details Entry</h2>
                <p class="text-muted mb-4">Enter tire information for inventory management</p>
                
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
                                    <input type="text" id="serialNumber" name="serialNumber" class="form-control" readonly />
                                </div>
                                <small class="text-muted">Auto-generated unique identifier</small>
                            </div>
                            
                            <!-- Press Number (Modified to be selectable first) -->
                            <div class="form-group">
                                <label for="pressNumber" class="form-label">Press Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-compress-alt"></i>
                                    </span>
                                    <select id="pressNumber" name="pressNumber" class="form-select" required>
                                        <option value="">Select Press Number</option>
                                        <!-- Press numbers will be populated via JavaScript -->
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="tireCode" class="form-label">Tire Code</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-code"></i>
                                    </span>
                                    <select id="tireCode" name="tireCode" class="form-select" required disabled>
                                        <option value="">Select Tire Code</option>
                                        <!-- Tire codes will be populated based on selected press number -->
                                    </select>
                                </div>
                                <small class="text-muted">Select a Press Number first to view available tire codes</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="brand" class="form-label">Brand</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-tag"></i>
                                            </span>
                                            <input type="text" id="brand" name="brand" class="form-control" readonly />
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
                                            <input type="text" id="tireWeight" name="tireWeight" class="form-control" readonly />
                                            <span class="input-group-text">kg</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-save me-2"></i>Update
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
                    <p class="mb-0 text-white">&copy; 2025 BI PRO PLAN S All rights reserved</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-white">Tire Inventory System v2.5</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.getElementById('plannedBtn').addEventListener('click', function () {
            window.location.href = 'about1.php';
        });
        
        document.getElementById('unplannedBtn').addEventListener('click', function () {
            window.location.href = 'abt1.php';
        });
        
        // Function to populate Press Numbers
        function populatePressNumbers() {
            fetch('get_pressNumbers.php')
                .then(response => response.json())
                .then(data => {
                    const pressNumberSelect = document.getElementById('pressNumber');
                    pressNumberSelect.innerHTML = '<option value="">Select Press Number</option>';
                    
                    data.pressNumbers.forEach(item => {
                        let option = document.createElement('option');
                        option.value = item.pressNumber;
                        option.textContent = item.pressNumber;
                        pressNumberSelect.appendChild(option);
                    });
                })
                .catch(error => console.error("Error fetching press numbers:", error));
        }
        
        // Function to populate Tire Codes based on Press Number
        function populateTireCodes(pressNumber) {
            fetch(`get_tireCodes.php?pressNumber=${pressNumber}`)
                .then(response => response.json())
                .then(data => {
                    const tireCodeSelect = document.getElementById('tireCode');
                    tireCodeSelect.innerHTML = '<option value="">Select Tire Code</option>';
                    
                    // Enable the select if we have tire codes
                    tireCodeSelect.disabled = data.tireCodes.length === 0;
                    
                    data.tireCodes.forEach(item => {
                        let option = document.createElement('option');
                        option.value = item.code;
                        option.textContent = `${item.code} - ${item.description}`;
                        tireCodeSelect.appendChild(option);
                    });
                    
                    // Show a message if no tire codes are available
                    if (data.tireCodes.length === 0) {
                        tireCodeSelect.innerHTML = '<option value="">No tire codes available for this press</option>';
                    }
                })
                .catch(error => console.error("Error fetching tire codes:", error));
        }
        
        // Function to fetch tire details based on selected tire code
        function fetchTireDetails(tireCode) {
            fetch(`get_tireDetails.php?tireCode=${tireCode}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('brand').value = data.brand;
                    document.getElementById('tireWeight').value = data.tireWeight;
                })
                .catch(error => console.error("Error fetching tire details:", error));
        }
        
        // Event listener for Press Number selection
        document.getElementById('pressNumber').addEventListener('change', function() {
            // Clear tire code and details fields
            document.getElementById('tireCode').innerHTML = '<option value="">Select Tire Code</option>';
            document.getElementById('brand').value = '';
            document.getElementById('tireWeight').value = '';
            
            // Populate tire codes based on selected press number
            if (this.value) {
                populateTireCodes(this.value);
            } else {
                // Disable tire code select if no press number is selected
                document.getElementById('tireCode').disabled = true;
            }
        });
        
        // Event listener for Tire Code selection
        document.getElementById('tireCode').addEventListener('change', function() {
            if (this.value) fetchTireDetails(this.value);
        });
        
        // Serial Number Generation
        function generateSerialNumber() {
            return fetch('generate_serial_number.php')
                .then(response => response.json())
                .then(data => data.serialNumber)
                .catch(error => {
                    console.error("Error fetching serial number:", error);
                    return '';
                });
        }
        
        // Set Serial Number on Page Load and populate press numbers
        document.addEventListener("DOMContentLoaded", function () {
            // Show loading state
            const serialNumberField = document.getElementById('serialNumber');
            serialNumberField.value = "Generating...";
            
            // Populate press numbers
            populatePressNumbers();
            
            // Generate serial number
            generateSerialNumber().then(serialNumber => {
                if (serialNumber) {
                    serialNumberField.value = serialNumber;
                } else {
                    serialNumberField.value = "Failed to generate";
                    alert('Failed to generate serial number. Please refresh the page or contact support.');
                }
            });
            
            // Add form validation
            const form = document.getElementById('tireDetailsForm');
            form.addEventListener('submit', function(event) {
                if (!document.getElementById('pressNumber').value) {
                    event.preventDefault();
                    alert('Please select a Press Number before submitting.');
                }
                
                if (!document.getElementById('tireCode').value) {
                    event.preventDefault();
                    alert('Please select a Tire Code before submitting.');
                }
            });
        });
    </script>
</body>
</html>