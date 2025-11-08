<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors',1);
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if ($email=="" || $password=="") {
        die("All fields required");
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows===1) {
        $u = $res->fetch_assoc();
        if (password_verify($password,$u["password"])) {
            $_SESSION["user_id"]=$u["id"];
            $_SESSION["user_name"]=$u["name"];
            $_SESSION["user_email"]=$u["email"];
            header("Location: ../dashboard.php");
            exit();
        } else {
            echo "Wrong password";
        }
    } else {
        echo "User not found";
    }
}
?>
