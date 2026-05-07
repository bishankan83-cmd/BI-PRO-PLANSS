<?php
// Start or resume the session
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["icode"]) && isset($_POST["highlighted"])) {
    // Retrieve the 'icode' and highlight state from the POST data
    $icode = $_POST["icode"];
    $highlighted = filter_var($_POST["highlighted"], FILTER_VALIDATE_BOOLEAN);

    // Initialize the session variable if it doesn't exist
    if (!isset($_SESSION["highlighted_icode"])) {
        $_SESSION["highlighted_icode"] = array();
    }

    // Update the highlight state for the specific 'icode'
    $_SESSION["highlighted_icode"][$icode] = $highlighted;

    // Send a success response
    echo "Highlight state for '$icode' has been updated successfully.";
} else {
    // Invalid or missing data in the request
    http_response_code(400); // Bad Request
    echo "Invalid request.";
}
?>
