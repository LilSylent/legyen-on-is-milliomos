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
        case "otven":
            otvenOtven();
            break;
    }
}

function kerdesLekerese()
{
    global $conn;
    session_start();
    $nev = $_SESSION["login"][1];

    if (isset($_POST["k"])) {
        //Ha az 1. körnél járunk, akkor a játékos kapjon 50/50-et
        if ($_POST["k"] == 1) {
            otvenOtvenUpdate($nev, 1);
        }

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

function otvenOtven()
{
    global $conn;

    session_start();

    $id = $_POST["id"];
    $nev = $_SESSION["login"][1];

    //Lekérdezzük, hogy a játékosnak van-e lehetősége 50/50-re
    $stmt = $conn->prepare("SELECT felezes FROM jatekos WHERE nev LIKE ?");
    $stmt->bind_param("s", $nev);
    $stmt->execute();
    $reader = $stmt->get_result();
    $sor = $reader->fetch_assoc();

    if (!empty($id) && $sor["felezes"] == 1) {
        $stmt = $conn->prepare("SELECT id, kerdes FROM kerdesek WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $reader = $stmt->get_result();
        $tomb = array();

        while ($sor = $reader->fetch_assoc()) {
            $tomb["kerdes"] = $sor;
        }

        //Helyes válasz lekérése
        $stmt = $conn->prepare("SELECT valaszok.id, valaszok.valasz FROM valaszok INNER JOIN kerdesek ON (kerdesek.id = valaszok.kid) WHERE valaszok.kid = ? AND helyes = 1 ORDER BY RAND()");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $reader = $stmt->get_result();

        $sor = $reader->fetch_assoc();
        $tomb["valasz"][] = $sor;

        //1 random válasz lekérése, ami nem helyes
        $stmt = $conn->prepare("SELECT valaszok.id, valaszok.valasz FROM valaszok INNER JOIN kerdesek ON (kerdesek.id = valaszok.kid) WHERE valaszok.kid = ? AND helyes = 0 ORDER BY RAND() LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $reader = $stmt->get_result();

        $sor = $reader->fetch_assoc();
        $tomb["valasz"][] = $sor;

        //Újrarendezzük a tömböt.
        shuffle($tomb["valasz"]);

        //Áttálítjuk az 50/50-et 0-ra
        otvenOtvenUpdate($nev, 0);

        echo json_encode($tomb);
    }
}

function korUpdate($szint, $nev)
{
    global $conn;

    $stmt = $conn->prepare("UPDATE jatekos SET szint = ? WHERE nev LIKE ?");
    $stmt->bind_param("is", $szint, $nev);
    $stmt->execute();
}

function otvenOtvenUpdate($nev, $van)
{
    global $conn;

    $stmt = $conn->prepare("UPDATE jatekos SET felezes = ? WHERE nev LIKE ?");
    $stmt->bind_param("is", $van, $nev);
    $stmt->execute();
}

$conn->close();
?>