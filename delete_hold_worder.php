<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// DELETE FUNCTION
if (isset($_POST['delete'])) {
    if (!empty($_POST['selected_value']) && !empty($_POST['delete_by'])) {
        $deleteBy = $_POST['delete_by'];
        $deleteValue = $_POST['selected_value'];

        $deleteQuery = "DELETE FROM worder72 WHERE $deleteBy = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("s", $deleteValue);
        $stmt->execute();

        echo "<script>alert('Records deleted successfully!'); window.location.href='delete_hold_worder.php';</script>";
    } else {
        echo "<script>alert('Please select a value to delete.');</script>";
    }
}

// FETCH DISTINCT ERP & REF VALUES
$erpResults = $conn->query("SELECT DISTINCT erp FROM worder72 WHERE erp IS NOT NULL AND erp != ''");
$refResults = $conn->query("SELECT DISTINCT ref FROM worder72 WHERE ref IS NOT NULL AND ref != ''");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Worder72 Records</title>
</head>
<body>

<h2>Delete Worder72 Records</h2>
<form method="POST">
    <label for="delete_by">Select Field:</label>
    <select name="delete_by" id="delete_by" required>
        <option value="erp">ERP</option>
        <option value="ref">Reference</option>
    </select>

    <label for="selected_value">Select Value:</label>
    <select name="selected_value" id="selected_value" required>
        <option value="">-- Select --</option>
        <?php
        while ($erpRow = $erpResults->fetch_assoc()) {
            echo "<option value='" . $erpRow['erp'] . "' class='erp'>" . $erpRow['erp'] . "</option>";
        }
        while ($refRow = $refResults->fetch_assoc()) {
            echo "<option value='" . $refRow['ref'] . "' class='ref' style='display: none;'>" . $refRow['ref'] . "</option>";
        }
        ?>
    </select>

    <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete all matching records?')">Delete</button>
</form>

<script>
    document.getElementById("delete_by").addEventListener("change", function () {
        let selectedField = this.value;
        let options = document.getElementById("selected_value").options;
        
        for (let i = 0; i < options.length; i++) {
            options[i].style.display = "none";
        }

        for (let i = 0; i < options.length; i++) {
            if (options[i].classList.contains(selectedField)) {
                options[i].style.display = "block";
            }
        }

        document.getElementById("selected_value").value = "";
    });
</script>

</body>
</html>

<?php
$conn->close();
?>
