<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Database Operations</title>
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
</head>
<body>

<style>
/*Reset some basic styles */
    body, h1, form, button {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    /* Basic body styling */
    body {
        font-family: Arial, sans-serif;
        background-color: #e3e3ec;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    
    /* Container styling */
    .container {
        background: #fbfbfb;
        padding: 60px; /* Increased padding for larger spacing */
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(247, 3, 3, 0.1);
        width: 100%; /* Ensure it takes full width */
        max-width: 600px; /* Limit the container width */
        text-align: center;
        display: flex;
        flex-direction: column; /* Stack elements vertically */
        justify-content: flex-start; /* Align items to the top */
    }
    
    /* Title styling */
    h1 {
        text-align: center;
        font-family: Cantarell, sans-serif;
        font-size: 36px; /* You can adjust the font size */
        margin-bottom: 20px; /* Add space below the title */
    }
    
    /* Form styling */
    .form {
        margin-top: 20px;
    }
    
    /* Button styling */
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 10px;
    }
    
    .delete-btn {
        background-color: #8a1104;
        color: #fff;
    }
    
    .upload-btn {
        background-color: #01384c;
        color: #fff;
    }
    
    /* File input styling */
    .file-input {
        display: block;
        margin: 10px auto;
    }
    
    /* Message styling */
    .success {
        color: green;
        margin-top: 20px;
    }
    
    .error {
        color: red;
        margin-top: 20px;
    }
</style>
    
    <div class='container'>
        <h1 style='text-align: center; font-family: Cantarell, sans-serif; font-size: 56px;'>BOM UPDATE OPERTAION</h1>


        <!-- Delete All Data Button -->
        <form id="deleteForm" class="form">
            <button type="submit" name="delete" class="btn delete-btn">Delete All Data</button>
        </form>

        <!-- Upload CSV File Form -->
        <form id="uploadForm" enctype="multipart/form-data" class="form">
            <input type="file" name="file" accept=".csv" class="file-input">
            <button type="submit" name="upload" class="btn upload-btn">Import CSV to Database</button>
        </form>

        <!-- Success message -->
        <div id="message"></div>
    </div>

    <script>
        $(document).ready(function() {
            // Handle delete form submission
            $('#deleteForm').on('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                $.ajax({
                    url: 'delete_bom.php',
                    type: 'POST',
                    data: { delete: true },
                    success: function(response) {
                        if (response.message) {
                            $('#message').html('<p class="success">' + response.message + '</p>');
                        } else if (response.error) {
                            $('#message').html('<p class="error">' + response.error + '</p>');
                        }
                    }
                });
            });

            // Handle upload form submission
            $('#uploadForm').on('submit', function(e) {
                e.preventDefault(); // Prevent default form submission
                var formData = new FormData(this);

                $.ajax({
                    url: 'delete_bom.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.message) {
                            $('#message').html('<p class="success">' + response.message + '</p>');
                        } else if (response.error) {
                            $('#message').html('<p class="error">' + response.error + '</p>');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#message').html('<p class="error">An error occurred: ' + error + '</p>');
                    }
                });
            });
        });
    </script>
</body>
</html>