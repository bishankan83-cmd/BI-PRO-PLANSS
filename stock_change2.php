
<style>
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


</style>


<div class="button-container" style="margin-top: 20px; margin-bottom: 20px;">
    <button>
        <a href="dashboard.php" style="text-decoration: none; color: #FFFFFF;">Click To Dashboard</a>
    </button>
</div>

<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Example: Delete specific rows from selected_stocks
$sqlDelete = "DELETE FROM selected_stocks";

if (mysqli_query($conn, $sqlDelete)) {
    //echo "Selected rows deleted successfully.";
} else {
   // echo "Error deleting rows: " . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);
?>








<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management</title>
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
        }
        
        .filter-form button {
            background-color: #F28018;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
        }
        .filter-form button:hover {
            background-color: #e06800;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #F28018;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .insert-button {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
        }
        .insert-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<?php
// Database connection parameters
$servername = "localhost";  // MySQL server hostname (usually "localhost")
$username = "planatir_task_managemen"; // MySQL username
$password = "Bishan@1919"; // MySQL password
$dbname = "planatir_task_managemen";   // MySQL database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize variables for filter values
$filterSQ = "";
$filterSerialNumber = "";
$filterIcode = "";
$filterDescription = "";
$filterLocationNumber = "";
$filterMonth = "";
$filterYear = "";
$filterBrand = "";
$filterColor = "";

// Process form submission for filtering
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assign filter values from form inputs
    $filterSQ = $_POST['filterSQ'];
    $filterSerialNumber = $_POST['filterSerialNumber'];
    $filterIcode = $_POST['filterIcode'];
    $filterDescription = $_POST['filterDescription'];
    $filterLocationNumber = $_POST['filterLocationNumber'];
    $filterMonth = $_POST['filterMonth'];
    $filterYear = $_POST['filterYear'];
    $filterBrand = $_POST['filterBrand'];
    $filterColor = $_POST['filterColor'];
}

// SQL query to select filtered data from the stocks table
$sql = "SELECT * FROM `stocks` WHERE 1=1";

// Add filters based on user input
if (!empty($filterSQ)) {
    $sql .= " AND `SQ` = '" . mysqli_real_escape_string($conn, $filterSQ) . "'";
}
if (!empty($filterSerialNumber)) {
    $sql .= " AND `SerialNumber` LIKE '%" . mysqli_real_escape_string($conn, $filterSerialNumber) . "%'";
}
if (!empty($filterIcode)) {
    $sql .= " AND `icode` LIKE '%" . mysqli_real_escape_string($conn, $filterIcode) . "%'";
}
if (!empty($filterDescription)) {
    $sql .= " AND `Description` LIKE '%" . mysqli_real_escape_string($conn, $filterDescription) . "%'";
}
if (!empty($filterLocationNumber)) {
    $sql .= " AND `LocationNumber` LIKE '%" . mysqli_real_escape_string($conn, $filterLocationNumber) . "%'";
}
if (!empty($filterMonth)) {
    $sql .= " AND `Month` LIKE '%" . mysqli_real_escape_string($conn, $filterMonth) . "%'";
}
if (!empty($filterYear)) {
    $sql .= " AND `Year` = '" . mysqli_real_escape_string($conn, $filterYear) . "'";
}
if (!empty($filterBrand)) {
    $sql .= " AND `Brand` LIKE '%" . mysqli_real_escape_string($conn, $filterBrand) . "%'";
}
if (!empty($filterColor)) {
    $sql .= " AND `Color` LIKE '%" . mysqli_real_escape_string($conn, $filterColor) . "%'";
}

// Execute query
$result = mysqli_query($conn, $sql);

// Check if there are any results
if (mysqli_num_rows($result) > 0) {
    // Output filter form with select options
    echo "<div class='filter-form'>";
    echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
    
    // Filter for SQ
    echo "<label for='filterSQ'>Filter SQ:</label>";
    echo "<select name='filterSQ'>";
    echo "<option value=''>-- Select SQ --</option>";
    $sqlSQ = "SELECT DISTINCT `SQ` FROM `stocks`";
    $resultSQ = mysqli_query($conn, $sqlSQ);
    while ($rowSQ = mysqli_fetch_assoc($resultSQ)) {
        $selected = ($rowSQ['SQ'] == $filterSQ) ? "selected" : "";
        echo "<option value='" . htmlspecialchars($rowSQ['SQ']) . "' " . $selected . ">" . htmlspecialchars($rowSQ['SQ']) . "</option>";
    }
    echo "</select>";

    // Filter for SerialNumber
    echo "<label for='filterSerialNumber'>Filter SerialNumber:</label>";
    echo "<select name='filterSerialNumber'>";
    echo "<option value=''>-- Select SerialNumber --</option>";
    $sqlSerialNumber = "SELECT DISTINCT `SerialNumber` FROM `stocks`";
    $resultSerialNumber = mysqli_query($conn, $sqlSerialNumber);
    while ($rowSerialNumber = mysqli_fetch_assoc($resultSerialNumber)) {
        $selected = ($rowSerialNumber['SerialNumber'] == $filterSerialNumber) ? "selected" : "";
        echo "<option value='" . htmlspecialchars($rowSerialNumber['SerialNumber']) . "' " . $selected . ">" . htmlspecialchars($rowSerialNumber['SerialNumber']) . "</option>";
    }
    echo "</select>";

    // Filter for Icode
    echo "<label for='filterIcode'>Filter Icode:</label>";
    echo "<select name='filterIcode'>";
    echo "<option value=''>-- Select Icode --</option>";
    $sqlIcode = "SELECT DISTINCT `icode` FROM `stocks`";
    $resultIcode = mysqli_query($conn, $sqlIcode);
    while ($rowIcode = mysqli_fetch_assoc($resultIcode)) {
        $selected = ($rowIcode['icode'] == $filterIcode) ? "selected" : "";
        echo "<option value='" . htmlspecialchars($rowIcode['icode']) . "' " . $selected . ">" . htmlspecialchars($rowIcode['icode']) . "</option>";
    }
    echo "</select>";

    // Filter for Description
    echo "<label for='filterDescription'>Filter Description:</label>";
    echo "<select name='filterDescription'>";
    echo "<option value=''>-- Select Description --</option>";
    $sqlDescription = "SELECT DISTINCT `Description` FROM `stocks`";
    $resultDescription = mysqli_query($conn, $sqlDescription);
    while ($rowDescription = mysqli_fetch_assoc($resultDescription)) {
        $selected = ($rowDescription['Description'] == $filterDescription) ? "selected" : "";
        echo "<option value='" . htmlspecialchars($rowDescription['Description']) . "' " . $selected . ">" . htmlspecialchars($rowDescription['Description']) . "</option>";
    }
    echo "</select>";

    // Filter for LocationNumber
    echo "<label for='filterLocationNumber'>Filter LocationNumber:</label>";
    echo "<select name='filterLocationNumber'>";
    echo "<option value=''>-- Select LocationNumber --</option>";
    $sqlLocationNumber = "SELECT DISTINCT `LocationNumber` FROM `stocks`";
    $resultLocationNumber = mysqli_query($conn, $sqlLocationNumber);
    while ($rowLocationNumber = mysqli_fetch_assoc($resultLocationNumber)) {
        $selected = ($rowLocationNumber['LocationNumber'] == $filterLocationNumber) ? "selected" : "";
        echo "<option value='" . htmlspecialchars($rowLocationNumber['LocationNumber']) . "' " . $selected . ">" . htmlspecialchars($rowLocationNumber['LocationNumber']) . "</option>";
    }
    echo "</select>";

    // Filter for Month
    echo "<label for='filterMonth'>Filter Month:</label>";
    echo "<select name='filterMonth'>";
    echo "<option value=''>-- Select Month --</option>";
    $sqlMonth = "SELECT DISTINCT `Month` FROM `stocks`";
    $resultMonth = mysqli_query($conn, $sqlMonth);
    while ($rowMonth = mysqli_fetch_assoc($resultMonth)) {
        $selected = ($rowMonth['Month'] == $filterMonth) ? "selected" : "";
        echo "<option value='" . htmlspecialchars($rowMonth['Month']) . "' " . $selected . ">" . htmlspecialchars($rowMonth['Month']) . "</option>";
    }
    echo "</select>";

    // Filter for Year
    echo "<label for='filterYear'>Filter Year:</label>";
    echo "<select name='filterYear'>";
    echo "<option value=''>-- Select Year --</option>";
    $sqlYear = "SELECT DISTINCT `Year` FROM `stocks`";
    $resultYear = mysqli_query($conn, $sqlYear);
    while ($rowYear = mysqli_fetch_assoc($resultYear)) {
        $selected = ($rowYear['Year'] == $filterYear) ? "selected" : "";
        echo "<option value='" . htmlspecialchars($rowYear['Year']) . "' " . $selected . ">" . htmlspecialchars($rowYear['Year']) . "</option>";
    }
    echo "</select>";

    // Filter for Brand
    echo "<label for='filterBrand'>Filter Brand:</label>";
    echo "<select name='filterBrand'>";
    echo "<option value=''>-- Select Brand --</option>";
    $sqlBrand = "SELECT DISTINCT `Brand` FROM `stocks`";
    $resultBrand = mysqli_query($conn, $sqlBrand);
    while ($rowBrand = mysqli_fetch_assoc($resultBrand)) {
        $selected = ($rowBrand['Brand'] == $filterBrand) ? "selected" : "";
        echo "<option value='" . htmlspecialchars($rowBrand['Brand']) . "' " . $selected . ">" . htmlspecialchars($rowBrand['Brand']) . "</option>";
    }
    echo "</select>";

    // Filter for Color
    echo "<label for='filterColor'>Filter Color:</label>";
    echo "<select name='filterColor'>";
    echo "<option value=''>-- Select Color --</option>";
    $sqlColor = "SELECT DISTINCT `Color` FROM `stocks`";
    $resultColor = mysqli_query($conn, $sqlColor);
    while ($rowColor = mysqli_fetch_assoc($resultColor)) {
        $selected = ($rowColor['Color'] == $filterColor) ? "selected" : "";
        echo "<option value='" . htmlspecialchars($rowColor['Color']) . "' " . $selected . ">" . htmlspecialchars($rowColor['Color']) . "</option>";
    }
    echo "</select>";

    echo "<button type='submit'>Apply Filters</button>";
    echo "</form>";
    echo "</div>";

    // Form for selecting rows to insert into another table
    echo "<div class='result-table'>";
    echo "<form method='post' action='insert_selected.php'>";
    echo "<table>";
    echo "<tr><th>Select</th><th>SQ</th><th>SerialNumber</th><th>icode</th><th>Description</th><th>LocationNumber</th><th>Month</th><th>Year</th><th>Brand</th><th>Color</th><th>Action</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td><input type='checkbox' name='selectedRows[]' value='" . $row["SQ"] . "'></td>";
        echo "<td>" . $row["SQ"] . "</td>";
        echo "<td>" . $row["SerialNumber"] . "</td>";
        echo "<td>" . $row["icode"] . "</td>";
        echo "<td>" . $row["Description"] . "</td>";
        echo "<td>" . $row["LocationNumber"] . "</td>";
        echo "<td>" . $row["Month"] . "</td>";
        echo "<td>" . $row["Year"] . "</td>";
        echo "<td>" . $row["Brand"] . "</td>";
        echo "<td>" . $row["Color"] . "</td>";
        echo "<td><button class='insert-button' type='submit' name='insertRow' value='" . $row["SQ"] . "'>Insert</button></td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</form>";
    echo "</div>";
} else {
    echo "<p>No records found.</p>";
}

// Close connection
mysqli_close($conn);
?>

</body>
</html>
