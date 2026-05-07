
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirect Button</title>
</head>
<body>
    <!-- Redirects to the edit_data.php page in the public folder -->
    <button onclick="location.href='/edit_data.php'">BACK</button>
</body>
</html>




<?php
include 'db.php';


$result = $conn->query("SELECT * FROM 	tire");

// Check if a search term is provided
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $result = $conn->query("SELECT * FROM 	tire WHERE icode LIKE '%$search%'");
} else {
    $result = $conn->query("SELECT * FROM 	tire");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tire</title>
</head>
<body>
    <h1 style="text-align: center; color:rgb(11, 11, 11); font-size: 60px;">Tire</h1>

    <nav>
    <div style="display: flex; gap: 10px;">
  
    <button style="padding: 10px 20px; background-color:rgb(32, 52, 73); color: white; border: none; border-radius: 5px; cursor: pointer;" onclick="window.location.href='index.php';">Home</button>
    <button style="padding: 10px 20px; background-color:rgb(245, 86, 6); color: white; border: none; border-radius: 5px; cursor: pointer;" onclick="window.location.href='add.php';">Add New</button>
</div>
    </nav>
    <hr style="margin: 30px 0; border: 0; border-top: 1px solid #ccc;">
<style>
        body {
            background-color: #f0f0f0; /* Light grey background color */
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color:rgb(121, 137, 155);
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>

<form method="GET" action="index.php" style="display: flex; justify-content: center;">
<div style="display: flex; gap: 20px;">
<label for="search" style="font-weight: bold; font-size: 30px;">Search:</label>
        <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" style="padding: 10px;">
        <button type="submit" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px;">Search</button>
        <a href="index.php" style="padding: 10px 20px; background-color: #e63946; color: white; border: none; border-radius: 5px; text-decoration: none;">Clear</a>
    </div>
    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ccc;">
</form>
<br></br>
<table>
    <tr>
        <th>icode</th>
        <th>description</th>
        <th>time_taken</th>
        <th>is_available</th>
        <th>availability_date</th>
        <th>cuing_group_id</th>
        <th>cuing_group_name</th>
        <th>Action</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['icode'] ?></td>
        <td><?= $row['description'] ?></td>
        <td><?= $row['time_taken'] ?></td>
        <td><?= $row['is_available'] ?></td>
        <td><?= $row['availability_date'] ?></td>
        <td><?= $row['cuing_group_id'] ?></td>
        <td><?= $row['cuing_group_name'] ?></td>
        <td>
    <a href="edit.php?icode=<?= $row['icode'] ?>" style="padding: 5px; background-color: #28a745; color: white; border: none; border-radius: 5px; text-decoration: none;">Edit</a> |
    <a href="delete.php?icode=<?= $row['icode'] ?>" style="padding: 5px; background-color: #dc3545; color: white; border: none; border-radius: 5px; text-decoration: none;" onclick="return confirm('Are you sure?')">Delete</a>
</td>

    </tr>
    <?php endwhile; ?>
</table>





