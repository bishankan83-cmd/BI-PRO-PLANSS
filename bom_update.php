<?php
// Include database connection
require_once 'db_connection.php'; // Make sure this file contains the correct DB connection setup

// Check if mold_size is set via POST request
if (isset($_POST['mold_size'])) {
    $mold_size = $_POST['mold_size'];

    // Prepare the SQL query to fetch band sizes based on mold size
    $query = "SELECT band_size FROM band_sizes WHERE mold_size = ?"; 
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $mold_size); // Bind the mold_size value to the query
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any results are returned
    if ($result->num_rows > 0) {
        // Output the band size options
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . $row['band_size'] . "'>" . $row['band_size'] . "</option>";
        }
    } else {
        // Output a default message if no results are found
        echo "<option value=''>No band sizes found</option>";
    }

    // Close the prepared statement
    $stmt->close();
} else {
    // Return a default message if mold_size is not set
    echo "<option value=''>Select Mold Size First</option>";
}

// Close the database connection
$conn->close();
?>
