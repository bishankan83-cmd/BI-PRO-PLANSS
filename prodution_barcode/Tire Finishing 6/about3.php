<?php
// Database connection
$host = 'localhost';
$db = 'planatir_task_managemen';
$user = 'planatir_task_managemen';
$password = 'Bishan@1919';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get serialNumber from about1.php
    $serialNumber = $_GET['serialNumber'] ?? null;

    if ($serialNumber) {
        // Fetch details from the tire_data table including pressNumber
        $stmt = $pdo->prepare("SELECT serialNumber, pressNumber FROM tire_data WHERE serialNumber = ?");
        $stmt->execute([$serialNumber]);
        $details = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$details) {
            die("No details found for the selected serial number.");
        }
    } else {
        die("Serial number is required.");
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Fables">
    <meta name="author" content="Enterprise Development">
    <link rel="shortcut icon" href="assets/custom/images/shortcut.png">
    <title>Tire Details</title>

    <!-- Include CSS Files -->
    <link href="assets/vendor/animate.css-master/animate.min.css" rel="stylesheet">
    <link href="assets/vendor/loadscreen/css/spinkit.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i" rel="stylesheet">
    <link href="assets/vendor/fontawesome/css/fontawesome-all.min.css" rel="stylesheet">
    <link href="assets/custom/css/fables-icons.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap-4-navbar.css" rel="stylesheet">
    <link href="assets/vendor/portfolio-filter-gallery/portfolio-filter-gallery.css" rel="stylesheet">
    <link href="assets/vendor/fancybox-master/jquery.fancybox.min.css" rel="stylesheet">
    <link href="assets/vendor/range-slider/range-slider.css" rel="stylesheet">
    <link href="assets/vendor/owlcarousel/owl.carousel.min.css" rel="stylesheet">
    <link href="assets/vendor/owlcarousel/owl.theme.default.min.css" rel="stylesheet">
    <link href="assets/custom/css/custom.css" rel="stylesheet">
    <link href="assets/custom/css/custom-responsive.css" rel="stylesheet">
</head>
<body>

<!-- Loading Screen -->
<div id="ju-loading-screen">
    <div class="sk-double-bounce">
        <div class="sk-child sk-double-bounce1"></div>
        <div class="sk-child sk-double-bounce2"></div>
    </div>
</div>

<!-- Start Fables Navigation -->
<div class="fables-navigation fables-main-background-color py-3 py-lg-0">
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-10 col-lg-9 pr-md-0">
                <nav class="navbar navbar-expand-md btco-hover-menu py-lg-2">
                    <a class="navbar-brand pl-0">
                        <img src="assets/custom/images/ATIRE-logo.png" alt="Fables Template" class="fables-logo">
                    </a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#fablesNavDropdown" aria-controls="fablesNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="fables-iconmenu-icon text-white font-16"></span>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</div> 
<!-- /End Fables Navigation -->

<!-- Start Header -->
<div class="fables-header fables-after-overlay bg-rules">
    <div class="container"> 
         <h2 class="fables-page-title fables-second-border-color wow fadeInLeft" data-wow-duration="1.5s"> Tire Details </h2>
    </div>
</div>  

<!-- /End Header -->
<!-- Start Breadcrumbs -->
<div class="fables-light-gary-background">
    <div class="container"> 
        <nav aria-label="breadcrumb">
          <ol class="fables-breadcrumb breadcrumb px-0 py-3">
            <li class="breadcrumb-item"><a href="index.php" class="fables-second-text-color">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tire Details </li>
          </ol>
        </nav> 
    </div>
</div>
<!-- /End Breadcrumbs -->

<!-- Start page content -->  
<div class="container">
    <div class="my-4 my-md-5 overflow-hidden">
        <div class="text-center mb-5 wow fadeInDown" data-wow-delay="1s">
            <h3 class="fables-about-top-head fables-forth-text-color font-15 semi-font d-inline-block bg-white position-relative">
                <span class="mx-4">Tire Details</span>
            </h3>
        </div>
    </div>
</div>
<!-- /end page content -->

<!-- Tire Details Form -->
<form action="process2.php" method="post">

    <!-- Serial Number (Auto-filled) -->
    <div data-mdb-input-init class="form-outline mb-4">
        <label class="form-label" for="serialNumber" style="margin-left: 200px; font-size: 18px; font-weight: bold;">Serial Number</label><br><br>
        <input type="text" id="serialNumber" name="serialNumber" class="aboutbox" value="<?= htmlspecialchars($details['serialNumber']) ?>" readonly />
    </div>





    <!-- Tire Code Dropdown -->
    <div data-mdb-input-init class="form-outline mb-4">
        <label class="form-label" for="tireCode" style="margin-left: 200px; font-size: 18px; font-weight: bold;">Tire Code</label><br><br>
        <select id="tireCode" name="tireCode" class="aboutbox" onchange="fetchDetails()">
            <option value="">Select Tire Code</option>
            <?php
            // Query to fetch icode and alias it as tireCode
            $stmt = $pdo->query("SELECT icode AS tireCode FROM tire_details");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$row['tireCode']}' " . 
                     (isset($details['tireCode']) && $details['tireCode'] == $row['tireCode'] ? 'selected' : '') . 
                     ">{$row['tireCode']}</option>";
            }
            ?>
        </select>
    </div>

    <!-- Tire Details -->
    <div data-mdb-input-init class="form-outline mb-4">
        <label class="form-label" for="brand" style="margin-left: 200px; font-size: 18px; font-weight: bold;">Brand</label><br><br>
        <input type="text" id="brand" name="brand" class="aboutbox" readonly />
    </div>

    <div data-mdb-input-init class="form-outline mb-4">
        <label class="form-label" for="tireWeight" style="margin-left: 200px; font-size: 18px; font-weight: bold;">Tire Weight</label><br><br>
        <input type="text" id="tireWeight" name="tireWeight" class="aboutbox" readonly />
    </div>


    
    
    <!-- Tire Code Dropdown -->
    <div data-mdb-input-init class="form-outline mb-4">
        <label class="form-label" for="pressNumber" style="margin-left: 200px; font-size: 18px; font-weight: bold;">Press Name </label><br><br>
        <select id="pressNumber" name="pressNumber" class="aboutbox" onchange="fetchDetails()">
            <option value="">Select Press Name </option>
            <?php
            // Query to fetch icode and alias it as tireCode
            $stmt = $pdo->query("SELECT press_name AS pressNumber FROM press");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$row['pressNumber']}' " . 
                     (isset($details['pressNumber']) && $details['pressNumber'] == $row['pressNumber'] ? 'selected' : '') . 
                     ">{$row['pressNumber']}</option>";
            }
            ?>
        </select>
    </div>

    <br><br><br>

    <!-- Submit Button -->
    <div class="col-12 col-md-4 mb-4 sub">
        <button type="submit" name="action" value="yes" class="btn btn-dark btn-lg btn-block" style="margin-left: 500px;"> Update</button>
    </div>

    <br><br>
</form>

<script>
function fetchDetails() {
    var tireCode = document.getElementById('tireCode').value;

    // Using AJAX to fetch details
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_tire_details.php?tireCode=' + tireCode, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var details = JSON.parse(xhr.responseText);
            if (details.error) {
                alert(details.error);
            } else {
                document.getElementById('brand').value = details.brand;
                document.getElementById('tireWeight').value = details.tireWeight;
                // Don't override the press number from tire_data table as it's now editable
            }
        }
    };
    xhr.send();
}
</script>

<script src="assets/vendor/jquery/jquery-3.3.1.min.js"></script>
<script src="assets/vendor/loadscreen/js/ju-loading-screen.js"></script>
<script src="assets/vendor/jquery-circle-progress/circle-progress.min.js"></script>
<script src="assets/vendor/popper/popper.min.js"></script>
<script src="assets/vendor/jQuery.countdown-master/jquery.countdown.min.js"></script>
<script src="assets/vendor/timeline/jquery.timelify.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.min.js"></script>

</body>
</html>