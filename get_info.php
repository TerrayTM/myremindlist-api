<?php

//Connect to database
include_once "./database_connect.php";

//Validate get parameters
$rules = array(
    array("token", "s")
);
if (!validate_parameters($_GET, $rules)) {
    die(json_encode(array("error" => "INVALID_ARGUMENTS")));
}

//Validate token and if valid, retrieve associated userID
$userID = validate_token($database_connection, $_GET['token']);

//Get reminds from database
$query = $database_connection->query("SELECT id, message, time, params FROM reminds WHERE userID=".$userID.";");
if (!$query) {
    die(json_encode(array("error" => "GET_REMINDS_ERROR")));
}
$results = array();
while ($row = $query->fetch_assoc()) {
    $results[] = $row;
}
$query->free();

//Fetch user info
$query = $database_connection->query("SELECT email, phone, firstName, lastName, verifyEmail, verifyPhone FROM users WHERE id=".$userID." LIMIT 1;");
if (!$query) {
    die(json_encode(array("error" => "FETCH_USER_INFO_ERROR")));
}
$user_info = $query->fetch_assoc();
if (!$user_info) {
    die(json_encode(array("error" => "FETCH_USER_INFO_ERROR")));
}
$query->free();

//Output information
echo json_encode(array(
    "reminds" => $results, 
    "firstName" => $user_info['firstName'], 
    "lastName" => $user_info['lastName'],
    "email" => $user_info['email'],
    "phone" => $user_info['phone'],
    "verifyEmail" => $user_info['verifyEmail'],
    "verifyPhone" => $user_info['verifyPhone']
));

?>