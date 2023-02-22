<?php
include("connect.php");
session_start();

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
        case "kozonseg":
            kozonseg();
            break;
    }
}

function kerdesLekerese()
{
    global $conn;
    $nev = $_SESSION["login"][1];

    if (isset($_POST["k"])) {
        //Ha az 1. körnél járunk, akkor a játékos kapjon 50/50-et
        if ($_POST["k"] == 1) {
            otvenOtvenUpdate($nev, 1);
            kozonsegUpdate($nev, 1);
        }

        $stmt = $conn->prepare("SELECT id, kerdes FROM kerdesek WHERE nehezseg = ? ORDER BY RAND() LIMIT 1");
        $stmt->bind_param("i", $_POST["k"]);
        $stmt->execute();
        $reader = $stmt->get_result();
        $tomb = array();

        while ($sor = $reader->fetch_assoc()) {
            $tomb["kerdes"] = $sor;
        }
        korUpdate($_POST["k"], $_SESSION["login"][1]);

        $ujtomb = valaszokLekerese($tomb["kerdes"]["id"], $tomb);
        shuffle($ujtomb["valasz"]);

        echo json_encode($ujtomb);
    }
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

    return $tomb;
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
        $stmt = $conn->prepare("SELECT valaszok.id, valaszok.valasz FROM valaszok INNER JOIN kerdesek ON (kerdesek.id = valaszok.kid) WHERE valaszok.kid = ? AND helyes = 1");
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

function kozonseg()
{
    global $conn;

    $id = $_POST["id"];
    $nev = $_SESSION["login"][1];

    //Lekérdezzük, hogy a játékosnak van-e lehetősége a közönségre
    $stmt = $conn->prepare("SELECT kozonseg FROM jatekos WHERE nev LIKE ?");
    $stmt->bind_param("s", $nev);
    $stmt->execute();
    $reader = $stmt->get_result();
    $sor = $reader->fetch_assoc();

    if (!empty($id) && $sor["kozonseg"] == 1) {
        //LEKÉREM A KÉRDÉST AZ ADATBÁZISBÓL
        $stmt = $conn->prepare("SELECT id, kerdes FROM kerdesek WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $reader = $stmt->get_result();
        $tomb = array();

        while ($sor = $reader->fetch_assoc()) {
            $tomb["kerdes"] = $sor;
        }

        //LEKÉREM A VÁLASZOKAT AZ ADATBÁZISBÓL
        $tomb = valaszokLekerese($id, $tomb);

        //LEKÉREM A HELYES VÁLASZT ADATBÁZISBÓL
        $stmt = $conn->prepare("SELECT valaszok.id, valaszok.valasz FROM valaszok INNER JOIN kerdesek ON (kerdesek.id = valaszok.kid) WHERE valaszok.kid = ? AND helyes = 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $reader = $stmt->get_result();
        $helyes = $reader->fetch_assoc();

        $jelenlegiSzazalek = 100;

        // A HELYES VÁLASZNAK LEGENERÁLOM ELŐRE A SZÁZALÉKÁT
        $helyes["szazalek"] = rand(40, 60);
        $jelenlegiSzazalek -= $helyes["szazalek"];

        // A HELYES VÁLASZT ELTÁVOLÍTOM A FŐ TÖMBBŐL
        $helyesIndex = -1;
        for ($i = 0; $i < count($tomb["valasz"]); $i++) {
            if ($tomb["valasz"][$i]["id"] == $helyes["id"]) {
                $helyesIndex = $i;
                break;
            }
        }

        if ($helyesIndex >= 0) {
            array_splice($tomb["valasz"], $helyesIndex, 1);
        }

        //MARADÉK 3 VÁLASZNAK GENERÁLOK EGY RANDOM SZÁZALÉKOT
        for ($i = 0; $i < count($tomb["valasz"]); $i++) {
            if ($i != count($tomb["valasz"]) - 1) {
                $tomb["valasz"][$i]["szazalek"] = rand(0, $jelenlegiSzazalek);
                $jelenlegiSzazalek -= $tomb["valasz"][$i]["szazalek"];
            } else {
                $tomb["valasz"][$i]["szazalek"] = $jelenlegiSzazalek;
            }
        }

        array_push($tomb["valasz"], $helyes);
        shuffle($tomb["valasz"]);

        kozonsegUpdate($nev, 0);

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

function kozonsegUpdate($nev, $van)
{
    global $conn;

    $stmt = $conn->prepare("UPDATE jatekos SET kozonseg = ? WHERE nev LIKE ?");
    $stmt->bind_param("is", $van, $nev);
    $stmt->execute();
}

$conn->close();
?>