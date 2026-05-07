<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wonderful Design</title>
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
            text-align: center;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        h3 {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            color: #F28018;
            margin-top: 50px;
        }

        h6 {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
            font-size: 12px;
        }

        h2 {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            color: #000000;
            font-size: 25px;
            padding: 5px;
            background-color: #2d2d2d;
            color: #fff;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50%, 100% { opacity: 1; }
            25%, 75% { opacity: 0; }
        }

        h8 {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
        }

        .cargo-loading-date {
            font-family: 'Open Sans', sans-serif;
            font-weight: normal;
            color: #F28018;
            padding: 5px;
            font-size: 16px;
            background-color: #000000;
            border: 1px dashed gray;
            border-radius: 10px;
            margin: 20px auto;
            width: fit-content;
        }

        .button-container {
            text-align: center;
            margin: 20px;
        }

        .button-container button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 15px 30px;
            border: none;
            cursor: pointer;
            border-radius: 40px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .button-container button:hover {
            background-color: #F28018;
            transform: scale(1.1);
        }

        .label-container {
            text-align: center;
            background-color: #F28018;
            color: #000000;
            padding: 15px;
            margin: 20px 0;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            font-size: 20px;
            border-radius: 8px;
        }

        .production-table {
            width: 80%;
            margin: 40px auto;
            border-collapse: collapse;
        }

        .production-table th, .production-table td {
            border: 1px solid #000000;
            padding: 12px;
            text-align: left;
            font-size: 16px;
        }

        .production-table th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }

        .production-table td {
            background-color: #f9f9f9;
        }

        .production-table tr:nth-child(even) td {
            background-color: #e8e8e8;
        }
    </style>
</head>
<body>

    <div class="label-container">
        <h2>Important Notice: Please take action!</h2>
    </div>

    <div class="label-container">
        <h5>Please click the button below to update.</h5>
    </div>
  
    <div class="button-container">
        <form action="submith.php" method="GET">
            <button type="submit">CLICK TO NEXT</button>
        </form>
    </div>

</body>
</html>
