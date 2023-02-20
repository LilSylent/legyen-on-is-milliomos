<?php
include("connect.php");

if (isset($_POST["f"])) {
    switch ($_POST["f"]) {
        case "jatszott":
            jatszottE();
            break;
        case "lekerdezes":
            kerdesLekerese();
            break;
        case "valasz":
            valaszEllenorzes();
            break;
    }
}

function kerdesLekerese()
{
    global $conn;
    session_start();

    $stmt = $conn->prepare("SELECT id, kerdes FROM kerdesek WHERE nehezseg = ? ORDER BY RAND() LIMIT 1");
    $stmt->bind_param("i", $_POST["k"]);
    $stmt->execute();
    $reader = $stmt->get_result();
    $tomb = array();

    while ($sor = $reader->fetch_assoc()) {
        $tomb["kerdes"] = $sor;
    }

    valaszokLekerese($tomb["kerdes"]["id"], $tomb);
    korUpdate($_POST["k"], $_SESSION["login"][1]);
}

function valaszokLekerese($id, $tomb)
{
    global $conn;
    $tomb["valasz"] = array();

    $stmt = $conn->prepare("SELECT valaszok.id, valaszok.valasz FROM valaszok INNER JOIN kerdesek ON (kerdesek.id = valaszok.kid) WHERE valaszok.kid = ? ORDER BY RAND()");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $reader = $stmt->get_result();

    while ($sor = $reader->fetch_assoc()) {
        $tomb["valasz"][] = $sor;
    }

    echo json_encode($tomb);
}

function valaszEllenorzes()
{
    global $conn;

    $stmt = $conn->prepare("SELECT helyes FROM valaszok WHERE id = ?");
    $stmt->bind_param("i", $_POST["i"]);
    $stmt->execute();
    $reader = $stmt->get_result();
    $sor = $reader->fetch_assoc();

    if ($sor["helyes"] == 1) {
        echo "OK";
    }
}

function korUpdate($szint, $nev)
{
    global $conn;

    $stmt = $conn->prepare("UPDATE jatekos SET szint = ? WHERE nev LIKE ?");
    $stmt->bind_param("is", $szint, $nev);
    $stmt->execute();
}

function jatszottE()
{
    global $conn;

    session_start();

    $stmt = $conn->prepare("SELECT szint FROM jatekos WHERE nev LIKE ?");
    $stmt->bind_param("s", $_SESSION["login"][1]);
    $stmt->execute();
    $reader = $stmt->get_result();
    $sor = $reader->fetch_assoc();

    if ($sor["szint"] > 1) {
        echo $sor["szint"];
    }
}

$conn->close();
?>