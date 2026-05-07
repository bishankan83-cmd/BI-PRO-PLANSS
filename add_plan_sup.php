<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Daily Plan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-lg p-4">
            <h2 class="text-center mb-4">Update Daily Plan</h2>
            <form method="post">
                <div class="d-flex justify-content-center">
                    <button type="submit" name="update_data" class="btn btn-primary btn-lg">Update Data</button>
                </div>
            </form>
            <div class="mt-3">
                <?php
                $servername = "localhost";
                $username = "planatir_task_managemen";
                $password = "Bishan@1919";
                $dbname = "planatir_task_managemen";

                $conn = new mysqli($servername, $username, $password, $dbname);
                if ($conn->connect_error) {
                    die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
                }

                if (isset($_POST['update_data'])) {
                    $delete_sql = "DELETE FROM daily_plan_tem";
                    if ($conn->query($delete_sql) === TRUE) {
                        echo "<div class='alert alert-success'>Old data deleted successfully.</div>";
                    } else {
                        echo "<div class='alert alert-danger'>Error deleting old data: " . $conn->error . "</div>";
                    }

                    $insert_sql = "INSERT INTO daily_plan_tem (id, Date, Shift, Icode, MoldName, CavityName, Plan) 
                                   SELECT id, Date, Shift, Icode, MoldName, CavityName, Plan FROM daily_plan";
                    
                    if ($conn->query($insert_sql) === TRUE) {
                        echo "<div class='alert alert-success'>New data inserted successfully!</div>";
                    } else {
                        echo "<div class='alert alert-danger'>Error inserting new data: " . $conn->error . "</div>";
                    }
                }
                $conn->close();
                ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
