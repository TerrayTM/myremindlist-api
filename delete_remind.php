<?php

//Connect to database
include_once "./database_connect.php";

//Validate get parameters
$rules = array(
    array("token", "s"),
    array("id", "i")
);
if (!validate_parameters($request, $rules)) {
    die(json_encode(array("error" => "INVALID_ARGUMENTS")));
}

//Validate token and if valid, retrieve associated userID
$userID = validate_token($database_connection, $request['token']);

//Delete the remind while making sure the user owns that remind
$statement = $database_connection->prepare("DELETE FROM reminds WHERE id=? AND userID=? LIMIT 1;");
$statement->bind_param("ii", $request['id'], $userID);
if (!$statement->execute()) {
    $statement->close();
    die(json_encode(array("error" => "DELETE_REMIND_UNAUTHORIZED")));
}
$statement->close();

//Output success
echo json_encode(array("status" => "success"));

?>