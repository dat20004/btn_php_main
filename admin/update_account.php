<?php
session_start();
require 'db_connection.php'; // Kết nối đến cơ sở dữ liệu

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy thông tin người dùng từ phiên
$user_id = $_SESSION['user_id'];

// Lấy dữ liệu từ biểu mẫu
$name = $_POST['name'];
$email = $_POST['email'];
$old_password = $_POST['old-password'];
$new_password = $_POST['new-password'];
$confirm_password = $_POST['confirm-password'];

// Truy vấn cơ sở dữ liệu để lấy thông tin người dùng hiện tại
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Kiểm tra mật khẩu cũ
if (!password_verify($old_password, $user['password'])) {
    echo "Mật khẩu cũ không chính xác.";
    exit();
}

// Kiểm tra mật khẩu mới và xác nhận mật khẩu mới
if ($new_password !== $confirm_password) {
    echo "Mật khẩu mới không khớp.";
    exit();
}

// Cập nhật thông tin người dùng
$hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
$update_query = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("sssi", $name, $email, $hashed_new_password, $user_id);

if ($update_stmt->execute()) {
    echo "Thông tin tài khoản đã được cập nhật thành công.";
} else {
    echo "Có lỗi xảy ra khi cập nhật thông tin.";
}

// Đóng kết nối
$stmt->close();
$update_stmt->close();
$conn->close();
?>