<?php

//Connect to database
include_once "./database_connect.php";

//Validate request parameters
$rules = array(
    array("email", "e"),
    array("password", "p"),
    array("firstName", "s"),
    array("lastName", "s")
);
if (!validate_parameters($request, $rules)) {
    die(json_encode(array("error" => "INVALID_ARGUMENTS")));
}

//Check if email exists
$statement = $database_connection->prepare("SELECT * FROM users WHERE email=? LIMIT 1;");
$statement->bind_param("s", $request['email']);
$statement->execute();
$statement->store_result();
if ($statement->num_rows != 0) {
    //Email exists, output error and exit
    die(json_encode(array("error" => "EMAIL_EXISTS")));
}
$statement->close();

//Create and insert new user
$statement = $database_connection->prepare("INSERT INTO users (email, password, firstName, lastName) VALUES (?, ?, ?, ?);");
$statement->bind_param("ssss", $request['email'], $password_hash, $request['firstName'], $request['lastName']);
$password_hash = hash("sha256", $request['password']);
if (!$statement->execute()) {
    die(json_encode(array("error" => "CREATE_USER_ERROR")));
}
$statement->close();

//Get last inserted row ID
$query = $database_connection->query("SELECT LAST_INSERT_ID();");
$new_id = $query->fetch_row();
if (!$new_id or !isset($new_id[0])) {
    die(json_encode(array("error" => "FETCH_NEW_USER_ID_ERROR")));
}
$query->free();

//Create email verification code and store it in database
$email_code = generateCode(6);
if (!$database_connection->query("INSERT INTO verification (code, userID, type) VALUES ('".$email_code."', ".$new_id[0].", 'verifyEmail');")) {
    die(json_encode(array("error" => "VERIFICATION_CODE_ERROR")));
}

//Load Composor's autoloader
require "./vendor/autoload.php";

//Import PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Email the code
try {
    //Configure email
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'mx1.hostinger.com'; 
    $mail->SMTPAuth = true; 
    $mail->Username = 'service@myremindlist.com';
    $mail->Password = '******';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->setFrom('service@myremindlist.com', 'MyRemindList.com');
    $mail->isHTML(true);
    $mail->Subject = 'MyRemindList - Email Verification';
    $mail->addAddress($request['email']);

    //Set content of email
    $mail->Body = '
        <div style="max-width: 800px; width: 90%; margin: 24px auto">
            <div style="padding: 4px 16px; background-color: #2B59C3; color: aliceblue; border-radius: 6px 6px 0 0; display: flex">
                <img src="https://myremindlist.com/logo.png" style="width: 45px; height: 45px; margin: auto 10px auto 0">
                <h1><a href="https://myremindlist.com" style="color: inherit; text-decoration: none" target="_blank">MyRemindList</a></h1>
            </div>
            <div style="padding: 16px; background-color: #eff4ff">
                <h3 style="margin: 0">Email Verification Code: <span style="background-color: yellow; padding: 4px 6px; border-radius: 4px">'.$email_code.'</span></h3>
            </div>
            <div style="color: aliceblue; padding: 16px 0 16px 16px; background-color: #2D2D2A; border-radius: 0 0 6px 6px">
                <h4 style="margin: 0">If you did not request an account at MyRemindList, please ignore this email.</h4>
            </div>
        </div>
    ';
    $mail->AltBody = "Verification Code: ".$email_code;

    //Send and close email
    $mail->send();
    $mail->SmtpClose();
} catch (Exception $error) {
    die(json_encode(array("error" => "BAD_EMAIL")));
}

//Output user information
include "./login_user.php";

?>
