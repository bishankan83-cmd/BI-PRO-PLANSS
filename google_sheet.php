<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=dropdown_example.xls");

// Dropdown options
$statusOptions = ['Pending', 'In Progress', 'Completed', 'Not Applicable'];

function buildDropdownXML($options) {
    $xml = '<!--[if gte mso 9]><xml><x:DataValidation>';
    $xml .= '<x:Type>List</x:Type><x:ListEntries>';
    foreach ($options as $opt) {
        $xml .= '<x:ListEntry>' . htmlspecialchars($opt) . '</x:ListEntry>';
    }
    $xml .= '</x:ListEntries></x:DataValidation></xml><![endif]-->';
    return $xml;
}

// Start of Excel-compatible HTML
echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>';

echo "<table border='1'>
<tr>
    <th>Task</th>
    <th>Status</th>
</tr>";

// Sample data rows
$data = [
    ['Prepare Report', 'Pending'],
    ['Design UI', 'In Progress'],
    ['Testing', 'Completed'],
];

foreach ($data as $row) {
    echo "<tr>";
    echo "<td>{$row[0]}</td>";

    // Add dropdown to Status column
    echo "<td>{$row[1]}";
    echo '<span style="mso-data-placement:same-cell; display:none;">' . buildDropdownXML($statusOptions) . '</span>';
    echo "</td>";

    echo "</tr>";
}

echo "</table>";
echo "</body></html>";
