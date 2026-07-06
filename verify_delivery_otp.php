<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once "config.php";
require_once "includes/notification_service.php";

// This endpoint is intended for delivery staff to verify OTP at delivery time.
// It accepts POST: order_id, otp
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$entered_otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
$ip = $_SERVER['REMOTE_ADDR'] ?? null;

if ($order_id <= 0 || empty($entered_otp)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$result = verify_delivery_otp($conn, $order_id, $entered_otp, $ip);
if ($result['success']) {
    echo json_encode(['success' => true, 'message' => $result['message']]);
} else {
    echo json_encode(['success' => false, 'message' => $result['message']]);
}

?>
