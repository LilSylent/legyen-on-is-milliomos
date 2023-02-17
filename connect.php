<?php
$conn = new mysqli("localhost", "root", "", "legyenonismilliomos");

if ($conn->connect_errno) {
    die("Nem sikerült csatlakozni a MySQL-hez: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>