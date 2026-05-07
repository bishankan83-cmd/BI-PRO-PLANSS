<!DOCTYPE html>
<html>
<head>
    <title>Select iCode</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
</head>
<body>
    <h1>Select iCode</h1>
    <form>
        <label for="icode">Select iCode:</label>
        <select name="icode" id="icode">
            <!-- Populate this dropdown with your available iCode options from your database -->
            <?php
            // Establish a database connection
            $servername = "localhost";
            $username = "planatir_task_managemen";
            $password = "Bishan@1919";
            $dbname = "planatir_task_managemen";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "SELECT DISTINCT icode FROM bom_new";
            $result = $conn->query($sql);

            if ($result === false) {
                die("Query failed: " . $conn->error);
            }

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row["icode"] . "'>" . $row["icode"] . "</option>";
                }
            }

            $conn->close();
            ?>
        </select>

        
    </form>

   


    <div id="info-container">
        <!-- Information will be displayed here -->
    </div>

    <button id="insertDataBtn">Insert Data</button>

    <script>
        $(document).ready(function() {
            $('#icode').change(function() {
                var selectedIcode = $(this).val();

                $.ajax({
                    type: 'POST',
                    url: 'get_info.php',
                    data: { 'icode': selectedIcode },
                    success: function(response) {
                        $('#info-container').html(response);
                    }
                });
            });

            $('#insertDataBtn').click(function() {
                var selectedIcode = $('#icode').val();
                var baseBatchNo = $('#baseBatchNo').val();
            var cussionBatchNo = $('#cussionBatchNo').val();
            var treadBatchNo = $('#treadBatchNo').val();

                $.ajax({
                    type: 'POST',
                    url: 'insert_data.php',
                    data: {
                        'icode': selectedIcode,
                        'baseBatchNo': baseBatchNo,
                    'cussionBatchNo': cussionBatchNo,
                    'treadBatchNo': treadBatchNo,
                    },
                    success: function(response) {
                        alert(response);
                    }
                });
            });
        });
    </script>
</body>
</html>
