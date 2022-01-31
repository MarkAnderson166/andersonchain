
<?php
/* ----------------------------------------------------------
#  Just calls getBalance() form helperFunctions.php
#
#  that function was in here, but including this php into any other triggered
#  the _POST? weirdly,  I'm sure its a php feature I just don't know about.
# ------------------------------------------------------------*/


ini_set('display_errors', TRUE);
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once('helperFunctions.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

// =================================
    //  get a list of all public keys in existance
    //  --replacement for wallet database, diagnostic only
  $blockArr = json_decode(file_get_contents('blockChain.json'), true);
  $allReceivers = [];
  foreach($blockArr as $arr => $block) {
    $transArr = $block['TransactionData'];
    foreach($transArr as $obj => $key) {
      $allReceivers[] =  $key['Receiver'];
    }
  }
  $allReceivers = array_unique($allReceivers);
// =================================


  $returnString = '';
  $Total = 0;
  foreach($allReceivers as $index => $key) {
    $bal = getBalance($key);
    $Total = $Total+$bal;
    $returnString = $returnString.$bal.' : '.substr($key,0,16).'...'.'<pre>';
  }
  $returnString = $returnString.'Total Marks : '.$Total;

  echo json_encode($returnString);
    


} else {
    error(405, 'POST requests only');
}

