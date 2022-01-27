
<?php
/*# ----------------------------------------------------------
# --    Mark Anderson    --     Student Number: 220180473   --
# --        UNE          --       Cosc301 - Blockchain      --
# ------------------------------------------------------------*/

ini_set('display_errors', TRUE);
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once('helperFunctions.php');

/*============================================================= */

  // ensure POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  
    // TODO: Validate here


  $json = file_get_contents('blockChain.json');
  $blocksJsonData = json_decode($json, true); 

  if (intval($_POST['Index']) === count($blocksJsonData)){

    $blocksJsonData[] =['Hash' => $_POST['Hash'],
                        'Miner' => $_POST['Miner'],
                        'Index' => $_POST['Index'],
                        'PreviousHash' => $_POST['PreviousHash'],
                        'Nonce' => $_POST['Nonce'],
                        'Coinbase' => $_POST['Coinbase'],
                        'Timestamp' => $_POST['Timestamp'], 
                        'Fees' => $_POST['Fees'], 
                        'TransactionData' => $_POST['TransactionData'],
                        'TransactionHashes' => $_POST['TransactionHashes'] ];

    file_put_contents('blockChain.json', json_encode($blocksJsonData));

  }  else if ( file_get_contents('serverID') === $_POST['Miner'] 
                  && !isset($_POST['DNR']) ) {

    // if we arn't going to use this block,
    // ---  and we are the server that mined it  ---
    // ---  and it didn't come from requestblock.php ---
    //  recycle the transactions into the mempool
    
    $json = file_get_contents('transactionDB.json');
    $transJsonData = json_decode($json, true);

    foreach ( $_POST['TransactionData'] as $ind => $obj ){
      if ( $obj['Sender'] !== 'Mining_Reward') {
        $transJsonData[] = ['Hash'   => $obj['Hash'],
                            'Sender' => $obj['Sender'],
                            'Receiver' => $obj['Receiver'],
                            'Value' => $obj['Value'],
                            'Fee' => $obj['Fee'],
                            'Timestamp' => $obj['Timestamp'] ];
      }

    }
    file_put_contents('transactionDB.json', json_encode($transJsonData));
  }
}


else {
    error(405, 'POST requests only');
}