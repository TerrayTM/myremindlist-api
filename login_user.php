<?php

//Connect to database
include_once "./database_connect.php";

//Validate request parameters
$rules = array(
    array("email", "e"),
    array("password", "p")
);
if (!validate_parameters($request, $rules)) {
    die(json_encode(array("error" => "INVALID_ARGUMENTS")));
}

//Login in user with password and fetch associated info
$statement = $database_connection->prepare("SELECT id, phone, firstName, lastName, verifyEmail, verifyPhone FROM users WHERE email=? AND password=? LIMIT 1;");
$statement->bind_param("ss", $request['email'], $password);
$password = hash("sha256", $request['password']);
$statement->execute();
$statement->bind_result($id, $phone, $first_name, $last_name, $email_verify, $phone_verify);
$statement->fetch();
if (!$id or !$first_name or !$last_name) {
    //Incorrect email or password, ouput error and exit
    die(json_encode(array("error" => "INCORRECT_EMAIL_OR_PASSWORD")));
}
$statement->close();

//Get reminds from database
$query = $database_connection->query("SELECT id, message, time, params FROM reminds WHERE userID=".$id.";");
if (!$query) {
    die(json_encode(array("error" => "GET_REMINDS_ERROR")));
}
$results = array();
while ($row = $query->fetch_assoc()) {
    $results[] = $row;
}
$query->free();

//If there is already a token associated with user ID, retrieve it
$query = $database_connection->query("SELECT token, expiry FROM tokens WHERE userID=".$id." LIMIT 1;");
$row = $query->fetch_assoc();
if ($row and $row['expiry'] > time()) {
    //Output user information and exit
    die(json_encode(array(
        "token" => $row['token'], 
        "expiry" => $row['expiry'] - time(),
        "reminds" => $results, 
        "firstName" => $first_name,
        "lastName" => $last_name,
        "phone" => $phone,
        "verifyEmail" => $email_verify,
        "verifyPhone" => $phone_verify
    )));
} else if ($row) {
    //Delete old token
    $database_connection->query("DELETE FROM token WHERE id=".$row['id']." LIMIT 1;");
}
$query->free();

//Generate token and expiry
$token = hash("sha256", (string)(time() * 1000 + rand(1, 999)));
$expiry = time() + 10800;

//Store token along with ID in database
$statement = $database_connection->prepare("INSERT INTO tokens (token, userID, expiry) VALUES (?, ?, ?);");
$statement->bind_param("sii", $token, $id, $expiry);
if (!$statement->execute()) {
    die(json_encode(array("error" => "TOKEN_ERROR")));
}
$statement->close();

//Output user information
echo json_encode(array(
    "token" => $token, 
    "expiry" => 10800,
    "reminds" => $results, 
    "firstName" => $first_name,
    "lastName" => $last_name,
    "phone" => $phone,
    "verifyEmail" => $email_verify,
    "verifyPhone" => $phone_verify
));

?>
