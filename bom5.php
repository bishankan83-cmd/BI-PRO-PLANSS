<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// If delete request is received
if (isset($_POST['delete'])) {
    $deleteQuery = "DELETE FROM bom_tem";
    if ($conn->query($deleteQuery) === TRUE) {
        echo "All data deleted successfully!";
    } else {
        echo "Error deleting data: " . $conn->error;
    }
    exit();
}

// If file upload request is received
if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    if (($handle = fopen($file, "r")) !== FALSE) {
        // Delete old data before inserting new data
        $conn->query("DELETE FROM bom_tem");

        // Skip header row
        fgetcsv($handle);

        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO bom_tem(Item, icode, t_size, `Item Description`, a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p, q, r, `Grand Totalcompound weight`, Color, Brand, `Green Tire weight`, PBweight) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $stmt->bind_param("sssssssssssssssssssssssssss", ...$data);
            $stmt->execute();
        }

        fclose($handle);
        echo "CSV uploaded successfully!";
        exit();
    } else {
        echo "Failed to open file.";
        exit();
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Database Operations</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        body, h1, form, button {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #e3e3ec;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #fbfbfb;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(247, 3, 3, 0.1);
            max-width: 600px;
            text-align: center;
        }
        h1 {
            font-family: Cantarell, sans-serif;
            font-size: 36px;
            margin-bottom: 20px;
        }
        .form {
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        .delete-btn {
            background-color: #8a1104;
            color: #fff;
        }
        .upload-btn {
            background-color: #01384c;
            color: #fff;
        }
        .file-input {
            display: block;
            margin: 10px auto;
        }
        .success {
            color: green;
            margin-top: 20px;
        }
        .error {
            color: red;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class='container'>
        <h1>Database Operations</h1>

        <!-- Delete All Data Button -->
        <form id="deleteForm" class="form">
            <button type="submit" class="btn delete-btn">Delete All Data</button>
        </form>

        <!-- Upload CSV File Form -->
        <form id="uploadForm" enctype="multipart/form-data" class="form">
            <input type="file" name="file" accept=".csv" class="file-input" required>
            <button type="submit" class="btn upload-btn">Import CSV to Database</button>
        </form>

        <!-- Message display -->
        <div id="message"></div>
    </div>

    <script>
        $(document).ready(function() {
            // Delete Data
            $("#deleteForm").submit(function(event) {
                event.preventDefault();
                $.post("bom5.php", { delete: true }, function(response) {
                    $("#message").html("<p class='success'>" + response + "</p>");
                });
            });

            // Upload CSV File
            $("#uploadForm").submit(function(event) {
                event.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: "bom5.php",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.includes("CSV uploaded successfully!")) {
                            window.location.href = "compare_bom.php";
                        } else {
                            $("#message").html("<p class='success'>" + response + "</p>");
                        }
                    },
                    error: function() {
                        $("#message").html("<p class='error'>Error uploading file.</p>");
                    }
                });
            });
        });
    </script>

</body>
</html>
