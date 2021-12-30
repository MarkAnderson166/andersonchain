
<?php
/*# ----------------------------------------------------------
# just a getter, aiming $.getJSON() stright at the .json on the server
    ( bypassing this .php ) worked for windows/chrome,
    but returned CORS error on linux/firefox  
# ------------------------------------------------------------*/

ini_set('display_errors', TRUE);
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");


echo file_get_contents('walletDB.json');
