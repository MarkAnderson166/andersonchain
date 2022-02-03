
<?php
/*# ----------------------------------------------------------
# --    Mark Anderson    --     Student Number: 220180473   --
# --        UNE          --       Cosc301 - Blockchain      --
# ------------------------------------------------------------*/

ini_set('display_errors', TRUE);
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once('helperFunctions.php');

/*============================================================================
#   $transactionData  =   entire contents of available mempool
#        V
#   $selectedTrans    =   trans we intend to mine into this block
#        V
#   $finalTransList   =   trans that have been varified and are going in
#
#        X
#   $ignoredTrans     =   trans that we didn't want or couldn't confirm,
#                         -not this miners problem, going back in the pool
/*===========================================================================*/

//--------------------------------------------------------------------------
  // ========   get basics together =============
//--------------------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $json = file_get_contents('blockChain.json');
  $blocksJsonData = json_decode($json, true);
  $index =        count($blocksJsonData);
  
  rehash($blocksJsonData);
  checkChainSync($_POST['miner'],$index);
  
  $miner        = file_get_contents('serverID');
  $previousHash = $blocksJsonData[$index-1]['Hash'];
  $nonce        = 0;
  $coinbase     = 100;
  $timestamp    = microtime(true);
  $fees         = 0;

    // coinbase halving based on chain length instead of blocks/time for now.
  foreach($blocksJsonData as $obj => $key) {
    if ( $key['Index'] % 20 == 0 ){
      if ($coinbase >= 2) { $coinbase = $coinbase/2; };
    }
  }

//--------------------------------------------------------------------------
  // ========   collect tranactions to mine   =============
//--------------------------------------------------------------------------

    // this was written in a way to allow selection of trans to be
    // based on fee size,  However, there's simply no reason to not mine
    // everything everytime. -the functionalty is just commented out for now.

    // load all trans data
  $json = file_get_contents('transactionDB.json');
  $transactionData = json_decode($json, true);
  $ignoredTrans = (array) null;
  $selectedTrans = (array) null;
  $finalTransList = (array) null;

  // sorts by fee amount, to cherry pick most profitable trans to mine.
  if (!empty($transactionData)){
  /*  usort($transactionData, function($a, $b) {
      return $a['Fee'] < $b['Fee'];
    });
  }
  $transCounter = 0; */
    foreach($transactionData as $obj => $key) {
      //$transCounter++;
        
        // discard where sender=receiver, you can't send marks to yourself
      if( //($transCounter < count($transactionData)) &&
              ($key['Sender'] != $key['Receiver'])){
                        
          //trans to be included in block:
        $selectedTrans[] =  $key ;
      } else {
          //trans NOT to be included in block:
        $ignoredTrans[] =  $key;
      }
    }
  }

//--------------------------------------------------------------------------
  // ========   varify transactions, put block together   =============
//--------------------------------------------------------------------------

    // all operations after this will be based on timestamp
  if (!empty($selectedTrans)){
    usort($selectedTrans, function($a, $b) {
      return $a['Timestamp'] > $b['Timestamp'];
    });
  }

      // each transaction must be balance checked and have balances recorded  
      // balance is retrieved from most recent varified trans pending for this
      // block, then second most recent etc..
      // if key is not previously found in the current pending block,
      // get balance from chain
  $finalTransList = [];
  if (!empty($selectedTrans)){
    foreach($selectedTrans as $obj => $key) {

      $senderBalance = balanceFromArrayThenChain($key['Sender'], $finalTransList);
      $receiverBalance = balanceFromArrayThenChain($key['Receiver'], $finalTransList);

        // actually transfer marks
      $senderBalance = $senderBalance-floatval($key['Value'])-floatval($key['Fee']);
      $receiverBalance = $receiverBalance+floatval($key['Value']);

      if($senderBalance >= 0){
          // now we are sure that this trans is going in the block, we add fees
        $fees += $key['Fee'];

        $finalTransList[] =['Hash'   =>           $key['Hash'],
                            'Sender' =>           $key['Sender'],
                            'Sender Balance' =>   $senderBalance,
                            'Receiver' =>         $key['Receiver'],
                            'Receiver Balance' => $receiverBalance,
                            'Value' =>            $key['Value'],
                            'Fee' =>              $key['Fee'],
                            'Timestamp' =>        $key['Timestamp'] ];
      } else {
        // if sender balance would go negative with this trans,
        // put it back in the mempool, no marks moved.
        $ignoredTrans[] =  $key;
      }
    }
  }

    // the miner receives the mining rewards as a transaction, which is 
    // made at the time of mining., trans must be made after fee calculation.
  $coinbaseTimestamp = microtime(true);
  //$hash = hash('sha256', $sender.$receiver.$value.$fee.$timestamp);

  $coinbaseTransHash = hash('sha256','Mining_Reward'.$miner.$coinbase.'0'.$coinbaseTimestamp);
  $minerBalance = balanceFromArrayThenChain($miner, $finalTransList) + $coinbase +$fees;

  $finalTransList[] = ['Hash'   => $coinbaseTransHash,
                      'Sender' => 'Mining_Reward',
                      'Sender Balance' => strval(0),
                      'Receiver' => $miner,
                      'Receiver Balance' => strval($minerBalance),
                      'Value' => strval($coinbase +$fees),
                      'Fee' => strval($fees),
                      'Timestamp' => $coinbaseTimestamp ];


      // a list of just trans hashes is also added to each block
      // for searching/gui reasons
  foreach($finalTransList as $obj => $key) {
    $includedTransactionHashes[] = $key['Hash'];
  }

    // put unused trans back in the mempool
  file_put_contents('transactionDB.json', json_encode($ignoredTrans));


//--------------------------------------------------------------------------
  // ======  Mine block and broadcast it to all other nodes  ==========
//--------------------------------------------------------------------------

    // this while loop is a fully working 'proof-of-work' hashing system
    // it just has the difficulty set extremely low, so its intermittent.
    // we don't need to pointlessly burn power for this assesment.

    // sleep() is to account for ajax ping (only needed for exessively easy 0's)
    // I'll explain this in the readme.md
  sleep(1);
  $hash = 'a';

  $dataStr = '';
  foreach( $finalTransList as $ind => $obj) {
    foreach( $obj as $key => $value) {
      $dataStr = $dataStr.$value;
    }
  }
  
  while ( substr($hash,0,4) !== '0000' ){ // && getChainLength() === $index){
    $nonce++;
    $hash = hash('sha256',$nonce.$miner.$index.$previousHash.$timestamp.$dataStr);
  }

  $newBlock = array(  'Hash' => $hash,
                      'Miner' => $miner,
                      'Index' => $index,
                      'PreviousHash' => $previousHash,
                      'Nonce' => $nonce,
                      'Coinbase' => $coinbase,
                      'Timestamp' => $timestamp, 
                      'Fees' => $fees, 
                      'TransactionData' => $finalTransList,
                      'TransactionHashes' => $includedTransactionHashes  );

    //  if our chain length has changed while we were mining, don't bother
    //  broadcasting, we lost the race, no one wants our block.
              // RECYCLING IS AFTER BROADCAST !!!!  -  OPTIMISE LATER
  //if ( getChainLength() === $index ){
    broadcastBlock($newBlock);
    echo json_encode('New block mined by: '.$miner);
  //}
}


//--------------------------------------------------------------------------
//--------------------------------------------------------------------------
else {
    error(405, 'POST requests only');
}


function balanceFromArrayThenChain($key, $array) {

  if (!empty($array)){
    foreach(array_reverse($array) as $a => $b) {
      if ($key === $b['Sender']){
        return floatval($b['Sender Balance']);
      }
      else if ($key === $b['Receiver']){
        return floatval($b['Receiver Balance']);
      }
    }
  }
    // if we get though the current selected list, and haven't got a
    // balance yet, dig into the blockchain for it
  return floatval(getBalance($key));
}


function broadcastBlock($newBlock){

  for ( $i = 1 ; $i <= 3; $i++){
    $url = 'https://turing.une.edu.au/~mander53/turing'.$i.'/catchblock.php';
    $result = httpPost($url, $newBlock);
  }

  if ($result === FALSE) { /* Handle error */ }
}


/* ==========  httpPost() ==============
  ripped off verbatum from stack-overflow user Dima L.
  https://stackoverflow.com/questions/5647461/how-do-i-send-a-post-request-with-php
  I was using a file_get_contents($url, false, stream_context_create());
  method, but had trouble with return values, so this Curl thing from stack
  worked better.
*/
function httpPost($url, $data) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

/* ==========  checkChainSync() ==============
    ask other nodes if they already have a block with the index we
    are about to mine, if they do, request it be sent to our catchblock.php
    do this in a loop until all nodes are trying to mine the same index
*/
function checkChainSync($server, $index) {
  $result = 'not 3';
  while (strlen($result) !== 3){ 
    for ( $i = 1 ; $i <= 3; $i++){
      $url = 'https://turing.une.edu.au/~mander53/turing'.$i.'/requestblock.php';
      $result = httpPost($url, array( 'server' => $server, 'Index' => $index));
    }
    $index++;
  }
}

/* ==========  rehash() ==============
  Go though chain rehashing blocks to check for changes. If any hash
  doesn't match we throw out all blocks after that point on THIS server.
   -other functions handle the repair 
*/
function rehash($blocksJsonData) {

  $PreviousHash = '$previousHash';

  foreach(array_reverse($blocksJsonData) as $ind => $obj) {

    $hashDataStr = $obj['Nonce'].$obj['Miner'].$obj['Index'].$obj['PreviousHash'].$obj['Timestamp'];
    foreach( $obj['TransactionData'] as $ind => $obj1) {
      foreach( $obj1 as $key => $value) {
        $hashDataStr = $hashDataStr.$value;
      }
    }
    $hash = hash('sha256',$hashDataStr);

    if ( $obj['Hash'] !== $hash && $obj['Index'] !== 0) {
      $dumped = file_get_contents('dump.json');
      $str = $dumped.' ## ERROR, ERROR WORLD IN CRYSIS! ##  |'.$obj['Index'].'| '.$obj['Hash'].' !== '.$hash;
      file_put_contents('dump.json', $str);

      scrapBlocks($blocksJsonData, $obj['Index']-1);
    }

    $PreviousHash = $obj['Hash'];
  }
}

function scrapBlocks($blocksJsonData, $brokenBlock){
  foreach($blocksJsonData as $ind => $obj) {
    if ($obj['Index'] < $brokenBlock ) {
      $newChain[] = $obj;
    }
  }
  file_put_contents('blockChain.json', json_encode($newChain));
}

/*
function getChainLength() {
  $json = file_get_contents('blockChain.json');
  $blocksJsonData = json_decode($json, true);
  return count($blocksJsonData);
}
*/
