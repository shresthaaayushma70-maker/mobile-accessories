<?php
/**
 * Mail helper: uses PHPMailer if available (via Composer), otherwise falls back to PHP mail().
 */
function send_email_smtp($to, $subject, $htmlBody, $altBody = '') {
    // Try to use PHPMailer if available
    $composerAutoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($composerAutoload)) {
        require_once $composerAutoload;
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            // SMTP settings from environment vars
            $smtpHost = getenv('SMTP_HOST');
            $smtpPort = getenv('SMTP_PORT') ?: 587;
            $smtpUser = getenv('SMTP_USER');
            $smtpPass = getenv('SMTP_PASS');
            $smtpSecure = getenv('SMTP_SECURE') ?: 'tls';

            if ($smtpHost && $smtpUser) {
                $mail->isSMTP();
                $mail->Host = $smtpHost;
                $mail->SMTPAuth = true;
                $mail->Username = $smtpUser;
                $mail->Password = $smtpPass;
                $mail->SMTPSecure = $smtpSecure;
                $mail->Port = (int)$smtpPort;
            }

            $from = getenv('MAIL_FROM') ?: 'noreply@bazario.com';
            $fromName = getenv('MAIL_FROM_NAME') ?: 'Bazario';

            $mail->setFrom($from, $fromName);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            if (!empty($altBody)) $mail->AltBody = $altBody;

            return $mail->send();
        } catch (Exception $e) {
            error_log('PHPMailer send failed: ' . $e->getMessage());
            // fallthrough to mail()
        }
    }

    // Fallback to native mail()
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Bazario <noreply@bazario.com>\r\n";
    return @mail($to, $subject, $htmlBody, $headers);
}
