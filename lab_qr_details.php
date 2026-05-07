<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Details</title>
    <style>
.button {
    display: inline-block;
    padding: 10px 20px;
    font-size: 16px;
    color: white;
    background-color: black; /* Bootstrap primary color */
    text-decoration: none;
    border-radius: 5px;
}

.button:hover {
    background-color: #F28018; /* Darker shade for hover effect */
}
</style>

</head>
<body>
    <h1>Compound QR Details</h1>
   


<a href="export_excel_lab.php" class="button">Export Filtered Data to Excel</a>


<a href="export_excel_lab2.php" class="button">Export Filtered Data to Excel date range</a>







<style>
    /* Your CSS styles */
    body {
        background-color: #f0f0f0;
        font-family: 'Cantarell', sans-serif; /* Set the default font for the entire page */
        text-align: center;
    }

    h4 {
        color: #F28018;
        font-family: 'Cantarell', sans-serif; /* Apply the Cantarell font to the h4 element */
    }

    .container {
        margin: 0 auto;
        
        padding: 20px;
        background-color: #f0f0f0;
        font-family: 'Cantarell', sans-serif; /* Use Cantarell as the default font */
    }

    .stock-table {
        width: 100%;
        border-collapse: collapse;
    }

    .stock-table th,
    .stock-table td {
        border: 1px solid #000000;
        padding: 10px;
        text-align: left;
    }

    .stock-table th {
        background-color: #F28018;
        color: #000000;
        font-family: 'Cantarell', sans-serif;
        font-weight: bold;
    }

    .filter-input {
        width: 100%;
        padding: 5px;
        box-sizing: border-box;
    }
</style>

    <script>
        function filterTable() {
            const filters = Array.from(document.querySelectorAll('.filter-select')).map(select => select.value.toLowerCase());
            const rows = document.querySelectorAll(".stock-table tbody tr");

            rows.forEach(row => {
                const cells = Array.from(row.querySelectorAll("td"));
                const match = cells.every((cell, index) => {
                    return filters[index] === "" || cell.textContent.toLowerCase().includes(filters[index]);
                });
                row.style.display = match ? "" : "none";
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const selects = document.querySelectorAll('.filter-select');
            selects.forEach(select => {
                select.addEventListener('change', filterTable);
            });
        });
    </script>
</head>
<body>
    <div class="container">
       

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

        // SQL query to select data from the current month and the past two months
        $sql = "SELECT * FROM another_table_name1 
                WHERE inputDate >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                ORDER BY inputDate DESC";

        $result = $conn->query($sql);

        // Check if there are rows in the result set
        if ($result->num_rows > 0) {
            // Prepare arrays for distinct values
            $shifts = [];
            $compoundNames = [];
            $supervisors = [];
            $batches = [];
            $pallets = [];
            $staffNames = [];

            // Fetch all data to create distinct filters
            while ($row = $result->fetch_assoc()) {
                if (!in_array($row["shift"], $shifts)) $shifts[] = $row["shift"];
                if (!in_array($row["compound_name"], $compoundNames)) $compoundNames[] = $row["compound_name"];
                if (!in_array($row["description"], $supervisors)) $supervisors[] = $row["description"];
                if (!in_array($row["batch"], $batches)) $batches[] = $row["batch"];
                if (!in_array($row["pallet"], $pallets)) $pallets[] = $row["pallet"];
                if (!in_array($row["staff_name"], $staffNames)) $staffNames[] = $row["staff_name"];
            }

            // Display the table with filters
            echo "<table class='stock-table'>
                    <thead>
                        <tr>
                            <th>ID<br><select class='filter-select'><option value=''>All</option>";
            foreach ($result as $row) {
                echo "<option value='" . $row["id"] . "'>" . $row["id"] . "</option>";
            }
            echo "</select></th>
                            <th>Job Number<br><select class='filter-select'><option value=''>All</option>";
            foreach ($result as $row) {
                echo "<option value='" . $row["serial_number"] . "'>" . $row["serial_number"] . "</option>";
            }
            echo "</select></th>
                            <th>Input Date<br><select class='filter-select'><option value=''>All</option>";
            foreach ($result as $row) {
                echo "<option value='" . $row["inputDate"] . "'>" . $row["inputDate"] . "</option>";
            }
            echo "</select></th>
                            <th>Shift<br><select class='filter-select'><option value=''>All</option>";
            foreach ($shifts as $shift) {
                echo "<option value='" . $shift . "'>" . $shift . "</option>";
            }
            echo "</select></th>
                            <th>Compound Name<br><select class='filter-select'><option value=''>All</option>";
            foreach ($compoundNames as $compound) {
                echo "<option value='" . $compound . "'>" . $compound . "</option>";
            }
            echo "</select></th>
                            <th>Mixing Supervisor<br><select class='filter-select'><option value=''>All</option>";
            foreach ($supervisors as $supervisor) {
                echo "<option value='" . $supervisor . "'>" . $supervisor . "</option>";
            }
            echo "</select></th>
                            <th>Compound Code<br><select class='filter-select'><option value=''>All</option>";
            foreach ($result as $row) {
                echo "<option value='" . $row["cstock"] . "'>" . $row["cstock"] . "</option>";
            }
            echo "</select></th>
                            <th>Batch<br><select class='filter-select'><option value=''>All</option>";
            foreach ($batches as $batch) {
                echo "<option value='" . $batch . "'>" . $batch . "</option>";
            }
            echo "</select></th>
                            <th>Pallet<br><select class='filter-select'><option value=''>All</option>";
            foreach ($pallets as $pallet) {
                echo "<option value='" . $pallet . "'>" . $pallet . "</option>";
            }
            echo "</select></th>
                            <th>Created At<br><select class='filter-select'><option value=''>All</option>";
            foreach ($result as $row) {
                echo "<option value='" . $row["created_at"] . "'>" . $row["created_at"] . "</option>";
            }
            echo "</select></th>
                            <th>Weight<br><select class='filter-select'><option value=''>All</option>";
            foreach ($result as $row) {
                echo "<option value='" . $row["weight"] . "'>" . $row["weight"] . "</option>";
            }
            echo "</select></th>
                            <th>Quality Approved<br><select class='filter-select'><option value=''>All</option>";
            foreach ($result as $row) {
                echo "<option value='" . $row["quality_approved"] . "'>" . $row["quality_approved"] . "</option>";
            }
            echo "</select></th>
                            <th>Expire Date<br><select class='filter-select'><option value=''>All</option>";
            foreach ($result as $row) {
                echo "<option value='" . $row["expire_date"] . "'>" . $row["expire_date"] . "</option>";
            }
            echo "</select></th>
                            <th>Lab Staff Name<br><select class='filter-select'><option value=''>All</option>";
            foreach ($staffNames as $staffName) {
                echo "<option value='" . $staffName . "'>" . $staffName . "</option>";
            }
            echo "</select></th>
                            <th>SG Value<br><select class='filter-select'><option value=''>All</option>";
            foreach ($result as $row) {
                echo "<option value='" . $row["sg_value"] . "'>" . $row["sg_value"] . "</option>";
            }
            echo "</select></th>
                            <th>Hardness<br><select class='filter-select'><option value=''>All</option>";
            foreach ($result as $row) {
                echo "<option value='" . $row["hardness"] . "'>" . $row["hardness"] . "</option>";
            }
            echo "</select></th>
                        </tr>
                    </thead>
                    <tbody>";

            // Reset result pointer to fetch data for the table body
            $result->data_seek(0);
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row["id"] . "</td>
                        <td>" . $row["serial_number"] . "</td>
                        <td>" . $row["inputDate"] . "</td>
                        <td>" . $row["shift"] . "</td>
                        <td>" . $row["compound_name"] . "</td>
                        <td>" . $row["description"] . "</td>
                        <td>" . $row["cstock"] . "</td>
                        <td>" . $row["batch"] . "</td>
                        <td>" . $row["pallet"] . "</td>
                        <td>" . $row["created_at"] . "</td>
                        <td>" . $row["weight"] . "</td>
                        <td>" . $row["quality_approved"] . "</td>
                        <td>" . $row["expire_date"] . "</td>
                        <td>" . $row["staff_name"] . "</td>
                        <td>" . $row["sg_value"] . "</td>
                        <td>" . $row["hardness"] . "</td>
                    </tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>No records found.</p>";
        }

        // Close the database connection
        $conn->close();
        ?>
    </div>
</body>
</html>
