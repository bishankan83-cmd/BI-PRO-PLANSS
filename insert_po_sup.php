<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Purchase Order Suppliers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #F28018;
            --secondary-color: #000000;
            --background-color: #f0f2f5;
            --card-background: #FFFFFF;
            --text-dark: #333;
            --text-light: #FFFFFF;
        }

        body {
            background-color: var(--background-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .supplier-container {
            background: var(--card-background);
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 40px;
            margin-top: 50px;
            border: 1px solid rgba(0,0,0,0.1);
        }

        .supplier-header {
            background: linear-gradient(135deg, var(--primary-color), #d4651a);
            color: var(--text-light);
            text-align: center;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            margin: -40px -40px 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .form-control, .form-control:focus {
            background-color: #f9f9f9;
            border: 1px solid rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(242, 128, 24, 0.25);
        }

        .btn-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--text-light);
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #d4651a;
            border-color: #d4651a;
            transform: translateY(-2px);
        }

        .form-label {
            font-weight: 600;
            color: var(--secondary-color);
        }

        @media (max-width: 768px) {
            .supplier-container {
                padding: 20px;
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="supplier-container">
                    <div class="supplier-header">
                        <h2 class="mb-0">Add Purchase Order Suppliers</h2>
                    </div>
                    
                    <?php
                    // Database credentials
                    $host = 'localhost';
                    $username = 'planatir_task_managemen';
                    $password = 'Bishan@1919';
                    $database = 'planatir_task_managemen';

                    // Create connection
                    $conn = new mysqli($host, $username, $password, $database);

                    // Check connection
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Function to sanitize input
                    function sanitize_input($data) {
                        $data = trim($data);
                        $data = stripslashes($data);
                        $data = htmlspecialchars($data);
                        return $data;
                    }

                    // Process form submission
                    $message = '';
                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        // Sanitize and validate inputs
                        $suppliers_code = sanitize_input($_POST['suppliers_code']);
                        $suppliers_name = sanitize_input($_POST['suppliers_name']);
                        $description = sanitize_input($_POST['description']);
                        $production_category = sanitize_input($_POST['production_category']);
                        $contact_person = sanitize_input($_POST['contact_person']);
                        $contact_no = sanitize_input($_POST['contact_no']);
                        $address = sanitize_input($_POST['address']);

                        // Validate inputs (add more validation as needed)
                        $errors = [];
                        if (empty($suppliers_code)) $errors[] = "Supplier Code is required";
                        if (empty($suppliers_name)) $errors[] = "Supplier Name is required";
                        if (empty($contact_no)) $errors[] = "Contact Number is required";

                        if (empty($errors)) {
                            // Prepared statement to avoid SQL injection
                            $stmt = $conn->prepare("INSERT INTO po_suppliers (suppliers_code, suppliers_name, description, production_category, contact_person, contact_no, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("sssssss", $suppliers_code, $suppliers_name, $description, $production_category, $contact_person, $contact_no, $address);

                            // Execute statement
                            if ($stmt->execute()) {
                                $message = '<div class="alert alert-success">Supplier added successfully!</div>';
                            } else {
                                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
                            }

                            // Close statement
                            $stmt->close();
                        } else {
                            // Display validation errors
                            $message = '<div class="alert alert-danger">' . implode('<br>', $errors) . '</div>';
                        }
                    }

                    // Close connection
                    $conn->close();
                    ?>

                    <?php echo $message; ?>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="suppliers_code" class="form-label">Supplier Code</label>
                                <input type="text" class="form-control" id="suppliers_code" name="suppliers_code" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="suppliers_name" class="form-label">Supplier Name</label>
                                <input type="text" class="form-control" id="suppliers_name" name="suppliers_name" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="production_category" class="form-label">Production Category</label>
                                <input type="text" class="form-control" id="production_category" name="production_category">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="contact_person" class="form-label">Contact Person</label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contact_no" class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" id="contact_no" name="contact_no" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-custom btn-lg px-5">Submit Supplier Information</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Optional: Add some client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            let contactNo = document.getElementById('contact_no');
            let supplierCode = document.getElementById('suppliers_code');

            // Simple phone number validation
            let phoneRegex = /^[0-9]{10}$/;
            if (!phoneRegex.test(contactNo.value)) {
                e.preventDefault();
                alert('Please enter a valid 10-digit phone number');
                contactNo.focus();
            }

            // Validate supplier code (example: must be alphanumeric)
            let codeRegex = /^[A-Za-z0-9]+$/;
            if (!codeRegex.test(supplierCode.value)) {
                e.preventDefault();
                alert('Supplier Code must be alphanumeric');
                supplierCode.focus();
            }
        });
    </script>
</body>
</html>