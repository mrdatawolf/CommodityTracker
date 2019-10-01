<?php
/**
* @file
* Contains \Drupal\econ_api\Controller\TestAPIController.
*/

namespace Drupal\econ_api\Controller;

use Drupal\Core\Controller\ControllerBase;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
* Controller routines for test_api routes.
*/
class EconAPIController extends ControllerBase {
    
    /**
     * PDO @var
     */
    private $conn;
    private $data;
/**
* Callback for `econ-api/get.json` API method.
*/
public function get_econ( Request $request ) {

$response['data'] = 'Some test data to return';
$response['method'] = 'GET';

return new JsonResponse( $response );
}

/**
* Callback for `econ-api/put.json` API method.
*/
public function put_econ( Request $request ) {

$response['data'] = 'Some test data to return';
$response['method'] = 'PUT';

return new JsonResponse( $response );
}

/**
* Callback for `econ-api/post.json` API method.
*/
public function post_econ( Request $request ) {

// This condition checks the `Content-type` and makes sure to
// decode JSON string from the request body into array.
if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/json' ) ) {
$data = json_decode( $request->getContent(), TRUE );
$request->request->replace( is_array( $data ) ? $data : [] );
}

$this->dbConnect('sqlite');
$this->parsePostData($data);

$response['data'] = 'Some test data to return';
$response['method'] = 'POST';

return new JsonResponse( $response );
}

/**
* Callback for `econ-api/delete.json` API method.
*/
public function delete_econ( Request $request ) {

$response['data'] = 'Some test data to return';
$response['method'] = 'DELETE';

return new JsonResponse( $response );
}

private function setupDBConnect($dbType)
{
    $this->conn = (strtolower($dbType)==='sqlite') ? $this->dbConnect('sqlite') : $this->dbConnect('mysql');
}

public function parsePostData($data)
{
//gather the data we want and sanitize it
    if ($this->isValidRequestedType()) {
        $whatToWorkOn = $this->sanitizeInput($data['dataType']);
//the rest we will set a default of null so when we workign thru our workload we have a consistent value for data to ignore.
        $this->data['serverId'] = (empty($data['ServerId'])) ? null : $this->sanitizeInput($data['ServerId']);
        $this->data['id'] = (empty($data['ItemId'])) ? null : $this->sanitizeInput($data['ItemId']);
        $this->data['itemName'] = (empty($data['ItemName'])) ? null : $this->sanitizeInput($data['ItemName']);
        $this->data['stationId'] = (empty($data['StationId'])) ? null : $this->sanitizeInput($data['StationId']);
        $this->data['stationName'] = (empty($data['StationName'])) ? null : $this->sanitizeInput($data['StationName']);
        $this->data['factionId'] = (empty($data['FactionId'])) ? null : $this->sanitizeInput($data['FactionId']);
        $this->data['factionName'] = (empty($data['FactionName'])) ? null : $this->sanitizeInput($data['FactionName']);
        $this->data['storeId'] = 1;
        $this->data['amount'] = (empty($data['Amount'])) ? null : $this->sanitizeInput($data['Amount']);
        $this->data['price'] = (empty($data['Price'])) ? null : $this->sanitizeInput($data['Price']);

//now we take what we think is fleshed out and ready to go data and work with it.
        switch ($whatToWorkOn) {
            case ('1') :
                //we are going to upsert based on the data we receive
                $this->upsertCommodity();
                break;
            //more cases as needed
            default :
                http_response_code(501);
                echo "Error: nothing matched";
        }
    }
}

//now we do the code for adding and updating records

//initially we are going to have the upserts...which means we are going to update the thing if it is in our database otherwise we will insert it.
    /**
     *
     */
    private function upsertFaction() {
        if($this->doesItExist('Factions', $this->data['factionId'])) {
            $this->updateFaction();
        } else {
            $this->addFaction();
        }
    }
    
    /**
     * @return bool
     */
    private function upsertStation() {
        if ($this->doesItExist('Stations', $this->data['stationId'])) {
            $this->addStation();
        } else {
            $this->updateStation();
        }
        
        return true;
    }
    
    /**
     * @return bool
     */
    private function upsertStore() {
        if ($this->doesItExist('Stores', $this->data['stationId'])) {
            $this->addStore($this->data['storeId'], $this->data['stationId']);
        } else {
            $this->updateStore($this->data['storeId'], $this->data['name']);
        }
        
        return true;
    }
    
    /**
     *
     */
    private function upsertCommodity() {
        if ( $this->doesItExist('Commodities', $this->data['id'])) {
            $this->addCommodity();
        } else {
            $this->updateCommodity();
        }
        //so now we have put in/updated the commodity it's self but we still need to put in the data for it.
        $this->upsertCommodityData();
    }
    
    /**
     *
     */
    private function upsertCommodityData() {
        if ($this->doesItExist('Commodities_Data')) {
            $this->addCommodityData();
        } else {
            $this->updateCommodityData();
        }
    }

//Now we run thru the various updates we might want to do.
    
    /**
     * @return bool
     */
    private function updateCommodity() {
        $sql = "UPDATE Commodities SET title='" . $this->data['name'] . "' WHERE id=" . $this->data['id'];
        return ($this->conn->query($sql) === TRUE);
    }
    private function updateCommodityData() {
        $sql = "UPDATE Commodities_Data SET amount='" . $this->data['amount'] . "', price='". $this->data['price'] . "', station_id='" . $this->data['stationId'] . "', faction_id='". $this->data['factionId'] . "', store_id='" . $this->data['storeId'] . "', server_id='". $this->data['serverId'] . "'  WHERE commodity_id=" . $this->data['id'];
        return ($this->conn->query($sql) === TRUE);
    }
    
    /**
     * @return bool
     */
    private function updateFaction() {
        $sql = "UPDATE Factions SET title='" . $this->data['name'] . "' WHERE id=". $this->data['id'];
        return ($this->conn->query($sql) === TRUE);
    }
    
    /**
     * @return bool
     */
    private function updateStore() {
        $sql = "UPDATE Stores SET title='" . $this->data['name'] . "' WHERE id=". $this->data['id'];
        return ($this->conn->query($sql) === TRUE);
    }
    
    /**
     * @return bool
     */
    private function updateStation() {
        $sql = "UPDATE Stations SET title='" . $this->data['name'] . "'where id=". $this->data['id'];
        return ($this->conn->query($sql) === TRUE);
    }

//now we run thru inserting new things.
    
    /**
     * @return bool
     */
    private function addCommodity() {
        $updatedAt = date("Y-m-d | h:i:sa"); //this will be used to set updated_at
        $createdAt = date("Y-m-d | h:i:sa"); //this will be used to set created_at
        $sql = "INSERT INTO Commodities (id, title) VALUES ($this->data['id'], $this->data['name'])";
        return ($this->conn->query($sql) === TRUE);
    }
    
    /**
     * @return bool
     */
    private function addFaction() {
        $sql = "INSERT INTO Factions (id, title) VALUES ($this->data['factionId,'], $this->data['name'])";
        return ($this->conn->query($sql) === TRUE);
    }
    
    /**
     * @return bool
     */
    private function addStore() {
        $sql = "INSERT INTO  Stores (id,title) VALUES ($this->data['id'], $this->data['name'])";
        return ($this->conn->query($sql) === TRUE);
    }
    
    /**
     * @return bool
     */
    private function addStation() {
        $sql = "INSERT INTO Stations (id, faction_id, title) VALUES ($this->data['id'], $this->data['factionId'], $this->data['name'])";
        return ($this->conn->query($sql) === TRUE);
    }


//now we will put helper functions.  Not things that are modifing the database but checking it or workign with the data before it's displayed or put into the db.
    /**
     * @param $table
     * @return bool
     * note this should be pulled out... to generic
     */
    private function doesItExist($table, $id) {
        $sql = "SELECT count(*) FROM $table WHERE id=$id";
        return ($this->conn->query($sql) === TRUE);
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
    private function isValidRequestedType() {
        $dataType = (! empty($this->data['DataType'])) ? $this->data['DataType'] : null;
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
    
    /**
     * @param string $dbType
     */
    function dbConnect($dbType) {
        try{
            if($dbType === 'mysql') {
                $hostname = "localhost";
                $username = "freder26_php";
                $password = "don't post passwords in discord";
                $dbname = "freder26_drup354";
                $this->conn = new PDO('mysql:host='. $hostname . ';dbname='. $dbname, $username, $password);
            } elseif($dbType === 'sqlite') {
                $this->conn = new PDO('sqlite:./commodityTracker.sqlite');
            }
            else {
                throw new PDOException('bad DB type given');
            }
        }
        catch(PDOException $ex){
            die(json_encode(array('outcome' => false, 'message' => 'Unable to connect')));
        }
    }
    
    function dd($input) {
        echo "<pre>";
        print_r($input);
        echo "</pre>";
        die();
    }

}
