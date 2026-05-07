
<!DOCTYPE html>
<html>
<head>
    <title>Copy Data</title>
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
            background-color: rgba(255, 255, 255, 0.8); /* Add a semi-transparent white background to the container */
            padding: 50px;
            border-radius: 20px; /* Add rounded corners to the container */
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

        button[type="submit"] {
            background-color: #F28018;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-family: 'Cantarell Bold', sans-serif;
        }

        button[type="submit"]:hover {
            background-color: #FFA726;
        }
    </style>
<body>

    <div class="container">
        <h1 id="mainMessage">Click "Next" to continue or "Add more" to add details</h1>
        
        <!-- Button 1 -->
        <form action="add_daily_production4.php" method="post">
            <button type="submit" name="button1" onclick="showMessage('Next button clicked!')">Next</button>
        </form>

        <!-- Button 2 -->
        <form action="next_page.php" method="post">
            <button type="submit" name="button2" onclick="showMessage('Add more button clicked!')">Add more</button>
        </form>

        <!-- Message container -->
        <div id="messageContainer"></div>
    </div>

    <script>
        function showMessage(message) {
            // Display the message below the buttons
            document.getElementById('messageContainer').innerHTML = `<p>${message}</p>`;
            // You can also update the main message if needed
            document.getElementById('mainMessage').innerText = message;
        }
    </script>
</body>
</html>