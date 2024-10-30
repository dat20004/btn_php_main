<?php
include 'connect.php';
require 'xacminh.php';

$email = $_GET['email'] ?? ''; 

// Kiểm tra xem có gửi mã xác nhận mới hay không
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend'])) {
    // Tạo mã xác nhận mới và lưu vào cơ sở dữ liệu
    $verificationCode = rand(100000, 999999);
    $stmt = $pdo->prepare("UPDATE users SET verification_code = :code WHERE email = :email");
    $stmt->execute(['code' => $verificationCode, 'email' => $email]);

    // Gửi email xác minh qua hàm sendVerificationEmail
    if (sendVerificationEmail($email, $verificationCode)) {
        echo "Mã xác minh mới đã được gửi tới email của bạn!";
    } else {
        echo "Không thể gửi mã xác minh. Vui lòng thử lại.";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    // Xử lý xác minh mã
    $verificationCode = $_POST['verification_code'] ?? '';
    
    // Kiểm tra mã xác nhận trong cơ sở dữ liệu
    $stmt = $pdo->prepare("SELECT verification_code FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verificationCode) {
        // Mã hợp lệ, cập nhật trạng thái tài khoản
        $stmt = $pdo->prepare("UPDATE users SET state = 'Active', verification_code = NULL WHERE email = :email");
        $stmt->execute(['email' => $email]);
        
        echo "Tài khoản của bạn đã được kích hoạt!";
        header("Location: login.php"); // Chuyển hướng về trang đăng nhập
        exit();
    } else {
        echo "Mã xác nhận không chính xác. Vui lòng thử lại.";
    }
}

// Tạo mã xác nhận ngẫu nhiên và lưu vào cơ sở dữ liệu chỉ khi không có yêu cầu POST
if (!$email) {
    // Nếu không có email, có thể chuyển hướng về trang khác
    header("Location: error_page.php");
    exit();
}

// Tạo mã xác nhận mới nếu cần
$verificationCode = rand(100000, 999999);
$stmt = $pdo->prepare("UPDATE users SET verification_code = :code WHERE email = :email");
$stmt->execute(['code' => $verificationCode, 'email' => $email]);

// Gửi email xác minh
if (sendVerificationEmail($email, $verificationCode)) {
    echo "Mã xác minh đã được gửi tới email của bạn!";
} else {
    echo "Không thể gửi mã xác minh. Vui lòng thử lại.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nhập Mã Xác Nhận</title>
</head>
<body>
    <h1>Nhập Mã Xác Nhận</h1>
    <form action="" method="POST">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email); ?>">
        <label for="verification_code">Mã xác nhận:</label>
        <input type="text" name="verification_code" required><br>
        <button type="submit" name="verify">Xác minh</button>
    </form>
    
    <!-- Nút Gửi lại mã xác nhận -->
    <form action="" method="POST" style="margin-top: 10px;">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email); ?>">
        <button type="submit" name="resend">Gửi lại mã xác nhận</button>
    </form>
</body>
</html>
