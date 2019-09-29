<?php
/**
 * @param $dbType
 * @return PDO|null
 */
function dbConnect($dbType) {
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