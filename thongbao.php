<?php
include 'header.php';
include 'connect.php';

try {
    // Truy vấn lấy tất cả các thông báo
    $sql = "SELECT posts.title, posts.content, posts.created_at, users.full_name
            FROM posts
            JOIN users ON posts.admin_id = users.id
            ORDER BY posts.created_at DESC";

    // Chuẩn bị và thực thi truy vấn
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Lấy tất cả thông báo
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Kiểm tra nếu không có thông báo nào
    if (empty($notifications)) {
        $error_message = "Không có thông báo nào.";
    }

} catch (PDOException $e) {
    $error_message = "Lỗi: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tất cả thông báo</title>
    <link rel="stylesheet" href="path_to_css/bootstrap.min.css"> <!-- Thay đường dẫn này -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Thêm Font Awesome -->
    <style>
        body {
            background-color: #f8f9fa; /* Màu nền sáng cho toàn trang */
            font-family: 'Arial', sans-serif; /* Phông chữ thân thiện */
        }

        .container {
            max-width: 800px; /* Chiều rộng tối đa của container */
            margin: 0 auto; /* Canh giữa trang */
            padding: 30px; /* Đệm xung quanh */
            background-color: #ffffff; /* Màu nền trắng cho container */
            border-radius: 8px; /* Bo tròn các góc */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Hiệu ứng bóng */
        }

        h1 {
            color: #007bff; /* Màu tiêu đề chính */
            margin-bottom: 30px; /* Khoảng cách dưới tiêu đề */
            text-align: center; /* Canh giữa tiêu đề */
            font-size: 2rem; /* Kích thước chữ cho tiêu đề */
        }

        .card {
            border: none; /* Không viền */
            transition: transform 0.2s; /* Hiệu ứng chuyển động khi hover */
        }

        .card:hover {
            transform: translateY(-5px); /* Nâng lên khi hover */
        }

        .card-header {
            background-color: #007bff; /* Màu nền cho header */
            color: white; /* Màu chữ trắng */
            font-weight: bold; /* Chữ đậm */
            padding: 15px; /* Đệm cho header */
        }

        .card-title {
            color: #343a40; /* Màu chữ cho tiêu đề */
            font-size: 1.5rem; /* Kích thước chữ cho tiêu đề */
            margin-bottom: 10px; /* Khoảng cách dưới tiêu đề */
        }

        .card-text {
            color: #495057; /* Màu chữ cho nội dung */
            font-size: 1rem; /* Kích thước chữ cho nội dung */
            line-height: 1.5; /* Khoảng cách dòng */
        }

        p {
            margin-bottom: 15px; /* Khoảng cách dưới mỗi đoạn văn */
        }

        /* Hiệu ứng cho nút xem thêm */
        .view-more {
            background-color: #007bff; /* Màu nền */
            color: white; /* Màu chữ */
            border: none; /* Không viền */
            padding: 10px 15px; /* Đệm */
            border-radius: 5px; /* Bo tròn */
            cursor: pointer; /* Con trỏ chuột khi hover */
            transition: background-color 0.3s; /* Hiệu ứng chuyển màu */
        }

        .view-more:hover {
            background-color: #0056b3; /* Màu nền khi hover */
        }

        /* Hiện thông báo nếu không có thông báo */
        .no-notifications {
            text-align: center; /* Canh giữa */
            color: #6c757d; /* Màu chữ */
            font-size: 1.2rem; /* Kích thước chữ */
            margin-top: 20px; /* Khoảng cách trên */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="my-4">Tất cả thông báo</h1>

        <!-- Hiển thị tất cả thông báo -->
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <strong>Người gửi: <?php echo htmlspecialchars($notification['full_name']); ?></strong><br>
                        <small>Thời gian gửi: <?php echo htmlspecialchars($notification['created_at']); ?></small>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($notification['title']); ?></h5>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($notification['content'])); ?></p>
                       
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-notifications"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
    </div>

    <script src="path_to_js/bootstrap.bundle.min.js"></script> <!-- Thay đường dẫn này -->
</body>
</html>
