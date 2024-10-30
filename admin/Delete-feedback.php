<?php
session_start();
include '../connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra xem có tham số id trong URL không
if (isset($_GET['id'])) {
    $feedback_id = $_GET['id'];

    try {
        // Thực hiện câu lệnh DELETE để xóa đánh giá
        $sql = "DELETE FROM course_feedbacks WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $feedback_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Nếu thành công, chuyển hướng về trang danh sách đánh giá
            $_SESSION['success_message'] = "Đã xóa đánh giá thành công.";
            header('Location: feedback.php'); // Điều hướng sau khi xóa thành công
            exit;
        } else {
            $_SESSION['error_message'] = "Không thể xóa đánh giá.";
        }

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Lỗi: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "Không tìm thấy ID đánh giá.";
}

// Điều hướng về trang danh sách đánh giá nếu không có id
header('Location: feedback.php');
exit;
