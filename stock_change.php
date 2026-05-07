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

    // Filter for icode
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

// Output filtered data in a table
echo "<table border='1'>";
echo "<tr><th>SQ</th><th>SerialNumber</th><th>icode</th><th>Description</th><th>LocationNumber</th><th>Month</th><th>Year</th><th>Brand</th><th>Color</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row["SQ"] . "</td>";
    echo "<td>" . $row["SerialNumber"] . "</td>";
    echo "<td>" . $row["icode"] . "</td>";
    echo "<td>" . $row["Description"] . "</td>";
    echo "<td>" . $row["LocationNumber"] . "</td>";
    echo "<td>" . $row["Month"] . "</td>";
    echo "<td>" . $row["Year"] . "</td>";
    echo "<td>" . $row["Brand"] . "</td>";
    echo "<td>" . $row["Color"] . "</td>";
    echo "</tr>";
}
echo "</table>";
} else {
    echo "0 results";
}

// Close connection
mysqli_close($conn);
?>
