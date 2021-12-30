
<?php
/*# ----------------------------------------------------------
# checks if any json files have timestamps younger than 
    ( $_POST['Timestamp'] - $interval ) 
# ------------------------------------------------------------*/

ini_set('display_errors', TRUE);
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once('helperFunctions.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // my way of doing a 4-way boolean, multiply 1 by 2,3 or 5 server end, 
    // check modulo client end
  $flag = 1;

  
  // ==================== ATTENTION! ==============================
  // ===== IF UPDATES ARE BROKEN ITS PROBABLY THIS LINE!!! ========
  $interval = 2;  // seconds between 'pings'    MUST BE >= CLIENT!


  $clientTime = intval($_POST['Timestamp']);

      // doing these vars in 1-2 steps threw weird errors
  $lastNewWallet = json_decode(file_get_contents('walletDB.json'), true);
  $lastNewWallet = array_pop($lastNewWallet);
  $lastNewWallet = floor($lastNewWallet['Timestamp']);
  if ($clientTime - $lastNewWallet < $interval ) { $flag = $flag*2;}

  $lastNewTrans = json_decode(file_get_contents('transactionDB.json'), true);
  $lastNewTrans = array_pop($lastNewTrans);
  $lastNewTrans = floor($lastNewTrans['Timestamp']);
  if ($clientTime - $lastNewTrans < $interval ) { $flag = $flag*3;}

  $lastNewBlock = json_decode(file_get_contents('blockChain.json'), true);
  $lastNewBlock = array_pop($lastNewBlock);
  $lastNewBlock = floor($lastNewBlock['Timestamp']);
  if ($clientTime - $lastNewBlock < $interval ) { $flag = $flag*5;}

  echo $flag;

  // 405: unsupported request method
} else {
    error(405, 'POST requests only');
}