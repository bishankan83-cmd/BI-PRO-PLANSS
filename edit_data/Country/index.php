<?php
include 'db.php';

// Check if a search term is provided
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $result = $conn->query("SELECT id, erp, country, pattern FROM country WHERE id LIKE '%$search%' OR country LIKE '%$search%' OR erp LIKE '%$search%' OR pattern LIKE '%$search%'");
} else {
    $result = $conn->query("SELECT id, erp, country, pattern FROM country");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Country</title>
    <style>
        body {
            background-color: #f0f0f0;
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
            background-color: rgb(121, 137, 155);
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        button, a {
            padding: 5px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            color: BLACK;
        }
        .btn-home {
            background-color: rgb(32, 52, 73);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: inline-block;
            text-align: left;
            margin-left: 0;
            width: auto;
        }
        .btn-add {
            background-color: rgb(245, 86, 6);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-align: left;
        }
        .btn-search {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn-clear {
            background-color: #e63946;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn-edit {
            background-color: #28a745;
        }
        .btn-delete {
            background-color: #dc3545;
        }
        .nav-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-start;
            padding-left: 20px;
        }
        .search-label {
            font-weight: bold;
            margin-right: 10px;
            color: #333;
            font-size: 25px;
        }
    </style>
</head>
<body>
    <h1 style="text-align: center; color: rgb(11, 11, 11); font-size: 60px;">Country</h1>

    <nav>
        <div class="nav-buttons">
            <button class="btn-home" onclick="window.location.href='index.php';">Home</button>
            <button class="btn-add" onclick="window.location.href='add.php';">Add New</button>
        </div>
    </nav>

    <hr style="margin: 30px 0; border: 0; border-top: 1px solid #ccc;">

    <form method="GET" action="index.php" style="display: flex; justify-content: center;">
        <div style="display: flex; gap: 20px;">
            <label for="search" class="search-label">Search:</label>
            <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" style="padding: 10px;">
            <button type="submit" class="btn-search">Search</button>
            <a href="index.php" class="btn-clear">Clear</a>
        </div>
    </form>

    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ccc;">

    <table>
        <tr>
            <th style="width: 10%;">ID</th>
            <th style="width: 20%;">ERP</th>
            <th style="width: 30%;">Country</th>
            <th style="width: 30%;">Pattern</th>
            <th style="width: 10%;">Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['erp']) ?></td>
                <td><?= htmlspecialchars($row['country']) ?></td>
                <td><?= htmlspecialchars($row['pattern']) ?></td>
                <td style="text-align: right;">
                    <a href="edit.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn-edit">Edit</a> |
                    <a href="delete.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>