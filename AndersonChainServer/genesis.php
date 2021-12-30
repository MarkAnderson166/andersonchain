
<?php
/*# ----------------------------------------------------------
# --    Mark Anderson    --     Student Number: 220180473   --
# --        UNE          --       Cosc301 - Blockchain      --
# ------------------------------------------------------------*/

ini_set('display_errors', TRUE);
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once('helperFunctions.php');


/*=============== Make 3 Wallets and a Genesis Block ==================*/


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $timestamp = microtime(true);
    $data[] = [ 'walletIndex' => 0,
                'name' => 'LeChuck',
                'Timestamp' =>  $timestamp,
                'publicKey' => hash('sha256','password'.'LeChuck'.$timestamp)];
    $data[] = [ 'walletIndex' => 1,
                'name' => 'Jester',
                'Timestamp' =>  $timestamp,
                'publicKey' => hash('sha256', 'password'.'Jester'.$timestamp)];
    $data[] = [ 'walletIndex' => 2,
                'name' => 'Cosmo',
                'Timestamp' =>  $timestamp,
                'publicKey' => hash('sha256', 'password'.'Cosmo'.$timestamp)];
         
    file_put_contents('walletDB.json', json_encode($data));


    $data1[] = [ 'Hash' => '000000000000000000',
                'Miner' => '$miner',
                'Index' => 0, 
                'Previous Hash' => '$previousHash',
                'Difficulty' => 0,
                'Coinbase' => 0,
                'Timestamp' => microtime(true), 
                'Fees' => 0, 
                'Transaction Data' => [],
                'Transaction Hashes' => [] ];

    file_put_contents('blockChain.json', json_encode($data1));


    file_put_contents('transactionDB.json', '');


  echo json_encode(1);
}

  // 405: unsupported request method
else {
    error(405, 'POST requests only');
}