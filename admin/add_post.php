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
    // Kiểm tra nếu dữ liệu đã được gửi qua POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $admin_id = $_SESSION['userID']; // Lấy admin_id từ session

        // Câu truy vấn thêm bài đăng
        $sql = "INSERT INTO posts (admin_id, title, content) VALUES (:admin_id, :title, :content)";

        // Chuẩn bị câu truy vấn với PDO
        $stmt = $pdo->prepare($sql);

        // Gán giá trị cho các tham số
        $stmt->bindParam(':admin_id', $admin_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);

        // Thực thi câu truy vấn
        if ($stmt->execute()) {
            // Thêm bài đăng thành công, lưu thông báo vào session
            $_SESSION['message'] = "Bài đăng đã được thêm thành công!";
            header('Location: index.php'); // Chuyển hướng đến trang index
            exit;
        } else {
            // Thêm bài đăng thất bại, lưu thông báo vào session
            $_SESSION['error'] = "Lỗi khi thêm bài đăng.";
            header('Location: index.php'); // Chuyển hướng đến trang index
            exit;
        }
    }
} catch (PDOException $e) {
    // Lỗi kết nối, lưu thông báo vào session
    $_SESSION['error'] = "Lỗi kết nối: " . $e->getMessage();
    header('Location: index.php'); // Chuyển hướng đến trang index
    exit;
}

// Không cần đóng kết nối vì PDO tự động đóng khi script kết thúc
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Bài Đăng</title>
    <style>
        .form-container {
            width: 50%;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-bottom: 8px;
        }
        input, textarea, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <h2>Thêm Bài Đăng Mới</h2>
        <form id="addPostForm" action="add_post.php" method="POST">
            <label for="title">Tiêu đề:</label>
            <input type="text" id="title" name="title" placeholder="Nhập tiêu đề bài đăng" required>

            <label for="content">Nội dung:</label>
            <textarea id="content" name="content" rows="6" placeholder="Nhập nội dung bài đăng" required></textarea>

            <button type="submit">Thêm Bài Đăng</button>
            <button type="button" class="btn btn-secondary back-button" onclick="window.history.back()">Huỷ</button>

        </form>
    </div>

</body>
</html>
