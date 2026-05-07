<?php 
session_start();
include('includes/config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Dynamic Investee News Portal">
    <meta name="author" content="Bishan Kanthana">
    <title>Dynamic Investee | Home Page</title>
    
    <!-- Bootstrap core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link href="css/modern-business.css" rel="stylesheet">
    
    <style>
        .tradingview-container {
            width: 100%;
            height: 80vh;
            margin: 20px auto;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .news-container {
            margin-top: 40px;
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }
        .card-img-top {
            max-height: 300px;
            object-fit: cover;
        }
        .category-badge {
            margin-right: 5px;
            background-color: #6c757d;
            color: #fff;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <?php include('includes/header.php');?>

    <!-- TradingView Widget Section -->
    <div class="tradingview-container text-center">
        <h2 class="text-primary mb-4">Live Sri Lankan Market Chart - ATrade</h2>
        <div class="tradingview-widget-container">
            <div class="tradingview-widget-container__widget"></div>
            <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js" async>
            {
                "autosize": true,
              
                "timezone": "Asia/Colombo",
                "theme": "light",
                "style": "1",
                "locale": "en",
                "withdateranges": true,
                "range": "ALL",
                "hide_side_toolbar": false,
                "allow_symbol_change": true,
                "details": true,
                "calendar": false,
                "support_host": "https://www.tradingview.com"
            }
            </script>
        </div>
    </div>

    <!-- News Content Section -->
    <div class="container news-container">
        <div class="row">
            <!-- Blog Entries Column -->
            <div class="col-md-8">
                <?php 
                // Pagination Setup
                $pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;
                $no_of_records_per_page = 8;
                $offset = ($pageno-1) * $no_of_records_per_page;

                // Get total pages
                $total_pages_sql = "SELECT COUNT(*) FROM tblposts";
                $result = mysqli_query($con, $total_pages_sql);
                $total_rows = mysqli_fetch_array($result)[0];
                $total_pages = ceil($total_rows / $no_of_records_per_page);

                // Fetch posts
                $query = mysqli_query($con, "SELECT 
                    tblposts.id as pid,
                    tblposts.PostTitle as posttitle,
                    tblposts.PostImage,
                    tblcategory.CategoryName as category,
                    tblcategory.id as cid,
                    tblsubcategory.Subcategory as subcategory,
                    tblposts.PostDetails as postdetails,
                    tblposts.PostingDate as postingdate,
                    tblposts.PostUrl as url 
                    FROM tblposts 
                    LEFT JOIN tblcategory ON tblcategory.id=tblposts.CategoryId 
                    LEFT JOIN tblsubcategory ON tblsubcategory.SubCategoryId=tblposts.SubCategoryId 
                    WHERE tblposts.Is_Active=1 
                    ORDER BY tblposts.id DESC 
                    LIMIT $offset, $no_of_records_per_page");

                while ($row = mysqli_fetch_array($query)) {
                ?>
                    <!-- News Card -->
                    <div class="card mb-4">
                        <img class="card-img-top" 
                             src="admin/postimages/<?php echo htmlentities($row['PostImage']);?>" 
                             alt="<?php echo htmlentities($row['posttitle']);?>">
                        <div class="card-body">
                            <h2 class="card-title text-primary"><?php echo htmlentities($row['posttitle']);?></h2>
                            <p>
                                <span class="badge category-badge"><?php echo htmlentities($row['category']);?></span>
                                <span class="badge category-badge"><?php echo htmlentities($row['subcategory']);?></span>
                            </p>
                            <a href="news-details.php?nid=<?php echo htmlentities($row['pid'])?>" 
                               class="btn btn-outline-primary">Read More &rarr;</a>
                        </div>
                        <div class="card-footer text-muted">
                            Posted on <?php echo htmlentities($row['postingdate']);?>
                        </div>
                    </div>
                <?php } ?>

                <!-- Pagination -->
                <ul class="pagination justify-content-center mb-4">
                    <li class="page-item">
                        <a href="?pageno=1" class="page-link">First</a>
                    </li>
                    <li class="page-item <?php if($pageno <= 1){ echo 'disabled'; } ?>">
                        <a href="<?php if($pageno <= 1){ echo '#'; } else { echo "?pageno=".($pageno - 1); } ?>" 
                           class="page-link">Prev</a>
                    </li>
                    <li class="page-item <?php if($pageno >= $total_pages){ echo 'disabled'; } ?>">
                        <a href="<?php if($pageno >= $total_pages){ echo '#'; } else { echo "?pageno=".($pageno + 1); } ?>" 
                           class="page-link">Next</a>
                    </li>
                    <li class="page-item">
                        <a href="?pageno=<?php echo $total_pages; ?>" class="page-link">Last</a>
                    </li>
                </ul>
            </div>

            <!-- Sidebar Widgets Column -->
            <?php include('includes/sidebar.php');?>
        </div>
    </div>

    <!-- Footer -->
    <?php include('includes/footer.php');?>

    <!-- Bootstrap core JavaScript -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
