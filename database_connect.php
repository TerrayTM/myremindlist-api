<?php

//Database constants
DEFINE ("DATABASE_HOST", "******");
DEFINE ("DATABASE_NAME", "******");
DEFINE ("DATABASE_USER", "******");
DEFINE ("DATABASE_PASSWORD", "******");

//Attempt to connect to database
$database_connection = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);

//Check if connection succeeds
if ($database_connection->connect_error) {
    //Connect fails, output error status
    die(json_encode(array("error" => "DATABASE_CONNECTION_FAILED")));
}

?>
