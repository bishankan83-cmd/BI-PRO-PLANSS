



<!DOCTYPE html>
<html>
<head>
    <title>Filter bcompound Data</title>
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            background: url('atire3.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #000000;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white background */
            border-radius: 50px;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            width: 400px;
        }

        h5 {
            font-weight: bold;
            font-size: 24px;
            font-family: 'Cantarell Bold', sans-serif;
            text-align: center;
            margin-bottom: 20px;
        }

        .alert {
            background-color: #FFD700;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        form {
            text-align: left;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
        }

        input[type="date"],
        input[type="text"] {
            width: 91%;
            padding: 10px;
            border: 1px solid #000000;
            border-radius: 4px;
            font-family: 'Open Sans', sans-serif;
        }

        input[type="submit"] {
            background-color: #F28018;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h5>Filter bcompound Data</h5>
        <form action="pfilter_bcompound.php" method="post">

        <div class="form-group">
                <label for="serial_number">Job Number:</label>
                <input type="text" id="serial_number" name="serial_number">
            </div>
            <div class="form-group">
                <label for="batch">Batch:</label>
                <input type="text" id="batch" name="batch">
            </div>
           
            <div class="form-group">
                <label for="inputDate">Input Date:</label>
                <input type="date" id="inputDate" name="inputDate">
            </div>
            <div class="form-group">
                <label for="shift">Shift:</label>
                <input type="text" id="shift" name="shift">
            </div>
            <input type="submit" value="Filter">
        </form>
    </div>
</body>
</html>
