
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
                'publicKey' => 'bd5aef5596a57be24b02766b6bb8bf2a9975c98ef62b97a379dad7a5a4851b22'];
              //  'publicKey' => hash('sha256','password'.'LeChuck'.$timestamp)];
    $data[] = [ 'walletIndex' => 1,
                'name' => 'Jester',
                'Timestamp' =>  $timestamp,
                'publicKey' => '5275ea582875af2965312fa2ab345f8da98aaf1e55acd32aa37407be357695f6'];
              //  'publicKey' => hash('sha256', 'password'.'Jester'.$timestamp)];
    $data[] = [ 'walletIndex' => 2,
                'name' => 'Cosmo',
                'Timestamp' =>  $timestamp,
                'publicKey' => 'e3f9de2fab8f1d2c1f4109b3a344d70da6a19fc0db9b8d0b9975904a39bcc4ee'];
              //  'publicKey' => hash('sha256', 'password'.'Cosmo'.$timestamp)];
         
    file_put_contents('walletDB.json', json_encode($data));


    $data1[] = ['Hash' => '27f7188a70105d726612b23a4d0acf3a2225e21111f3768f0803640c390dd344',
                'Miner' => '$miner',
                'Index' => 0, 
                'PreviousHash' => '$previousHash',
                'Coinbase' => 0,
                'Timestamp' => 0,//microtime(true), 
                'Fees' => 0, 
                'TransactionData' => [],
                'TransactionHashes' => [] ];

    file_put_contents('blockChain.json', json_encode($data1));


    file_put_contents('transactionDB.json', '');


  echo json_encode(1);
}

  // 405: unsupported request method
else {
    error(405, 'POST requests only');
}