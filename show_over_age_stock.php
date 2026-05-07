<?php
// Database connection
require_once 'db_connection.php';

// Default filter values
$filter_year = isset($_GET['year']) ? $_GET['year'] : '';
$filter_brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$filter_month = isset($_GET['month']) ? $_GET['month'] : '';

// Build query with optional filters
$query = "SELECT * FROM over_age WHERE 1=1";
$params = [];

if (!empty($filter_year)) {
    $query .= " AND year = ?";
    $params[] = $filter_year;
}

if (!empty($filter_brand)) {
    $query .= " AND brand LIKE ?";
    $params[] = "%$filter_brand%";
}

if (!empty($filter_month)) {
    $query .= " AND month = ?";
    $params[] = $filter_month;
}

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get distinct years and brands for filter dropdowns
$years_query = $conn->query("SELECT DISTINCT year FROM over_age ORDER BY year");
$brands_query = $conn->query("SELECT DISTINCT brand FROM over_age ORDER BY brand");
$months_query = $conn->query("SELECT DISTINCT month FROM over_age ORDER BY month");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Over Age Tire Tracking</title>
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .filter-container {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .filter-container select, .filter-container input {
            margin-right: 10px;
            padding: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #F28018;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Over Age Tire Tracking</h1>
    
    <div class="filter-container">
        <form method="GET">
            <select name="year">
                <option value="">All Years</option>
                <?php while($year = $years_query->fetch_assoc()): ?>
                    <option value="<?= $year['year'] ?>" <?= $filter_year == $year['year'] ? 'selected' : '' ?>>
                        <?= $year['year'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <select name="brand">
                <option value="">All Brands</option>
                <?php while($brand = $brands_query->fetch_assoc()): ?>
                    <option value="<?= $brand['brand'] ?>" <?= $filter_brand == $brand['brand'] ? 'selected' : '' ?>>
                        <?= $brand['brand'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <select name="month">
                <option value="">All Months</option>
                <?php while($month = $months_query->fetch_assoc()): ?>
                    <option value="<?= $month['month'] ?>" <?= $filter_month == $month['month'] ? 'selected' : '' ?>>
                        <?= $month['month'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit">Apply Filters</button>
            <button type="button" onclick="window.location.href='show_over_age_stock.php'">Reset</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Serial Number</th>
              
                <th>Tire Code</th>
                <th>Description</th>
                <th>Number of Tyres</th>
                <th>Year</th>
                <th>Brand</th>
                <th>Color</th>
                <th>Month</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['serial_number']) ?></td>
                    
                    <td><?= htmlspecialchars($row['tyre_code']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= htmlspecialchars($row['number_of_tires']) ?></td>
                    <td><?= htmlspecialchars($row['year']) ?></td>
                    <td><?= htmlspecialchars($row['brand']) ?></td>
                    <td><?= htmlspecialchars($row['color']) ?></td>
                    <td><?= htmlspecialchars($row['month']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>