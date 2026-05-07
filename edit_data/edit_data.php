<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirect Button</title>
    <style>
        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h2>SETTING</h2>
    <!-- Using a form to redirect -->
    <form method="post" action="edit_data/Realstock">
        <button type="submit">STOCK </button>
    </form>
    </br>
    <form method="post" action="edit_data/Tire">
        <button type="submit">TIRE </button>
    </form>
    </br>

    <form method="post" action="edit_data/TireDetails">
        <button type="submit">EDIT STOCK </button>
    </form>
    </br>


    
    <form method="post" action="edit_data/TireMold">
        <button type="submit">EDIT MOLD</button>
    </form>
    </br>


    <br>

  
</body>
</html>
