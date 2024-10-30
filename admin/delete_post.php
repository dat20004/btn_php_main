<?php
// Khởi động session
session_start();

// Kiểm tra nếu chưa đăng nhập, chuyển hướng về trang login
if (!isset($_SESSION['userID'])) {
    header('Location: login.php'); // Chuyển hướng đến trang đăng nhập
    exit;
}

// Include file kết nối
include '../connect.php';

try {
    // Kiểm tra nếu ID bài đăng được gửi qua GET
    if (isset($_GET['id'])) {
        $post_id = $_GET['id'];

        // Câu truy vấn xóa bài đăng
        $sql = "DELETE FROM posts WHERE id = :id";

        // Chuẩn bị câu truy vấn với PDO
        $stmt = $pdo->prepare($sql);

        // Gán giá trị cho tham số
        $stmt->bindParam(':id', $post_id);

        // Thực thi câu truy vấn
        if ($stmt->execute()) {
            // Xóa bài đăng thành công, lưu thông báo vào session
            $_SESSION['message'] = "Bài đăng đã được xóa thành công!";
        } else {
            // Xóa bài đăng thất bại, lưu thông báo vào session
            $_SESSION['error'] = "Lỗi khi xóa bài đăng.";
        }
    }
} catch (PDOException $e) {
    // Lỗi kết nối, lưu thông báo vào session
    $_SESSION['error'] = "Lỗi kết nối: " . $e->getMessage();
}

// Chuyển hướng về trang index
header('Location: index.php');
exit;
?>
