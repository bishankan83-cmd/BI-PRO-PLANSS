<?php
include 'db.php';


$result = $conn->query("SELECT * FROM stock");

// Check if a search term is provided
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $result = $conn->query("SELECT * FROM stock WHERE icode LIKE '%$search%'");
} else {
    $result = $conn->query("SELECT * FROM stock");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real Stock</title>
</head>
<body>
    <h1 style="text-align: center; color:rgb(11, 11, 11); font-size: 60px;">Stock</h1>

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
        <th>ID</th>
        <th>icode</th>
        <th>t_size</th>
        <th>brand</th>
        <th>col</th>
        <th>rim</th>
        <th>gweight</th>
        <th>cstock</th>
        <th>Details</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['icode'] ?></td>
        <td><?= $row['t_size'] ?></td>
        <td><?= $row['brand'] ?></td>
        <td><?= $row['col'] ?></td>
        <td><?= $row['rim'] ?></td>
        <td><?= $row['gweight'] ?></td>
        <td><?= $row['cstock'] ?></td>
        <td>
    <a href="edit.php?id=<?= $row['id'] ?>" style="padding: 5px; background-color: #28a745; color: white; border: none; border-radius: 5px; text-decoration: none;">Edit</a> |
    <a href="delete.php?id=<?= $row['id'] ?>" style="padding: 5px; background-color: #dc3545; color: white; border: none; border-radius: 5px; text-decoration: none;" onclick="return confirm('Are you sure?')">Delete</a>
</td>

    </tr>
    <?php endwhile; ?>
</table>





