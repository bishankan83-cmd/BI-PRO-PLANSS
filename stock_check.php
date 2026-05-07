

<!DOCTYPE html>
<html>

<head>
    <script>
        function searchStock() {
            var input = document.getElementById('icodeInput').value.toLowerCase();
            var table = document.getElementById('stockr-table');
            var rows = table.getElementsByClassName('stockr-row');

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
            updateTotalsDisplay(); // Update totals when searching
        }

        function filterByBrandAndColor() {
            var brandSelect = document.getElementById('brandSelect');
            var colorSelect = document.getElementById('colorSelect');
            var selectedBrand = brandSelect.value.toLowerCase();
            var selectedColor = colorSelect.value.toLowerCase();
            var table = document.getElementById('stockr-table');
            var rows = table.getElementsByClassName('stockr-row');

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
            updateTotalsDisplay(); // Update totals when filtering
        }

        function calculateTotalStockOnHand() {
    let totalStockOnHand = 0;
    const table = document.getElementById('stockr-table');
    const rows = table.getElementsByClassName('stockr-row');

    for (let i = 0; i < rows.length; i++) {
        if (rows[i].style.display !== "none") { // Only include visible rows
            const stockCell = rows[i].getElementsByTagName('td')[4]; // Assuming stock on hand is in the 5th column
            if (stockCell) {
                const stockValue = parseFloat(stockCell.textContent || stockCell.innerText);
                if (!isNaN(stockValue)) {
                    totalStockOnHand += stockValue;
                }
            }
        }
    }
    return totalStockOnHand;
}

function calculateTotalRequirement() {
    let totalRequirement = 0;
    const table = document.getElementById('stockr-table');
    const rows = table.getElementsByClassName('stockr-row');

    for (let i = 0; i < rows.length; i++) {
        if (rows[i].style.display !== "none") { // Only include visible rows
            const requirementCell = rows[i].getElementsByTagName('td')[5]; // Assuming requirement is in the 6th column
            if (requirementCell) {
                const requirementValue = parseFloat(requirementCell.textContent || requirementCell.innerText);
                if (!isNaN(requirementValue)) {
                    totalRequirement += requirementValue;
                }
            }
        }
    }
    return totalRequirement;
}

function calculateTotalFreeStock() {
    let totalFreeStock = 0;
    const table = document.getElementById('stockr-table');
    const rows = table.getElementsByClassName('stockr-row');

    for (let i = 0; i < rows.length; i++) {
        if (rows[i].style.display !== "none") { // Only include visible rows
            const freeStockCell = rows[i].getElementsByTagName('td')[4]; // Assuming free stock is in the 7th column
            if (freeStockCell) {
                const freeStockValue = parseFloat(freeStockCell.textContent || freeStockCell.innerText);
                if (!isNaN(freeStockValue)) {
                    totalFreeStock += freeStockValue;
                }
            }
        }
    }
    return totalFreeStock;
}


        function updateTotalsDisplay() {
            document.getElementById('totalStockOnHand').innerText = 'Total stock On Hand: ' + calculateTotalStockOnHand();
            document.getElementById('totalRequirement').innerText = 'Total Requirement Weight: ' + calculateTotalRequirement();
            document.getElementById('totalFreeStock').innerText = 'Total Free stock: ' + calculateTotalFreeStock();
        }

        window.onload = function () {
            updateTotalsDisplay();
        };
    </script>
</head>

<body>
    <div class="button-container">
        <button>
            <a href="dashboard.php" style="text-decoration: none; color: #FFFFFF;">Click To dashboard</a>
        </button>
    </div>

    <?php
    // Database connection parameters
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $dbname = "planatir_task_managemen";

    // Create a database connection
    $con = mysqli_connect($servername, $username, $password, $dbname);

    // Check the connection
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Function to calculate the total stock On Hand
    function calculateTotalStockOnHand()
    {
        global $con;
        $query = "SELECT SUM(cstock) AS total_stock FROM realstock";
        $result = mysqli_query($con, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['total_stock'];
        }

        return "N/A";
    }

    // Function to calculate the total Free Stock
    function calculateTotalFreeStock()
    {
        global $con;
        $query = "SELECT SUM(cstock) AS total_stockk FROM stockr";
        $result = mysqli_query($con, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['total_stockk'];
        }

        return "N/A";
    }

    // Function to calculate the total Requirement
    function calculateTotalRequirement()
    {
        $totalStockOnHand = calculateTotalStockOnHand();
        $totalFreeStock = calculateTotalFreeStock();

        if ($totalStockOnHand !== "N/A" && $totalFreeStock !== "N/A") {
            // Calculate the difference
            $totalRequirement = $totalStockOnHand - $totalFreeStock;
            return $totalRequirement;
        }

        return "N/A";
    }

    // Close the database connection when done
    mysqli_close($con);
    ?>

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

        <div id="totalsDisplay">
            <p id="totalStockOnHand">Total stock On Hand: 0</p>
            <p id="totalRequirement">Total Requirement Weight: 0</p>
            <p id="totalFreeStock">Total Free stock: 0</p>
        </div>

        <table id="stockr-table">
            <thead>
                <tr>
                    <th>Item Code</th>
                    <th>Description</th>
                    <th>Brand</th>
                    <th>Color</th>
                    <th>Stock On Hand</th>
                    <th>Requirement</th>
                    <th>Free Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Example rows from your database
                // Fetch rows from realstock for example
                $stock_query = "SELECT * FROM realstock"; // Adjust query as needed
                $stock_query_run = mysqli_query($con, $stock_query);

                if ($stock_query_run) {
                    while ($stock = mysqli_fetch_assoc($stock_query_run)) {
                        echo '<tr class="stockr-row">
                                <td>' . $stock['icode'] . '</td>
                                <td>' . $stock['description'] . '</td>
                                <td>' . $stock['brand'] . '</td>
                                <td>' . $stock['col'] . '</td>
                                <td>' . $stock['cstock'] . '</td>
                                <td>' . $stock['requirement'] . '</td>
                                <td>' . $stock['free_stock'] . '</td>
                              </tr>';
                    }
                } else {
                    echo "Error fetching stock data: " . mysqli_error($con);
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>
