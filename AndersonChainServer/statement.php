
<?php
/* ----------------------------------------------------------
#  just calls a server-side function that dumps a statement printout
#    (helperFunctions.php)
# ------------------------------------------------------------*/


ini_set('display_errors', TRUE);
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once('helperFunctions.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  generateStatement();

  echo json_encode(1);
    
  // 405: unsupported request method
} else {
    error(405, 'POST requests only');
}
