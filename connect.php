<?php
$conn = new mysqli("localhost", "root", "", "legyenonismilliomos");

if ($conn->connect_errno) {
    echo "Nem sikerült csatlakozni a MySQL-hez: " . $conn->connect_error;
    exit();
}
?>