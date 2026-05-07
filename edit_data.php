<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirect Button</title>
</head>
<body>
    <!-- Using anchor tag for redirection -->
    <a href="dashboard.php">
        <button>BACK</button>
    </a>
</body>
</html>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirect Button</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            text-align: center;
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        h2 {
            font-size: 28px;
            margin-bottom: 30px;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        form {
            margin: 10px 0;
        }

        button {
            padding: 12px 30px;
            font-size: 18px;
            background-color: #F28018;
            color: white;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }

        button:hover {
            background-color:black;
            transform: translateY(-2px);
        }

        button:active {
            transform: translateY(2px);
        }

        button:focus {
            outline: none;
        }

        /* Adding a soft shadow effect on hover */
        button:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        /* Adding responsive design for smaller screens */
        @media (max-width: 600px) {
            h2 {
                font-size: 24px;
            }

            button {
                font-size: 16px;
                padding: 10px 25px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>SETTING</h2>

        <form method="post" action="edit_data/Realstock">
            <button type="submit">STOCK</button>
        </form>

        <form method="post" action="edit_data/Tire">
            <button type="submit">TIRE</button>
        </form>

        <form method="post" action="edit_data/TireDetails">
            <button type="submit">TIRE DETAILS</button>
        </form>

        <form method="post" action="mold_list.php?filter_mold_id=M606&filter_icode=&filter_press_id=&filter_mold_name=&filter_mold_size=">
            <button type="submit">MOLD</button>
        </form>

        <form method="post" action="edit_data/Country">
            <button type="submit">COUNTRY</button>
        </form>

        
        <form method="post" action="edit_data/Holiday">
            <button type="submit">HOLIDAY</button>
        </form>

        <form method="post" action="work_order_edit.php">
            <button type="submit">EDIT WORK ORDER DETAILS</button>
        </form>


        <form method="post" action="edit_data/CompleteDate">
            <button type="submit">COMPLETE DATE</button>
        </form>
    </div>
</body>
</html>
