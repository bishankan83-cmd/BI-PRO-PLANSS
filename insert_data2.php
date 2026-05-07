<?php
session_start(); // Start the session to access session variables

// Check if the results are stored in the session
if (isset($_SESSION['results'])) {
    $results = $_SESSION['results'];
} else {
    $results = array(); // Initialize an empty array if no results are found
}

// ... (Rest of your code for displaying the results)

?>

<!DOCTYPE html>
<html>
<head>
    <title>Display Plan Data</title>
    <!-- Add your CSS styles here if needed -->
</head>
<body>
    <div class="container">
        <h1>Plan Data</h1>
        
        <!-- Display the results from the session variable -->
        <table>
            <!-- Your table headers as before -->
            <thead>
                <tr>
                    <th>Tire Id</th>
                    <th>Description</th>
                    <th>Mold Name</th>
                    <th>Cavity Name</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Order Quantity</th>
                    <th>Plan</th>
                    <th>Time Given</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result) { ?>
                    <tr>
                        <td><?php echo $result['icode']; ?></td>
                        <td><?php echo $result['description']; ?></td>
                        <td><?php echo $result['mold_name']; ?></td>
                        <td><?php echo $result['cavity_name']; ?></td>
                        <td><?php echo $result['found_start_time']; ?></td>
                        <td><?php echo $result['found_end_time']; ?></td>
                        <td><?php echo $workOrders[$result['icode']]['total_quantity']; ?></td>
                        <td><?php echo round($result['tobe']); ?></td>
                        <td><?php echo $result['time_given']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <!-- Your CSS styles as before -->
</body>
</html>
