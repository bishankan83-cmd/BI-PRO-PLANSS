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

    .stock-table td, .stock-table th {
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

    /* Style for totals display */
    .totals {
        text-align: center;
        margin: 20px 0;
        font-family: 'Open Sans', sans-serif;
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
        updateTotalsDisplay(); // Update totals after filtering
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
        updateTotalsDisplay(); // Update totals after filtering
    }

    function calculateTotalStockOnHand() {
        var table = document.getElementById('stock-table');
        var rows = table.getElementsByClassName('stock-row');
        var totalStockOnHand = 0;

        for (var i = 0; i < rows.length; i++) {
            if (rows[i].style.display !== "none") {
                var cells = rows[i].getElementsByTagName('td');
                var stockOnHandCell = cells[4]; // Adjust the index based on your table structure

                if (stockOnHandCell) {
                    var stockOnHandValue = parseFloat(stockOnHandCell.textContent) || 0;
                    totalStockOnHand += stockOnHandValue;
                }
            }
        }

        return totalStockOnHand.toFixed(2);
    }

    function calculateTotalRequirement() {
        var table = document.getElementById('stock-table');
        var rows = table.getElementsByClassName('stock-row');
        var totalRequirement = 0;

        for (var i = 0; i < rows.length; i++) {
            if (rows[i].style.display !== "none") {
                var cells = rows[i].getElementsByTagName('td');
                var requirementCell = cells[5]; // Adjust the index based on your table structure

                if (requirementCell) {
                    var requirementValue = parseFloat(requirementCell.textContent) || 0;
                    totalRequirement += requirementValue;
                }
            }
        }

        return totalRequirement.toFixed(2);
    }

    function calculateTotalFreeStock() {
        var table = document.getElementById('stock-table');
        var rows = table.getElementsByClassName('stock-row');
        var totalFreeStock = 0;

        for (var i = 0; i < rows.length; i++) {
            if (rows[i].style.display !== "none") {
                var cells = rows[i].getElementsByTagName('td');
                var freeStockCell = cells[6]; // Adjust the index based on your table structure

                if (freeStockCell) {
                    var freeStockValue = parseFloat(freeStockCell.textContent) || 0;
                    totalFreeStock += freeStockValue;
                }
            }
        }

        return totalFreeStock.toFixed(2);
    }

    function updateTotalsDisplay() {
        document.getElementById('totalStockOnHand').innerText = 'Total Stock On Hand: ' + calculateTotalStockOnHand();
        document.getElementById('totalRequirement').innerText = 'Total Requirement: ' + calculateTotalRequirement();
        document.getElementById('totalFreeStock').innerText = 'Total Free Stock: ' + calculateTotalFreeStock();
    }

    // Call updateTotalsDisplay on page load to display initial totals
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

    <div class="container">
        <div class="search-form">
            <input type="text" id="icodeInput" placeholder="Enter Item Code, Description, Brand, Or Colour" oninput="searchStock()">
            <label for="brandSelect">Select Brand:</label>
            <select id="brandSelect" onchange="filterByBrandAndColor()">
                <option value="">All Brands</option>
                <option value="Brand1">Brand1</option>
                <option value="Brand2">Brand2</option>
                <!-- Add more brand options as needed -->
            </select>

            <label for="colorSelect">Select Color:</label>
            <select id="colorSelect" onchange="filterByBrandAndColor()">
                <option value="">All Colors</option>
                <option value="Color1">Color1</option>
                <option value="Color2">Color2</option>
                <!-- Add more color options as needed -->
            </select>
        </div>

        <table id="stock-table" class="stock-table">
            <thead>
                <tr class="header">
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
                <!-- Example data rows (replace with your data) -->
                <tr class="stock-row">
                    <td>IC001</td>
                    <td>Description1</td>
                    <td>Brand1</td>
                    <td>Color1</td>
                    <td>100</td>
                    <td>50</td>
                    <td>50</td>
                    <td>IC001</td>
                    <td>Description1</td>
                    <td>Brand1</td>
                    <td>Color1</td>
                    <td>100</td>
                    <td>50</td>
                    <td>50</td>

                </tr>
                <!-- Add more rows as needed -->
            </tbody>
        </table>

        <div class="totals">
            <p id="totalStockOnHand">Total Stock On Hand: 0</p>
            <p id="totalRequirement">Total Requirement: 0</p>
            <p id="totalFreeStock">Total Free Stock: 0</p>
        </div>
    </div>
</body>
</html>
