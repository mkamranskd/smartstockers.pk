<?php
require_once __DIR__ . '/../vendor/autoload.php';  // Autoload Twilio package
use Twilio\Rest\Client;  // Use Twilio's Client class

// Function to send WhatsApp message using Twilio
function sendWhatsAppMessage($to, $messageBody) {
    $sid    = "AC90fc3a0f2c0718e994797ab397e6c4b7";  // Your Twilio SID
    $token  = "7eb35c832bd521f09b838fe0ff17481e";  // Your Twilio Auth Token
    $twilio = new Client($sid, $token);

    try {
        $message = $twilio->messages->create(
            "whatsapp:" . $to,
            [
                "from" => "whatsapp:+14155238886",
                "body" => $messageBody
            ]
        );
        echo json_encode([
            'success' => true,
            'sid' => $message->sid,
            'status' => 'Message sent successfully.'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Twilio error: ' . $e->getMessage()
        ]);
    }
}    