<style>
        /* Your CSS styles */

        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
            font-family: 'Cantarell', sans-serif;
        }

        h1 {
            color: #F28018;
            font-family: 'Cantarell', sans-serif;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input[type="date"],
        select {
            padding: 10px;
            width: 200px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
            cursor: pointer;
        }

        table th {
            background-color: #F28018;
            color: #000000;
            font-weight: bold;
        }

        .btn-container {
            margin-top: 20px;
            text-align: center;
        }

        input[type="button"],
        input[type="submit"] {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="button"]:hover,
        input[type="submit"]:hover {
            background-color: #333333;
        }

        table {
            border-collapse: collapse;
        }

        td {
            background-color: #fff; /* Set the default background color */
        transition: background-color 0.5s ease; /* Add transition for smooth color change */
        }

       
    .highlighted-row td {
        background-color: #f4a659; /* Set the color for the highlighted table row */
    }
    </style>

 
<script>

document.addEventListener('DOMContentLoaded', function() {
        var inputFields = document.querySelectorAll('input[type="text"]');

        // Add event listeners to input fields
        inputFields.forEach(function(input) {
            input.addEventListener('focus', function() {
                // Remove highlight from previously highlighted rows
                var previouslyHighlightedRows = document.querySelectorAll('.highlighted-row');
                previouslyHighlightedRows.forEach(function(row) {
                    row.classList.remove('highlighted-row');
                });

                // Highlight the parent row
                var parentRow = input.closest('tr');
                parentRow.classList.add('highlighted-row');
            });
            input.addEventListener('blur', function() {
                // Remove highlight when the input loses focus
                var parentRow = input.closest('tr');
                parentRow.classList.remove('highlighted-row');
            });
        });
    });

document.addEventListener('DOMContentLoaded', function () {
        // Get all cells of the second column (index 1)
        var cells = document.querySelectorAll('table td:nth-child(2)');

        // Attach click event listener to each cell
        cells.forEach(function (cell) {
            cell.addEventListener('click', function () {
                // Update the content in the display area with the clicked cell's content
                document.getElementById('displayArea').innerText = cell.innerText;
            });
        });
    });

    document.addEventListener('input', function (event) {
        if (event.target.name === 'additionalData[]') {
            updateAdditionalDataTotal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            moveFocus(event);
        }
    });

    function moveFocus(event) {
        var inputs = document.getElementsByName('additionalData[]');

        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i] === document.activeElement) {
                if (event.key === 'Enter') {
                    // Move to the next input on Enter
                    var nextIndex = i === inputs.length - 1 ? 0 : i + 1;
                    inputs[nextIndex].focus();
                    event.preventDefault(); // Prevent the default Enter key behavior (submitting the form)
                } else if (event.key === 'ArrowDown' && i < inputs.length - 1) {
                    // Move down on ArrowDown
                    inputs[i + 1].focus();
                    event.preventDefault();
                } else if (event.key === 'ArrowUp' && i > 0) {
                    // Move up on ArrowUp
                    inputs[i - 1].focus();
                    event.preventDefault();
                }
                break;
            }
        }
    }

    function updateAdditionalDataTotal() {
        var inputs = document.getElementsByName('additionalData[]');
        var total = 0;

        for (var i = 0; i < inputs.length; i++) {
            if (!isNaN(parseFloat(inputs[i].value))) {
                total += parseFloat(inputs[i].value);
            }
        }

        document.getElementById('additionalDataTotal').innerText = total;
    }
</script>
<?php

error_reporting(E_ALL);

// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedDate = $_POST["inputDate"];
    $selectedShift = $_POST["shift"];

    // Connect to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM daily_plan WHERE Date = '$selectedDate' AND Shift = '$selectedShift'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
    echo "<form method='post'>";
    echo "<table border='1'>";
    echo "<tr>
          <th>Press</th>
          <th>Icode</th>
          <th>Description</th>
          <th>Plan</th>
          <th>Actual</th>
        </tr>";

    $sumTotal = 0; // Variable to store the sum total
    $additionalDataTotal = 0; // Variable to store the sum total of additional data

    while ($row = $result->fetch_assoc()) {
        // Fetch description from tire_details based on Icode
        $icode = $row['Icode'];
        $descriptionQuery = "SELECT Description FROM tire_details WHERE Icode = '$icode'";
        $descriptionResult = $conn->query($descriptionQuery);
        $descriptionRow = $descriptionResult->fetch_assoc();
        $description = $descriptionRow['Description'];
    
        $planValue = $row['Plan'];
    
        echo "<tr>
        <td>{$row['CavityName']}</td>
        <td>{$row['Icode']}</td>
        <td>{$description}</td>
        <td>{$planValue}</td>
        <td><input type='text' name='additionalData[]' value='{$planValue}' class='actual-input'></td>
        
        <input type='hidden' name='dates[]' value='{$row['Date']}'>
        <input type='hidden' name='shifts[]' value='{$row['Shift']}'>
        <input type='hidden' name='icodes[]' value='{$row['Icode']}'>
        <input type='hidden' name='moldNames[]' value='{$row['MoldName']}'>
        <input type='hidden' name='cavityNames[]' value='{$row['CavityName']}'>
        <input type='hidden' name='plans[]' value='{$row['Plan']}'>
    </tr>";
    

        // Calculate the sum of the "Plan" values
        $sumTotal += $row['Plan'];
    }

    echo "<tr><td colspan='3'></td><td>Total Plan: $sumTotal</td><td>Total Additional Data: <span id='additionalDataTotal'>$additionalDataTotal</span></td></tr>"; // Display total below the "Plan" column

   // echo "<tr><td colspan='4'></td><td>Total Additional Data: <span id='additionalDataTotal'>$additionalDataTotal</span></td></tr>"; // Display total below the "Actual" column

    echo "</table>";

    echo "<input type='submit' name='submitData' value='Submit Data'>";
    echo "</form>";

} else {
    echo "Please click next button.<br>";
    echo "<button onclick='goToAnotherPage()'>NEXT</button>"; 
    // JavaScript function to go back to the previous page
    echo "<script>
                function goBack() {
                    window.history.back();
                }
                function goToAnotherPage() {
                    window.location.href = 'add_daily_production3.php'; // Replace 'another_page.php' with the URL of the desired page
                }
              </script>";
}


    if (isset($_POST['submitData'])) {
        $additionalData = $_POST['additionalData'];
        $dates = $_POST['dates'];
        $shifts = $_POST['shifts'];
        $icodes = $_POST['icodes'];
        $moldNames = $_POST['moldNames'];
        $cavityNames = $_POST['cavityNames'];
        $plans = $_POST['plans'];

        foreach ($additionalData as $index => $data) {
            $escapedData = $conn->real_escape_string($data);
            $date = $conn->real_escape_string($dates[$index]);
            $shift = $conn->real_escape_string($shifts[$index]);
            $icode = $conn->real_escape_string($icodes[$index]);
            $moldName = $conn->real_escape_string($moldNames[$index]);
            $cavityName = $conn->real_escape_string($cavityNames[$index]);
            $plan = $conn->real_escape_string($plans[$index]);

            // Insert data into 'another_table' (Modify the table name and fields accordingly)
            $sql = "INSERT INTO daily_plan_data1 (Date, Shift, Icode, MoldName, CavityName, Plan, AdditionalData)
                    VALUES ('$date', '$shift', '$icode', '$moldName', '$cavityName', '$plan', '$escapedData')";

            if ($conn->query($sql) !== TRUE) {
                // Handle insertion error if needed
            }

            // Update the total of additional data
            $additionalDataTotal += (int)$escapedData;
        }
    }
    
    //header('Location: check_daily_production.php');
    //exit();
    header('Location: add_daily_production3.php');
    exit();
}
?>


</body>
</html>
