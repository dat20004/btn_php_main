<?php
session_start();
include 'connect.php'; // Kết nối đến cơ sở dữ liệu

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

// Lấy ID người dùng từ session
$user_id = $_SESSION['userID'];

// Kiểm tra xem dữ liệu form đã được gửi chưa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy thông tin từ form
    $full_name = $_POST['full_name'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    // Cập nhật thông tin người dùng trong cơ sở dữ liệu
    $sql_update = "UPDATE users SET full_name = :full_name, gender = :gender, phone_number = :phone, email = :email, address = :address WHERE id = :user_id";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->bindParam(':full_name', $full_name);
    $stmt_update->bindParam(':gender', $gender);
    $stmt_update->bindParam(':phone', $phone);
    $stmt_update->bindParam(':email', $email);
    $stmt_update->bindParam(':address', $address);
    $stmt_update->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    // Thực thi truy vấn
    if ($stmt_update->execute()) {
        // Cập nhật thành công, chuyển hướng lại trang profile
        header('Location: myProfile.php');
        exit();
    } else {
        echo "Cập nhật thông tin thất bại.";
    }
}