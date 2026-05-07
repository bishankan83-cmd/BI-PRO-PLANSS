


<?php
// Database connection
$con = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Delete data from the stocks table
$deleteStocksSQL = "DELETE FROM `stockb`;";

if (mysqli_query($con, $deleteStocksSQL)) {
    //echo "Data deleted from stocks table successfully<br>";
} else {
    //echo "Error deleting data from stocks table: " . mysqli_error($con) . "<br>";
}

// Close the connection
mysqli_close($con);
?>






<?php
// Database connection configuration
$host = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Establish database connection
$con = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to retrieve data from specified tables
function retrieveTableData($connection, $tables) {
    $results = [];

    foreach ($tables as $table) {
        // Sanitize table name to prevent SQL injection
        $safeTableName = mysqli_real_escape_string($connection, $table);
        
        // Retrieve all columns from the table
        $query = "SELECT * FROM `{$safeTableName}`";
        
        // Execute query
        $result = mysqli_query($connection, $query);
        
        if ($result) {
            // Fetch all rows
            $tableData = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $tableData[] = $row;
            }
            
            // Store results with table name as key
            $results[$table] = $tableData;
            
            // Free result set
            mysqli_free_result($result);
        } else {
            // Handle query error
            $results[$table] = "Error retrieving data: " . mysqli_error($connection);
        }
    }

    return $results;
}

// Function to prepare and insert data into stockb table
function prepareAndInsertStockbData($connection, $tableData) {
    // Prepare an array to store consolidated data
    $consolidatedData = [];

    // Process each table
    foreach ($tableData as $tableName => $rows) {
        foreach ($rows as $row) {
            // Extract relevant columns for stockb table
            // Adjust these keys based on your actual table structures
            $icode = isset($row['tyre_code']) ? $row['tyre_code'] : 
                     (isset($row['icode']) ? $row['icode'] : '');
            $brand = isset($row['brand']) ? $row['brand'] : '';
            $description = isset($row['description']) ? $row['description'] : '';
            $color = isset($row['color']) ? $row['color'] : '';

            // If icode exists, consolidate data
            if (!empty($icode)) {
                // Initialize or update consolidated entry
                if (!isset($consolidatedData[$icode])) {
                    $consolidatedData[$icode] = [
                        'icode' => $icode,
                        'cstock' => 1,
                        't_size' => $description,
                        'Brand' => $brand,
                        'col' => $color
                    ];
                } else {
                    // Increment stock count
                    $consolidatedData[$icode]['cstock']++;
                    
                    // Update other fields if needed
                    $consolidatedData[$icode]['Brand'] = !empty($brand) 
                        ? $brand 
                        : $consolidatedData[$icode]['Brand'];
                    
                    $consolidatedData[$icode]['t_size'] = !empty($description) 
                        ? $description 
                        : $consolidatedData[$icode]['t_size'];
                    
                    $consolidatedData[$icode]['col'] = !empty($color) 
                        ? $color 
                        : $consolidatedData[$icode]['col'];
                }
            }
        }
    }

    // Prepare and execute bulk insert
    if (!empty($consolidatedData)) {
        // Start transaction
        mysqli_begin_transaction($connection);

        try {
            // Prepare the bulk insert query
            $insertQuery = "INSERT INTO `stockb` 
                            (`icode`, `cstock`, `t_size`, `Brand`, `col`) 
                            VALUES ";
            
            $valueStrings = [];
            $params = [];

            foreach ($consolidatedData as $data) {
                $valueStrings[] = "(?, ?, ?, ?, ?)";
                array_push(
                    $params, 
                    $data['icode'], 
                    $data['cstock'], 
                    $data['t_size'], 
                    $data['Brand'], 
                    $data['col']
                );
            }

            $insertQuery .= implode(", ", $valueStrings);
            $insertQuery .= " ON DUPLICATE KEY UPDATE 
                            `cstock` = VALUES(`cstock`),
                            `t_size` = VALUES(`t_size`),
                            `Brand` = VALUES(`Brand`),
                            `col` = VALUES(`col`)";

            // Prepare statement
            $stmt = mysqli_prepare($connection, $insertQuery);

            // Dynamically bind parameters
            $types = str_repeat("s", count($params));
            mysqli_stmt_bind_param($stmt, $types, ...$params);

            // Execute the statement
            $result = mysqli_stmt_execute($stmt);

            if ($result) {
                // Commit transaction
                mysqli_commit($connection);
                echo "Successfully inserted/updated " . count($consolidatedData) . " records in stockb table.\n";
            } else {
                // Rollback transaction
                mysqli_rollback($connection);
                echo "Failed to insert data: " . mysqli_error($connection) . "\n";
            }

            // Close statement
            mysqli_stmt_close($stmt);

        } catch (Exception $e) {
            // Rollback transaction in case of error
            mysqli_rollback($connection);
            echo "Error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "No data to insert.\n";
    }
}

// Tables to retrieve data from
$tablesToRetrieve = [
    'non_moveing_tire',
    'over_age',
    'stocks'
];

// Retrieve data from tables
$tableData = retrieveTableData($con, $tablesToRetrieve);

// Prepare and insert data into stockb table
prepareAndInsertStockbData($con, $tableData);

// Close the database connection
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
               

               

                $query = "SELECT * FROM stockb";
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
