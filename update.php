<?php
/**
 * Created by PhpStorm.
 * User: Patrick
 * Date: 09/27/19
 * Time: 09:05 PM
 */


$hostname="localhost";
$username="freder26_php";
$password="don't post passwords in discord";
$dbname="freder26_drup354";
$usertable="test_table";

// Create connection
$conn = mysqli_connect($hostname, $username, $password, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$whatToWorkOn   = preg_replace('/[^A-Za-z0-9 ]/', '', $_POST['dataType']);
$id             = preg_replace('/[^A-Za-z0-9 ]/', '', $_POST['commodity_id']);
$stationId      = preg_replace('/[^A-Za-z0-9 ]/', '', $_POST['station_id']);
$factionId      = preg_replace('/[^A-Za-z0-9 ]/', '', $_POST['faction_id']);
$storeId        = preg_replace('/[^A-Za-z0-9 ]/', '', $_POST['store_id']);
$amount         = preg_replace('/[^A-Za-z0-9 ]/', '', $_POST['amount']);
$name           = preg_replace('/[^A-Za-z0-9 ]/', '', $_POST['name']);
//okay here we go thru and check if the data we were given already has a match in our system.  If it doesn't we add it.
dealWithFaction($factionId, $name, $conn);
dealWithStation($stationId, $factionId, $name, $conn);
dealWithStore($storeId, $factionId, $stationId, $conn);

//now we take what we think is fleshed out and ready to go data and add it.
switch ($whatToWorkOn) {
    case ('commodity') :
        dealWithCommodity($id, $stationId, $factionId, $storeId, $conn);
            //we are going to upsert based on the data we receive
            $sql = "SELECT count(*) FROM Commodities_Amount WHERE commodity_id=$id AND station_id=$stationId and faction_id=$factionId AND store_id=$storeId";
            if (mysqli_query($conn, $sql) < 1) {
                $sql = "UPDATE Commodities_Amount SET amount = $amount WHERE station_id=$stationId AND faction_id=$factionId AND store_id=$storeId";
                if (mysqli_query($conn, $sql)) {
                    echo "New record created successfully";
                } else {
                    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
                }
            } else {
                $sql = "INSERT INTO Commodities_Amount (commodity_id, station_id, faction_id, store_id, amount) VALUES ($id, $stationId, $factionId, $amount)";
                if (mysqli_query($conn, $sql)) {
                    echo "New record created successfully";
                } else {
                    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
                }
            }
    break;
    default :
        echo "Error: nothing matched";
}
mysqli_close($conn);

function dealWithStation($stationId, $factionId, $name, $conn) {
    $sql = "SELECT count(*) FROM Stations WHERE id=$stationId";
    if (mysqli_query($conn, $sql) < 1) {
        $sql = "INSERT INTO Stations (id, faction_id, name) VALUES ($stationId, $factionId, $name)";
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
    return true;
}

function dealWithFaction($factionId, $name, $conn) {
    $sql = "SELECT count(*) FROM Factions WHERE id=$factionId";
    if (mysqli_query($conn, $sql) < 1) {
        $sql = "INSERT INTO Factions (id, faction_id, name) VALUES ($factionId, $factionId, $name)";
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
    return true;
}

function dealWithCommodity($id, $stationId, $factionId, $storeId, $conn) {

    return true;
}

function dealWithStore($storeId, $factionId, $stationId, $conn) {

    return true;
}