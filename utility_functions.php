<?php

//Function for checking parameters
function validate_parameters($request, $rules) {
    for ($i = 0; $i < count($rules); ++$i) {
        //Check if argument is set
        if (!isset($request[$rules[$i][0]])) {
            return false;
        }
  
        $item = $request[$rules[$i][0]];
        
        //Check argument type 
        switch ($rules[$i][1]) {
            case "s":
                if (!is_string($item) or empty(trim($item))) {
                    return false;
                }
            break;
            case "i":
                if (!is_numeric($item)) {
                    return false;
                }
            break;
            case "e":
                if(!is_string($item) or !filter_var($item, FILTER_VALIDATE_EMAIL)) {
                    return false;
                }
            break;
            case "p":
                if(!is_string($item) or empty(trim($item)) or strlen($item) < 6) {
                    return false;
                }
            break;
            default:
            return false;
        }
    }
  
    //All cases passed, return true
    return true;
}

//Function for validating token and returning associated userID
function validate_token($database_connection, $token) {
    $statement = $database_connection->prepare("SELECT userID, expiry FROM tokens WHERE token=? LIMIT 1;");
    $statement->bind_param("s", $token);
    $statement->execute();
    $statement->bind_result($userID, $expiry);
    $statement->fetch();
    if (!$userID or !$expiry) {
        //Token is invalid, output error and exit
        die(json_encode(array("error" => "INVALID_TOKEN")));
    }
    if ($expiry < time()) {
        //Token is expired, remove token from database and output error
        $statement->close();
        $database_connection->query("DELETE FROM tokens WHERE token='".$token."' LIMIT 1;");
        die(json_encode(array("error" => "TOKEN_EXPIRED")));
    }
    $statement->close();

    //Return userID
    return $userID;
}

//Function for generating verification code
function generateCode($length, $keyspace = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ') {
    $pieces = [];
    $max = mb_strlen($keyspace, "8bit") - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces[] = $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}

?>