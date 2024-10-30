<?php

include '../connect.php';

// Kiểm tra nếu có `chapter_id` được truyền qua URL
if (!isset($_GET['chapter_id'])) {
    die("ID chương không hợp lệ.");
}

$chapter_id = $_GET['chapter_id'];

// Lấy thông tin chương từ cơ sở dữ liệu
try {
    $stmt = $pdo->prepare("SELECT * FROM chapters WHERE id = :id");
    $stmt->bindParam(':id', $chapter_id);
    $stmt->execute();
    $chapter = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$chapter) {
        die("Chương không tồn tại.");
    }
    // Lưu course_id để sử dụng cho việc chuyển hướng sau này
    $course_id = $chapter['course_id'];
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}

// Nếu người dùng gửi form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $chapter_title = $_POST['chapter_title'];
    $course_id = $_POST['course_id'];

    // Kiểm tra dữ liệu nhập
    if (!empty($chapter_title) && !empty($course_id)) {
        try {
            // Cập nhật chương trong cơ sở dữ liệu
            $stmt = $pdo->prepare("UPDATE chapters SET title = :title, course_id = :course_id WHERE id = :id");
            $stmt->bindParam(':title', $chapter_title);
            $stmt->bindParam(':course_id', $course_id);
            $stmt->bindParam(':id', $chapter_id);
            $stmt->execute();

            // Chuyển hướng về trang quản lý chương của khóa học vừa chỉnh sửa
            header("Location: manage_chapters.php?course_id=" . urlencode($course_id));
            exit();
        } catch (PDOException $e) {
            die("Lỗi: " . $e->getMessage());
        }
    } else {
        echo "<p style='color:red;'>Tiêu đề chương và khóa học không thể trống!</p>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Chương</title>
    
    <!-- Thêm Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }

        h1 {
            color: #343a40;
            margin-bottom: 20px;
        }

        .form-container {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 20px auto;
            max-width: 600px; /* Chiều rộng tối đa */
        }

        .form-group label {
            font-weight: bold;
        }

        .btn-update {
            background-color: #007bff;
            color: white;
        }

        .btn-update:hover {
            background-color: #0056b3;
        }

        .back-button {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Chỉnh sửa Chương</h1>
            <form action="editchapter.php?chapter_id=<?php echo $chapter_id; ?>" method="POST">
                <div class="form-group">
                    <label for="chapter_title">Tiêu đề chương:</label>
                    <input type="text" name="chapter_title" id="chapter_title" class="form-control" value="<?php echo htmlspecialchars($chapter['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="course_id">Chọn Khóa Học:</label>
                    <select name="course_id" id="course_id" class="form-control" required>
                        <?php
                        // Lấy danh sách khóa học từ cơ sở dữ liệu
                        try {
                            $stmt = $pdo->prepare("SELECT id, name FROM courses");
                            $stmt->execute();
                            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($courses as $course) {
                                $selected = ($course['id'] == $chapter['course_id']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($course['id']) . "' $selected>" . htmlspecialchars($course['name']) . "</option>";
                            }
                        } catch (PDOException $e) {
                            die("Lỗi: " . $e->getMessage());
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-update">Cập nhật Chương</button>
                <button type="button" class="btn btn-secondary back-button" onclick="window.history.back()">Huỷ</button>
            </form>
        </div>
    </div>
</body>
</html>