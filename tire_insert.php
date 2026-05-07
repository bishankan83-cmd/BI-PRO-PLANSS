<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Data</title>
</head>
<body>
    <h1>Insert Data into Tire Table</h1>
    <form action="insert_tire2.php" method="post">
        <label for="icode">Code:</label>
        <input type="text" id="icode" name="icode" required><br><br>

        <label for="description">Description:</label>
        <input type="text" id="description" name="description" required><br><br>

        <label for="time_taken">Time Taken (minutes):</label>
        <input type="number" id="time_taken" name="time_taken"><br><br>

        <label for="is_available">Is Available:</label>
        <input type="checkbox" id="is_available" name="is_available" value="1" checked><br><br>

        <label for="availability_date">Availability Date:</label>
        <input type="datetime-local" id="availability_date" name="availability_date"><br><br>

        <label for="cuing_group_id">Cuing Group ID:</label>
        <input type="number" id="cuing_group_id" name="cuing_group_id"><br><br>

        <label for="cuing_group_name">Cuing Group Name:</label>
        <input type="text" id="cuing_group_name" name="cuing_group_name"><br><br>

        <input type="submit" value="Insert Data">
    </form>
</body>
</html>
