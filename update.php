<?php
/**
 * Created by PhpStorm.
 * User: Patrick
 * Date: 09/27/19
 * Time: 09:05 PM
 */

$conn = dbConnect();

//gather the data we want and sanitize it
if(isValidRequestedType()) {
    $whatToWorkOn = sanitizeInput($_POST['dataType']);
//the rest we will set a default of null so when we workign thru our workload we have a consistent value for data to ignore.
    $id         = (empty($_POST['commodity_id'])) ? null : sanitizeInput($_POST['commodity_id']);
    $stationId  = (empty($_POST['station_id'])) ? null : sanitizeInput($_POST['station_id']);
    $factionId  = (empty($_POST['faction_id'])) ? null : sanitizeInput($_POST['faction_id']);
    $storeId    = (empty($_POST['store_id'])) ? null : sanitizeInput($_POST['store_id']);
    $amount     = (empty($_POST['amount'])) ? null : sanitizeInput($_POST['amount']);
    $name       = (empty($_POST['name'])) ? null : sanitizeInput($_POST['name']);

//now we take what we think is fleshed out and ready to go data and work with it.
    switch ($whatToWorkOn) {
        case ('commodity') :
            //we are going to upsert based on the data we receive
            upsertCommodity($id, $stationId, $factionId, $storeId, $amount, $conn);
            break;
        case ('faction') :
            upsertFaction($factionId, $name, $conn);
            break;
        case ('station') :
            upsertStation($stationId, $factionId, $name, $conn);
            break;
        case ('store') :
            upsertStore($storeId, $factionId, $stationId, $conn);
            break;
        default :
            http_response_code(501);
            echo "Error: nothing matched";
    }
}

mysqli_close($conn);

//now we do the code for adding and updating records
function upsertFaction($factionId, $name, $conn) {
    if(doesItExist('Factions', $factionId, $conn)) {
        updateFaction($factionId, $conn);
    } else {
        addFaction($factionId, $name, $conn);
    }
}

function upsertStation($stationId, $factionId, $name, $conn) {
    if (doesItExist('Stations', $stationId, $conn)) {
        addStation($stationId, $factionId, $name, $conn);
    } else {
        updateStation($stationId, $conn);
    }
    
    return true;
}

function upsertStore($storeId, $factionId, $stationId, $conn) {
    if (doesItExist('Stores', $stationId, $conn)) {
        addStore($storeId, $stationId,$conn);
    } else {
        updateStore($storeId, $conn);
    }

    return true;
}

function upsertCommodity($id, $stationId, $factionId, $storeId, $amount, $conn) {
    if (doesItExist('Commodities', $id, $conn)) {
        addCommodity($id, $amount, $stationId, $factionId, $storeId, $conn);
    } else {
        updateCommodity($id, $amount, $stationId, $factionId, $storeId, $conn);
    }
    
    upsertCommodityAmount($id, $stationId, $factionId, $storeId);
}

function upsertCommodityAmount($id, $stationId, $factionId, $storeId) {
    $sql = "SELECT count(*) FROM Commodities_Amount WHERE commodity_id=$id AND station_id=$stationId and faction_id=$factionId AND store_id=$storeId";
}

/**
 * @param $id
 * @param $amount
 * @param $stationId
 * @param $factionId
 * @param $storeId
 * @param $conn
 */
function updateCommodity($id, $amount, $stationId, $factionId, $storeId, $conn) {
    $sql = "UPDATE Commodities_Amount SET amount = $amount WHERE commodity_id=$id AND station_id=$stationId AND faction_id=$factionId AND store_id=$storeId";
    if (mysqli_query($conn, $sql)) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

function updateFaction($id, $conn) {

}

function updateStore($id, $conn) {

}

function updateStation($id, $conn) {

}
/**
 * @param $id
 * @param $amount
 * @param $stationId
 * @param $factionId
 * @param $storeId
 * @param $conn
 */
function addCommodity($id, $amount, $stationId, $factionId, $storeId, $conn) {
    $sql = "INSERT INTO Commodities_Amount (commodity_id, station_id, faction_id, store_id, amount) VALUES ($id, $stationId, $factionId, $storeId, $amount)";
    if (mysqli_query($conn, $sql)) {
    } else {
        //echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

/**
 * @param $factionId
 * @param $name
 * @param $conn
 * @return string
 */
function addFaction($factionId, $name, $conn) {
    $sql = "INSERT INTO Factions (id, name) VALUES ($factionId, $name)";
    mysqli_query($conn, $sql);
    
    return  "New record created successfully";
}

function addStore($id, $name, $conn) {

}

function addStation($id, $factionId, $name, $conn) {
    $sql = "INSERT INTO Stations (id, faction_id, name) VALUES ($id, $factionId, $name)";
}


//now we will put helper functions to the main  update and insert functions
/**
 * @param $table
 * @param $id
 * @param $conn
 * @return bool
 */
function doesItExist($table, $id, $conn) {
    $sql = "SELECT count(*) FROM $table WHERE id=$id";
    return (mysqli_query($conn, $sql) > 0);
}

/**
 * @param $value
 * @return null|string|string[]
 */
function sanitizeInput($value) {
    return preg_replace('/[^A-Za-z0-9 ]/', '', $value);
}

/**
 * @return bool
 */
function isValidRequestedType() {
    $allowedDataTypes = ['commodity', 'faction', 'station', 'store'];
    if(empty($_POST['dataType'])) {
        //they didn't send a dataType so we can't do anything with it...
        http_response_code(406);
        
        return false;
    }
    elseif(! in_array($_POST['dataType'], $allowedDataTypes)) {
        http_response_code(501);
        
        return false;
    }
    
    return true;
}

/**
 * @return mysqli
 */
function dbConnect() {
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
    
    return $conn;
}