<?php
/**
 * @param $dbType
 * @return PDO|null
 */
function dbConnect($dbType) {
    try{
        if($dbType === 'mysql') {
            $hostname = "localhost";
            $username = "freder26_php";
            $password = "don't post passwords in discord";
            $dbname = "freder26_drup354";
            $conn = new PDO('mysql:host='. $hostname . ';dbname='. $dbname, $username, $password);
        } elseif($dbType === 'sqlite') {
            $conn = new PDO('sqlite:./commodityTracker.sqlite');
        }
        else {
            $conn = null;
        }
    
        return $conn;
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