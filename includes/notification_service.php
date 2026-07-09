<?php
/**
 * Notification service helper functions.
 * This file provides the notification API used by the application.
 */

function get_unread_notifications_count($conn, $user_id) {
    $sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return 0;
    }
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return (int)$row['unread_count'];
    }
    mysqli_stmt_close($stmt);
    return 0;
}

function get_user_notifications($conn, $user_id, $limit = 20, $offset = 0) {
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return [];
    }
    mysqli_stmt_bind_param($stmt, 'iii', $user_id, $limit, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $notifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $notifications;
}

function mark_notification_read($conn, $notification_id) {
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'i', $notification_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

function mark_all_read($conn, $user_id) {
    $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

function get_notification_preferences($conn, $user_id) {
    $sql = "SELECT * FROM notification_preferences WHERE user_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $preferences = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $preferences ?: false;
}

function create_default_preferences($conn, $user_id) {
    $sql = "INSERT IGNORE INTO notification_preferences (user_id) VALUES (?)";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

function update_notification_preferences($conn, $user_id, $preferences) {
    $defaults = [
        'email_on_order_placed' => 0,
        'email_on_order_confirmed' => 0,
        'email_on_processing' => 0,
        'email_on_packing' => 0,
        'email_on_shipped' => 0,
        'email_on_delivered' => 0,
        'sms_on_order_placed' => 0,
        'sms_on_order_confirmed' => 0,
        'sms_on_processing' => 0,
        'sms_on_packing' => 0,
        'sms_on_shipped' => 0,
        'sms_on_delivered' => 0,
        'phone_number' => null
    ];

    $values = [];
    foreach ($defaults as $key => $default) {
        $values[$key] = isset($preferences[$key]) ? $preferences[$key] : $default;
    }

    $sql = "UPDATE notification_preferences SET 
        email_on_order_placed = ?,
        email_on_order_confirmed = ?,
        email_on_processing = ?,
        email_on_packing = ?,
        email_on_shipped = ?,
        email_on_delivered = ?,
        sms_on_order_placed = ?,
        sms_on_order_confirmed = ?,
        sms_on_processing = ?,
        sms_on_packing = ?,
        sms_on_shipped = ?,
        sms_on_delivered = ?,
        phone_number = ?
        WHERE user_id = ?";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        'iiiiiiiiiiiisi',
        $values['email_on_order_placed'],
        $values['email_on_order_confirmed'],
        $values['email_on_processing'],
        $values['email_on_packing'],
        $values['email_on_shipped'],
        $values['email_on_delivered'],
        $values['sms_on_order_placed'],
        $values['sms_on_order_confirmed'],
        $values['sms_on_processing'],
        $values['sms_on_packing'],
        $values['sms_on_shipped'],
        $values['sms_on_delivered'],
        $values['phone_number'],
        $user_id
    );

    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

function create_notification($conn, $user_id, $order_id, $type, $title, $message, $link = null) {
    $sql = "INSERT INTO notifications (user_id, order_id, type, title, message, icon_class, is_read, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return false;
    }

    $icon_class = get_notification_icon($type);
    mysqli_stmt_bind_param($stmt, 'iissss', $user_id, $order_id, $type, $title, $message, $icon_class);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

function get_notification_icon($type) {
    $map = [
        'order_placed' => 'fa-shopping-cart',
        'order_confirmed' => 'fa-check-circle',
        'processing' => 'fa-cogs',
        'packing' => 'fa-box',
        'Out for Delivery' => 'fa-truck',
        'delivered' => 'fa-check-double',
        'cancelled' => 'fa-times-circle'
    ];
    return isset($map[$type]) ? $map[$type] : 'fa-info-circle';
}

function get_notification_for_status($status) {
    $map = [
        'Order Placed' => [
            'type' => 'order_placed',
            'title' => 'Order Placed',
            'message' => 'Your order has been placed successfully.'
        ],
        'Confirmed' => [
            'type' => 'order_confirmed',
            'title' => 'Order Confirmed',
            'message' => 'Your order has been confirmed.'
        ],
        'Processing' => [
            'type' => 'processing',
            'title' => 'Order Processing',
            'message' => 'Your order is being processed.'
        ],
        'Packing' => [
            'type' => 'packing',
            'title' => 'Order Packing',
            'message' => 'Your order is being packed.'
        ],
        'Out for Delivery' => [
            'type' => 'out_for_delivery',
            'title' => 'Out for Delivery',
            'message' => 'Your order is out for delivery.'
        ],
        'Delivered' => [
            'type' => 'delivered',
            'title' => 'Order Delivered',
            'message' => 'Your order has been delivered.'
        ],
        'Cancelled' => [
            'type' => 'cancelled',
            'title' => 'Order Cancelled',
            'message' => 'Your order has been cancelled.'
        ]
    ];
    return isset($map[$status]) ? $map[$status] : ['type' => 'notification', 'title' => 'Notification', 'message' => 'You have a new notification.'];
}
