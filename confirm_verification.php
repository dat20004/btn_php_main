<?php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $verificationCode = $_POST['verification_code'] ?? '';

    // Kiểm tra mã xác nhận trong cơ sở dữ liệu
    $stmt = $pdo->prepare("SELECT verification_code FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verificationCode) {
        // Mã hợp lệ, cập nhật trạng thái tài khoản
        $stmt = $pdo->prepare("UPDATE users SET state = 'Active', verification_code = NULL WHERE email = :email");
        $stmt->execute(['email' => $email]);

        // Thông báo kích hoạt thành công và chuyển hướng đến trang đăng nhập
        echo "Tài khoản của bạn đã được kích hoạt!";
        header("Location: login.php"); // Chuyển hướng về trang đăng nhập
        exit(); // Kết thúc script để đảm bảo không có code nào khác được thực thi
    } else {
        echo "Mã xác nhận không chính xác. Vui lòng thử lại.";
    }
}
