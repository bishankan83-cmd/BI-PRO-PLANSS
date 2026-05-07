<?php
// Retrieve shift and date parameters from the URL
$shift = $_GET["shift"] ?? '';
$date = $_GET["date"] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Your head content goes here -->
    <title>Add Daily Production</title>
</head>
<body>
    <div class="container">
        <form id="data-form" action="add_daily_production2.php" method="post">
            <label for="inputDate">Date:</label>
            <input type="date" id="inputDate" name="inputDate" value="<?php echo htmlspecialchars($date); ?>" required>
            
            <label for="shift">Shift:</label>
            <select name="shift" id="shift">
                <option value="DAY A" <?php if ($shift == 'DAY A') echo 'selected'; ?>>DAY A</option>
                <option value="DAY B" <?php if ($shift == 'DAY B') echo 'selected'; ?>>DAY B</option>
                <option value="DAY C" <?php if ($shift == 'DAY C') echo 'selected'; ?>>DAY C</option>
                <option value="NIGHT A" <?php if ($shift == 'NIGHT A') echo 'selected'; ?>>NIGHT A</option>
                <option value="NIGHT B" <?php if ($shift == 'NIGHT B') echo 'selected'; ?>>NIGHT B</option>
                <option value="NIGHT C" <?php if ($shift == 'NIGHT C') echo 'selected'; ?>>NIGHT C</option>
            </select>
            
            <input type="submit" value="Submit">
        </form>
    </div>
    <!-- Your other HTML content goes here -->
</body>
</html>