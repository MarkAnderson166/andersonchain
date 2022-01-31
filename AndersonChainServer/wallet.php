
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
/* get array of 3-5 random words ,  was more work then its worth. --------
       words.json lifted from:
       https://github.com/words/an-array-of-english-words.git
       contributors: Zeke Sikelianos, Titus, Johnny B
*/
  $words = file_get_contents('words.json'); 
  $words = explode(',', $words);
  $randWords =  '';
  $privateKey =  '';
  while (strlen($randWords) < 25){
    $newWord = $words[rand(0,sizeof($words))];
    if ( strlen($newWord) < 9 && strlen($newWord) > 5 ){
      $randWords = $randWords.$newWord.' ';
    }
  }
  for ($i = 0; $i< strlen($randWords); $i++){  // cut out quotation marks
    if ( $randWords[$i] !== '"') { $privateKey = $privateKey.$randWords[$i]; }
  }
  $privateKey = substr($privateKey,0,strlen($privateKey)-1);//cut off trailing ' '
// ---------------------------------------------------------------------------


  if (!file_exists('walletDB.json')){
    $json = "";
  } else {
    $json = file_get_contents('walletDB.json');
  }
  $data = json_decode($json, true);
  if (!empty($data)) {$walletIndex = count($data);}

  $publicKey = hash('sha256', $privateKey);

  $data[] = [ 'privateKey' => $privateKey,
              'publicKey' => $publicKey,
              'Timestamp' => microtime(true)  ];
    // Timestamp is only used for GUI updates
    // for testing only
  file_put_contents('walletDB.json', json_encode($data));

      // return newly created ID
  echo json_encode([$publicKey.'   '.$privateKey.'<pre>']);
}


else {
    error(405, 'POST requests only');
}

/*========================= Input Validation ========================*/
/*
function validateEntry($param) {

      // Check the given name is valid
  if ($param === 'name') {
    if(strlen($_POST[$param]) < 2 || strlen($_POST[$param]) > 100) {
      error(400, "Name must be between 2 and 100 characters long");
    }
    else if(!preg_match('/^[a-zA-Z ]*$/', $_POST[$param])) {
      error(400, "Name must consist of letters and white space only");
    }
    else {
      return $_POST[$param];
    }
  }
      // Check the given password is valid
  else if ($param === 'password') {
    if(strlen($_POST[$param]) < 2 || strlen($_POST[$param]) > 20) {
      error(400, "password must be between 2 and 20 characters long");
    }
    else {
      return $_POST[$param];
    }
  }
      // extra fail condition in case a wrong or missing field is sent
  else {
    error(400, "Server requires a POST request with POST data");
  }
}*/




