<?php

use PHPMailer\PHPMailer\PHPMailer;

require __DIR__ . '/../vendor/autoload.php';

function sendMail($to, $subject, $body) {

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username   = getenv('SMTP_USER');
    $mail->Password   = getenv('SMTP_PASS');
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('rojha5250@gmail.com', 'Pandit-Sangama');
    $mail->addAddress($to);
    // $mail->SMTPDebug = 2;

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;

    $mail->send();
}