

<?php
$selectedIcode = $_POST['icode'];


// Establish a database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to retrieve the corresponding information based on the selected iCode
$sql = "SELECT t_size, `Green Tire weight`, `B-ATS 15`, `B-BNS 24`, `BG-BLS 12`, `CG - BS 901`, `C - SMS 501`, `C-ATS 20`, `C-SMS 702`, `T - TRS 102`, `T-ATNM S`, `T-ATS 30`, `T-ATS 35`, `T-KS 40`, `T-TRNMS 402`, `T-TRNMS 402G`, `T-TRS 202` FROM bom_new WHERE icode = '$selectedIcode'";

$result = $conn->query($sql);

if ($result === false) {
    die("Query failed: " . $conn->error);
}

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $tSize = $row["t_size"];
    $greenTireWeight = $row["Green Tire weight"];

    $bATS15 = $row["B-ATS 15"];
    $bBNS24 = $row["B-BNS 24"];
    $bgBLS12 = $row["BG-BLS 12"];
    $cgBS901 = $row["CG - BS 901"];
    $cSMS501 = $row["C - SMS 501"];
    $cATS20 = $row["C-ATS 20"];
    $cSMS702 = $row["C-SMS 702"];
    $tTRS102 = $row["T - TRS 102"];
    $tATNMS = $row["T-ATNM S"];
    $tATS30 = $row["T-ATS 30"];
    $tATS35 = $row["T-ATS 35"];
    $tKS40 = $row["T-KS 40"];
    $tTRNMS402 = $row["T-TRNMS 402"];
    $tTRNMS402G = $row["T-TRNMS 402G"];
    $tTRS202 = $row["T-TRS 202"];
    
    
// Get the current day
$currentDay = date("d");

if ($currentDay === "01") {
    // Reset the last 4 digits to "0001" on the 1st day of every month
    $nextSerial = date("md") . "0001";
} else {
    // Find the maximum serial number for the current month
    $sql = "SELECT MAX(SUBSTRING(serial_number, 5)) AS max_serial FROM bom_new3 WHERE serial_number LIKE '" . date("m") . "%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $maxSerial = $row["max_serial"];

    if ($maxSerial === null) {
        $nextSerial = date("md") . "0001";
    } else {
        // Increment the last 4 digits based on the existing serial numbers in the database for the current month
        $nextSerial = date("md") . str_pad((intval($maxSerial) + 1), 4, '0', STR_PAD_LEFT);
    }
}
echo "<table border='1'>";


// Echo the retrieved data and the next serial number as HTM
echo "<tr><th>Selected iCode</th> ";                  
echo "<th>Tire Description</th> ";                 
echo "<th>Green Tire Weight </th>";              
echo "<th>Next Serial Number </th>"; 



         // Repeat this pattern for other fields you want to conditionally display
         if ($bATS15 !== null) {
            echo "<th>Base weight:</th>";
        }
        if ($bBNS24 !== null) {
            echo "<th>Base weight:</th>";
        }
        if ($bgBLS12 !== null) {
            echo "<th>Bonding Weight</th>";
        }
        if ($cgBS901 !== null) {
            echo "<th>Bonding Weight</th>";
        }
        if ($cSMS501 !== null) {
            echo "<th>Cussion Weight</th>";
        }
        if ($cATS20 !== null) {
            echo "<th>Cussion Weight</th>";
        }
        if ($cSMS702 !== null) {
            echo "<th>Cussion Weight</th>";
        }
        if ($tTRS102 !== null) {
            echo "<th>Thread Weight</th>";
        }
        if ($tATNMS !== null) {
            echo "<th>Thread Weight</th>";
        }
        if ($tATS30 !== null) {
            echo "<th>Thread Weight</th>";
        }
        if ($tATS35 !== null) {
            echo "<th>Thread Weight</th>";
        }
        if ($tKS40 !== null) {
            echo "<th>Thread Weight</th>";
        }
        if ($tTRNMS402 !== null) {
            echo "<th>Thread Weight</th>";
        }
        if ($tTRNMS402G !== null) {
            echo "<th>Thread Weight</th>";
        }
        if ($tTRS202 !== null) {
            echo "<th>Thread Weight</th>";
        }


echo "<tr>";
               echo "<td>$selectedIcode</td>";
                  echo "<td>$tSize</td>";
             echo "<td>$greenTireWeight</td>";
                 echo "<td>$nextSerial</td>";

                 if ($bATS15 !== null) {
                    echo "<td> $bATS15</td>";
                }

                if ($bBNS24 !== null) {
                    echo "<td> $bBNS24</td>";
                }
                if ($bgBLS12 !== null) {
                    echo "<td>$bgBLS12</p>";
                }
                if ($cgBS901 !== null) {
                    echo "<td>$cgBS901</td>";
                }
                if ($cSMS501 !== null) {
                    echo "<td>$cSMS501</td>";
                }
                if ($cATS20 !== null) {
                    echo "<td>$cATS20</td>";
                }
                if ($cSMS702 !== null) {
                    echo "<td>$cSMS702</td>";
                }
                if ($tTRS102 !== null) {
                    echo "<td>$tTRS102</td>";
                }
                if ($tATNMS !== null) {
                    echo "<td>$tATNMS</td>";
                }
                if ($tATS30 !== null) {
                    echo "<td>$tATS30</td>";
                }
                if ($tATS35 !== null) {
                    echo "<td>$tATS35</td>";
                }
                if ($tKS40 !== null) {
                    echo "<td>$tKS40</td>";
                }
                if ($tTRNMS402 !== null) {
                    echo "<td>$tTRNMS402</td>";
                }
                if ($tTRNMS402G !== null) {
                    echo "<td>$tTRNMS402G</td>";
                }
                if ($tTRS202 !== null) {
                    echo "<td>$tTRS202</td>";
                }
                
        


echo"</tr>";
echo "<form id='data-entry-form'>";
echo "<h2>Data Entry</h2>";
echo "<table>";
echo "<tr><th>Base (Batch No.)</th><th>Cussion (Batch No.)</th><th>Tread (Batch No.)</th></tr>";
echo "<tr>";
echo "<td><input type='text' name='baseBatchNo' id='baseBatchNo'></td>";
echo "<td><input type='text' name='cussionBatchNo' id='cussionBatchNo'></td>";
echo "<td><input type='text' name='treadBatchNo' id='treadBatchNo'></td>";
echo "</tr>";
echo "</table>";

               
// Close the HTML table
echo "</table>";


} else {
    echo "No matching record found.";
}

$conn->close();

