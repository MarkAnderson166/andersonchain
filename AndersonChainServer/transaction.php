
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
  $sender =    $_POST['sender'];
  $password =  $_POST['password'];
  $receiver =  $_POST['receiver'];
  $value =     $_POST['value'];
  $fee   =     $_POST['fee'];
  $timestamp = microtime(true);


    //  TODO: Password validation can't use wallet database
    // ( because there shouldn't be a wallet database - its for testing only )
  $json = file_get_contents('walletDB.json');
  $walletJsonData = json_decode($json, true);
  foreach($walletJsonData as $obj => $key) {
    if ( $key['publicKey'] === $sender) {
      if ( hash('sha256', $password.$key['name'].$key['Timestamp']) !== $sender) {
        error(400, "Password does not match ".$key['name']);
      }
    }
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
  // 405: unsupported request method
else {
    error(405, 'POST requests only');
}