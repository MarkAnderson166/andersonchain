
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

    $data[] = [ 'privateKey'=> 'Guybrush Threep Wood',
                'publicKey' => hash('sha256','Guybrush Threep Wood'),
                'Timestamp' => 0 ];
    $data[] = [ 'privateKey'=> 'Jester LeVorr Stone',
                'publicKey' => hash('sha256','Jester LeVorr Stone'),
                'Timestamp' => 0 ];
    $data[] = [ 'privateKey'=> 'Lindy Llyod Beige',
                'publicKey' => hash('sha256','Lindy Llyod Beige'),
                'Timestamp' => 0 ];

         
    file_put_contents('walletDB.json', json_encode($data));


    $data1[] = ['Hash' => '27f7188a70105d726612b23a4d0acf3a2225e21111f3768f0803640c390dd344',
                'Miner' => '$miner',
                'Index' => 0, 
                'PreviousHash' => '$previousHash',
                'Nonce' => 0,
                'Coinbase' => 0,
                'Timestamp' => 0,
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