<!DOCTYPE html>
<html>
<head>
    <title>PHP Page 2</title>
</head>
<body>
    <!-- Your HTML code goes here -->

    <?php
    // Start the second PHP block here
    // Rest of your PHP code (the part that deals with inserting data and header redirect) goes here
    // ...

    // Prepare the data for insertion into the quick_plan table
    $quick_plan_values = '';
    $production_schedule = array();
    $mold_availability = array();
    $cavity_availability = array();
    $mold_tire_count = array();

    foreach ($tires as $tire) {
        $mold_id = $tire['mold_id'];
        $cavity_id = $tire['cavity_id'];

        $icode = $tire['icode'];

        if (
            !isset($mold_availability[$mold_id])
            && !isset($cavity_availability[$cavity_id])
            && $tire['tobe'] > 0
        ) {
            $mold_availability[$mold_id] = $tire['mold_avail_date'];
            $cavity_availability[$cavity_id] = $tire['cavity_avail_date'];

            $production_schedule[] = $tire;

            // Reduce the amount of tires to be made for the corresponding icode
            $tire['tobe']--;

            // Track the number of tires processed by each mold_id
            if (!isset($mold_tire_count[$mold_id])) {
                $mold_tire_count[$mold_id] = 0;
            }
            $mold_tire_count[$mold_id]++;

            // Prepare the values for the quick_plan table insertion
            $quick_plan_values .= "('" . $tire['icode'] . "', '" . $mold_id . "', '" . $cavity_id . "'),";
        }
    }

    // Remove the trailing comma from the values
    $quick_plan_values = rtrim($quick_plan_values, ',');

    // Insert the values into the quick_plan table
    if (!empty($quick_plan_values)) {
        $insert_sql = "INSERT INTO quick_plan2 (icode, mold_id, cavity_id) VALUES " . $quick_plan_values;

        // ...

        header("Location: quickplan_update2.php");
        exit();
    }
    ?>
</body>
</html>
