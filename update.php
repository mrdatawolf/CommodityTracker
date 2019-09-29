<?php
/**
 * Created by PhpStorm.
 * User: Patrick
 * Date: 09/27/19
 * Time: 09:05 PM
 */

include_once('common.php');
$conn = dbConnect('sqlite');
//$conn = dbConnect('mysql');

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
    
    upsertCommodityData($id, $stationId, $factionId, $storeId);
}

function upsertCommodityData($id, $stationId, $factionId, $storeId, $conn) {
    $sql = "SELECT count(*) FROM Commodities_Data WHERE commodity_id=$id AND station_id=$stationId and faction_id=$factionId AND store_id=$storeId";
    return ($conn->query($sql) === TRUE);
}

/**
 * @param $id
 * @param $amount
 * @param $stationId
 * @param $factionId
 * @param $storeId
 * @param $conn
 * @return bool
 */
function updateCommodity($id, $amount, $stationId, $factionId, $storeId, $conn) {
    $updatedAt = date("Y-m-d | h:i:sa"); //this will be used to update updated_at
    $sql = "UPDATE Commodities_Data SET amount = $amount, updated_at=$updatedAt WHERE commodity_id=$id AND station_id=$stationId AND faction_id=$factionId AND store_id=$storeId";
    return ($conn->query($sql) === TRUE);
}

function updateFaction($id, $conn) {
    $sql = "UPDATE Factions where id=". $id;
    return ($conn->query($sql) === TRUE);
}

function updateStore($id, $conn) {
    $sql = "UPDATE Stores where id=". $id;
    return ($conn->query($sql) === TRUE);
}

function updateStation($id, $conn) {
    $sql = "UPDATE Stations where id=". $id;
    return ($conn->query($sql) === TRUE);
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
    $updatedAt = date("Y-m-d | h:i:sa"); //this will be used to update updated_at
    $createdAt = date("Y-m-d | h:i:sa"); //this will be used to update created_at
    $sql = "INSERT INTO Commodities_Data (commodity_id, station_id, faction_id, store_id, amount) VALUES ($id, $stationId, $factionId, $storeId, $amount)";
    return ($conn->query($sql) === TRUE);
}

/**
 * @param $factionId
 * @param $name
 * @param PDO $conn
 * @return string
 */
function addFaction($factionId, $name, $conn) {
    $sql = "INSERT INTO Factions (id, title) VALUES ($factionId, $name)";
    return ($conn->query($sql) === TRUE);
}

function addStore($id, $name, $conn) {
    $sql = "INSERT INTO  Stores (id,title) VALUES ($id, $name)";
    return ($conn->query($sql) === TRUE);
}

function addStation($id, $factionId, $name, $conn) {
    $sql = "INSERT INTO Stations (id, faction_id, title) VALUES ($id, $factionId, $name)";
    return ($conn->query($sql) === TRUE);
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
    return ($conn->query($sql) === TRUE);
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