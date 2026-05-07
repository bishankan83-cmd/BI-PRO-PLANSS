<!DOCTYPE html>
<html>
<head>
    <title>Enter Daily Production</title>
    <!-- Your CSS styles here -->
</head>
<body>
    <h1>Enter Daily Production</h1>
    <form method="post" action="plan_edit.php">
        <label for="inputDate">Date:</label>
        <input type="date" id="inputDate" name="inputDate" required>

        <label for="shift">Shift:</label>
        <select name="shift" id="shift">
            <option value="DAY A">DAY A</option>
            <option value="DAY B">DAY B</option>
            <option value="DAY C">DAY C</option>
            <option value="NIGHT A">NIGHT A</option>
            <option value="NIGHT B">NIGHT B</option>
            <option value="NIGHT C">NIGHT C</option>
        </select>

        <!-- Hidden input field to store the JSON data -->
        <input type="hidden" name="table_data" id="table_data" value="">
      
        <!-- Submit button -->
        <input type="submit" name="submit" value="Submit">
    </form>
</body>
</html>
