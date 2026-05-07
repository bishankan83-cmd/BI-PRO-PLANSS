
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan Data Management</title>
    <style>
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


        

.button-container button {
    background-color: #F28018;
    color: #FFFFFF;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.button-container button:hover {
    background-color: orange;
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
        document.addEventListener('input', function (event) {
            if (event.target.name === 'new_additional_data[]') {
                updateAdditionalDataTotal();
            }
        });

        function updateAdditionalDataTotal() {
            var inputs = document.getElementsByName('new_additional_data[]');
            var total = 0;

            for (var i = 0; i < inputs.length; i++) {
                if (!isNaN(parseFloat(inputs[i].value))) {
                    total += parseFloat(inputs[i].value);
                }
            }

            document.getElementById('additionalDataTotal').innerText = total;
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                moveFocus(event);
            }
        });

        function moveFocus(event) {
            var inputs = document.getElementsByName('new_additional_data[]');

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

        function redirectToAnotherPage() {
            // Redirect to another PHP page
            window.location.href = 'add_daily_pro23.php'; // Replace 'another_page.php' with your actual page name
        }
    </script>


<?php
error_reporting(0);
// Connection details
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

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Loop through the posted data to update records
    foreach ($_POST['new_plan_value'] as $recordID => $newPlanValue) {
        $newAdditionalData = $_POST['new_additional_data'][$recordID];

        // Update query
        $sql = "UPDATE daily_plan_data1
                SET Plan = ?, AdditionalData = ?
                WHERE ID = ?";

        // Prepare and bind the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $newPlanValue, $newAdditionalData, $recordID);

        // Execute the statement
        $stmt->execute();
        $stmt->close();
    }

    echo "Records updated successfully";
}

$sql = "SELECT dpd.id, dpd.*, tire_details.Description
        FROM daily_plan_data1 dpd
        INNER JOIN tire_details ON dpd.Icode = tire_details.Icode
        ORDER BY dpd.id ASC";

$result = $conn->query($sql);

$additionalDataTotal = 0; // Initialize total before the loop
$sumTotal = 0; // Initialize total for Plan values

if ($result->num_rows > 0) {
    // Display the data in an HTML table
    echo "<form method='post' action=''>";

    echo "<table border='1'>
            <tr>
                <th>Press Name</th>
                <th>Icode</th>
                <th>Description</th>
                <th>Plan</th>
                <th>Actual</th>
            </tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['CavityName']}</td>
                <td>{$row['Icode']}</td>
                <td>{$row['Description']}</td>
                <td><input type='text' name='new_plan_value[{$row['ID']}]' value='{$row['Plan']}'></td>
                <td><input type='text' name='new_additional_data[{$row['ID']}]' value='{$row['AdditionalData']}'></td>
            </tr>";

        // Calculate the sum of the "AdditionalData" values
        $additionalDataTotal += (float)$row['AdditionalData'];

        // Calculate the sum of the "Plan" values
        $sumTotal += (float)$row['Plan'];
    }

    echo "<tr><td colspan='3'></td><td>Total Plan: $sumTotal</td><td>Total Additional Data: <span id='additionalDataTotal'>$additionalDataTotal</span></td></tr>"; // Display total below the "Plan" column

    echo "</table>";
    echo "<input type='submit' value='Update'>";
    echo "</form>";

  // Add a button to go to another PHP page
echo "<div class='button-container'><button onclick='redirectToAnotherPage()'>Click To Next</button></div>";


} else {
    echo "No records found";
}

// Close the connection
$conn->close();
?>
