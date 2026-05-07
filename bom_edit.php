
<!-- Add this code inside the .card-header div -->
<a href="qad.php" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back to Dashboard
</a>


<?php
// For PHP applications
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection configuration
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

// Function to sanitize input data
function sanitize($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Insert operation
    if (isset($_POST['insert'])) {
        $item = sanitize($conn, $_POST['Item']);
        $icode = sanitize($conn, $_POST['icode']);
        $t_size = sanitize($conn, $_POST['t_size']);
        $a = sanitize($conn, $_POST['a']);
        $b = sanitize($conn, $_POST['b']);
        $c = sanitize($conn, $_POST['c']);
        $d = sanitize($conn, $_POST['d']);
        $e = sanitize($conn, $_POST['e']);
        $f = sanitize($conn, $_POST['f']);
        $g = sanitize($conn, $_POST['g']);
        $h = sanitize($conn, $_POST['h']);
        $i = sanitize($conn, $_POST['i']);
        $j = sanitize($conn, $_POST['j']);
        $k = sanitize($conn, $_POST['k']);
        $l = sanitize($conn, $_POST['l']);
        $m = sanitize($conn, $_POST['m']);
        $n = sanitize($conn, $_POST['n']);
        $o = sanitize($conn, $_POST['o']);
        $p = sanitize($conn, $_POST['p']);
        $q = sanitize($conn, $_POST['q']);
        $r = sanitize($conn, $_POST['r']);
        $grandTotal = sanitize($conn, $_POST['grand_total']);
        $color = sanitize($conn, $_POST['color']);
        $brand = sanitize($conn, $_POST['brand']);
        $greenTireWeight = sanitize($conn, $_POST['green_tire_weight']);
        $pbweight = sanitize($conn, $_POST['pbweight']);
        
        $sql = "INSERT INTO bom_new (Item, icode, t_size, a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p, q, r, 
                `Grand Totalcompound weight`, Color, Brand, `Green Tire weight`, PBweight) 
                VALUES ('$item', '$icode', '$t_size', '$a', '$b', '$c', '$d', '$e', '$f', '$g', '$h', '$i', '$j', 
                '$k', '$l', '$m', '$n', '$o', '$p', '$q', '$r', '$grandTotal', '$color', '$brand', '$greenTireWeight', '$pbweight')";
        
        if ($conn->query($sql) === TRUE) {
            $message = "New record created successfully";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    
    // Update operation
    if (isset($_POST['update'])) {
        $id = sanitize($conn, $_POST['id']);
        $item = sanitize($conn, $_POST['Item']);
        $icode = sanitize($conn, $_POST['icode']);
        $t_size = sanitize($conn, $_POST['t_size']);
        $a = sanitize($conn, $_POST['a']);
        $b = sanitize($conn, $_POST['b']);
        $c = sanitize($conn, $_POST['c']);
        $d = sanitize($conn, $_POST['d']);
        $e = sanitize($conn, $_POST['e']);
        $f = sanitize($conn, $_POST['f']);
        $g = sanitize($conn, $_POST['g']);
        $h = sanitize($conn, $_POST['h']);
        $i = sanitize($conn, $_POST['i']);
        $j = sanitize($conn, $_POST['j']);
        $k = sanitize($conn, $_POST['k']);
        $l = sanitize($conn, $_POST['l']);
        $m = sanitize($conn, $_POST['m']);
        $n = sanitize($conn, $_POST['n']);
        $o = sanitize($conn, $_POST['o']);
        $p = sanitize($conn, $_POST['p']);
        $q = sanitize($conn, $_POST['q']);
        $r = sanitize($conn, $_POST['r']);
        $grandTotal = sanitize($conn, $_POST['grand_total']);
        $color = sanitize($conn, $_POST['color']);
        $brand = sanitize($conn, $_POST['brand']);
        $greenTireWeight = sanitize($conn, $_POST['green_tire_weight']);
        $pbweight = sanitize($conn, $_POST['pbweight']);
        
        $sql = "UPDATE bom_new SET 
                Item = '$item', 
                icode = '$icode', 
                t_size = '$t_size', 
                a = '$a', 
                b = '$b', 
                c = '$c', 
                d = '$d', 
                e = '$e', 
                f = '$f', 
                g = '$g', 
                h = '$h', 
                i = '$i', 
                j = '$j', 
                k = '$k', 
                l = '$l', 
                m = '$m', 
                n = '$n', 
                o = '$o', 
                p = '$p', 
                q = '$q', 
                r = '$r', 
                `Grand Totalcompound weight` = '$grandTotal', 
                Color = '$color', 
                Brand = '$brand', 
                `Green Tire weight` = '$greenTireWeight', 
                PBweight = '$pbweight' 
                WHERE id = $id";
        
        if ($conn->query($sql) === TRUE) {
            $message = "Record updated successfully";
        } else {
            $error = "Error updating record: " . $conn->error;
        }
    }
    
    // Delete operation
    if (isset($_POST['delete'])) {
        $id = sanitize($conn, $_POST['id']);
        
        $sql = "DELETE FROM bom_new WHERE id = $id";
        
        if ($conn->query($sql) === TRUE) {
            $message = "Record deleted successfully";
        } else {
            $error = "Error deleting record: " . $conn->error;
        }
    }
}

// Search functionality
$search = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';
$searchCondition = '';
if (!empty($search)) {
    $searchCondition = " WHERE 
        Item LIKE '%$search%' OR 
        icode LIKE '%$search%' OR 
        t_size LIKE '%$search%' OR 
        Color LIKE '%$search%' OR 
        Brand LIKE '%$search%' OR 
        `Grand Totalcompound weight` LIKE '%$search%' OR
        `Green Tire weight` LIKE '%$search%' OR
        PBweight LIKE '%$search%'";
}

// Retrieve record for editing if ID is provided in the URL
$editData = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = sanitize($conn, $_GET['edit']);
    $result = $conn->query("SELECT * FROM bom_new WHERE id = $id");
    if ($result->num_rows > 0) {
        $editData = $result->fetch_assoc();
    }
}

// Pagination
$recordsPerPage = 10; // Changed from 50 to 10 as requested
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $recordsPerPage;

// Count total records with search condition if applicable
$totalRecordsQuery = "SELECT COUNT(*) as count FROM bom_new" . $searchCondition;
$totalRecords = $conn->query($totalRecordsQuery)->fetch_assoc()['count'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get paginated data with search condition if applicable
$paginatedQuery = "SELECT * FROM bom_new" . $searchCondition . " ORDER BY id DESC LIMIT $offset, $recordsPerPage";
$paginatedResult = $conn->query($paginatedQuery);

// Retrieve item codes and sizes for select options
$itemCodes = array();
$sizes = array();

$result = $conn->query("SELECT DISTINCT icode FROM bom_new");
while ($row = $result->fetch_assoc()) {
    $itemCodes[] = $row['icode'];
}

$result = $conn->query("SELECT DISTINCT t_size FROM bom_new");
while ($row = $result->fetch_assoc()) {
    $sizes[] = $row['t_size'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>BOM Management System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
    --primary-color: #F28018;
    --secondary-color:rgb(0, 4, 7);
    --accent-color: #1abc9c;
    --success-color: #2ecc71;
    --warning-color: #f39c12;
    --danger-color: #e74c3c;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
    --gray-color: #6c757d;
    --body-bg: #f4f6f9;
    --border-color: #f0f0f0;
    --text-color: #000000;
    --background-color: #CCCCCC;
    --white-color: #FFFFFF;
    --dark-text-color: #333333;
    --orange-color: #F28018;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: var(--body-bg);
    color: var(--text-color);
    line-height: 1.6;
}

.container {
    width: 95%;
    max-width: 1600px;
    margin: 0 auto;
    padding: 20px 0;
}

header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 25px 0;
    margin-bottom: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
}

h1 {
    text-align: center;
    font-size: 2.2rem;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    margin-bottom: 25px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.card-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 18px 25px;
    font-size: 1.2rem;
    font-weight: 500;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-body {
    padding: 25px;
}

.toggle-form {
    background-color: #F28018;
    border: none;
    color: white;
    padding: 10px 18px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.toggle-form:hover {
    background-color: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

.form-container {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.5s ease;
}

.form-container.show {
    max-height: 5000px;
}

.grid-form {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
}

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--text-color);
    font-size: 0.95rem;
}

input[type="text"],
input[type="number"],
select {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    font-size: 0.95rem;
    transition: all 0.3s;
    background-color: var(--background-color);
}

input[type="text"]:focus,
input[type="number"]:focus,
select:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
    background-color: var(--white-color);
}

.form-buttons {
    margin-top: 25px;
    display: flex;
    gap: 15px;
}

.btn {
    padding: 12px 22px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 0.95rem;
    letter-spacing: 0.3px;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-success {
    background: linear-gradient(135deg, var(--success-color), #27ae60);
    color: white;
}

.btn-success:hover {
    background: linear-gradient(135deg, #27ae60, var(--success-color));
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-danger {
    background: linear-gradient(135deg, var(--danger-color), #c0392b);
    color: white;
}

.btn-danger:hover {
    background: linear-gradient(135deg, #c0392b, var(--danger-color));
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-secondary {
    background: linear-gradient(135deg, var(--gray-color), #5a6268);
    color: white;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268, var(--gray-color));
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.table-responsive {
    overflow-x: auto;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
}

table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 10px;
    overflow: hidden;
    background-color: var(--white-color);
    margin-bottom: 20px;
}

th, td {
    padding: 15px 18px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

thead {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
}

thead th {
    font-weight: 500;
    letter-spacing: 0.5px;
}

tr:nth-child(even) {
    background-color: #f8fafc;
}

tr:hover {
    background-color: #f1f7fe;
}

.action-column {
    white-space: nowrap;
    width: 180px;
}

.alert {
    padding: 18px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    border: 1px solid transparent;
    font-weight: 500;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert i {
    font-size: 1.2rem;
}

.alert-success {
    background-color: #e8f8f0;
    border-color: #c5e9d8;
    color: var(--dark-text-color);
}

.alert-danger {
    background-color: #fef1f0;
    border-color: #fcd9d7;
    color: var(--dark-text-color);
}

.pagination {
    display: flex;
    justify-content: center;
    list-style: none;
    margin: 30px 0 10px;
    padding: 0;
    flex-wrap: wrap;
    gap: 8px;
}

.pagination li {
    margin: 0;
}

.pagination a {
    display: block;
    padding: 10px 16px;
    background-color: var(--white-color);
    border: 1px solid var(--border-color);
    color: var(--primary-color);
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s;
    font-weight: 500;
}

.pagination a:hover {
    background-color: #f1f7fe;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}

.pagination .active a {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-color: var(--primary-color);
}

.footer {
    text-align: center;
    padding: 25px 0;
    margin-top: 40px;
    color: var(--text-color);
    font-size: 0.95rem;
    border-top: 1px solid var(--border-color);
}

.table-actions {
    display: flex;
    gap: 10px;
}

.icon-button {
    background-color: transparent;
    border: none;
    cursor: pointer;
    font-size: 18px;
    transition: all 0.3s;
    padding: 5px;
    border-radius: 50%;
}

.edit-btn {
    color: var(--primary-color);
}

.edit-btn:hover {
    color: var(--secondary-color);
    background-color: rgba(52, 152, 219, 0.1);
}

.delete-btn {
    color: var(--danger-color);
}

.delete-btn:hover {
    color: #c0392b;
    background-color: rgba(231, 76, 60, 0.1);
}

.search-container {
    margin-bottom: 25px;
    display: flex;
    gap: 15px;
}

.search-box {
    flex-grow: 1;
    display: flex;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    border-radius: 8px;
    overflow: hidden;
}

.search-box input {
    flex-grow: 1;
    border: 1px solid var(--border-color);
    border-right: none;
    border-radius: 8px 0 0 8px;
    padding: 14px 18px;
    font-size: 1rem;
}

.search-box input:focus {
    outline: none;
    border-color: var(--primary-color);
}

.search-box button {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 0 8px 8px 0;
    padding: 0 25px;
    cursor: pointer;
    font-size: 1.1rem;
    transition: all 0.3s;
}

.search-box button:hover {
    background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
}

.data-count {
    margin-bottom: 20px;
    font-size: 0.95rem;
    color: var(--text-color);
    display: flex;
    align-items: center;
    gap: 8px;
}

.data-count i {
    color: var(--primary-color);
}

.required-field {
    color: var(--danger-color);
    margin-left: 3px;
}

.card-header-icon {
    margin-right: 10px;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
    font-size: 0.95rem;
}

.breadcrumb a {
    color: var(--primary-color);
    text-decoration: none;
}

.breadcrumb i {
    color: #999;
    font-size: 0.8rem;
}

@media (max-width: 768px) {
    .grid-form {
        grid-template-columns: 1fr;
    }
    
    .card-header {
        flex-direction: column;
        gap: 10px;
        padding: 15px;
    }
    
    .toggle-form {
        width: 100%;
    }
    
    .action-column {
        width: auto;
    }
    
    .table-actions {
        flex-direction: column;
        gap: 8px;
    }
    
    .btn {
        padding: 10px 15px;
        font-size: 0.9rem;
    }
    
    th, td {
        padding: 12px 10px;
        font-size: 0.9rem;
    }
    
    h1 {
        font-size: 1.8rem;
    }
}

    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><i class="fas fa-boxes card-header-icon"></i> BOM Management System</h1>
        </div>
    </header>
    
    <div class="container">
        <?php if(isset($message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="breadcrumb">
            <a href="#"><i class="fas fa-home"></i> Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>BOM Management</span>
        </div>
        
        <!-- Form Card -->
        <div class="card">
            <div class="card-header">
                <h2>
                    <i class="fas <?php echo $editData ? 'fa-edit' : 'fa-plus'; ?> card-header-icon"></i>
                    <?php echo $editData ? 'Edit Record' : 'Add New Record'; ?>
                </h2>
                <?php if(!$editData): ?>
                <button class="toggle-form" id="toggleFormBtn">
                    <i class="fas fa-chevron-down"></i> <?php echo $editData ? 'Edit Form' : 'Show/Hide Form'; ?>
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="form-container <?php echo $editData ? 'show' : ''; ?>" id="formContainer">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <?php if($editData): ?>
                            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="grid-form">
                            <div class="form-group">
                                <label for="Item">Item</label>
                                <input type="text" name="Item" id="Item" value="<?php echo $editData ? $editData['Item'] : ''; ?>">
                            </div>
                            
                        </div>

                            <div class="grid-form">
                            <div class="form-group">
                                <label for="icode">Item code</label>
                                <input type="text" name="icode" id="icode" value="<?php echo $editData ? $editData['icode'] : ''; ?>">
                            </div>
                            
                            
                                    </div>
    <div class="grid-form">
                            <div class="form-group">
                                <label for="t_size">Size</label>
                                <input type="text" name="t_size" id="t_size" value="<?php echo $editData ? $editData['t_size'] : ''; ?>">
                            </div>
                            

                            <div class="form-group">
                                <label for="a">ATPRS</label>
                                <input type="text" name="a" id="a" value="<?php echo $editData ? $editData['a'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="b">B-ATS 15</label>
                                <input type="text" name="b" id="b" value="<?php echo $editData ? $editData['b'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="c">B-BNS 24</label>
                                <input type="text" name="c" id="c" value="<?php echo $editData ? $editData['c'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="d">BG-BLS 12</label>
                                <input type="text" name="d" id="d" value="<?php echo $editData ? $editData['d'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="e">CG - BS 901</label>
                                <input type="text" name="e" id="e" value="<?php echo $editData ? $editData['e'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="f">C - SMS 501</label>
                                <input type="text" name="f" id="f" value="<?php echo $editData ? $editData['f'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="g">C-ATS 20</label>
                                <input type="text" name="g" id="g" value="<?php echo $editData ? $editData['g'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="h">C-SMS 702</label>
                                <input type="text" name="h" id="h" value="<?php echo $editData ? $editData['h'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="i">C-ATNMS 20</label>
                                <input type="text" name="i" id="i" value="<?php echo $editData ? $editData['i'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="j">T - TRS 102</label>
                                <input type="text" name="j" id="j" value="<?php echo $editData ? $editData['j'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="k">T-ATNM S</label>
                                <input type="text" name="k" id="k" value="<?php echo $editData ? $editData['k'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="l">T-ATS 10</label>
                                <input type="text" name="l" id="l" value="<?php echo $editData ? $editData['l'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="m">T-ATS 15</label>
                                <input type="text" name="m" id="m" value="<?php echo $editData ? $editData['m'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="n">T-ATRS 15</label>
                                <input type="text" name="n" id="n" value="<?php echo $editData ? $editData['n'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="o">W - BLS 701</label>
                                <input type="text" name="o" id="o" value="<?php echo $editData ? $editData['o'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="p">SKIM</label>
                                <input type="text" name="p" id="p" value="<?php echo $editData ? $editData['p'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="q">CPIL-MT</label>
                                <input type="text" name="q" id="q" value="<?php echo $editData ? $editData['q'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="r">CURING</label>
                                <input type="text" name="r" id="r" value="<?php echo $editData ? $editData['r'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="grand_total">Grand Total Compound Weight</label>
                                <input type="text" name="grand_total" id="grand_total" value="<?php echo $editData ? $editData['Grand Totalcompound weight'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="color">Color</label>
                                <input type="text" name="color" id="color" value="<?php echo $editData ? $editData['Color'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="brand">Brand</label>
                                <input type="text" name="brand" id="brand" value="<?php echo $editData ? $editData['Brand'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="green_tire_weight">Green Tire Weight</label>
                                <input type="text" name="green_tire_weight" id="green_tire_weight" value="<?php echo $editData ? $editData['Green Tire weight'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="pbweight">PB Weight</label>
                                <input type="text" name="pbweight" id="pbweight" value="<?php echo $editData ? $editData['PBweight'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-buttons">
                            <?php if($editData): ?>
                                <button type="submit" name="update" class="btn btn-success">
                                    <i class="fas fa-save"></i> Update Record
                                </button>
                                <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            <?php else: ?>
                                <button type="submit" name="insert" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Add Record
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Search Card -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-search card-header-icon"></i> Search Records</h2>
            </div>
            <div class="card-body">
                <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="search-container">
                        <div class="search-box">
                            <input type="text" name="search" placeholder="Search by Item, Code, Size, Color, Brand..." value="<?php echo $search; ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </div>
                        <?php if(!empty($search)): ?>
                            <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Data Table Card -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-table card-header-icon"></i> BOM Records</h2>
            </div>
            <div class="card-body">
                <div class="data-count">
                    <i class="fas fa-info-circle"></i>
                    Showing <?php echo min($recordsPerPage, $paginatedResult->num_rows); ?> of <?php echo $totalRecords; ?> records
                    <?php if(!empty($search)): ?>
                        (filtered by "<?php echo $search; ?>")
                    <?php endif; ?>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Code</th>
                                <th>Size</th>
                                <th>Color</th>
                                <th>Brand</th>
                                <th>GT Weight</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($paginatedResult->num_rows > 0): ?>
                                <?php while($row = $paginatedResult->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['Item']); ?></td>
                                        <td><?php echo htmlspecialchars($row['icode']); ?></td>
                                        <td><?php echo htmlspecialchars($row['t_size']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Color']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Brand']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Grand Totalcompound weight']); ?></td>
                                        <td class="action-column">
                                            <div class="table-actions">
                                                <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?edit=' . $row['id']; ?>" class="btn btn-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="delete" class="btn btn-danger">
                                                        <i class="fas fa-trash-alt"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 20px;">No records found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if($totalPages > 1): ?>
                    <ul class="pagination">
                        <?php if($page > 1): ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?page=1' . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?page=' . ($page - 1) . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        
                        for($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <li class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if($page < $totalPages): ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?page=' . ($page + 1) . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?page=' . $totalPages . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        
        <footer class="footer">
            <p>&copy; 2025 BOM Management System. All Rights Reserved.</p>
        </footer>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle form visibility
            const toggleFormBtn = document.getElementById('toggleFormBtn');
            const formContainer = document.getElementById('formContainer');
            
            if(toggleFormBtn) {
                toggleFormBtn.addEventListener('click', function() {
                    formContainer.classList.toggle('show');
                    
                    const icon = this.querySelector('i');
                    if(formContainer.classList.contains('show')) {
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-up');
                        this.innerHTML = this.innerHTML.replace('Show Form', 'Hide Form');
                    } else {
                        icon.classList.remove('fa-chevron-up');
                        icon.classList.add('fa-chevron-down');
                        this.innerHTML = this.innerHTML.replace('Hide Form', 'Show Form');
                    }
                });
            }
            
            // Auto-calculate grand total
            const compoundFields = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r'];
            const grandTotalField = document.getElementById('grand_total');
            
            compoundFields.forEach(field => {
                const input = document.getElementById(field);
                if(input) {
                    input.addEventListener('input', calculateGrandTotal);
                }
            });
            
            function calculateGrandTotal() {
                let total = 0;
                compoundFields.forEach(field => {
                    const input = document.getElementById(field);
                    if(input && input.value) {
                        const value = parseFloat(input.value);
                        if(!isNaN(value)) {
                            total += value;
                        }
                    }
                });
                
                if(grandTotalField) {
                    grandTotalField.value = total.toFixed(2);
                }
            }
        });
    </script>
</body>
</html>