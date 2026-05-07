<?php
include 'db.php';

$result = $conn->query("SELECT * FROM bom_new");

// Check if a search term is provided
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $result = $conn->query("SELECT * FROM bom_new WHERE icode LIKE '%$search%'");
} else {
    $result = $conn->query("SELECT * FROM bom_new");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>bom_new</title>
</head>
<body>
    <h1 style="text-align: center; color:rgb(11, 11, 11); font-size: 60px;">BOM New</h1>

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
            width: 200%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            width: 10px;
            height: 50px;
            text-align: center;
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
            <input Type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" style="padding: 10px;">
            <button Type="submit" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px;">Search</button>
            <a href="index.php" style="padding: 10px 20px; background-color: #e63946; color: white; border: none; border-radius: 5px; text-decoration: none;">Clear</a>
        </div>
        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ccc;">
    </form>

    <br></br>
    <table>
        <tr>
            <th>Item</th>
            <th>icode</th>
            <th>t_size</th>
            <th>Item Description</th>
            <th>a</th>
            <th>b</th>
            <th>c</th>
            <th>d</th>
            <th>e</th>
            <th>f</th>
            <th>g</th>
            <th>h</th>
            <th>i</th>
            <th>j</th>
            <th>k</th>
            <th>l</th>
            <th>m</th>
            <th>n</th>
            <th>o</th>
            <th>p</th>
            <th>q</th>
            <th>r</th>
            <th>Grand Total compound weight</th>
            <th>Color</th>
            <th>Brand</th>
            <th>Green Tire weight</th>
            <th>PBweight</th>
            <th>id</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['Item'] ?></td>
            <td><?= $row['icode'] ?></td>
            <td><?= $row['t_size'] ?></td>
            <td><?= $row['Item_Description'] ?></td>
            <td><?= $row['a'] ?></td>
            <td><?= $row['b'] ?></td>
            <td><?= $row['c'] ?></td>
            <td><?= $row['d'] ?></td>
            <td><?= $row['e'] ?></td>
            <td><?= $row['f'] ?></td>
            <td><?= $row['g'] ?></td>
            <td><?= $row['h'] ?></td>
            <td><?= $row['i'] ?></td>
            <td><?= $row['j'] ?></td>
            <td><?= $row['k'] ?></td>
            <td><?= $row['l'] ?></td>
            <td><?= $row['m'] ?></td>
            <td><?= $row['n'] ?></td>
            <td><?= $row['o'] ?></td>
            <td><?= $row['p'] ?></td>
            <td><?= $row['q'] ?></td>
            <td><?= $row['r'] ?></td>
            <td><?= $row['Grand_Totalcompound_weight'] ?></td>
            <td><?= $row['Color'] ?></td>
            <td><?= $row['Brand'] ?></td>
            <td><?= $row['Green_Tire_weight'] ?></td>
            <td><?= $row['PBweight'] ?></td>
            <td><?= $row['id'] ?></td>
            <td>
                <a href="edit.php?id=<?php echo $row['id']; ?>" style="padding: 5px; background-color: #28a745; color: white; border: none; border-radius: 5px; text-decoration: none;">Edit</a> |
                <a href="delete.php?icode=<?= $row['icode'] ?>" style="padding: 5px; background-color: #dc3545; color: white; border: none; border-radius: 5px; text-decoration: none;" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
