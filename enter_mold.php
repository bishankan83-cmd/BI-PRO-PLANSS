<!DOCTYPE html>
<html>
<head>
    <title>Insert Mold Data</title>
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            background: url('atire3.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 50px;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        h2 {
            color: #000000;
            font-weight: bold;
            font-size: 24px;
            margin-bottom: 20px;
        }

        form {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            font-size: 18px;
            margin-bottom: 5px;
            color: #333333;
        }

        input[type="text"],
        input[type="number"],
        input[type="datetime-local"],
        select,
        input[type="submit"] {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-bottom: 15px;
            width: 300px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #F28018;
            color: #FFFFFF;
            border: none;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #FFA726;
        }

        .message {
            margin-top: 20px;
            font-size: 18px;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        .radio-group {
            margin-bottom: 15px;
            display: flex;
            gap: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Insert Mold Data</h2>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $hostname = 'localhost';
            $username = 'planatir_task_managemen';
            $password = 'Bishan@1919';
            $database = 'planatir_task_managemen';

            // Create connection
            $conn = new mysqli($hostname, $username, $password, $database);

            // Check connection
            if ($conn->connect_error) {
                die("<p class='message error'>Connection failed: " . $conn->connect_error . "</p>");
            }

            // Get form data
            $mold_id = $_POST['mold_id'];
            $mold_name = $_POST['mold_name'] ?: NULL; // Allow NULL for mold_name
            $size_option = $_POST['size_option'];
            $mold_size = ($size_option === 'existing') ? $_POST['existing_size'] : $_POST['new_size'];
            $per_day = ($size_option === 'new') ? (int)$_POST['per_day'] : 0;
            $availability_date = $_POST['availability_date'];
            $quantity = 0;
            $is_available = 1;

            // Begin transaction
            $conn->begin_transaction();

            try {
                // Check if mold_id already exists
                $check_mold_id_stmt = $conn->prepare("SELECT mold_id FROM mold WHERE mold_id = ?");
                $check_mold_id_stmt->bind_param("s", $mold_id);
                $check_mold_id_stmt->execute();
                $check_mold_id_result = $check_mold_id_stmt->get_result();
                if ($check_mold_id_result->num_rows > 0) {
                    throw new Exception("Mold ID '$mold_id' already exists. Please use a unique Mold ID.");
                }
                $check_mold_id_stmt->close();

                // Check if mold_size already exists in mold_p
                $check_stmt = $conn->prepare("SELECT mold_size FROM mold_p WHERE mold_size = ?");
                $check_stmt->bind_param("s", $mold_size);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $mold_p_success = true;

                // Insert into mold_p only if new size is provided and doesn't exist
                if ($size_option === 'new' && !empty($mold_size) && $check_result->num_rows === 0) {
                    if ($per_day <= 0) {
                        throw new Exception("Per Day value must be greater than 0 for new mold size");
                    }
                    $stmt_mold_p = $conn->prepare("INSERT INTO mold_p (mold_size, per_day) VALUES (?, ?)");
                    $stmt_mold_p->bind_param("si", $mold_size, $per_day);
                    $mold_p_success = $stmt_mold_p->execute();
                    $stmt_mold_p->close();
                }

                // Insert into mold table (id is auto-incremented, so exclude it)
                $stmt_mold = $conn->prepare("INSERT INTO mold (mold_id, mold_name, quantity, is_available, availability_date) VALUES (?, ?, ?, ?, ?)");
                $stmt_mold->bind_param("ssiss", $mold_id, $mold_name, $quantity, $is_available, $availability_date);
                $mold_success = $stmt_mold->execute();

                if ($mold_success && $mold_p_success) {
                    $conn->commit();
                    echo "<p class='message success'>New record created successfully</p>";
                } else {
                    throw new Exception("Error inserting data");
                }

                // Close statements
                $stmt_mold->close();
                $check_stmt->close();
            } catch (Exception $e) {
                $conn->rollback();
                echo "<p class='message error'>Error: " . $e->getMessage() . "</p>";
            }

            // Close connection
            $conn->close();
        }
        ?>

        <form method="post" action="">
            <label for="mold_id">Mold ID:</label>
            <input type="text" id="mold_id" name="mold_id" required><br>
            
            <label for="mold_name">Mold Name:</label>
            <input type="text" id="mold_name" name="mold_name"><br>
            
            <div class="radio-group">
                <label><input type="radio" name="size_option" value="existing" checked> Select Existing Size</label>
                <label><input type="radio" name="size_option" value="new"> Enter New Size</label>
            </div>

            <label for="existing_size">Existing Mold Size:</label>
            <select id="existing_size" name="existing_size">
                <?php
                // Connect to database to fetch existing mold sizes
                $conn = new mysqli('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
                if ($conn->connect_error) {
                    echo "<option value=''>Error loading sizes</option>";
                } else {
                    $result = $conn->query("SELECT DISTINCT mold_size FROM mold_p");
                    if ($result->num_rows > 0) {
                        echo "<option value=''>Select a size</option>";
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['mold_size']) . "'>" . htmlspecialchars($row['mold_size']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>No sizes available</option>";
                    }
                    $conn->close();
                }
                ?>
            </select><br>

            <label for="new_size">New Mold Size:</label>
            <input type="text" id="new_size" name="new_size"><br>

            <label for="per_day">Per Day (for new size):</label>
            <input type="number" id="per_day" name="per_day" min="1"><br>
            
            <label for="availability_date">Availability Date:</label>
            <input type="datetime-local" id="availability_date" name="availability_date" required><br>
            
            <input type="submit" value="Submit">
        </form>

        <script>
            // JavaScript to enable/disable inputs based on radio selection
            const existingRadio = document.querySelector('input[value="existing"]');
            const newRadio = document.querySelector('input[value="new"]');
            const existingSize = document.getElementById('existing_size');
            const newSize = document.getElementById('new_size');
            const perDayInput = document.getElementById('per_day');
            const moldIdInput = document.getElementById('mold_id');
            const moldNameInput = document.getElementById('mold_name');

            function toggleInputs() {
                existingSize.disabled = !existingRadio.checked;
                newSize.disabled = !newRadio.checked;
                perDayInput.disabled = !newRadio.checked;
                existingSize.required = existingRadio.checked;
                newSize.required = newRadio.checked;
                perDayInput.required = newRadio.checked;
            }

            existingRadio.addEventListener('change', toggleInputs);
            newRadio.addEventListener('change', toggleInputs);
            toggleInputs(); // Initial call to set correct state

            // Fetch mold_name based on mold_id
            moldIdInput.addEventListener('change', function() {
                const moldId = this.value;
                if (moldId) {
                    fetch('get_mold_name.php?mold_id=' + encodeURIComponent(moldId))
                        .then(response => response.json())
                        .then(data => {
                            if (data.mold_name) {
                                moldNameInput.value = data.mold_name;
                            } else {
                                moldNameInput.value = ''; // Clear if no matching mold_name
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching mold name:', error);
                            moldNameInput.value = ''; // Clear on error
                        });
                } else {
                    moldNameInput.value = ''; // Clear if mold_id is empty
                }
            });
        </script>
    </div>
</body>
</html>