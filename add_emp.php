<!DOCTYPE html>
<html>
<head>
    <title>Employee Data Entry</title>
</head>
<body>
    <h2>Employee Data Entry</h2>
    <form method="post" action="add_emp.php">
        <label for="emp_code">Employee Code:</label>
        <input type="text" name="emp_code" required><br>

        <label for="emp_name">Employee Name:</label>
        <input type="text" name="emp_name" required><br>

        <label for="user_id">User ID:</label>
        <input type="text" name="user_id" required><br>

        <label for="pswd">Password:</label>
<input type="password" name="pswd" required><br>

        <label for="status">Status:</label>
        <input type="number" name="status" required><br>

        <label for="user_role">User Role:</label>
        <input type="text" name="user_role" required><br>

        <label for="emp_pro">Employee Profession:</label>
        <input type="text" name="emp_pro" required><br>

        <label for="email_id">Email ID:</label>
        <input type="email" name="email_id" required><br>

        <label for="emp_mob">Employee Mobile:</label>
        <input type="text" name="emp_mob" required><br>

        <input type="submit" value="Submit">
    </form>
</body>
</html>
<?php
// Database connection parameters
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';


// Create a database connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data from the form
$emp_code = $_POST['emp_code'];
$emp_name = $_POST['emp_name'];
$user_id = $_POST['user_id'];
$raw_password = $_POST['pswd']; // Raw password from the form input
$status = $_POST['status'];
$user_role = $_POST['user_role'];
$emp_pro = $_POST['emp_pro'];
$email_id = $_POST['email_id'];
$emp_mob = $_POST['emp_mob'];

// Hash the password
$hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

// SQL query to insert data
$sql = "INSERT INTO emp_login (emp_code, emp_name, user_id, pswd, status, user_role, emp_pro, email_id, emp_mob, created)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

// Prepare and execute the SQL statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssissss", $emp_code, $emp_name, $user_id, $hashed_password, $status, $user_role, $emp_pro, $email_id, $emp_mob);
$result = $stmt->execute();

if ($result === TRUE) {
    echo "Data inserted successfully!";
} else {
    echo "Error: " . $conn->error;
}

// Close the database connection
$stmt->close();
$conn->close();
?>