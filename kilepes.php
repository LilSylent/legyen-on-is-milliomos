<?php
include("connect.php");
session_start();

if (!isset($_SESSION["login"])) {
    return;
}

$_SESSION["login"] = false;
echo "OK";

$conn->close();
exit();
?>