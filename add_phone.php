<?php

//Connect to database
include_once "./database_connect.php";

//Validate request parameters
$rules = array(
    array("token", "s"),
    array("phone", "i")
);
if (!validate_parameters($request, $rules)) {
    die(json_encode(array("error" => "INVALID_ARGUMENTS")));
}

//Validate token and if valid, retrieve associated userID
$userID = validate_token($database_connection, $request['token']);

//Update user phone
$statement = $database_connection->prepare("UPDATE users SET phone=? WHERE id=? LIMIT 1;");
$statement->bind_param("si", $request['phone'], $userID);
if (!$statement->execute()) {
    die(json_encode(array("error" => "ADD_PHONE_FAILED")));
}
$statement->close();

//Create phone verification code and store it in database
$phone_code = generateCode(6);
if (!$database_connection->query("INSERT INTO verification (code, userID, type) VALUES ('".$phone_code."', ".$userID.", 'verifyPhone');")) {
    die(json_encode(array("error" => "VERIFICATION_CODE_ERROR")));
}

//Load Composor's autoloader
require "./vendor/autoload.php";

//Import libraries
use Twilio\Rest\Client;

//Initalize SMS service
$client = new Client("******", "******");

//Send verification code
$client->messages->create(
    $request['phone'],
    array(
        "from" => "+16474901643",
        "body" => "Verification Code: ".$phone_code
    )
);

//Output success
echo json_encode(array("status" => "success"));

?>