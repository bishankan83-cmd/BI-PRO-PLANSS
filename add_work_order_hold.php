<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hold Work Orders</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            text-align: center;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn {
            margin: 10px;
            width: 200px;
            padding: 12px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Manage Hold Work Orders</h1>
        
        <div class="row">
            <div class="col-12">
                <button onclick="window.location.href='add_work_hold.php'" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add Order
                </button>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <button onclick="window.location.href='delete_hold_worder.php'" class="btn btn-danger">
                    <i class="bi bi-trash me-2"></i>Delete Order
                </button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>