<?php
require_once '../vendor/autoload.php';
use Twilio\Rest\Client;



$sid    = "AC90fc3a0f2c0718e994797ab397e6c4b7";
$token  = "7eb35c832bd521f09b838fe0ff17481e";
$twilio = new Client($sid, $token);

$message = $twilio->messages
  ->create("whatsapp:+923242711265", // to
    array(
      "from" => "whatsapp:+14155238886",
      "body" => "Your appointment is coming up on July 21 at 3PM"
    )
  );

print($message->sid);