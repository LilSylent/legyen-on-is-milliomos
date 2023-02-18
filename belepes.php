<?php
session_start();

//Ha már bevagyunk lépve
if (isset($_SESSION["login"]) && $_SESSION["login"] == true) {
    echo "OK";
    return;
}

//Ha nem adtunk meg semmit a username inputba akkor visszatérünk.
if (empty($_POST["username"])) {
    $_SESSION["login"] = false;
    return;
}

include("connect.php");

$conn->set_charset("utf8");

//Megkeressük a felhasználót az adatbázisban.
$stmt = $conn->prepare("SELECT nev FROM jatekos WHERE nev LIKE ?");
$stmt->bind_param("s", $_POST["username"]);
$stmt->execute();
$stmt->store_result(); //Letároljuk az eredményt, hogy a következő sorban ellenőrizzük, hogy van-e ilyen felhasználó.

//Ha nem találtuk meg a felhasználót az adatbázisban, akkor létrehozzuk.
if ($stmt->num_rows() < 1) {
    $stmt = $conn->prepare("INSERT INTO jatekos(nev) VALUES (?)");
    $stmt->bind_param("s", $_POST["username"]);
    $stmt->execute();
}

$_SESSION["login"] = array();
$_SESSION["login"][] = true;
$_SESSION["login"][] = $_POST["username"];
echo "OK";

$conn->close();
exit();
?>