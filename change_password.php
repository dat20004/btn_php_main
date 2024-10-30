<?php
session_start();
include 'connect.php';  // Kết nối tới cơ sở dữ liệu

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['userID'])) {
    header('Location: login.php'); // Chuyển hướng đến trang đăng nhập nếu chưa đăng nhập
    exit;
}

// Lấy ID người dùng từ session
$user_id = $_SESSION['userID']; 

// Kiểm tra xem form đã được submit chưa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra mật khẩu mới và mật khẩu xác nhận có khớp không
    if ($new_password !== $confirm_password) {
        echo "Mật khẩu mới và xác nhận mật khẩu không khớp.";
        exit;
    }

    try {
        // Truy vấn để lấy mật khẩu hiện tại từ cơ sở dữ liệu
        $query = "SELECT password FROM users WHERE id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra mật khẩu hiện tại có đúng không
        if (password_verify($current_password, $user['password'])) {
            // Nếu đúng, mã hóa mật khẩu mới
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Cập nhật mật khẩu mới vào cơ sở dữ liệu
            $update_query = "UPDATE users SET password = :new_password WHERE id = :user_id";
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->bindParam(':new_password', $hashed_new_password, PDO::PARAM_STR);
            $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

            if ($update_stmt->execute()) {
                unset($_SESSION['cur_login']);

                header('Location: login.php');
        exit();
            } else {
                echo "Đã xảy ra lỗi khi cập nhật mật khẩu.";
            }
        } else {
            echo "Mật khẩu hiện tại không chính xác.";
        }
    } catch (PDOException $e) {
        echo "Lỗi: " . $e->getMessage();
    }
} else {
    echo "Không có dữ liệu nào được gửi.";
}
?>