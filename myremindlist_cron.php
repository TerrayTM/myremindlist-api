<?php

//Connect to database
include dirname(__FILE__)."/database_connect.php";

//Find active reminds and check if they need to be sent
$query = $database_connection->query("SELECT * FROM reminds WHERE time <= ".time().";");
$active_reminds = array();
while ($row = $query->fetch_assoc()) {
    if (isset($row['params']) and strpos($row['params'], "active") !== false) {
        $active_reminds[$row['userID']][] = $row;
    }
}
$query->free();

//If no active reminds exist, exit script
if (count($active_reminds) < 1) {
    exit;
}

//Load Composor's autoloader
require dirname(__FILE__)."/vendor/autoload.php";

//Import libraries
use Twilio\Rest\Client;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Function for emailing
function sendMail($mail, $email, $message) {
    try {
        //Configure email
        $mail->clearAddresses();
        $mail->addAddress($email);

        //Set content of email
        $mail->Body = '
            <div style="max-width: 800px; width: 90%; margin: 24px auto">
                <div style="padding: 4px 16px; background-color: #2B59C3; color: aliceblue; border-radius: 6px 6px 0 0; display: flex">
                    <img src="https://myremindlist.com/logo.png" style="width: 45px; height: 45px; margin: auto 10px auto 0">
                    <h1><a href="https://myremindlist.com" style="color: inherit; text-decoration: none" target="_blank">MyRemindList</a></h1>
                </div>
                <div style="padding: 16px; background-color: #eff4ff">
                    <h3 style="margin: 0">Scheduled Message:</h3>
                    <p>'.$message.'</p>
                </div>
                <div style="color: aliceblue; padding: 16px 0 16px 16px; background-color: #2D2D2A; border-radius: 0 0 6px 6px">
                    <h4 style="margin: 0">Reminder Service by Terry™</h4>
                </div>
            </div>
        ';
        $mail->AltBody = $message;

        //Send the email
        $mail->send();
    } catch (Exception $error) {
        echo 'Error: '.$mail->ErrorInfo;
    }
}

//Function for sending SMS
function sendSMS($client, $number, $message) {
    $client->messages->create(
        $number,
        array(
            "from" => "+16474901643",
            "body" => "Reminder: ".$message
        )
    );
}

//Initalize mailing service
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->SMTPKeepAlive = true;
$mail->Host = 'mx1.hostinger.com'; 
$mail->SMTPAuth = true; 
$mail->Username = 'service@myremindlist.com';
$mail->Password = '******';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;
$mail->setFrom('service@myremindlist.com', 'MyRemindList.com');
$mail->isHTML(true);
$mail->Subject = 'MyRemindList - Remind';

//Initalize SMS service
$client = new Client("******", "******");

//Send reminds
foreach ($active_reminds as $key => $value) {
    if ($query = $database_connection->query("SELECT email, phone FROM users WHERE id=".$key." LIMIT 1;")) {
        $info = $query->fetch_assoc();
        $query->free();
        for ($i = 0; $i < count($value); ++$i) {
            if (strpos($value[$i]['params'], "email") !== false) {
                //Send remind via email
                sendMail($mail, $info['email'], $value[$i]['message']);
            } else if (strpos($value[$i]['params'], "sms") !== false) {
                //Send remind via SMS if message is less than 256 characters
                if (strlen($value[$i]['message']) < 257) {
                    sendSMS($client, $info['phone'], $value[$i]['message']);
                }
            }

            //Mark remind as archived
            $database_connection->query("UPDATE reminds SET params='".str_replace("active", "archived", $value[$i]['params'])."' WHERE id=".$value[$i]['id']." LIMIT 1;");
        }
    }
}

//Clean up
$mail->SmtpClose();

?>
