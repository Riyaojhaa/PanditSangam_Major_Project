<?php

use PHPMailer\PHPMailer\PHPMailer;

function smtp_env(string $key, ?string $default = null): ?string {
    $v = $_ENV[$key] ?? $_SERVER[$key] ?? null;
    if ($v !== null && $v !== '') {
        return (string) $v;
    }
    $g = getenv($key);
    return ($g !== false && $g !== '') ? (string) $g : $default;
}

function sendMail($to, $subject, $body) {
    $user = smtp_env('SMTP_USER');
    $pass = smtp_env('SMTP_PASS');
    if (!$user || !$pass) {
        throw new InvalidArgumentException(
            'SMTP_USER and SMTP_PASS must be set. Copy .env.example to .env in RegistrationAPI and add your Gmail address and App Password.'
        );
    }

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = smtp_env('SMTP_HOST', 'smtp.gmail.com');
    $mail->SMTPAuth = true;
    $mail->Username = $user;
    $mail->Password = $pass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int) smtp_env('SMTP_PORT', '587');

    $fromEmail = smtp_env('SMTP_FROM', $user);
    $fromName = smtp_env('SMTP_FROM_NAME', 'Pandit-Sangama');
    $mail->setFrom($fromEmail, $fromName);
    $mail->addAddress($to);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $body;

    $mail->send();
}
