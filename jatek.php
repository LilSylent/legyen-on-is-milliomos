<?php
include("connect.php");

if (isset($_POST["f"])) {
    switch ($_POST["f"]) {
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

    $stmt = $conn->prepare("SELECT id, kerdes FROM kerdesek WHERE nehezseg = ? ORDER BY RAND() LIMIT 1");
    $stmt->bind_param("i", $_POST["k"]);
    $stmt->execute();
    $reader = $stmt->get_result();
    $tomb = array();

    while ($sor = $reader->fetch_assoc()) {
        $tomb["kerdes"] = $sor;
    }

    valaszokLekerese($tomb["kerdes"]["id"], $tomb);
}

function valaszokLekerese($id, $tomb)
{
    global $conn;
    $tomb["valasz"] = array();

    $stmt = $conn->prepare("SELECT valaszok.id, valaszok.valasz FROM valaszok INNER JOIN kerdesek ON (kerdesek.id = valaszok.kid) WHERE valaszok.kid = ?");
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

$conn->close();
?>