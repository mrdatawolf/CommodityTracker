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
* Controller routines for econ_api routes.
*/
class EconAPIController extends ControllerBase {

    private $data, $createdAt, $updatedAt;
    /**
     * @var Array
     */
    private  $response;


    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function get_econ( Request $request ) {
        $data = $request->query->all();
        $this->data['dataType'] = (empty($data['dataType'])) ? null : $this->sanitizeInput($data['dataType']);
        if ($this->isValidRequestedType()) {
          $this->parseData($data);
            //here we should allow filtering where we look for $data['fields'] sanatize it and then limit the respose to data from those fields
            //Also we should allow them to add other limiting data like commodity_id=11224122143412,updated_after=2019/10/03
          $commodities = $this->getCommodities(1, 2000);
         
          $rows = $this->formatCommodityData($commodities);
  
  
          $this->response['data'] = $rows;
          $this->response['method'] = 'GET';
        } else {
          $this->response['data'] = [];
          $this->response['method'] = 'GET';
          http_response_code(501);
        }
    
        return new JsonResponse( $this->response );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function put_econ( Request $request ) {
        $this->response['data'] = 'Some test data to return';
        $this->response['method'] = 'PUT';

        return new JsonResponse( $this->response );
    }


    /**
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function post_econ( Request $request ) {
        if(empty($this->response)) {
            $this->response['data'] = [];
        }
      //now we take what we think is fleshed out and ready to go data and work with it.
      $data = $request->query->all();
      $this->data['dataType'] = (empty($data['dataType'])) ? null : $this->sanitizeInput($data['dataType']);
      if ($this->isValidRequestedType()) {
        switch ($this->data['dataType']) {
          case ('1') :
            $parseResult = $this->parseData($data);
            $this->response['data']['parse'] = $parseResult;
            $factionResult =  $this->upsertFaction();
            $this->response['data']['faction'] = $factionResult;
            $stationResult = $this->upsertStation();
            $this->response['data']['station'] = $stationResult;
            $storeResult = $this->upsertStore();
            $this->response['data']['store'] = $storeResult;
            //we are going to upsert based on the data we receive
            $commodityResult = $this->upsertCommodity();
            $this->response['data']['commodity'] = $commodityResult;
            $this->response['data'][] = 'OK';
            break;
          //more cases as needed
          default :
            $this->response['data'][] ="unknown data type";
            http_response_code(501);
        }
      } else {
        $this->response['data'][] = "invalid request";
        http_response_code(501);
      }
      
        $this->response['method'] = 'POST';
        return new JsonResponse($this->response);
    }

    /**
    * Callback for `econ-api/delete.json` API method.
    */
    public function delete_econ( Request $request ) {

        $this->response['data'] = 'Some test data to return';
        $this->response['method'] = 'DELETE';

        return new JsonResponse( $this->response );
    }
    
    /**
     * @param $data
     *  adds the given data into class global $data (assuming we care about it)...
     * @return bool
     */
    public function parseData($data)
    {
      $this->updatedAt = date("Y-m-d | h:i:sa"); //this will be used to set updated_at
      $this->createdAt = date("Y-m-d | h:i:sa"); //this will be used to set created_at
        //gather the data we want and sanitize it
        $this->data['dataType']     = $this->sanitizeInput($data['dataType']);
        if ($this->isValidRequestedType()) {
            $this->data['whatToWorkOn'] = $this->sanitizeInput($data['dataType']);
            $this->data['storeId']      = 1;
            $this->data['storeName']      = "Default";
            $this->data['serverId']     = (empty($data['ServerId']))    ? null : $this->sanitizeInput($data['ServerId']);
            $this->data['id']           = (empty($data['ItemId']))      ? null : $this->sanitizeInput($data['ItemId']);
            $this->data['itemName']     = (empty($data['ItemName']))    ? null : $this->sanitizeInput($data['ItemName']);
            $this->data['stationId']    = (empty($data['StationId']))   ? null : $this->sanitizeInput($data['StationId']);
            $this->data['stationName']  = (empty($data['StationName'])) ? null : $this->sanitizeInput($data['StationName']);
            $this->data['factionId']    = (empty($data['FactionId']))   ? null : $this->sanitizeInput($data['FactionId']);
            $this->data['factionName']  = (empty($data['FactionName'])) ? null : $this->sanitizeInput($data['FactionName']);
            $this->data['amount']       = (empty($data['Amount']))      ? null : $this->sanitizeInput($data['Amount']);
            $this->data['price']        = (empty($data['Price']))       ? null : $this->sanitizeInput($data['Price']);
            $this->data['gridName']    = (empty($data['gridName']))  ? null : $this->sanitizeInput($data['gridName']);

            $this->data['location']['X']    = (empty($data['lx']))  ? null : $this->sanitizeInput($data['lx']);
            $this->data['location']['Y']    = (empty($data['ly']))  ? null : $this->sanitizeInput($data['ly']);
            $this->data['location']['Z']    = (empty($data['lz']))  ? null : $this->sanitizeInput($data['lz']);
            
            return true;
        }
        
        return false;
    }

//now we do the code for adding and updating records

//initially we are going to have the upserts...which means we are going to update the thing if it is in our database otherwise we will insert it. Then will also verify we have the needed data available
    private function upsertFaction() {
      if(! empty($this->data['factionId']) && ! empty($this->data['factionName'])) {
        $fetch = \Drupal::database()->query("SELECT * FROM {Factions} WHERE id = " . $this->data['factionId'])->fetch();
        if ( empty($fetch)) {
          $this->updateFaction();
        } else {
          $this->addFaction();
        }
        
        return true;
      }
      
      return false;
    }
    
    /**
     * @return bool
     */
    private function upsertStation() {
      if(! empty($this->data['stationId']) && ! empty($this->data['stationName']) && ! empty($this->data['gridName'])) {
        $fetch = \Drupal::database()->query("SELECT * FROM {Stations} WHERE id = " . $this->data['stationId'])->fetch();
        if ( empty($fetch)) {
          $this->addStation();
        } else {
          $this->updateStation();
        }
  
        return true;
      }
      
      return false;
    }
    
    /**
     * @return bool
     */
    private function upsertStore() {
      if(!empty($this->data['storeId']) && ! empty($this->data['storeName'])) {
        $fetch = \Drupal::database()->query("SELECT * FROM {Stores} WHERE id = " . $this->data['storeId'])->fetch();
        if ( empty($fetch)) {
          $this->addStore();
        } else {
          $this->updateStore();
        }
  
        return true;
      }
      
      return false;
    }
    
    /**
     *
     */
    private function upsertCommodity() {
      if(! empty($this->data['id']) && ! empty($this->data['itemName'])) {
        $fetch = \Drupal::database()->query("SELECT * FROM {Commodities} WHERE id = " . $this->data['id'])->fetch();
        if ( empty($fetch)) {
          $this->addCommodity();
        } else {
          $this->updateCommodity();
        }
        
        $commodityDataResult = $this->upsertCommodityData();
          $this->response['data']['commodityData'] = $commodityDataResult;
        return true;
      }
      
      return false;
    }
    
    private function upsertCommodityData() {
      //so now we have put in/updated the commodity it's self but we still need to put in the data for it.
      if(!empty($this->data['id']) &&
        ! empty($this->data['itemName']) &&
        !empty($this->data['amount']) &&
        ! empty($this->data['price']) &&
        ! empty($this->data['stationId']) &&
        ! empty($this->data['factionId']) &&
        ! empty($this->data['storeId']) &&
        ! empty($this->data['serverId'])
      ) {
        $fetch = \Drupal::database()->query("SELECT * FROM {Commodities_Data} WHERE station_id=" . $this->data['stationId'] . " AND server_id=" . $this->data['serverId'] . " AND commodity_id = " . $this->data['id'])->fetch();
        if ( empty($fetch)) {
                $this->addCommodityData();
            } else {
                $this->updateCommodityData();
            }
            
            return true;
      }
      
      return false;
    }

//Now we run thru the various updates we might want to do.
    
    /**
     * @return bool
     */
    private function updateCommodity() {
        $sql = "UPDATE {Commodities} SET title='" . $this->data['itemName'] . "' WHERE id=" . $this->data['id'];
  
        return (\Drupal::database()->query($sql) === TRUE);
    }
    
    private function updateCommodityData() {
        $sql = "UPDATE {Commodities_Data} SET amount=" . $this->data['amount'] . ", price=" . $this->data['price'] . ", station_id=" . $this->data['stationId'] . ", faction_id=" . $this->data['factionId'] . ", store_id=" . $this->data['storeId'] . ", server_id=" . $this->data['serverId'] . ", updated_at='" . $this->updatedAt  . "'  WHERE commodity_id=" . $this->data['id'];
  
        return (\Drupal::database()->query($sql) === TRUE);
    }
    
    /**
     * @return bool
     */
    private function updateFaction() {
        $sql = "UPDATE {Factions} SET title='" . $this->data['factionName'] . "' WHERE id=" . $this->data['factionId'];
  
        return (\Drupal::database()->query($sql) === TRUE);
    }
    
    /**
     * @return bool
     */
    private function updateStore() {
        $sql = "UPDATE {Stores} SET title='" . $this->data['storeName'] . "' WHERE id=" . $this->data['storeId'];
  
        return (\Drupal::database()->query($sql) === TRUE);
    }
    
    /**
     * @return bool
     */
    private function updateStation() {
        $sql = "UPDATE {Stations} SET title='" . $this->data['stationName'] . "', x=" . $this->data['location']['X'] . " , y=" . $this->data['location']['Y'] . " , z=" . $this->data['location']['Z'] . ", gridName='" . $this->data['gridName'] . "'  where id=" . $this->data['stationId'];
  
        return (\Drupal::database()->query($sql) === TRUE);
    }

//now we run thru inserting new things.
    
    /**
     * @return bool
     */
    private function addCommodity() {
        $sql = "INSERT INTO {Commodities} (id, title) VALUES (" . $this->data['id'] . ", '" . $this->data['itemName'] . "')";
        
        return (\Drupal::database()->query($sql) === TRUE);
    }
    
    private function addCommodityData() {
        $sql = "INSERT INTO {Commodities_Data} (commodity_id, amount, price, station_id, faction_id, store_id, server_id, created_at, updated_at) VALUES (" . $this->data['id'] . ", " . $this->data['amount'] . ", " . $this->data['price'] . ", " . $this->data['stationId'] . ", " . $this->data['factionId'] . ", " . $this->data['storeId'] . ", " . $this->data['serverId'] . ", '" . $this->createdAt  . "', '" . $this->updatedAt  . "')";
        
        return (\Drupal::database()->query($sql) === TRUE);
    }
    
    /**
     * @return bool
     */
    private function addFaction() {
        $sql = "INSERT INTO {Factions} (id, title) VALUES (" . $this->data['factionId'] . ", '" . $this->data['factionName'] . "')";
        
        return (\Drupal::database()->query($sql) === TRUE);
    }
    
    /**
     * @return bool
     */
    private function addStore() {
        $sql = "INSERT INTO  {Stores} (id,title) VALUES (" . $this->data['storeId'] . ", '" . $this->data['storeName'] . "')";
        
        return (\Drupal::database()->query($sql) === TRUE);
    }
    
    /**
     * @return bool
     */
    private function addStation() {
        $sql = "INSERT INTO {Stations} (id, faction_id, title, x, y, z, gridName) VALUES (" . $this->data['stationId'] . ", " . $this->data['factionId'] . ", '" . $this->data['stationName'] . "', " . $this->data['location']['X'] . ", " . $this->data['location']['Y'] . ", " . $this->data['location']['Z'] . ", '" . $this->data['gridName'] . "')";
        
        return (\Drupal::database()->query($sql) === TRUE);
    }


//now we will put helper functions.  Not things that are modifing the database but checking it or workign with the data before it's displayed or put into the db.
    
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
        $dataType = (! empty($this->data['dataType'])) ? $this->data['dataType'] : null;
        $allowedDataTypes = ['1'];

        return (is_null($dataType) || ! in_array($dataType, $allowedDataTypes)) ? false : true;
    }
    
    function dd($input) {
        echo "<pre>";
        print_r($input);
        echo "</pre>";
        die();
    }

//now we add the display section for get

    function formatCommodityData($commodities) {
        $rows = [];
        foreach ($commodities as $commodity) {
            $row = [
              "name" => $commodity['name'],
              "totalAmount" => $commodity['data']['Amounts']['totalAmount'],
              "maxAmount" => $commodity['data']['Amounts']['maxAmount'],
              "minAmount" => $commodity['data']['Amounts']['minAmount'],
              "maxPrice" => $commodity['data']['Prices']['maxPrice'],
              "minPrice" => $commodity['data']['Prices']['minPrice'],
              "avgPrice" => $commodity['data']['Prices']['avgPrice'],
              "totalPrices" => $commodity['data']['Prices']['totalPrices']
            ];
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param int $serverId
     * @param int $maximumToDisplay
     * @return array
     */
    function getCommodities($serverId, $maximumToDisplay = 500) {
        $commodityArray = [];
        $commoditiesSelect = "SELECT * FROM {Commodities} LIMIT " . $maximumToDisplay;
        foreach(\Drupal::database()->query($commoditiesSelect) as $row) {
            $commodityArray[$row->id]   = [
                'name' => $row->title,
                'data' => $this->getCommodityData($row->id, $serverId)
            ];
        }

        return $commodityArray;
    }

    /**
     * @param $commodityId
     * @param $serverId
     * @return array
     */
    function getCommodityData($commodityId, $serverId) {
        $return = [
            'Amounts' => [],
            'Prices' => []
        ];
        $totalAmountSelect = "SELECT SUM(amount) as totalAmount, MAX(amount) as maxAmount, MIN(amount) as minAmount FROM {Commodities_Data} WHERE commodity_id=" . $commodityId . " AND server_id=" . $serverId;
        foreach(\Drupal::database()->query($totalAmountSelect) as $row) {
            $return['Amounts'] = [
                'totalAmount' => $row->totalAmount,
                'maxAmount' => $row->maxAmount,
                'minAmount' => $row->minAmount
            ];
        }

        $priceDataSelect = "SELECT MAX(price) as maxPrice, MIN(price) as minPrice, AVG(price) as avgPrice, COUNT(price) as totalPrices FROM {Commodities_Data} WHERE commodity_id=" . $commodityId . " AND server_id=" . $serverId;

        foreach(\Drupal::database()->query($priceDataSelect) as $row) {
            $return['Prices'] = [
                'maxPrice' => $row->maxPrice,
                'minPrice' => $row->minPrice,
                'avgPrice' => $row->avgPrice,
                'totalPrices' => $row->totalPrices
            ];
        }

        return $return;
    }

}

