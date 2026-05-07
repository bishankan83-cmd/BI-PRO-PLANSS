


<style>
.back-btn-link {
    display: inline-block;
    background-color: black;
    color: white;
    text-decoration: none;
    padding: 12px 24px;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.back-btn-link:hover {
    background-color: #333;
    transform: translateX(-5px);
}

.arrow-left {
    border: solid white;
    border-width: 0 3px 3px 0;
    display: inline-block;
    padding: 3px;
    transform: rotate(135deg);
    margin-right: 8px;
}
</style>

<!-- Option 2: Using history.back() -->
<button onclick="goBack()" class="history-btn">
    Go Back
</button>

<script>
function goBack() {
    window.history.back();
    // Fallback if no history
    setTimeout(function() {
        window.location.href = 'dashboard.php';
    }, 100);
}
</script>

<style>
.history-btn {
    background-color: black;
    color: white;
    border: 2px solid black;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s ease;
}

.history-btn:hover {
    background-color: white;
    color: black;
}
</style>


<script>
$(document).ready(function() {
    $("#fadeBackBtn").click(function() {
        $("body").fadeOut(500, function() {
            window.location.href = "dashboard.php";
        });
    });
});
</script>

<style>
.fade-btn {
    background-color: black;
    color: white;
    padding: 15px 30px;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-size: 16px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.fade-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 8px rgba(0,0,0,0.2);
}
</style>



<script>
function goToDashboardAjax() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'dashboard.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            window.location.href = 'dashboard.php';
        }
    };
    xhr.send();
}
</script>

<style>
.ajax-btn {
    background-color: black;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
    position: relative;
    overflow: hidden;
}

.ajax-btn::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.3s, height 0.3s;
}

.ajax-btn:hover::after {
    width: 200px;
    height: 200px;
}
</style>





<?php
include 'db.php';

// Check if a search term is provided
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $result = $conn->query("SELECT id, RM_code, band_size, ard FROM rm_band_data WHERE id LIKE '%$search%' OR RM_code LIKE '%$search%' OR band_size LIKE '%$search%'");
} else {
    $result = $conn->query("SELECT id, RM_code, band_size, ard FROM rm_band_data");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>rm band data</title>
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
            background-color: #f28018;
            color: black;
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
            color: white;
        }
        .btn-home {
    background-color: rgb(5, 5, 5); /* Dark blue background */
    color: white; /* White text */
    padding: 10px 20px; /* Padding around the text */
    border-radius: 5px; /* Rounded corners */
    font-weight: bold; /* Bold text */
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2); /* Shadow effect */
    transition: background-color 0.3s ease, transform 0.2s ease; /* Smooth transition */
    display: inline-block; /* Ensures inline display */
    text-align: left; /* Ensures text aligns to the left */
    margin-left: 0; /* Removes unnecessary left margin */
    width: auto; /* Optional: Ensures button adjusts to content */

            
        }
        .btn-add {
            background-color: #f28018;
            color: white; /* White text */
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            color: black;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-align: left; /* Aligns text to the left */
            
        }
        .btn-search {
            background-color:rgb(12, 12, 12);
            color: white; /* White text */
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            color:white;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease, transform 0.2s ease;
            
        }
        .btn-clear {
            background-color:rgb(169, 4, 4); /* Red background */
            color: white; /* White text */
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn-edit {
            background-color:rgb(161, 168, 163);
            color: black; /* White text */
        }
        .btn-delete {
            background-color:rgb(10, 10, 10);
        }
        .nav-buttons {
        
    display: flex;
    gap: 10px;
    justify-content: flex-start; /* Align buttons to the left */
    padding-left: 20px; /* Optional: Adds padding to create space from the left edge */
}
.search-label {
    font-weight: bold;
    margin-right: 10px;
    color: #333; /* Optional text color */
    font-size: 25px; /* Setting the font size */
}


    </style>
</head>
<body>
    <h1 style="text-align: center; color: rgb(11, 11, 11); font-size: 60px;">RM band data</h1>

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
        <th style="width: 20%;">RM Code</th>
        <th style="width: 30%;">Band Size</th>
        <th style="width: 25%;">ARD</th>
        <th style="width: 20%;">Action</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['RM_code']) ?></td>
            <td><?= htmlspecialchars($row['band_size']) ?></td>
            <td><?= htmlspecialchars($row['ard']) ?></td>
            <td style="text-align: right;">
                <a href="edit.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn-edit">Edit</a> |
                <a href="delete.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>


</body>
</html>
