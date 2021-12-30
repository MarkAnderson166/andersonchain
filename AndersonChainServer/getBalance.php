
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

  $returnString = '';
  $json = file_get_contents('walletDB.json');
  $walletJsonData = json_decode($json, true);
  foreach($walletJsonData as $obj => $key) {
    $returnString = $returnString.getBalance($key['publicKey']).' : '.$key['name'].'<pre>';
  }

  echo json_encode($returnString);
    
  // 405: unsupported request method
} else {
    error(405, 'POST requests only');
}
