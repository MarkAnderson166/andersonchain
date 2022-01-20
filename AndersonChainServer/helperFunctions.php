
<?php
/*# ----------------------------------------------------------
# --    Mark Anderson    --     Student Number: 220180473   --
# --        UNE          --       Cosc301 - Blockchain      --
# ------------------------------------------------------------*/



/*========================= Error Handling ========================*/

function error($code, $msg) {
  $responses = [
    400 => "Bad Request",
    404 => "Not Found",
    405 => "Method Not Allowed",
    500 => "Internal server error"
  ];
  header($_SERVER['SERVER_PROTOCOL']." ".$code." - ".$responses[$code]);
  echo json_encode( [ "error"=> $code." - ".$responses[$code].": ".$msg] );
  die();
}


/*================== Server-Side 'In-Block' Balance ====================*/

function getBalance($publicKey){

  $blockArr = json_decode(file_get_contents('blockChain.json'), true);

    // make an array of recent transactions
    // this array must have all values from the last 10 blocks,
    // and must have have at least 1 value

  $atLeastTenBlocks = 0;  //counter for 'orphan-block' fringe case

      // we need a dummy value for new/unused publicKey's
  $recentTranslist[] = [ 'Timestamp' => 1, 'Balance' => 0 ];

  while( ( sizeof($recentTranslist)<2 || $atLeastTenBlocks<10 ) && sizeof($blockArr)>1  ){ 

    $atLeastTenBlocks++;
    $youngestBlock = array_pop($blockArr);
    $transArr = $youngestBlock['TransactionData'];
    if (!empty($transArr)){

      foreach($transArr as $obj => $key) {
        if ($publicKey === $key['Sender']){

          $recentTranslist[] = [ 'Timestamp' => $key['Timestamp'],
                                'Balance' => $key['Sender Balance']];
        }
        else if ($publicKey === $key['Receiver']){

          $recentTranslist[] = [ 'Timestamp' => $key['Timestamp'],
                                'Balance' => $key['Receiver Balance']];
        } 
      }
    }
  }

    // sort new list by timestamp, grab the youngest
  usort($recentTranslist, function($a, $b) {return $a['Timestamp'] > $b['Timestamp'];});

    // return the balance as at the most recent trans.
  return array_pop($recentTranslist)['Balance'];
}



/*================== Server-Side Statement Generator ====================*/

# this should probably be a wallet feature instead of a purely diagnostic tool,
# but this was much easier to throw together to find a bug, so its here for now.

function generateStatement() {

  $walletJsonData = json_decode(file_get_contents('walletDB.json'), true);
  foreach($walletJsonData as $obj => $key) {
    $walletKeyList[] = $key['publicKey'];
  }

  $blockArr = json_decode(file_get_contents('blockChain.json'), true);


  foreach($walletKeyList as $i => $walletKey) {
    $translist = [];
    foreach($blockArr as $a => $b) {
      foreach($b['Transaction Data'] as $index => $obj) {
        if($walletKey == $obj['Sender']) {
          $translist[] = [ 'Time' => substr($obj['Timestamp'],0,16),
                          'Value' =>     ' - '.$obj['Value'].'   ',
                          'Balance' =>   '   '.$obj['Sender Balance'].'   ',
                          'Receiver' =>  '   '.substr($obj['Receiver'],0,10) ];
        }
        if($walletKey == $obj['Receiver']) {
          $translist[] = [ 'Time' => substr($obj['Timestamp'],0,16),
                          'Value' =>     ' + '.$obj['Value'].'   ',
                          'Balance' =>   '   '.$obj['Receiver Balance'].'   ',
                          'Sender' =>    '   '.substr($obj['Sender'],0,10) ];
        }
      } 
      usort($translist, function($c, $d) {return $c['Timestamp'] > $d['Timestamp'];});
      file_put_contents('listDump_'.substr($walletKey,0,10).'.json', json_encode($translist));
    }
  }
}