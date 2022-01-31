
<?php
/*# ----------------------------------------------------------
# --    Mark Anderson    --     Student Number: 220180473   --
# --        UNE          --       Cosc301 - Blockchain      --
# ------------------------------------------------------------*/

ini_set('display_errors', TRUE);
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once('helperFunctions.php');

/*============================= Main ================================*/

  // ensure POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $sender =    hash('sha256', $_POST['sender']);
  $receiver =  $_POST['receiver'];
  $value =     $_POST['value'];
  $fee   =     $_POST['fee'];
  if ( !isset( $_POST['Timestamp'] ) ){ $timestamp = microtime(true); }


  if (getBalance($sender) === 0){
    error(400, "Wallet has no balance ".$sender);
  } else if ( $sender === $receiver ) {
    error(400, "Sender cannot be Reciever");
  }


  $json = file_get_contents('transactionDB.json');
  $transJsonData = json_decode($json, true);

  $hash = hash('sha256', $sender.$receiver.$value.$fee.$timestamp);
  $transJsonData[] = ['Hash'   => $hash,
                      'Sender' => $sender,
                      'Receiver' => $receiver,
                      'Value' => $value,
                      'Fee' => $fee,
                      'Timestamp' => $timestamp ];

  file_put_contents('transactionDB.json', json_encode($transJsonData));

  echo json_encode('Tranaction added');
}


else {
    error(405, 'POST requests only');
}