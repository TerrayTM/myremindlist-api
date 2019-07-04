<?php

//Include utility functions
include "./utility_functions.php";

//Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
  case "DELETE":
  //Decode JSON request
  $request = json_decode(file_get_contents("php://input"), true);

  if (isset($request['id'])) {
    //Request is to delete a remind
    include "./delete_remind.php";
  } else {
    //Invalid request, exit and output error
    die(json_encode(array("error" => "INVALID_REQUEST")));
  }
  break;
  case "POST":
  //Decode JSON request
  $request = json_decode(file_get_contents("php://input"), true);

  if (isset($request['firstName'])) {
    //Request is to create an account
    include "./create_account.php";
  } else if (isset($request['message'])) {
    //Request is to create a remind
    include "./create_remind.php";
  } else if (isset($request['email'])) {
    //Request is to generate a token
    include "./login_user.php";
  } else if (isset($request['phone'])) {
    //Request is to add phone number
    include "./add_phone.php";
  } else if (isset($request['code'])) {
    //Request is to verify email or phone
    include "./verification.php";
  } else {
    //Invalid request, exit and output error
    die(json_encode(array("error" => "INVALID_REQUEST")));
  }
  break;
  case "GET":
  if (isset($_GET['token'])) {
      //Making a request to get reminds
      include "./get_info.php";
  } else {
    //Invalid request, exit and output error
    die(json_encode(array("error" => "INVALID_REQUEST")));
  }
  break;
  default:
  //Invalid request, exit and output error
  die(json_encode(array("error" => "INVALID_REQUEST")));
  break;
}

?>