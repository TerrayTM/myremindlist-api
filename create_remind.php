<?php

//Connect to database
include_once "./database_connect.php";

//Validate request parameters
$rules = array(
    array("token", "s"),
    array("message", "s"),
    array("time", "i"),
    array("params", "s")
);
if (!validate_parameters($request, $rules)) {
    die(json_encode(array("error" => "INVALID_ARGUMENTS")));
}

//Validate token and if valid, retrieve associated userID
$userID = validate_token($database_connection, $request['token']);

//Check if user has their email verified
$query = $database_connection->query("SELECT verifyEmail, verifyPhone FROM users WHERE id=".$userID." LIMIT 1;");
$verify = $query->fetch_assoc();
if (!$verify) {
    die(json_encode(array("error" => "FETCH_VERIFY_FAILED")));
}
if ($verify['verifyEmail'] == 0) {
    die(json_encode(array("error" => "NOT_VERIFIED_EMAIL")));
}
$query->free();

//Check if user has their phone verified if remind is set to SMS
if (strpos($request['params'], "sms") !== false) {
    if ($verify['verifyPhone'] == 0) {
        die(json_encode(array("error" => "NOT_VERIFIED_PHONE")));
    }
}

//Post new remind
$statement = $database_connection->prepare("INSERT INTO reminds (message, time, userID, params) VALUES (?, ?, ?, ?);");
$statement->bind_param("siis", $request['message'], $request['time'], $userID, $request['params']);
if (!$statement->execute()) {
    die(json_encode(array("error" => "CREATE_REMIND_ERROR")));
}
$statement->close();

//Get new remind ID
$query = $database_connection->query("SELECT LAST_INSERT_ID();");
$remind_id = $query->fetch_row();
if (!$remind_id or !isset($remind_id[0])) {
    die(json_encode(array("error" => "CREATE_REMIND_ERROR")));
}
$query->free();

//Output new remind ID
echo json_encode(array("id" => $remind_id[0]));

?>