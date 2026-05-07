<?php
// Database connection
$host = 'localhost';
$db = 'planatir_task_managemen';
$user = 'planatir_task_managemen';
$password = 'Bishan@1919';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch serial numbers
    $stmt = $pdo->query("SELECT id, serialNumber FROM tire_data");
    $serials = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    <title> Home </title>

     <!--animate.css-->
     <link href="assets/vendor/animate.css-master/animate.min.css" rel="stylesheet">
    <!--Load Screen -->
   <link href="assets/vendor/loadscreen/css/spinkit.css" rel="stylesheet">
     <!--GOOGLE FONT -->
   <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i" rel="stylesheet">
     <!--Font Awesome 5 -->
    <link href="assets/vendor/fontawesome/css/fontawesome-all.min.css" rel="stylesheet">
     <!--Fables Icons -->
   <link href="assets/custom/css/fables-icons.css" rel="stylesheet"> 
     <!--Bootstrap CSS --> 
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap-4-navbar.css" rel="stylesheet">
     <!--portfolio filter gallery -->
    <link href="assets/vendor/portfolio-filter-gallery/portfolio-filter-gallery.css" rel="stylesheet">
     <!--FANCY BOX -->
    <link href="assets/vendor/fancybox-master/jquery.fancybox.min.css" rel="stylesheet"> 
     <!--RANGE SLIDER -->
   <link href="assets/vendor/range-slider/range-slider.css" rel="stylesheet">
     <!--OWL CAROUSEL  --> 
    <link href="assets/vendor/owlcarousel/owl.carousel.min.css" rel="stylesheet">
    <link href="assets/vendor/owlcarousel/owl.theme.default.min.css" rel="stylesheet">
     <!--FABLES CUSTOM CSS FILE -->
   <link href="assets/custom/css/custom.css" rel="stylesheet">
     <!--FABLES CUSTOM CSS RESPONSIVE FILE -->
    <link href="assets/custom/css/custom-responsive.css" rel="stylesheet"> 
</head>


<body>



<div class="fables-transparent py-3 py-lg-0">
    <div class="container">
           <div class="row">
               <div class="col-12 col-md-10 pr-md-0">                       
                   <nav class="navbar navbar-expand-md btco-hover-menu py-lg-2">

                        <a class="navbar-brand fables-logo-brand pl-0" ><img src="assets/custom/images/ATIRE-logo.png" alt="Fables Template" class="fables-logo"></a>
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#fablesNavDropdown" aria-controls="fablesNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="fables-iconmenu-icon text-white font-16"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="fablesNavDropdown"> 
                   </div>

               </div>
           </div>
    </div>
</div>
     
<!-- Start Header -->

<!-- login page -->

<div class="fables-header fables-after-overlay overlay-lighter index-traingle bg-rules" 
    style="background-image: url(assets/custom/images/index-background.jpg);">
    <div class="container">  
        <div class="row">
            <div class="col-md-10 col-lg-7 mr-auto index-carousel">
                <div class="owl-carousel owl-theme default-carousel nav-0 z-index mt-md-4 mt-xl-5 pt-md-4 pt-xl-5 dots-0 pb-md-5">
                    <div class="pt-0 mt-0 pt-xl-5 mt-xl-5 wow slideInUp" data-wow-duration="2s" data-wow-delay=".4s">
                        <!-- Form begins here -->
                        <form action="about1.php" method="get" class="php-email-form">
                        <div class="form-group">
                        <label for="serialNumber"style="font-size: 45px; font-family: Arial, sans-serif; font-weight: bold;">Choose Serial Number</label>
                        <select id="serialNumber" name="serialNumber" class="form-control" required>
                            <option value="">Select Serial Number</option>
            <?php foreach ($serials as $serial): ?>
                <option value="<?= $serial['serialNumber'] ?>"><?= htmlspecialchars($serial['serialNumber']) ?></option>
            <?php endforeach; ?>
        </select>
                                <br></br>
                                <br></br>
                                <div class="pt-1 mb-4"> 
                                <button type="submit" class="btn btn-dark btn-lg btn-block" data-mdb-button-init data-mdb-ripple-init>
                                    Update
                                </button>
                            </div>
                        </form>
                        
                        
                        <!-- Form ends here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/vendor/jquery/jquery-3.3.1.min.js"></script>
<script src="assets/vendor/loadscreen/js/ju-loading-screen.js"></script>
<script src="assets/vendor/jquery-circle-progress/circle-progress.min.js"></script>
<script src="assets/vendor/popper/popper.min.js"></script>
<script src="assets/vendor/jQuery.countdown-master/jquery.countdown.min.js"></script>
<script src="assets/vendor/timeline/jquery.timelify.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap-4-navbar.js"></script>
<script src="assets/vendor/WOW-master/dist/wow.min.js"></script>
<script src="assets/vendor/owlcarousel/owl.carousel.min.js"></script> 
<script src="assets/custom/js/jquery-data-to.js"></script>   
<script src="assets/vendor/jquery-circle-progress/circle.js"></script>
<script src="assets/vendor/portfolio-filter-gallery/jquery.isotope.min.js"></script>
<script src="assets/vendor/portfolio-filter-gallery/portfolio-filter-gallery.js"></script>
<script src="assets/vendor/fancybox-master/jquery.fancybox.min.js"></script>
<script src="assets/custom/js/custom.js"></script>

 
</body>
</html>
