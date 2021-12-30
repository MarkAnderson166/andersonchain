
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

$json = file_get_contents('blockChain.json');
$blocksJsonData = json_decode($json, true);

  // ensure POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $miner =        $_POST['miner'];
  $index =        count($blocksJsonData);
  $previousHash = $blocksJsonData[$index-1]['Hash'];
  $leadingZeros = 0;
  $coinbase =     100;
  $timestamp =    microtime(true);
  $fees   =       0;
  //$transactionData = ;
  //$transactionHashes = ;


    // coinbase halving based on chain length instead of blocks/time for now.
  foreach($blocksJsonData as $obj => $key) {
    if ( $key['Index'] % 20 == 0 ){
      if ($coinbase >= 2) { $coinbase = $coinbase/2; };
    }
  }

//--------------------------------------------------------------------------

    // load all trans data
  $json = file_get_contents('transactionDB.json');
  $transactionData = json_decode($json, true);
  $ignoredTrans = (array) null;
  $selectedTrans = (array) null;
  $finalTransList = (array) null;

  // TODO: the whole 'which trans will go in this block' idea needs work,
  // old trans need to be deleted and stakeholders notified somehow.
  //    for now, this freaks out if the pool has less than 2 USEABLE trans
  //    - trans with insufficent marks, or with sender==receiver
  //      will stay in pool forever

  // sorts by fee amount, to cherry pick most profitable trans to mine.
  if (!empty($transactionData)){
    usort($transactionData, function($a, $b) {
      return $a['Fee'] < $b['Fee'];
    });
  }
  $transCounter = 0;
  foreach($transactionData as $obj => $key) {
    $transCounter++;
       
      // at this point we're picking an arbitary amount of trans, and throwing 
      // out trans where sender=receiver, you can't send marks to yourself
    if(($transCounter < count($transactionData)-1) &&
            ($key['Sender'] != $key['Receiver'])){
                       
        //trans to be included in block:
      $selectedTrans[] =  $key ;
    } else {
        //trans NOT to be included in block:
      $ignoredTrans[] =  $key;
    }
  }

//--------------------------------------------------------------------------

// after selecting by fee, all operations will be based on timestamp, so re-sort

  if (!empty($selectedTrans)){
    usort($selectedTrans, function($a, $b) {
      return $a['Timestamp'] > $b['Timestamp'];
    });
  }

      // each transaction must be balance checked and have balances recorded  
      // balance is retrieved from most recent varified trans pending for this
      // block, then second most recent etc..
      // if key is not previously found in the current pending block,
      // get balance from chain (function in helpers.php)
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
    // made at the time on mining., trans must be made after fee calculation.
  $coinbaseTimestamp = microtime(true);
  $coinbaseTransHash = hash('sha256','Mining_Reward'.$miner.$coinbase.'0'.$coinbaseTimestamp);
  $minerBalance = balanceFromArrayThenChain($miner, $finalTransList) + $coinbase +$fees;

  $finalTransList[] = ['Hash'   => $coinbaseTransHash,
                      'Sender' => 'Mining_Reward__this_needs_to_be_64_characters_loong_for_indexing',
                      'Sender Balance' => 123456789, // nothing should EVER use this number
                      'Receiver' => $miner,
                      'Receiver Balance' => $minerBalance,
                      'Value' => $coinbase +$fees,
                      'Fee' => 0,
                      'Timestamp' => $coinbaseTimestamp ];


      // a list of just trans hashes is also added to each block
      // for searching/gui reasons
  foreach($finalTransList as $obj => $key) {
    $includedTransactionHashes[] = $key['Hash'];
  }
//--------------------------------------------------------------------------


    // put unused trans back in the mempool
  file_put_contents('transactionDB.json', json_encode($ignoredTrans));

  // TODO: entire difficulty/0's and reset functionality

  // TODO: chain integrity check (re-hash)


//--------------------------------------------------------------------------
    // put the block together, write the json, report to client.
  $hash = hash('sha256', $miner.$index.$timestamp);
  $blocksJsonData[] =['Hash' => $hash,
                      'Miner' => $miner,
                      'Index' => $index,
                      'Previous Hash' => $previousHash,
                      'Difficulty' => $leadingZeros,
                      'Coinbase' => $coinbase,
                      'Timestamp' => $timestamp, 
                      'Fees' => $fees, 
                      'Transaction Data' => $finalTransList,
                      'Transaction Hashes' => $includedTransactionHashes ];

  file_put_contents('blockChain.json', json_encode($blocksJsonData));
  echo json_encode('New block mined by: '.$miner);
}

//--------------------------------------------------------------------------
  // 405: unsupported request method
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
  //else {
    return floatval(getBalance($key));
  //}

  // the above '}else{}' cost me about 20 hours of bug hunting...... ouch
  //return 2222222222; //obvious number
}