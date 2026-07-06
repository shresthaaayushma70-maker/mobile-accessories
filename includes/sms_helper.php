<?php
/**
 * SMS/WhatsApp helper.
 * This project does not use Twilio; messages are simulated and logged for local development.
 */
function send_sms_whatsapp($toNumber, $message, $method = 'sms') {
    $method = strtolower($method);
    $to = preg_replace('/[^0-9+]/', '', $toNumber);

    error_log("[SMS/WhatsApp] Simulated send to {$to} via {$method}: {$message}");
    return true;
}
