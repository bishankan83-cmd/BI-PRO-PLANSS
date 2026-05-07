<?php
// Da<?php
// Database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch unique dates
$dateQuery = "SELECT DISTINCT `date` FROM `calculated_data` ORDER BY `date`";
$dateResult = $conn->query($dateQuery);

$dates = [];
while ($row = $dateResult->fetch_assoc()) {
  $dates[] = $row['date'];
}

// Fetch data
$dataQuery = "SELECT `icode`, `description`, `mold_id`, `cavity_id`, `date`, `plan` FROM `calculated_data`";
$dataResult = $conn->query($dataQuery);

$data = [];
while ($row = $dataResult->fetch_assoc()) {
  $data[] = $row;
}

// Group data by ERP, ICode, Description, Mold ID, and Cavity ID
$groupedData = [];
foreach ($data as $row) {
  $key = $row['erp'] . '-' . $row['icode'] . '-' . $row['description'] . '-' . $row['mold_id'] . '-' . $row['cavity_id'];
  if (!isset($groupedData[$key])) {
    $groupedData[$key] = [
      'erp' => $row['erp'],
      'icode' => $row['icode'],
      'description' => $row['description'],
      'mold_id' => $row['mold_id'],
      'cavity_id' => $row['cavity_id'],
      'plans' => [],
      'row_total' => 0
    ];
  }
  $groupedData[$key]['plans'][$row['date']] = $row['plan'];
  $groupedData[$key]['row_total'] += $row['plan'];
}

// Initialize column totals
$columnTotals = array_fill_keys($dates, 0);

// Display data in a horizontal format
echo "<table border='1'>";
echo "<tr><th>ERP</th><th>ICode</th><th>Description</th><th>Mold ID</th><th>Cavity ID</th>";

foreach ($dates as $date) {
  echo "<th>" . $date . "</th>";
}
echo "<th>Total</th>";
echo "</tr>";

foreach ($groupedData as $entry) {
  echo "<tr>";
  echo "<td>" . $entry['erp'] . "</td>";
  echo "<td>" . $entry['icode'] . "</td>";
  echo "<td>" . $entry['description'] . "</td>";
  echo "<td>" . $entry['mold_id'] . "</td>";
  echo "<td>" . $entry['cavity_id'] . "</td>";

  foreach ($dates as $date) {
    if (isset($entry['plans'][$date])) {
      echo "<td>" . $entry['plans'][$date] . "</td>";
      $columnTotals[$date] += $entry['plans'][$date];
    } else {
      echo "<td></td>";
    }
  }
  echo "<td>" . $entry['row_total'] . "</td>";
  echo "</tr>";
}

// Display column totals
echo "<tr><th colspan='5'>Total</th>";
foreach ($dates as $date) {
  echo "<th>" . $columnTotals[$date] . "</th>";
}
echo "<th>" . array_sum($columnTotals) . "</th>";
echo "</tr>";

echo "</table>";

$conn->close();
?>
