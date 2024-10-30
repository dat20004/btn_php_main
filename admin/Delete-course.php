<?php
include '../connect.php';  // Kết nối với cơ sở dữ liệu

// Kiểm tra nếu có ID khóa học được truyền vào
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $course_id = (int)$_GET['id'];  // Chuyển đổi ID sang kiểu số nguyên

    // Truy vấn SQL để xóa khóa học
    try {
        $delete_query = "DELETE FROM courses WHERE id = :id";
        $stmt = $conn->prepare($delete_query);
        $stmt->bindParam(':id', $course_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Nếu xóa thành công, thông báo và chuyển hướng về trang danh sách khóa học
            $_SESSION['success'] = "Khóa học đã được xóa thành công.";
            header("Location: course.php");  // Thay đổi link này cho phù hợp với trang danh sách khóa học của bạn
            exit();
        } else {
            // Nếu xóa thất bại, thông báo lỗi
            $_SESSION['error'] = "Không thể xóa khóa học. Vui lòng thử lại.";
            header("Location: course.php");  // Thay đổi link này cho phù hợp với trang danh sách khóa học của bạn
            exit();
        }
    } catch (PDOException $e) {
        // Xử lý lỗi nếu có
        $_SESSION['error'] = "Lỗi khi xóa khóa học: " . $e->getMessage();
        header("Location: course.php");
        exit();
    }
} else {
    // Nếu không có ID khóa học hợp lệ, thông báo lỗi
    $_SESSION['error'] = "ID khóa học không hợp lệ.";
    header("Location: course.php");
    exit();
}
?>
