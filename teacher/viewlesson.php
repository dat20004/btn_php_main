<?php
session_start(); // Đảm bảo session đã được khởi động
include '../connect.php'; // Kết nối cơ sở dữ liệu đã có sẵn

// Lấy ID bài học từ query string
if (isset($_GET['lesson_id'])) {
    $lesson_id = $_GET['lesson_id'];

    // Lấy nội dung bài học từ cơ sở dữ liệu
    $sql = "SELECT title, content, link FROM lessons WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([$lesson_id]);
        $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Kiểm tra nếu bài học không tồn tại
        if (!$lesson) {
            die("Không tìm thấy bài học.");
        }

    } catch (PDOException $e) {
        die("Câu truy vấn thất bại: " . $e->getMessage());
    }
} else {
    die("Bài học không được tìm thấy.");
}

// Đóng kết nối không cần thiết vì PDO tự động giải phóng khi script kết thúc
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xem Bài Học</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        p {
            font-size: 1.2rem;
            line-height: 1.5;
        }
        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($lesson['title']); ?></h2>
        <h3> Nội dung bài học</h3>
        <p><?php echo nl2br(htmlspecialchars($lesson['content'])); ?></p>
        <h4>Tài liệu</h4>
        <?php if (!empty($lesson['link'])): ?>
            <p><a href="<?php echo htmlspecialchars($lesson['link']); ?>" target="_blank">Xem tài liệu</a></p>
        <?php endif; ?>
        <a class="back-button" href="index.php">Quay lại</a>
    </div>
</body>
</html>
