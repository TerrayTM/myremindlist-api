<?php

//Connect to database
include_once "./database_connect.php";

//Validate request parameters
$rules = array(
    array("token", "s"),
    array("code", "s")
);
if (!validate_parameters($request, $rules)) {
    die(json_encode(array("error" => "INVALID_ARGUMENTS")));
}

//Validate token and if valid, retrieve associated userID
$userID = validate_token($database_connection, $request['token']);

//Check verification code
$statement = $database_connection->prepare("SELECT id, type FROM verification WHERE code=? AND userID=? LIMIT 1;");
$statement->bind_param("si", $request['code'], $userID);
$statement->execute();
$statement->bind_result($id, $type);
$statement->fetch();
if (!$id or !$type) {
    die(json_encode(array("error" => "INVALID_CODE")));
}
$statement->close();

//Delete verification code
if (!$database_connection->query("DELETE FROM verification WHERE id=".$id." LIMIT 1;")) {
    die(json_encode(array("error" => "DELETE_VERIFICATION_CODE_FAILED")));
}

//Update user info
if (!$database_connection->query("UPDATE users SET ".$type."=1 WHERE id=".$userID." LIMIT 1;")) {
    die(json_encode(array("error" => "UPDATE_USER_INFO_FAILED")));
}

//Output success
echo json_encode(array("status" => "success"));

?>