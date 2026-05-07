


<?php
// Database connection
$con = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Delete data from the stocks table
$deleteStocksSQL = "DELETE FROM `stockh`;";

if (mysqli_query($con, $deleteStocksSQL)) {
    //echo "Data deleted from stocks table successfully<br>";
} else {
    //echo "Error deleting data from stocks table: " . mysqli_error($con) . "<br>";
}

// Close the connection
mysqli_close($con);
?>






<?php
// Database connection
$con = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}



// Insert summarized data into icode_summary table
$insertDataSQL = "
INSERT INTO `stockh` (`icode`, `cstock`, `t_size`, `Brand`, `col` )
SELECT 
  `icode`, 
  COUNT(*) AS `count`,
  MIN(`Description`) AS `Description`,
  MIN(`Brand`) AS `Brand`,
  MIN(`color`) AS `color`
FROM 
  `stocks`
GROUP BY 
  `icode`;
";

if (mysqli_query($con, $insertDataSQL)) {
   // echo "Data inserted into icode_summary successfully<br>";
} else {
    //echo "Error inserting data: " . mysqli_error($con) . "<br>";
}

// Close the connection
mysqli_close($con);
?>













<!DOCTYPE html>
<html>

<head>
<style>
        /* Your CSS styles */
        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
        }

        .stock-table {
            width: 100%;
            border-collapse: collapse;
        }

        .stock-table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        .button-container {
            text-align: left;
            margin: 10px;
            border-radius: 4px;
        }

        .button-container button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 40px;
        }

        .search-form {
            text-align: center;
            margin: 10px;
        }

        .search-form input[type="text"] {
            padding: 10px;
            width: 200px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            font-family: 'Cantarell', sans-serif;
            font-weight: regular;
        }

        .search-form button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .button-container button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: background-color 0.3s; /* Add a smooth transition for the background color change */
        }

        .button-container button:hover {
            background-color: #333333; /* Change the background color on hover */
        }

        .stock-row td {
            font-family: 'Open Sans', sans-serif;
            font-weight: regular;
        }

        /* Add a fixed position to the table header */
        .stock-table th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Add a background color and some padding to the header row */
        .stock-table .header {
            background-color: #F28018;
            padding: 10px;
        }

        /* Adjust the position of the body cells to make room for the fixed header */
        .stock-table td {
            padding-top: 30px; /* Adjust this value based on your header height */
        }

        /* Style the select box */
        .select-container {
            margin: 10px;
            text-align: center;
        }

        select {
            padding: 10px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            font-family: 'Cantarell', sans-serif;
            font-weight: regular;
        }
    </style>

    <script>
        function searchStock() {
            var input = document.getElementById('icodeInput').value.toLowerCase();
            var table = document.getElementById('stock-table');
            var rows = table.getElementsByClassName('stock-row');

            for (var i = 0; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName('td');
                var icodeCell = cells[0];
                var colorCell = cells[1];
                var brandCell = cells[2];
                

                if (icodeCell && colorCell && brandCell) {
                    var icodeValue = icodeCell.textContent || icodeCell.innerText;
                    var colorValue = colorCell.textContent || colorCell.innerText;
                    var brandValue = brandCell.textContent || brandCell.innerText;

                    icodeValue = icodeValue.toLowerCase();
                    colorValue = colorValue.toLowerCase();
                    brandValue = brandValue.toLowerCase();

                    if (icodeValue.includes(input) || colorValue.includes(input) || brandValue.includes(input)) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        }

        function filterByBrandAndColor() {
            var brandSelect = document.getElementById('brandSelect');
            var colorSelect = document.getElementById('colorSelect');
            var selectedBrand = brandSelect.value.toLowerCase();
            var selectedColor = colorSelect.value.toLowerCase();
            var table = document.getElementById('stock-table');
            var rows = table.getElementsByClassName('stock-row');

            for (var i = 0; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName('td');
                var brandCell = cells[2];
                var colorCell = cells[3];

                if (brandCell && colorCell) {
                    var brandValue = brandCell.textContent || brandCell.innerText;
                    var colorValue = colorCell.textContent || colorCell.innerText;
                    brandValue = brandValue.toLowerCase();
                    colorValue = colorValue.toLowerCase();

                    if ((selectedBrand === '' || brandValue.includes(selectedBrand)) &&
                        (selectedColor === '' || colorValue.includes(selectedColor))) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</head>

<body>
    <div class="button-container">
        <button>
            <a href="dashboard.php" style="text-decoration: none; color: #FFFFFF;">Click To dashboard</a>
        </button>
    </div>

    <div class="container">
        <div class="search-form">
            <input type="text" id="icodeInput" placeholder="Enter Item Code, Description, Brand, Or Colour" oninput="searchStock()">
            <label for="brandSelect">Select Brand:</label>
            <select id="brandSelect" onchange="filterByBrandAndColor()">
                <option value="">All Brands</option>
                <?php
                $con = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

                if (!$con) {
                    die("Connection failed: " . mysqli_connect_error());
                }

                $brand_query = "SELECT DISTINCT brand FROM realstock";
                $brand_query_run = mysqli_query($con, $brand_query);

                if ($brand_query_run) {
                    while ($brand = mysqli_fetch_assoc($brand_query_run)) {
                        echo '<option value="' . $brand['brand'] . '">' . $brand['brand'] . '</option>';
                    }
                } else {
                    echo "Error in brand query: " . mysqli_error($con);
                }
                ?>
            </select>

            <label for="colorSelect">Select Color:</label>
            <select id="colorSelect" onchange="filterByBrandAndColor()">
                <option value="">All Colors</option>
                <?php
                $color_query = "SELECT DISTINCT col FROM realstock";
                $color_query_run = mysqli_query($con, $color_query);

                if ($color_query_run) {
                    while ($color = mysqli_fetch_assoc($color_query_run)) {
                        echo '<option value="' . $color['col'] . '">' . $color['col'] . '</option>';
                    }
                } else {
                    echo "Error in color query: " . mysqli_error($con);
                }
                ?>
            </select>
        </div>

   
    </div>
            
        <table id="stock-table" class="stock-table">
            <tr class="header">
                <th>Item Code</th>
                <th>Description</th>
                <th>Brand</th>
                <th>Colour</th>
                <th>Stock On Hand</th>
             
            </tr>
            <tbody>
                <?php
               

               

                $query = "SELECT * FROM stockh";
                $query_run = mysqli_query($con, $query);

                if (!$query_run) {
                    echo "Error in stock query: " . mysqli_error($con);
                } else {
                    while ($items = mysqli_fetch_assoc($query_run)) {
                ?>
                        <tr class="stock-row">
                            <td><?= $items['icode']; ?></td>
                            <td><?= $items['t_size']; ?></td>
                            <td><?= $items['brand']; ?></td>
                            <td><?= $items['col']; ?></td>
                            <td><?= $items['cstock']; ?></td>
                            
                        </tr>
                <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>
