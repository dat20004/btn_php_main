<?php
session_start();
require 'connect.php'; // Kết nối CSDL
require 'send_verification_email.php'; // Đảm bảo bạn đã bao gồm tệp gửi email

$errors = [];
$attempts = $_SESSION['attempts'] ?? 0;
$lockout_time = $_SESSION['lockout_time'] ?? 0;

// Kiểm tra khóa tạm thời
if ($lockout_time > time()) {
    $errors['verification'] = "Tài khoản của bạn đã bị khóa tạm thời. Vui lòng thử lại sau " . (($lockout_time - time()) / 60) . " phút.";
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Kiểm tra xem có gửi mã xác nhận lại không
        if (isset($_POST['resend'])) {
            // Lấy email từ session
            if (isset($_SESSION['email'])) {
                $email = $_SESSION['email'];

                // Tạo mã xác thực ngẫu nhiên
                $verification_code = random_int(100000, 999999);
                
                // Gửi email xác thực
                sendVerificationEmail($email, $verification_code);

                // Cập nhật mã xác thực vào cơ sở dữ liệu
                $stmt = $pdo->prepare("UPDATE users SET verification_code = :code, expires_at = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE email = :email");
                $stmt->bindParam(':code', $verification_code);
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                $_SESSION['message'] = 'Mã xác thực mới đã được gửi đến email của bạn.';
            } else {
                echo "Không tìm thấy email.";
                exit();
            }
        } else {
            // Mã xác nhận từ người dùng
            $code = $_POST['code'];

            // Lấy email từ session
            if (isset($_SESSION['email'])) {
                $email = $_SESSION['email'];
            } else {
                echo "Không tìm thấy email.";
                exit();
            }

            // Tạo kết nối PDO
            global $pdo;

            // Kiểm tra mã xác nhận
            $stmt = $pdo->prepare("SELECT verification_code, expires_at FROM users WHERE email = :email ORDER BY created_at DESC LIMIT 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $verification = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($verification) {
                // Kiểm tra mã xác nhận và thời gian
                if (trim($verification['verification_code']) == trim($code) && strtotime($verification['expires_at']) > time()) {
                    // Cập nhật trạng thái xác minh
                    $stmt = $pdo->prepare("UPDATE users SET state = 'Active' WHERE email = :email");
                    $stmt->bindParam(':email', $email);
                    $stmt->execute();

                    // Lưu thông báo xác nhận vào session
                    $_SESSION['verificationMessage'] = "Mã xác nhận hợp lệ! Tài khoản đã được xác minh.";
                    $_SESSION['redirectPage'] = "login.php";  // Trang đăng nhập sau khi xác minh thành công

                    // Chuyển hướng tới trang thông báo
                    header("Location: success_message.php");
                    exit();
                } else {
                    // Nếu mã không hợp lệ, tăng số lần thử
                    $attempts++;
                    $_SESSION['attempts'] = $attempts;
                    if ($attempts >= 3) {
                        // Khóa tài khoản tạm thời trong 15 phút
                        $_SESSION['lockout_time'] = time() + 15 * 60; // 15 phút
                        $errors['verification'] = "Bạn đã nhập sai mã quá 3 lần. Tài khoản của bạn đã bị khóa tạm thời. Vui lòng thử lại sau 15 phút.";
                    } else {
                        $errors['verification'] = "Mã xác nhận không hợp lệ hoặc đã hết hạn. Bạn còn " . (3 - $attempts) . " lần thử.";
                    }
                }
            } else {
                $errors['verification'] = "Không tìm thấy mã xác nhận cho email này.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhập mã xác nhận</title>
</head>
<body>
    <h1>Nhập mã xác nhận</h1>

    <?php if (!empty($errors['verification'])): ?>
        <div class="alert alert-danger">
            <?php echo $errors['verification']; ?>
        </div>
    <?php endif; ?>

    <?php if ($lockout_time <= time()): ?>
        <form method="POST">
            <label for="code">Mã xác nhận:</label>
            <input type="text" name="code" required>
            <button type="submit">Xác nhận</button>
        </form>
    <?php endif; ?>

    <!-- Tùy chọn gửi lại mã xác nhận -->
    <form method="POST">
        <input type="hidden" name="resend" value="1">
        <button type="submit">Gửi lại mã xác nhận</button>
    </form>
</body>
</html>
