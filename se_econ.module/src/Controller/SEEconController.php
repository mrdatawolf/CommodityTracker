<?php
/**
* @file
* Contains \Drupal\se_econ\Controller\SEEconController.
*/

namespace Drupal\se_econ\Controller;

use Drupal\Core\Controller\ControllerBase;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;

/**
* Controller routines for test_api routes.
*/
class SEEconController extends ControllerBase {

    private $data, $createdAt, $updatedAt;
    /**
     * @var Array
     */
    private  $response;


    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function get_econ( Request $request ) {
        //here we should allow filtering where we look for $data['fields'] sanatize it and then limit the respose to data from those fields
        //Also we should allow them to add other limiting data like commodity_id=11224122143412,updated_after=2019/10/03
        $data = $request->query->all();
        $this->parseData($data);
        $commodities = $this->getCommodities(1);

        $rows = $this->formatCommodityData($commodities);

        return [
          '#theme' => 'economy_table',
          '#rows' => $rows
        ];
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function post_econ( Request $request ) {
        $response['data'] = 'OK';
        $response['method'] = 'POST';
  
        return new JsonResponse($response);
    }
  
  /**
   * @param $data
   * adds the given data into class global $data (assuming we care about it...
   */
    public function parseData($data)
    {
      $this->updatedAt = date("Y-m-d | h:i:sa"); //this will be used to set updated_at
      $this->createdAt = date("Y-m-d | h:i:sa"); //this will be used to set created_at
        //gather the data we want and sanitize it
        $this->data['dataType']     = (empty($data['dataType']))    ? null : $this->sanitizeInput($data['dataType']);
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
    }



//now we will put helper functions.  Not things that are modifing the database but checking it or workign with the data before it's displayed or put into the db.
    
    /**
     * @param $value
     * @return null|string|string[]
     */
    function sanitizeInput($value) {
        return preg_replace('/[^A-Za-z0-9 ]/', '', $value);
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
     * @param $serverId
     * @return array
     */
    function getCommodities($serverId) {
        $commodityArray = [];
        $commoditiesSelect = "SELECT * FROM {Commodities}";
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
        //, lag(amount) OVER (ORDER BY updated_at) as prevAmount 
        $totalAmountSelect = "SELECT SUM(amount) as totalAmount, MAX(amount) as maxAmount, MIN(amount) as minAmount FROM {Commodities_Data} WHERE commodity_id=" . $commodityId . " AND server_id=" . $serverId;
        foreach(\Drupal::database()->query($totalAmountSelect) as $row) {
            $return['Amounts'] = [
                'totalAmount' => $row->totalAmount,
                'maxAmount' => $row->maxAmount,
                'minAmount' => $row->minAmount,
                'trending'  => $trend
            ];
        }
        //, lag(price) OVER (ORDER BY updated_at) as prevPrice
        $priceDataSelect = "SELECT MAX(price) as maxPrice, MIN(price) as minPrice, AVG(price) as avgPrice, COUNT(price) as totalPrices FROM {Commodities_Data} WHERE commodity_id=" . $commodityId . " AND server_id=" . $serverId;
        foreach(\Drupal::database()->query($priceDataSelect) as $row) {
            $return['Prices'] = [
                'maxPrice' => $row->maxPrice,
                'minPrice' => $row->minPrice,
                'avgPrice' => $row->avgPrice,
                'totalPrices' => $row->totalPrices,
                'trending' => $trend
            ];
        }

        return $return;
    }

}

