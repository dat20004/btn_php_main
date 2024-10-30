<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // Tải PHPMailer từ Composer

function sendVerificationEmail($email, $code) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'nvdat22082004@gmail.com'; // Địa chỉ email của bạn
        $mail->Password = 'exqx fozv qxru ucev'; // Mật khẩu email của bạn
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('nvdat22082004@gmail.com', 'FastLearn');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Mã xác nhận của bạn';
        $mail->Body = "Mã xác nhận của bạn là: <b>$code</b>";

        $mail->send();
    } catch (Exception $e) {
        echo "Không thể gửi mã xác nhận: {$mail->ErrorInfo}";
    }
}
?>
