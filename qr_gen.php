<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Fables">
    <meta name="author" content="Enterprise Development">
    <link rel="shortcut icon" href="assets/custom/images/shortcut.png">
    <title>Home</title>

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
                </nav>
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
                        <form action="save_shift.php" method="POST">
                            <div data-mdb-input-init class="form-outline mb-4">
                                <input type="date" id="logDate" name="logDate" class="form-control form-control-lg" required />
                                <label class="form-label" for="logDate">System Logging Date</label>
                            </div>
            
                            <div data-mdb-input-init class="form-outline mb-4">
                                <input type="text" id="shift" name="shift" class="form-control form-control-lg" 
                                       placeholder="Enter Shift (e.g., Morning)" required />
                                <label class="form-label" for="shift">Shift</label>
                            </div>
                            <br>
            
                            <div class="pt-1 mb-4">
                                <button type="submit" class="btn btn-dark btn-lg btn-block">
                                    Login
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Get today's date
        const today = new Date();
        const dd = String(today.getDate()).padStart(2, '0');
        const mm = String(today.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
        const yyyy = today.getFullYear();

        // Format the date as YYYY-MM-DD
        const formattedDate = yyyy + '-' + mm + '-' + dd;

        // Set the value of the date input field to today's date
        document.getElementById('logDate').value = formattedDate;
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('generateSerial').addEventListener('click', function () {
                const logDate = document.getElementById('logDate').value;
                const shift = document.getElementById('shift').value;

                if (!logDate || !shift) {
                    alert('Please select a log date and shift.');
                    return;
                }

                fetch('generate_serial_number.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ logDate: logDate, shift: shift }),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('serialNumberDisplay').textContent =
                                'Serial Number: ' + data.serialNumber;
                        } else {
                            alert('Error: ' + data.error);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
</script>

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
