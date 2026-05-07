<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Inward Suppliers Management</title>
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
        // Sanitize inputs
        $suppliers_code = sanitize_input($_POST['suppliers_code']);
        $suppliers_name = sanitize_input($_POST['suppliers_name']);
        $description = sanitize_input($_POST['description']);

        // Validate inputs
        $errors = [];
        if (empty($suppliers_code)) $errors[] = "Supplier Code is required";
        if (empty($suppliers_name)) $errors[] = "Supplier Name is required";
        if (empty($description)) $errors[] = "Description is required";

        if (empty($errors)) {
            // Prepared statement to prevent SQL injection
            $stmt = $conn->prepare("INSERT INTO loan_inward_suppliers (suppliers_code, suppliers_name, description) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $suppliers_code, $suppliers_name, $description);

            // Execute statement
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">New record created successfully in Loan Inward Suppliers table</div>';
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

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="supplier-container">
                    <div class="supplier-header">
                        <h2 class="mb-0">Loan Inward Suppliers Management</h2>
                    </div>
                    
                    <?php echo $message; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="suppliers_code" class="form-label">Supplier Code</label>
                            <input type="text" class="form-control" id="suppliers_code" name="suppliers_code" required>
                        </div>

                        <div class="mb-3">
                            <label for="suppliers_name" class="form-label">Supplier Name</label>
                            <input type="text" class="form-control" id="suppliers_name" name="suppliers_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
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
        // Client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            let suppliersCode = document.getElementById('suppliers_code');
            let suppliersName = document.getElementById('suppliers_name');
            let description = document.getElementById('description');

            // Validate supplier code (alphanumeric)
            let codeRegex = /^[A-Za-z0-9]+$/;
            if (!codeRegex.test(suppliersCode.value)) {
                e.preventDefault();
                alert('Supplier Code must be alphanumeric');
                suppliersCode.focus();
                return;
            }

            // Validate supplier name (letters and spaces)
            let nameRegex = /^[A-Za-z\s]+$/;
            if (!nameRegex.test(suppliersName.value)) {
                e.preventDefault();
                alert('Supplier Name must contain only letters and spaces');
                suppliersName.focus();
                return;
            }

            // Validate description length
            if (description.value.trim().length < 10) {
                e.preventDefault();
                alert('Description must be at least 10 characters long');
                description.focus();
            }
        });
    </script>
</body>
</html>