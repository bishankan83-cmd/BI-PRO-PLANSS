<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Your first SQL query
    $sql1 = "
        UPDATE `plannew`
        SET
          `start_date` = DATE_ADD(`start_date`, INTERVAL 1 DAY),
          `end_date` = DATE_ADD(`end_date`, INTERVAL 1 DAY)
        WHERE
          DATE(`start_date`) IN (SELECT `holiday_date` FROM `holidays`)
    ";

    // Prepare and execute the first query
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute();

    // Your second SQL query
    $sql2 = "
        UPDATE `plannew`
        SET
          `end_date` = DATE_ADD(`end_date`, INTERVAL 1 DAY)
        WHERE
          DATE(`end_date`) IN (SELECT `holiday_date` FROM `holidays`)
    ";

    // Prepare and execute the second query
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute();

    // Redirect to another page
    header("Location: check_date_enter32.php");
    exit();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$pdo = null;
?>
