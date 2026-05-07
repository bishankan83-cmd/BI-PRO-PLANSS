<?php
$target_dir = "charts/";
$target_file = $target_dir . basename($_FILES["chartImage"]["name"]);

if (move_uploaded_file($_FILES["chartImage"]["tmp_name"], $target_file)) {
    echo "The file ". basename($_FILES["chartImage"]["name"]). " has been uploaded.";
} else {
    echo "Sorry, there was an error uploading your file.";
}
?>
