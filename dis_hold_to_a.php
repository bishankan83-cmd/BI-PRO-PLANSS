<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selected Stocks</title>
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
            margin-top: 20px; /* Added margin to separate from the container */
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
            transition: background-color 0.3s; /* Add a smooth transition for the background color change */
        }

        .button-container button:hover {
            background-color: #333333; /* Change the background color on hover */
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
</head>
<body>

<div class="container">
    <h2>Selected Stocks</h2>

    <div class="search-form">
        <input type="text" id="searchInput" placeholder="Search...">
    </div>

    <table class="stock-table">
        <thead>
            <tr>
                <th>SQ</th>
                <th>Serial Number</th>
                <th>Item Code</th>
                <th>Description</th>
                <th>Location Number</th>
                <th>Month</th>
                <th>Year</th>
                <th>Brand</th>
                <th>Color</th>
            </tr>
        </thead>
        <tbody id="stockTableBody">
        <?php
        // Database connection parameters
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

        // SQL query to fetch all data from selected_stocks2 table
        $sql = "SELECT * FROM selected_stocks2";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr class='stock-row'>
                        <td>".$row['SQ']."</td>
                        <td>".$row['SerialNumber']."</td>
                        <td>".$row['icode']."</td>
                        <td>".$row['Description']."</td>
                        <td>".$row['LocationNumber']."</td>
                        <td>".$row['Month']."</td>
                        <td>".$row['Year']."</td>
                        <td>".$row['Brand']."</td>
                        <td>".$row['Color']."</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='9'>0 results</td></tr>";
        }

        // Close connection
        $conn->close();
        ?>
        </tbody>
    </table>
</div>

<script>
    // JavaScript for real-time multi-column search filtering
    document.getElementById("searchInput").addEventListener("input", function() {
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.querySelector(".stock-table");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows, and hide those that do not match the search query
        for (i = 0; i < tr.length; i++) {
            // Loop through all table columns within each row
            var rowVisible = false;
            for (j = 0; j < tr[i].cells.length; j++) {
                td = tr[i].getElementsByTagName("td")[j];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        rowVisible = true;
                    }
                }
            }
            if (rowVisible) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    });
</script>

</body>
</html>
