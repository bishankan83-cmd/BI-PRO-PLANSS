<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data to Excel</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        button { padding: 10px 20px; background: green; color: white; border: none; cursor: pointer; font-size: 16px; }
        button:hover { background: darkgreen; }
    </style>
</head>
<body>
    <h2>Export Tyre Data to Excel</h2>
    <form action="export.php" method="POST">
        <button type="submit" name="export">Download Excel</button>
    </form>
</body>
</html>
