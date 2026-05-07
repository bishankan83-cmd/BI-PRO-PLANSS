<?php

$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create mold_mapping table
$sqlCreateTable = "CREATE TABLE IF NOT EXISTS mold_mapping (
                    mold_id INT,
                    mold_name VARCHAR(255),
                    cavity_id INT,
                    cavity_name VARCHAR(255),
                    icode VARCHAR(255)
                  )";

if ($conn->query($sqlCreateTable) === TRUE) {
    echo "Table mold_mapping created successfully or already exists.<br>";

    // Delete existing records in the mold_mapping table
    $deleteQuery = "DELETE FROM mold_mapping";
    $conn->query($deleteQuery);

    // Retrieve and display mold_name, along with cavity_id, cavity_name, and icode, matching each mold_id
    $sqlSelect = "SELECT m.mold_id, m.mold_name, c.cavity_id, c.cavity_name, p.icode
                  FROM mold m
                  JOIN plannew p ON m.mold_id = p.mold_id
                  JOIN cavity c ON p.cavity_id = c.cavity_id";

    $resultSelect = $conn->query($sqlSelect);

    if ($resultSelect->num_rows > 0) {
        // Output data of each row
        while ($rowSelect = $resultSelect->fetch_assoc()) {
            echo "mold_id: " . $rowSelect["mold_id"] . " | mold_name: " . $rowSelect["mold_name"] . " | cavity_id: " . $rowSelect["cavity_id"] . " | cavity_name: " . $rowSelect["cavity_name"] . " | icode: " . $rowSelect["icode"] . "<br>";

            // Insert data into the mold_mapping table
            $sqlInsert = "INSERT INTO mold_mapping (mold_id, mold_name, cavity_id, cavity_name, icode)
                          VALUES ('" . $rowSelect["mold_id"] . "', '" . $rowSelect["mold_name"] . "', '" . $rowSelect["cavity_id"] . "', '" . $rowSelect["cavity_name"] . "', '" . $rowSelect["icode"] . "')";

            if ($conn->query($sqlInsert) === TRUE) {
                echo "Data inserted into the mold_mapping table successfully<br>";
            } else {
                echo "Error inserting data into the mold_mapping table: " . $conn->error . "<br>";
            }
        }
    } else {
        echo "0 results";
    }
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Close the connection
$conn->close();
?>
