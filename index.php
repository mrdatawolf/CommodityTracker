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

$commodities = getCommodities(1, $conn);
$rows = formatCommodityDataAsRows($commodities);
?>
<table>
    <thead>
    <tr>
    <th>Commodity</th>
    <th>totalAmount</th>
    <th>maxAmount</th>
    <th>minAmount</th>
    <th>maxPrice</th>
    <th>minPrice</th>
    <th>avgPrice</th>
    <th>totalPrices</th>
    </tr>
    </thead>
    <tbody>
        <?php
        foreach ($rows as $row) {
            ?>
            <tr>
            <?php
            echo $row;
            ?>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
<?php

function formatCommodityDataAsRows($commodities) {
    $rows = [];
    foreach ($commodities as $commodity) {
        $row = "<td>" . $commodity['title'] . "</td>";
        foreach($commodity['data'] as $data) {
            foreach($data['Amounts'] as $amount) {
                $row .= "<td class='totalAmount'>" . $amount['totalAmount'] . "</td>";
                $row .= "<td class='maxAmount'>" . $amount['maxAmount'] . "</td>";
                $row .= "<td class='minAmount'>" . $amount['minAmount'] . "</td>";
            }
            foreach($data['Prices'] as $price) {
                $row .= "<td class='maxPrice'>" . $amount['maxPrice'] . "</td>";
                $row .= "<td class='minPrice'>" . $amount['minPrice'] . "</td>";
                $row .= "<td class='avgPrice'>" . $amount['avgPrice'] . "</td>";
                $row .= "<td class='totalPrices'>" . $amount['totalPrices'] . "</td>";
            }
        }
    }
    
    return $rows;
}

function getCommodities($serverId, $conn) {
    $commodityArray = [];
    $commoditiesSelect = "SELECT * FROM Commodities";
    foreach(mysqli_query($conn, $commoditiesSelect) as $row) {
        $commodityId                    = $row['id'];
        $commodityName                  = $row['title'];
        $commodityData                  = getCommodityData($commodityId, $serverId, $conn);
        $commodityArray[$commodityId]   = [
            'name' => $commodityName,
            'data' => $commodityData
        ];
    }
    
    return $commodityArray;
}

function getCommodityData($commodityId, $serverId, $conn) {
    $return = [
        'Amounts' => [],
        'Prices' => []
    ];
    $totalAmountSelect = "SELECT SUM(amount) as totalAmount, MAX(amount) as maxAmount, MIN(amount) as minAmount FROM Commodities_Data WHERE commodity_id=" . $commodityId . " AND server_id=" . $serverId;
    foreach(mysqli_query($conn, $totalAmountSelect) as $row) {
        $return['Amounts'] = [
            'totalAmount' => $row['totalAmount'],
            'maxAmount' => $row['maxAmount'],
            'minAmount' => $row['minAmount']
        ];
    }
    
    $priceDataSelect = "SELECT MAX(price) as maxPrice, MIN(price) as minPrice, AVG(price) as avgPrice, COUNT(price) as totalPrices";
    foreach(mysqli_query($conn, $priceDataSelect) as $row) {
        $return['Prices'] = [
            'maxPrice' => $row['maxPrice'],
            'minPrice' => $row['minPrice'],
            'avgPrice' => $row['avgPrice'],
            'totalPrices' => $row['totalPrices']
        ];
    }
    
    return $return;
}
