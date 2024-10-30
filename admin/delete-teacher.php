<?php
session_start();
include '../connect.php';

// Kiểm tra xem có ID giáo viên không
if (isset($_GET['id'])) {
    $teacherID = $_GET['id'];

    // Xóa giáo viên khỏi cơ sở dữ liệu
    $deleteQuery = "DELETE FROM users WHERE id = :id";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bindParam(':id', $teacherID, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Giáo viên đã được xóa thành công!";
        header("Location: teacher.php");
        exit();
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra khi xóa giáo viên.";
        header("Location: teacher.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Không tìm thấy giáo viên.";
    header("Location: teacher.php");
    exit();
}
?>
