<?php
// You can add any PHP logic here if needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two Button Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        .button-container {
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            font-size: 16px;
            margin: 10px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
        }
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        .btn-secondary {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Welcome to the Page with Two Buttons!</h1>

    <div class="button-container">
        <!-- First Button: A Simple Action -->
        <button class="btn btn-primary" onclick="window.location.href='bom5.php'">Use Excel to edit</button>
        </div>

        <!-- Second Button: Redirect to Another Page -->
        <button class="btn btn-secondary" onclick="window.location.href='bom_edit.php'">Edit Bom</button>
    </div>
</body>
</html>
