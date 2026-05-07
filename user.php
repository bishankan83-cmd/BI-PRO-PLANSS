<?php
// Start the session
session_start();

// Check if the user_id session variable is set
if(isset($_SESSION['Admin1'])) {
    // User is logged in
    echo "User is logged in as ".$_SESSION['Admin1'];
} else {
    // User is not logged in
    echo "User is not logged in";
}
?>
