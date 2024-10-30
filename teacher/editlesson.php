<?php
// Kết nối với cơ sở dữ liệu
require_once('../connect.php');

// Kiểm tra nếu có lesson_id được truyền qua URL
if (!isset($_GET['lesson_id'])) {
    die("ID bài học không hợp lệ.");
}

$lesson_id = $_GET['lesson_id'];
$course_id = null;

// Lấy thông tin bài học từ cơ sở dữ liệu và tìm course_id
try {
    $stmt = $pdo->prepare("SELECT lessons.*, chapters.course_id 
                           FROM lessons 
                           JOIN chapters ON lessons.chapter_id = chapters.id 
                           WHERE lessons.id = :lesson_id");
    $stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
    $stmt->execute();
    $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lesson) {
        die("Bài học không tồn tại.");
    }

    // Lưu course_id để sử dụng trong chuyển hướng
    $course_id = $lesson['course_id'];
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}

// Xử lý khi form được gửi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lesson_title = $_POST['lesson_title'];
    $lesson_content = $_POST['lesson_content'];
    $lesson_link = $_POST['lesson_link'];
    $lesson_duration = $_POST['lesson_duration'];

    if (!empty($lesson_title) && !empty($lesson_content) && !empty($lesson_link) && !empty($lesson_duration)) {
        try {
            // Cập nhật thông tin bài học trong cơ sở dữ liệu
            $stmt = $pdo->prepare("UPDATE lessons 
                                   SET title = :title, content = :content, link = :link, duration = :duration 
                                   WHERE id = :lesson_id");
            $stmt->bindParam(':title', $lesson_title);
            $stmt->bindParam(':content', $lesson_content);
            $stmt->bindParam(':link', $lesson_link);
            $stmt->bindParam(':duration', $lesson_duration);
            $stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
            $stmt->execute();

            // Chuyển hướng về trang quản lý chương với course_id cụ thể
            header("Location: manage_chapters.php?course_id=" . urlencode($course_id));
            exit();
        } catch (PDOException $e) {
            die("Lỗi: " . $e->getMessage());
        }
    } else {
        echo "Tiêu đề, nội dung, link và thời gian bài học không thể trống!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa Bài Học</title>
</head>
<body>
<h1>Chỉnh Sửa Bài Học</h1>

<form action="editlesson.php?lesson_id=<?php echo $lesson['id']; ?>" method="POST">
    <label for="lesson_title">Tiêu đề bài học:</label>
    <input type="text" name="lesson_title" id="lesson_title" value="<?php echo htmlspecialchars($lesson['title']); ?>" required>
    <br><br>

    <label for="lesson_content">Nội dung bài học:</label>
    <textarea name="lesson_content" id="lesson_content" required><?php echo htmlspecialchars($lesson['content']); ?></textarea>
    <br><br>

    <label for="lesson_link">Link bài học:</label>
    <input type="url" name="lesson_link" id="lesson_link" value="<?php echo htmlspecialchars($lesson['link']); ?>" required>
    <br><br>

    <label for="lesson_duration">Thời gian học (phút):</label>
    <input type="number" name="lesson_duration" id="lesson_duration" value="<?php echo htmlspecialchars($lesson['duration']); ?>" required min="1" placeholder="Nhập thời gian học bằng phút">
    <br><br>

    <button type="submit">Cập nhật Bài Học</button>
</form>

<a href="index.php">Quay lại danh sách chương và bài học</a>
</body>
</html>
