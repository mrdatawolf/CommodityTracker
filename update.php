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
    $serverId       = (empty($_POST['ServerId'])) ? null : sanitizeInput($_POST['ServerId']);
    $id             = (empty($_POST['ItemId'])) ? null : sanitizeInput($_POST['ItemId']);
    $itemName       = (empty($_POST['ItemName'])) ? null : sanitizeInput($_POST['ItemName']);
    $stationId      = (empty($_POST['StationId'])) ? null : sanitizeInput($_POST['StationId']);
    $stationName    = (empty($_POST['StationName'])) ? null : sanitizeInput($_POST['StationName']);
    $factionId      = (empty($_POST['FactionId'])) ? null : sanitizeInput($_POST['FactionId']);
    $factionName    = (empty($_POST['FactionName'])) ? null : sanitizeInput($_POST['FactionName']);
    $storeId        = 1;
    $amount         = (empty($_POST['Amount'])) ? null : sanitizeInput($_POST['Amount']);
    $price          = (empty($_POST['Price'])) ? null : sanitizeInput($_POST['Price']);

//now we take what we think is fleshed out and ready to go data and work with it.
    switch ($whatToWorkOn) {
        case ('1') :
            //we are going to upsert based on the data we receive
            upsertCommodity($id, $stationId, $factionId, $storeId, $amount, $conn);
            break;
            //more cases as needed
        default :
            http_response_code(501);
            echo "Error: nothing matched";
    }
}

//now we do the code for adding and updating records

//initially we are going to have the upserts...which means we are going to update the thing if it is in our database otherwise we will insert it.
/**
 * @param $factionId
 * @param $name
 * @param $conn
 */
function upsertFaction($factionId, $name, $conn) {
    if(doesItExist('Factions', $factionId, $conn)) {
        updateFaction($factionId, $conn);
    } else {
        addFaction($factionId, $name, $conn);
    }
}

/**
 * @param $stationId
 * @param $factionId
 * @param $name
 * @param $conn
 * @return bool
 */
function upsertStation($stationId, $factionId, $name, $conn) {
    if (doesItExist('Stations', $stationId, $conn)) {
        addStation($stationId, $factionId, $name, $conn);
    } else {
        updateStation($stationId, $conn);
    }
    
    return true;
}

/**
 * @param $storeId
 * @param $factionId
 * @param $stationId
 * @param $conn
 * @return bool
 */
function upsertStore($storeId, $factionId, $stationId, $conn) {
    if (doesItExist('Stores', $stationId, $conn)) {
        addStore($storeId, $stationId,$conn);
    } else {
        updateStore($storeId, $conn);
    }

    return true;
}

/**
 * @param $id
 * @param $name
 * @param $stationId
 * @param $factionId
 * @param $storeId
 * @param $amount
 * @param $price
 * @param $serverId
 * @param PDO $conn
 */
function upsertCommodity($id, $name, $stationId, $factionId, $storeId, $amount, $price, $serverId, $conn) {
    if (doesItExist('Commodities', $id, $conn)) {
        addCommodity($id, $name, $conn);
    } else {
        updateCommodity($id, $name, $conn);
    }
    //so now we have put in/updated the commodity it's self but we still need to put in the data for it.
    upsertCommodityData($id, $amount, $price, $stationId, $factionId, $storeId, $serverId, $conn);
}

/**
 * @param $id
 * @param $amount
 * @param $stationId
 * @param $factionId
 * @param $storeId
 * @param $conn
 */
function upsertCommodityData($id, $amount, $price, $stationId, $factionId, $storeId, $serverId, $conn) {
    if (doesItExist('Commodities_Data', $id, $conn)) {
        addCommodityData($id, $amount, $price, $stationId, $factionId, $storeId, $serverId, $conn);
    } else {
        updateCommodityData($id, $amount, $price, $stationId, $factionId, $storeId, $serverId, $conn);
    }
}

//Now we run thru the various updates we might want to do.
/**
 * @param $id
 * @param $name
 * @param PDO $conn
 * @return bool
 */
function updateCommodity($id, $name, $conn) {
    $sql = "UPDATE Commodities SET title='" . $name . "' WHERE id=" . $id;
    return ($conn->query($sql) === TRUE);
}
function updateCommodityData($id, $amount, $price, $stationId, $factionId, $storeId, $serverId, $conn) {
    $sql = "UPDATE Commodities_Data SET amount='" . $amount . "', price='". $price . "', station_id='" . $stationId . "', faction_id='". $factionId . "', store_id='" . $storeId . "', server_id='". $serverId . "'  WHERE commodity_id=" . $id;
    return ($conn->query($sql) === TRUE);
}
/**
 * @param $id
 * @param $name
 * @param PDO $conn
 * @return bool
 */
function updateFaction($id, $name, $conn) {
    $sql = "UPDATE Factions SET title='" . $name . "' WHERE id=". $id;
    return ($conn->query($sql) === TRUE);
}

/**
 * @param $id
 * @param $name
 * @param PDO $conn
 * @return bool
 */
function updateStore($id, $name, $conn) {
    $sql = "UPDATE Stores SET title='" . $name . "' WHERE id=". $id;
    return ($conn->query($sql) === TRUE);
}

/**
 * @param $id
 * @param $name
 * @param PDO $conn
 * @return bool
 */
function updateStation($id, $name, $conn) {
    $sql = "UPDATE Stations SET title='" . $name . "'where id=". $id;
    return ($conn->query($sql) === TRUE);
}

//now we run thru inserting new things.
/**
 * @param $id
 * @param $name
 * @param PDO $conn
 * @return bool
 */
function addCommodity($id, $name, $conn) {
    $updatedAt = date("Y-m-d | h:i:sa"); //this will be used to set updated_at
    $createdAt = date("Y-m-d | h:i:sa"); //this will be used to set created_at
    $sql = "INSERT INTO Commodities (id, title) VALUES ($id, $name)";
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

/**
 * @param $id
 * @param $name
 * @param PDO $conn
 * @return bool
 */
function addStore($id, $name, $conn) {
    $sql = "INSERT INTO  Stores (id,title) VALUES ($id, $name)";
    return ($conn->query($sql) === TRUE);
}

/**
 * @param $id
 * @param $factionId
 * @param $name
 * @param PDO $conn
 * @return bool
 */
function addStation($id, $factionId, $name, $conn) {
    $sql = "INSERT INTO Stations (id, faction_id, title) VALUES ($id, $factionId, $name)";
    return ($conn->query($sql) === TRUE);
}


//now we will put helper functions.  Not things that are modifing the database but checking it or workign with the data before it's displayed or put into the db.
/**
 * @param $table
 * @param $id
 * @param PDO $conn
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
    $dataType = (! empty($_POST['DataType'])) ? $_POST['DataType'] : null;
    $allowedDataTypes = ['1'];
    if(is_null($dataType)) {
        //406 is "Not Acceptable" they didn't send a dataType so we can't do anything...
        http_response_code(406);
        
        return false;
    }
    elseif(! in_array($dataType, $allowedDataTypes)) {
        //501 means "Not Implemented"  so we are saying the datatype they asked for isn't implemented
        http_response_code(501);
        
        return false;
    }
    
    return true;
}