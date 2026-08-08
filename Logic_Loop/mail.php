<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

function sendMail($to, $subject, $body)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kihallaye@gmail.com';
        $mail->Password = 'tum tum tanao';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('kihallaye@gmail.com', 'CodVion');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
    } catch (Exception $e) {
        $_SESSION['mail'] = "Mailer Error: {$mail->ErrorInfo} Redirecting...";
    }
}
?>