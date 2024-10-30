<?php
require_once('../connect.php'); // Kết nối với cơ sở dữ liệu

// Kiểm tra nếu có `chapter_id` được truyền qua URL
if (!isset($_GET['chapter_id'])) {
    die("ID chương không hợp lệ.");
}

$chapter_id = $_GET['chapter_id'];

// Xóa chương khỏi cơ sở dữ liệu
try {
    $stmt = $pdo->prepare("DELETE FROM chapters WHERE id = :id");
    $stmt->bindParam(':id', $chapter_id);
    $stmt->execute();

    // Chuyển hướng về trang quản lý chương
    header("Location: manage_chapters.php");
    exit();
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>
