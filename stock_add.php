





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculate and Insert Green Tire Weight</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Fetch description and weights when an icode is selected
            $('#icode').change(function() {
                var icode = $(this).val();
                if (icode) {
                    $.ajax({
                        url: 'get_description1.php', // Script to fetch description and weights
                        type: 'GET',
                        data: {icode: icode},
                        dataType: 'json',
                        success: function(data) {
                            if (data.success) {
                                $('#description').val(data.description);
                                $('#greenweight').val(data.greenweight);
                                $('#stgreenweight').val(data.stgreenweight);
                            } else {
                                $('#description').val("Description not found");
                                $('#greenweight').val("");
                                $('#stgreenweight').val("");
                            }
                        },
                        error: function() {
                            alert("Error fetching data.");
                        }
                    });
                } else {
                    $('#description').val('');
                    $('#greenweight').val('');
                    $('#stgreenweight').val('');
                }
            });
        });
    </script>
</head>
<body>

<h2>Calculate and Insert Green Tire Weight</h2>

<form action="" method="post">
    <label for="date">Date:</label>
    <input type="date" id="date" name="date" required><br><br>

    <label for="icode">ICode:</label>
    <select id="icode" name="icode" required>
        <option value="">Select ICode</option>
        <?php
        // Fetch all icode values for the dropdown
        $conn = new mysqli("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql_icode = "SELECT icode FROM tire_details";
        $result_icode = $conn->query($sql_icode);
        while ($row = $result_icode->fetch_assoc()) {
            echo "<option value='" . $row['icode'] . "'>" . $row['icode'] . "</option>";
        }
        ?>
    </select><br><br>

    <label for="description">Description:</label>
    <input type="text" id="description" name="description" readonly required><br><br>

    <label for="greenweight">Green Weight:</label>
    <input type="number" step="0.01" id="greenweight" name="greenweight" readonly><br><br>

    <label for="stgreenweight">ST Green Weight:</label>
    <input type="number" step="0.01" id="stgreenweight" name="stgreenweight" readonly><br><br>

    <label for="mold_id">Mold ID:</label>
    <input type="text" id="mold_id" name="mold_id" required><br><br>

    <label for="cavity_name">Cavity Name:</label>
    <select id="cavity_name" name="cavity_name" required>
        <option value="">Select Cavity Name</option>
        <?php
        // Fetch all cavity names for the dropdown
        $sql_cavity = "SELECT cavity_name FROM cavity";
        $result_cavity = $conn->query($sql_cavity);
        while ($row = $result_cavity->fetch_assoc()) {
            echo "<option value='" . $row['cavity_name'] . "'>" . $row['cavity_name'] . "</option>";
        }
        ?>
    </select><br><br>

    <label for="start_date">Start Date:</label>
    <input type="datetime-local" id="start_date" name="start_date" required><br><br>

    <label for="end_date">End Date:</label>
    <input type="datetime-local" id="end_date" name="end_date" required><br><br>

    <label for="plan">Plan:</label>
    <input type="number" id="plan" name="plan" required><br><br>

    <input type="submit" value="Submit">
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $conn->real_escape_string($_POST['date']);
    $icode = $conn->real_escape_string($_POST['icode']);
    $description = $conn->real_escape_string($_POST['description']);
    $mold_id = $conn->real_escape_string($_POST['mold_id']);
    $cavity_name = $conn->real_escape_string($_POST['cavity_name']);
    $start_date = $conn->real_escape_string($_POST['start_date']);
    $end_date = $conn->real_escape_string($_POST['end_date']);
    $plan = (int)$_POST['plan'];
    $greenweight = (float)$_POST['greenweight'];
    $stgreenweight = (float)$_POST['stgreenweight'];

    $sql_cavity = "SELECT cavity_id FROM cavity WHERE cavity_name = '$cavity_name'";
    $result_cavity = $conn->query($sql_cavity);

    if ($result_cavity->num_rows > 0) {
        $row = $result_cavity->fetch_assoc();
        $cavity_id = $row['cavity_id'];
    } else {
        echo "Error: Cavity name not found!";
        exit;
    }

    $calculated_green_tire_weight = $greenweight * $plan;
    $calculated_stgreen_tire_weight = $stgreenweight * $plan;

    $sql_insert = "INSERT INTO calculated_data_stock 
                  (date, erp, icode, description, mold_id, cavity_id, start_date, end_date, plan, calculated_green_tire_weight, calculated_stgreen_tire_weight)
                   VALUES ('$date', 1, '$icode', '$description', '$mold_id', '$cavity_id', '$start_date', '$end_date', '$plan', '$calculated_green_tire_weight', '$calculated_stgreen_tire_weight')";

    if ($conn->query($sql_insert) === TRUE) {
        echo "New record created successfully!";
    } else {
        echo "Error: " . $sql_insert . "<br>" . $conn->error;
    }

    $conn->close();
}
?>

</body>
</html>
