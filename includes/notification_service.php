<?php
// Minimal wrapper to include the project's notification_service implementation
if (file_exists(__DIR__ . '/../notification_service.php')) {
    require_once __DIR__ . '/../notification_service.php';
} else {
    error_log('notification_service.php not found in project root');
}
