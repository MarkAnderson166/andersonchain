
<?php
/* ----------------------------------------------------------
#  This file stores balance retrieval functions used for serveral
#       purposes including essential mining use
#  The _POST made to this file are for 'everyones balance' and
#     'Active wallets List'  diagnostic gui elements.
# ------------------------------------------------------------*/

ini_set('display_errors', TRUE);
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
require_once('helperFunctions.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if ( $_POST['justList'] === '1') {
    activeWallets();
  } else {

    $allReceivers = getListOfReceivers();

    $returnString = '';
    $Total = 0;
    foreach($allReceivers as $index => $key) {
      $bal = getBalance($key);
      $Total = $Total+$bal;
      $returnString = $returnString.$bal.' : '.substr($key,0,16).'...'.'<pre>';
    }
    $returnString = $returnString.'Total Marks : '.$Total;

    echo json_encode($returnString);
  }
    
} else {
    error(405, 'POST requests only');
}



function getListOfReceivers(){
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
  return array_unique($allReceivers);
}



function activeWallets() {
    //  match all used keys for 'ease of testing' wallet list
  $returnString = '';
  $wallets = json_decode(file_get_contents('walletDB.json'), true);
  foreach($wallets as $ind => $value) {
    $returnString = 
    $returnString.$value['publicKey'].'  =  '.$value['privateKey'].'<pre>';
  }
  echo json_encode($returnString);
}
