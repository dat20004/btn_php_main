<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // PHPMailer

function sendVerificationEmail($email, $verification_code) {
    $mail = new PHPMailer(true);
    
    try {
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'nvdat22082004@gmail.com';  // Địa chỉ Gmail
        $mail->Password   = 'exqx fozv qxru ucev';      // Mật khẩu ứng dụng
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Cấu hình người gửi và người nhận
        $mail->setFrom('nvdat22082004@gmail.com', 'FastLearn');
        $mail->addAddress($email);

        // Nội dung email
        $mail->isHTML(true);
        $mail->Subject = 'Xác minh tài khoản';
        $mail->Body    = 'Mã xác minh của bạn là: <b>' . $verification_code . '</b>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Lỗi gửi email: {$mail->ErrorInfo}";
        return false;
    }
}
?>
