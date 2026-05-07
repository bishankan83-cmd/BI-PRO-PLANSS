<!DOCTYPE html>
<html>
<head>
    <title>Dispacth Word Order</title>
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            background: url('atire3.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 50px;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        h1 {
            color: #000000;
            font-weight: bold;
            font-size: 24px;
        }

        form {
            margin-top: 20px;
        }

        input[type="submit"] {
            background-color: #F28018;
            color: #FFFFFF;
            padding: 10px 30px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-family: 'Cantarell Bold', sans-serif;
        }

        input[type="submit"]:hover {
            background-color: #FFA726;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Search ERP Data</h1>
    <form action="dispatch2.php" method="post">
      <input type="text" name="erp_number" required>
        <input type="submit" value="Search">
    </form>
</body>
</html>
