  



<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('atire3.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            text-align: center;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
            padding: 20px;
            max-width: 400px;
            margin: 0 auto;
        }

        p {
            font-size: 20px;
        }

        .icon img {
            max-width: 100%;
            max-height: 200px;
            background: transparent;
        }

        #rotatingTire {
            animation: rotateTire 2s linear infinite;
        }

        @keyframes rotateTire {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
    </style>
    <script>
        setTimeout(function () {
            window.location.href = "indienddate.php";
        }, 1); // 1000 milliseconds (1 second)
    </script>
</head>
<body>
<div class="container">
    <div class="icon">
        <img src="tire4.gif" alt="Your Icon">
    </div>

    <p>Please wait, generating the plan...</p>
</div>
</body>
</html>
