<?php
kerdesLekerese(1);

function kerdesLekerese($nehezseg)
{
    include("connect.php");

    $conn->set_charset("utf8");

    $stmt = $conn->prepare("SELECT id, kerdes FROM kerdesek WHERE nehezseg = ? ORDER BY RAND() LIMIT 1");
    $stmt->bind_param("i", $nehezseg);
    $stmt->execute();
    $reader = $stmt->get_result();
    $tomb = array();

    while ($sor = $reader->fetch_assoc()) {
        $tomb["kerdes"] = $sor;
    }

    $conn->close();

    valaszokLekerese($tomb["kerdes"]["id"], $tomb);
}

function valaszokLekerese($id, $tomb)
{
    include("connect.php");
    $tomb["valasz"] = array();

    $conn->set_charset("utf8");

    $stmt = $conn->prepare("SELECT valaszok.id, valaszok.valasz FROM valaszok INNER JOIN kerdesek ON (kerdesek.id = valaszok.kid) WHERE valaszok.kid = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $reader = $stmt->get_result();

    while ($sor = $reader->fetch_assoc()) {
        $tomb["valasz"][] = $sor;
    }
    $conn->close();

    echo json_encode($tomb);
}

?>