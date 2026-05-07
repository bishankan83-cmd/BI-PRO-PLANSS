<?php
// Database connection parameters
$host = "localhost";    // Change to your database host
$username = "planatir_task_managemen"; // Change to your database username
$password = "Bishan@1919"; // Change to your database password
$database = "planatir_task_managemen"; // Change to your database name


// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get data from reverse_serial table, sorted by production_date in descending order
$sql = "SELECT id, serial_number, formatted_serial, comment, created_at, updated_at, production_date 
        FROM reverse_serial 
        ORDER BY production_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reverse Serial Data</title>
    <link href="https://fonts.googleapis.com/css2?family=Cantarell:wght@400;700&family=Open+Sans&display=swap" rel="stylesheet">
    <style>
        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
        }

        .stockr-table {
            width: 100%;
            border-collapse: collapse;
        }

        .stockr-table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
            font-family: 'Open Sans', sans-serif;
        }

        .button-container {
            text-align: left;
            margin: 10px;
            border-radius: 4px;
        }

        .button-container button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 40px;
            transition: background-color 0.3s;
        }

        .button-container button:hover {
            background-color: #333333;
        }

        .search-form {
            text-align: center;
            margin: 10px;
        }

        .search-form input[type="text"] {
            padding: 10px;
            width: 200px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            font-family: 'Cantarell', sans-serif;
        }

        .search-form button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .stockr-table th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 10px;
            border: 1px solid #000000;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="search-form">
            <input type="text" id="searchInput" placeholder="Search...">
            <button onclick="searchTable()">Search</button>
        </div>
        
        <div class="button-container">
            <button onclick="exportToCSV()">Export to CSV</button>
        </div>
        
        <table class="stockr-table" id="reverseTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Serial Number</th>
                    
                    <th>Comment</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Production Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Check if we have data and display it
                if ($result->num_rows > 0) {
                    // Output data of each row
                    while($row = $result->fetch_assoc()) {
                        echo "<tr class='stockr-row'>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["serial_number"] . "</td>";
                       
                        echo "<td>" . $row["comment"] . "</td>";
                        echo "<td>" . $row["created_at"] . "</td>";
                        echo "<td>" . $row["updated_at"] . "</td>";
                        echo "<td>" . $row["production_date"] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        function searchTable() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const table = document.getElementById('reverseTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                let rowVisible = false;
                const cells = rows[i].getElementsByTagName('td');
                
                for (let j = 0; j < cells.length; j++) {
                    const cellText = cells[j].textContent.toLowerCase();
                    if (cellText.includes(input)) {
                        rowVisible = true;
                        break;
                    }
                }
                
                rows[i].style.display = rowVisible ? '' : 'none';
            }
        }

        function exportToCSV() {
            const table = document.getElementById('reverseTable');
            const rows = table.getElementsByTagName('tr');
            let csv = [];
            
            // Get header row
            const headers = [];
            const headerCells = rows[0].getElementsByTagName('th');
            for (let i = 0; i < headerCells.length; i++) {
                headers.push(headerCells[i].textContent);
            }
            csv.push(headers.join(','));
            
            // Get data rows
            for (let i = 1; i < rows.length; i++) {
                if (rows[i].style.display !== 'none') {
                    const cells = rows[i].getElementsByTagName('td');
                    const rowData = [];
                    for (let j = 0; j < cells.length; j++) {
                        rowData.push(cells[j].textContent);
                    }
                    csv.push(rowData.join(','));
                }
            }
            
            // Create and download the CSV file
            const csvString = csv.join('\n');
            const blob = new Blob([csvString], { type: 'text/csv' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'reverse_serial_data.csv';
            link.click();
        }
    </script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>