
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($_SESSION['department']); ?> Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-xl font-bold">
                            Steel Bands - <?php echo ucfirst($_SESSION['department']); ?>
                        </h1>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <span class="text-gray-700 mr-4"><?php echo $_SESSION['username']; ?></span>
                    <a href="logout.php" class="bg-red-500 text-white rounded-md py-2 px-4 hover:bg-red-600">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    