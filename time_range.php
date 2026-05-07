
<!DOCTYPE html>
<html>
<head>
    <title>Retrieve Events</title>
    <style>
        body {
            font-family: "Cantarell", sans-serif;
        }

        h2 {
            font-family: "Open Sans", sans-serif;
        }

        form {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        label {
            font-weight: bold;
        }

        input[type="datetime-local"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            background-color: #F28018;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #FFA500; /* Change the color on hover */
        }
    </style>
</head>
<body>
    
    <form action="compound.php" method="post">
        <label for="start_time">Start Date and Time:</label>
        <input type="datetime-local" id="start_time" name="start_time" required>
        <br><br>
        <label for="end_time">End Date and Time:</label>
        <input type="datetime-local" id="end_time" name="end_time" required>
        <br><br>
        <input type="submit" value="Retrieve Events">
    </form>
</body>
</html>
