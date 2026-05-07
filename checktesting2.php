


<html>
<head>
    <title>Redirecting...</title>
    <style>


/* Loading animation styles */
.loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8); /* Replace with official color */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s, visibility 0.3s;
        }


        .loading-logo {
            width: 100px; /* Adjust size as needed */
            height: 100px; /* Adjust size as needed */
            animation: spin 4s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Show loading overlay when needed */
        .loading {
            overflow: hidden;
        }

        .loading .loading-overlay {
            visibility: visible;
            opacity: 1;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f9;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 1em;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2em;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .footer {
            text-align: center;
            padding: 1em;
            background-color: #333;
            color: #fff;
        }


        /* New blinking animation */
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }

        /* Apply blinking animation to the h1 element */
        .header h1 {
            animation: blink 2s infinite;
            color: 'blue'; /* Replace with official color */
        }
    </style>

    <script>
        // Specify the target page you want to redirect to
        var targetPage = "testingbis2.php";
        
        // Set the time (in milliseconds) you want the delay before redirection
        var delayMilliseconds = 0.000000000000000000000000000000000001; // Change this value to the desired delay
        
        // Function to perform the redirection
        function redirectToTargetPage() {
            window.location.href = targetPage;

              // Simulate loading completion after a delay
        window.addEventListener('load', function () {
            setTimeout(function () {
                document.querySelector('.loading').classList.remove('loading');
            }, 2000); // Adjust delay as needed
        });
        }
        
        // Call the redirectToTargetPage function after the delay
        setTimeout(redirectToTargetPage, delayMilliseconds);
    </script>
</head>
<body>

<div class="loading">
        <div class="loading-overlay">
            
        </div>
    </div>
    <div class="header">
    <h1>Please Wait.............</h1><img class="loading-logo" src="loading-logo2.png" alt="Loading...">
   
    </div>
   
  
</body>
</html>

