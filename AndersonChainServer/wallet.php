
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
  $walletIndex = 0;
  $name =    validateEntry('name');
  $password =  validateEntry('password');
  $timestamp = microtime(true);
    //$publicKey = generateKey($password, $name, $wallet);
    // key can't be generated until after wallet index is counted
    // index included just for GUI reasons in the event of duplicate names

  if (!file_exists('walletDB.json')){
    $json = "";
  } else {
    $json = file_get_contents('walletDB.json');
  }
  $data = json_decode($json, true);

  if (!empty($data)) {$walletIndex = count($data);}

  $publicKey = hash('sha256', $password.$name.$timestamp);

  $data[] = [ 'walletIndex' => $walletIndex,
              'name' => $name,
              'Timestamp' => $timestamp,
              'publicKey' => $publicKey ];
  file_put_contents('walletDB.json', json_encode($data));

      // return newly created ID
  echo json_encode([$name]);
}
  // 405: unsupported request method
else {
    error(405, 'POST requests only');
}

/*========================= Input Validation ========================*/

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
}