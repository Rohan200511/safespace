<?php
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $location = $_POST["location"];
    $type = $_POST["type"];
    $desc = $_POST["description"];

    $stmt = $conn->prepare("INSERT INTO reports (user_email, location, type, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $location, $type, $desc);
    $stmt->execute();

    echo "Report submitted successfully";
}
?>
