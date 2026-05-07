<?php
$host = 'localhost'; // Your database host
$username = 'planatir_task_managemen'; // Your database username
$password = 'Bishan@1919'; // Your database password
$database = 'planatir_task_managemen'; // Your database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to join the compound and bom_new tables and perform the multiplication
$sql = "SELECT 
          compound.icode,
          compound.quan,
          bom_new.Item,
          bom_new.icode AS bom_icode,
          bom_new.t_size,
          bom_new.`Item Description`,
          bom_new.a * compound.quan AS a,
          bom_new.b * compound.quan AS b,
          bom_new.c * compound.quan AS c,
          bom_new.d * compound.quan AS d,
          bom_new.e * compound.quan AS e,
          bom_new.f * compound.quan AS f,
          bom_new.g * compound.quan AS g,
          bom_new.h * compound.quan AS h,
          bom_new.i * compound.quan AS i,
          bom_new.j * compound.quan AS j,
          bom_new.k * compound.quan AS k,
          bom_new.l * compound.quan AS l,
          bom_new.m * compound.quan AS m,
          bom_new.n * compound.quan AS n,
          bom_new.o * compound.quan AS o,
          bom_new.p * compound.quan AS p,
          bom_new.q * compound.quan AS q,
          bom_new.`Grand Totalcompound weight`,
          bom_new.Color,
          bom_new.Brand,
          bom_new.`Green Tire weight`,
          bom_new.PBweight
        FROM 
          compound
        JOIN 
          bom_new 
        ON 
          compound.icode = bom_new.icode";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Initialize arrays to hold column sums
    $columnSums = array_fill_keys(['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q'], 0);

    // Output data of each row
    echo "<table border='1'>
            <tr>
                <th>icode</th>
                <th>quan</th>
                <th>Item</th>
                <th>bom_icode</th>
                <th>t_size</th>
                <th>Item Description</th>
                <th>ATPRS</th>
                <th>B-ATS 15</th>
                <th>B-BNS 24</th>
                <th>BG-BLS 12</th>
                <th>CG - BS 901</th>
                <th>C - SMS 501</th>
                <th>C-ATS 20</th>
                <th>C-SMS 702</th>
                <th>T - TRS 102</th>
                <th>T-ATNM S</th>
                <th>T-ATS 30</th>
                <th>T-ATS 35</th>
                <th>T-KS 40</th>
                <th>T-TRNMS 402</th>
                <th>T-TRNMS 402G</th>
                <th>T-TRS 202</th>
                <th>WC0001</th>
                <th>Grand Totalcompound weight</th>
                <th>Color</th>
                <th>Brand</th>
                <th>Green Tire weight</th>
                <th>PBweight</th>
                <th>Row Sum</th>
            </tr>";
    while($row = $result->fetch_assoc()) {
        $rowSum = $row["a"] + $row["b"] + $row["c"] + $row["d"] + $row["e"] + $row["f"] + $row["g"] + $row["h"] + $row["i"] + $row["j"] + $row["k"] + $row["l"] + $row["m"] + $row["n"] + $row["o"] + $row["p"] + $row["q"];

        // Add to column sums
        foreach ($columnSums as $key => $value) {
            $columnSums[$key] += $row[$key];
        }

        echo "<tr>
                <td>" . $row["icode"]. "</td>
                <td>" . $row["quan"]. "</td>
                <td>" . $row["Item"]. "</td>
                <td>" . $row["bom_icode"]. "</td>
                <td>" . $row["t_size"]. "</td>
                <td>" . $row["Item Description"]. "</td>
                <td>" . $row["a"]. "</td>
                <td>" . $row["b"]. "</td>
                <td>" . $row["c"]. "</td>
                <td>" . $row["d"]. "</td>
                <td>" . $row["e"]. "</td>
                <td>" . $row["f"]. "</td>
                <td>" . $row["g"]. "</td>
                <td>" . $row["h"]. "</td>
                <td>" . $row["i"]. "</td>
                <td>" . $row["j"]. "</td>
                <td>" . $row["k"]. "</td>
                <td>" . $row["l"]. "</td>
                <td>" . $row["m"]. "</td>
                <td>" . $row["n"]. "</td>
                <td>" . $row["o"]. "</td>
                <td>" . $row["p"]. "</td>
                <td>" . $row["q"]. "</td>
                <td>" . $row["Grand Totalcompound weight"]. "</td>
                <td>" . $row["Color"]. "</td>
                <td>" . $row["Brand"]. "</td>
                <td>" . $row["Green Tire weight"]. "</td>
                <td>" . $row["PBweight"]. "</td>
                <td>" . $rowSum . "</td>
              </tr>";
    }

    // Display column sums
    echo "<tr>
            <td colspan='6'>Column Sum</td>
            <td>" . $columnSums['a'] . "</td>
            <td>" . $columnSums['b'] . "</td>
            <td>" . $columnSums['c'] . "</td>
            <td>" . $columnSums['d'] . "</td>
            <td>" . $columnSums['e'] . "</td>
            <td>" . $columnSums['f'] . "</td>
            <td>" . $columnSums['g'] . "</td>
            <td>" . $columnSums['h'] . "</td>
            <td>" . $columnSums['i'] . "</td>
            <td>" . $columnSums['j'] . "</td>
            <td>" . $columnSums['k'] . "</td>
            <td>" . $columnSums['l'] . "</td>
            <td>" . $columnSums['m'] . "</td>
            <td>" . $columnSums['n'] . "</td>
            <td>" . $columnSums['o'] . "</td>
            <td>" . $columnSums['p'] . "</td>
            <td>" . $columnSums['q'] . "</td>
            <td colspan='6'></td>
          </tr>";

    echo "</table>";
} else {
    echo "0 results";
}
$conn->close();
?>
