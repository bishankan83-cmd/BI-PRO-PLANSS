<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mold Details</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
    <style>
        :root {
            --primary-color: #f28018;
            --secondary-color: #000000;
            --background-color: #f5f5f5;
            --white: #ffffff;
            --gray-200: #e5e7eb;
            --gray-700: #374151;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--background-color);
            color: var(--gray-700);
            line-height: 1.5;
        }

        .container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .header {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .filter-section {
            background-color: var(--white);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--secondary-color);
        }

        select {
            padding: 10px;
            border: 2px solid var(--gray-200);
            border-radius: 6px;
            background-color: white;
            width: 100%;
            transition: border-color 0.3s ease;
        }

        select:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .search-button, .clear-button {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .search-button {
            background-color: var(--primary-color);
            color: white;
        }

        .clear-button {
            background-color: var(--secondary-color);
            color: white;
        }

        .search-button:hover, .clear-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .table-container {
            background-color: var(--white);
            border-radius: 8px;
            overflow: auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        th {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            color: white;
            font-weight: bold;
        }

        tr:hover {
            background-color: rgba(242, 128, 24, 0.05);
        }

        .icon {
            margin-right: 5px;
            color: var(--primary-color);
        }

        /* Select2 Custom Styling */
        .select2-container {
            width: 100% !important;
        }
        
        .select2-container--default .select2-selection--single {
            height: 42px;
            padding: 6px;
            border: 2px solid var(--gray-200);
            border-radius: 6px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px;
        }
        
        .select2-dropdown {
            border: 2px solid var(--primary-color);
            border-radius: 6px;
        }
        
        .select2-search__field {
            padding: 8px !important;
            border-radius: 4px !important;
        }
        
        .select2-results__option {
            padding: 8px 12px;
        }
        
        .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary-color) !important;
        }

        .back-button {
            display: inline-block;
            background-color: var(--secondary-color);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            margin-bottom: 15px;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #333333;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
            }

            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <?php
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Database connection details
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $dbname = "planatir_task_managemen";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection and handle errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch distinct values for dropdown filters
    $icodes = $conn->query("SELECT DISTINCT icode FROM mold_list WHERE icode != 0 ORDER BY icode");
    $descriptions = $conn->query("SELECT DISTINCT description FROM tire_details ORDER BY description");
    $mold_ids = $conn->query("SELECT DISTINCT mold_id FROM mold_list ORDER BY mold_id");
    $mold_sizes = $conn->query("SELECT DISTINCT mold_size FROM mold_list ORDER BY mold_size");
    $per_days = $conn->query("SELECT DISTINCT per_day FROM mold_list ORDER BY per_day");
    // Modified to include 0 as a valid brand option
    $Brand = $conn->query("SELECT DISTINCT IFNULL(Brand, '0') AS Brand FROM tire_details ORDER BY Brand");

    // Initialize filters
    $icode_filter = isset($_GET['icode']) ? $_GET['icode'] : '';
    $description_filter = isset($_GET['description']) ? $_GET['description'] : '';
    $mold_id_filter = isset($_GET['mold_id']) ? $_GET['mold_id'] : '';
    $mold_size_filter = isset($_GET['mold_size']) ? $_GET['mold_size'] : '';
    $per_day_filter = isset($_GET['per_day']) ? $_GET['per_day'] : '';
    $Brand_filter = isset($_GET['Brand']) ? $_GET['Brand'] : '';

    // Base SQL query - modified to use LEFT JOIN and handle NULL/0 brand values
    $sql = "SELECT 
        ml.icode, 
        td.description, 
        IFNULL(td.Brand, '0') AS Brand, 
        GROUP_CONCAT(DISTINCT ml.mold_id ORDER BY ml.mold_id ASC SEPARATOR ', ') AS mold_ids,
        ml.mold_size,
        ml.per_day,
        COUNT(DISTINCT ml.mold_id) AS per_mold_capacity,
        COUNT(DISTINCT ml.mold_id) * ml.per_day AS total_capacity
    FROM mold_list ml
    LEFT JOIN tire_details td ON ml.icode = td.icode
    WHERE ml.icode != 0";

    // Apply filters
    $conditions = [];
    $parameters = [];

    if (!empty($icode_filter)) {
        $conditions[] = "ml.icode = ?";
        $parameters[] = $icode_filter;
    }
    if (!empty($description_filter)) {
        $conditions[] = "td.description = ?";
        $parameters[] = $description_filter;
    }
    if (!empty($mold_id_filter)) {
        $conditions[] = "ml.mold_id = ?";
        $parameters[] = $mold_id_filter;
    }
    if (!empty($mold_size_filter)) {
        $conditions[] = "ml.mold_size = ?";
        $parameters[] = $mold_size_filter;
    }
    if (!empty($per_day_filter)) {
        $conditions[] = "ml.per_day = ?";
        $parameters[] = $per_day_filter;
    }
    if (!empty($Brand_filter)) {
        // Modified to handle '0' brand filter specially
        if ($Brand_filter === '0') {
            $conditions[] = "(td.Brand IS NULL OR td.Brand = '0' OR td.Brand = '')";
        } else {
            $conditions[] = "td.Brand = ?";
            $parameters[] = $Brand_filter;
        }
    }

    if (count($conditions) > 0) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    $sql .= " GROUP BY ml.icode, td.description, IFNULL(td.Brand, '0'), ml.mold_size, ml.per_day";

    // Prepare and execute statement
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        if (count($parameters) > 0) {
            $types = str_repeat('s', count($parameters));
            $stmt->bind_param($types, ...$parameters);
        }
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        die("Prepare failed: " . $conn->error);
    }
    ?>

    <div class="container">
        <div class="header">
            <h1><i class="fas fa-industry"></i> Mold Capacity Details Dashboard</h1>
        </div>

        <a href="dashboard.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="filter-section">
            <form method="get" id="filterForm">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="icode"><i class="fas fa-barcode icon"></i> Icode</label>
                        <select name="icode" id="icode" class="searchable-select">
                            <option value="">Select Icode</option>
                            <?php while ($row = $icodes->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($row['icode']) ?>" <?= $icode_filter == $row['icode'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['icode']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="description"><i class="fas fa-align-left icon"></i> Description</label>
                        <select name="description" id="description" class="searchable-select">
                            <option value="">Select Description</option>
                            <?php while ($row = $descriptions->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($row['description']) ?>" <?= $description_filter == $row['description'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['description']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="mold_id"><i class="fas fa-fingerprint icon"></i> Mold ID</label>
                        <select name="mold_id" id="mold_id" class="searchable-select">
                            <option value="">Select Mold ID</option>
                            <?php while ($row = $mold_ids->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($row['mold_id']) ?>" <?= $mold_id_filter == $row['mold_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['mold_id']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="mold_size"><i class="fas fa-ruler icon"></i> Mold Size</label>
                        <select name="mold_size" id="mold_size" class="searchable-select">
                            <option value="">Select Mold Size</option>
                            <?php while ($row = $mold_sizes->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($row['mold_size']) ?>" <?= $mold_size_filter == $row['mold_size'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['mold_size']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="per_day"><i class="fas fa-calendar-day icon"></i> Per Day</label>
                        <select name="per_day" id="per_day" class="searchable-select">
                            <option value="">Select Per Day</option>
                            <?php while ($row = $per_days->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($row['per_day']) ?>" <?= $per_day_filter == $row['per_day'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['per_day']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="Brand"><i class="fas fa-trademark icon"></i> Brand</label>
                        <select name="Brand" id="Brand" class="searchable-select">
                            <option value="">Select Brand</option>
                            <?php while ($row = $Brand->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($row['Brand']) ?>" <?= $Brand_filter == $row['Brand'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['Brand']) === '0' ? 'No Brand' : htmlspecialchars($row['Brand']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="search-button">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button type="button" class="clear-button" id="clearFiltersBtn">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-barcode"></i> Icode</th>
                        <th><i class="fas fa-align-left"></i> Description</th>
                        <th><i class="fas fa-fingerprint"></i> Mold IDs</th>
                        <th><i class="fas fa-layer-group"></i> Mold Qty</th>
                        <th><i class="fas fa-ruler"></i> Mold Size</th>
                        <th><i class="fas fa-industry"></i> Per Mold Capacity</th>
                        <th><i class="fas fa-chart-line"></i> Per Day Capacity</th>
                        <th><i class="fas fa-trademark"></i> Brand</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['icode']) ?></td>
                                <td><?= htmlspecialchars($row['description'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['mold_ids']) ?></td>
                                <td><?= htmlspecialchars($row['per_mold_capacity']) ?></td>
                                <td><?= htmlspecialchars($row['mold_size']) ?></td>
                                <td><?= htmlspecialchars($row['per_day']) ?></td>
                                <td><?= htmlspecialchars($row['total_capacity']) ?></td>
                                <td><?= htmlspecialchars($row['Brand']) === '0' ? 'No Brand' : htmlspecialchars($row['Brand']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No results found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                window.location.href = 'planbuttoon.php';
            }
        });

        $(document).ready(function() {
            // Initialize Select2 for all searchable selects
            $('.searchable-select').select2({
                placeholder: 'Search...',
                allowClear: true
            });

            // Handle clear filters button
            $('#clearFiltersBtn').click(function() {
                // Reset all select2 dropdowns
                $('.searchable-select').val(null).trigger('change');
                // Submit the form
                $('#filterForm').submit();
            });
            
            // Add hover effect to table rows
            $('tbody tr').hover(
                function() {
                    $(this).css('background-color', 'rgba(242, 128, 24, 0.1)');
                },
                function() {
                    $(this).css('background-color', '');
                }
            );

            // Smooth scrolling for table container
            $('.table-container').css('scroll-behavior', 'smooth');

            // Add focus visual feedback for select elements
            $('select').on('focus', function() {
                $(this).css('box-shadow', '0 0 0 2px rgba(242, 128, 24, 0.2)');
            }).on('blur', function() {
                $(this).css('box-shadow', 'none');
            });
        });
    </script>
</body>
</html>
<?php
// Close connections
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>