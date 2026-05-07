<?php
session_start();
include("include/config.php");

// 🔒 Check Login Status
if(!isset($_SESSION['alogin'])){
    header("location:index.php");
    exit();
}

if(isset($_POST['submit'])) {

    $fullname = mysqli_real_escape_string($con, $_POST['fullname']);
    $role = mysqli_real_escape_string($con, $_POST['role']);
    $mobilenumber = mysqli_real_escape_string($con, $_POST['mobilenumber']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $username = mysqli_real_escape_string($con, $_POST['username']);

    // 🔑 Password stored as MD5 (same as login system)
    $password = md5($_POST['password']);

    $query = mysqli_query($con, "
        INSERT INTO admin (fullname, role, mobilenumber, email, username, password, updationDate)
        VALUES ('$fullname', '$role', '$mobilenumber', '$email', '$username', '$password', NOW())
    ");

    if($query){
        echo "<script>alert('Admin User Added Successfully');</script>";
    } else {
        echo "<script>alert('Error: Unable to insert data');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Admin</title>
</head>
<body>

<h2>Add New Admin User</h2>

<form method="post">

    <label>Full Name:</label><br>
    <input type="text" name="fullname" required><br><br>

    <label>Role:</label><br>
    <select name="role" required>
        <option value="admin">Admin</option>
        <option value="account_manager">Account Manager</option>
        <option value="staff">Staff</option>
    </select><br><br>

    <label>Mobile Number:</label><br>
    <input type="text" name="mobilenumber"><br><br>

    <label>Email:</label><br>
    <input type="email" name="email"><br><br>

    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit" name="submit">Save Admin</button>

</form>

</body>
</html>
