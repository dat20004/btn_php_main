<?php
// Kết nối với cơ sở dữ liệu
require_once('../connect.php');

// Kiểm tra nếu có lesson_id được truyền qua URL
if (!isset($_GET['lesson_id'])) {
    die("ID bài học không hợp lệ.");
}

$lesson_id = $_GET['lesson_id'];

// Lấy thông tin bài học trước khi xóa
try {
    $stmt =$pdo->prepare("SELECT title FROM lessons WHERE id = :lesson_id");
    $stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
    $stmt->execute();
    $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lesson) {
        die("Bài học không tồn tại.");
    }
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Nếu người dùng xác nhận xóa, thực hiện xóa bài học
    if (isset($_POST['confirm_delete'])) {
        try {
            $stmt =$pdo->prepare("DELETE FROM lessons WHERE id = :lesson_id");
            $stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
            $stmt->execute();

            // Chuyển hướng về trang quản lý chương sau khi xóa thành công
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            die("Lỗi: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Nhận Xóa Bài Học</title>
</head>
<body>
    <h1>Xác Nhận Xóa Bài Học</h1>
    <p>Bạn chắc chắn muốn xóa bài học: <strong><?php echo htmlspecialchars($lesson['title']); ?></strong>?</p>

    <form action="deletelesson.php?lesson_id=<?php echo $lesson_id; ?>" method="POST">
        <button type="submit" name="confirm_delete" value="yes">Xóa</button>
        <a href="index.php">Hủy</a>
    </form>
</body>
</html>
