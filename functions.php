<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // Tải PHPMailer (nếu dùng Composer)

function sendVerificationEmail($email, $verification_code) {
    $mail = new PHPMailer(true);
    
    try {
        // Cấu hình SMTP Server
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // SMTP server của Gmail
        $mail->SMTPAuth   = true;
        $mail->Username   = 'nvdat22082004@gmail.com'; // Email của bạn
        $mail->Password   = 'exqx fozv qxru ucev';  // Mật khẩu ứng dụng của email
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Thông tin người gửi và người nhận
        $mail->setFrom('nvdat22082004@fastlearn.com', 'FastLearn');
        $mail->addAddress($email);

        // Nội dung email
        $mail->isHTML(true);
        $mail->Subject = 'Mã xác thực quên mật khẩu';
        $mail->Body    = 'Để đặt lại mật khẩu của bạn, vui lòng sử dụng mã xác thực sau: ' . $verification_code;

        $mail->send();
        echo 'Đã gửi mã xác thực!';
    } catch (Exception $e) {
        echo "Không thể gửi email. Lỗi: {$mail->ErrorInfo}";
    }
}
?>
