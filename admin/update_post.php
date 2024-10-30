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

        // Lấy thông tin bài đăng từ cơ sở dữ liệu
        $stmt = $pdo->prepare("SELECT title, content FROM posts WHERE id = :id");
        $stmt->bindParam(':id', $post_id);
        $stmt->execute();
        $post = $stmt->fetch();

        // Kiểm tra nếu bài đăng tồn tại
        if (!$post) {
            echo "Bài đăng không tồn tại.";
            exit;
        }
    } else {
        echo "ID bài đăng không hợp lệ.";
        exit;
    }

    // Xử lý cập nhật bài đăng nếu dữ liệu đã được gửi qua POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $title = $_POST['title'];
        $content = $_POST['content'];

        // Câu truy vấn cập nhật bài đăng
        $sql = "UPDATE posts SET title = :title, content = :content WHERE id = :id";

        // Chuẩn bị câu truy vấn với PDO
        $stmt = $pdo->prepare($sql);

        // Gán giá trị cho các tham số
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':id', $post_id);

        // Thực thi câu truy vấn
        if ($stmt->execute()) {
            $_SESSION['message'] = "Bài đăng đã được cập nhật thành công!";
        } else {
            $_SESSION['error'] = "Lỗi khi cập nhật bài đăng.";
        }

        // Chuyển hướng về trang index
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    echo "Lỗi kết nối: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Bài Đăng</title>
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
        input, textarea {
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
    <h2>Sửa Bài Đăng</h2>
    <form action="" method="POST">
        <input type="hidden" name="id" value="<?php echo $post_id; ?>">
        
        <label for="title">Tiêu đề:</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>

        <label for="content">Nội dung:</label>
        <textarea id="content" name="content" rows="6" required><?php echo htmlspecialchars($post['content']); ?></textarea>

        <button type="submit">Cập Nhật Bài Đăng</button>
        <button type="button" class="btn btn-secondary back-button" onclick="window.history.back()">Huỷ</button>

    </form>
</div>

</body>
</html>
