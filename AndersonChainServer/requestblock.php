
<?php
/*# ----------------------------------------------------------
# --    Mark Anderson    --     Student Number: 220180473   --
# --        UNE          --       Cosc301 - Blockchain      --
# ------------------------------------------------------------*/

ini_set('display_errors', TRUE);
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");


/*============================================================= */
// recieves message that server X is about to mine block X.
// if THIS server already has block X, send it to server X's catchblock.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $json = file_get_contents('blockChain.json');
  $blocksJsonData = json_decode($json, true); 

  if ($_POST['Index'] < count($blocksJsonData)){

    foreach($blocksJsonData as $ind => $obj) {
      if ($obj['Index'] === $_POST['Index']){
        $foo = array( 'Hash' => $obj['Hash'],
                      'Miner' => $obj['Miner'],
                      'Index' => $obj['Index'],
                      'PreviousHash' => $obj['PreviousHash'],
                      'Nonce' => $obj['Nonce'],
                      'Coinbase' => $obj['Coinbase'],
                      'Timestamp' => $obj['Timestamp'], 
                      'Fees' => $obj['Fees'], 
                      'TransactionData' => $obj['TransactionData'],
                      'TransactionHashes' => $obj['TransactionHashes'],
                      'DNR' => 'do not recycle flag, used to avoid rare bug');
      

        $options = array(
          'http' => array(
              'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
              'method'  => 'POST',
              'content' => http_build_query($foo)
          )
        );
        $context  = stream_context_create($options);
        $url = 'https://turing.une.edu.au/~mander53/turing'.$_POST['server'].'/catchblock.php';
        $result = file_get_contents($url, false, $context);
      }
    }
    

  } else {
      // this echo is the signal that 'your chain is sync'd, carry on mining'
    echo 1;
  }


}  

else {
    error(405, 'POST requests only');
}