<?php
include 'connect.php'; // Kết nối đến cơ sở dữ liệu

if (isset($_GET['lesson_id'])) {
    $lesson_id = (int)$_GET['lesson_id']; // Chuyển đổi sang số nguyên để bảo mật

    // Truy vấn để lấy nội dung bài học
    $sql = "SELECT content, link FROM lessons WHERE id = :lesson_id"; 
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
    
    try {
        $stmt->execute();
        $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

        // Bắt đầu phần hiển thị nội dung
        ?>
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($lesson ? $lesson['content'] : 'Bài học'); ?></title>
            <link rel="stylesheet" href="styles.css"> <!-- Liên kết đến tệp CSS -->
            <style>
                /* body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    padding: 20px;
                    background-color: #f4f4f4;
                    color: #333;
                } */
                .lesson-container {
                    background: #fff;
                    border-radius: 5px;
                    padding: 20px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .lesson-title {
                    font-size: 24px;
                    margin-bottom: 20px;
                }
                .lesson-content {
                    font-size: 18px;
                    line-height: 1.6;
                }
                .lesson-link {
                    margin-top: 20px;
                    display: inline-block;
                    background-color: #28a745;
                    color: white;
                    padding: 10px 15px;
                    text-decoration: none;
                    border-radius: 5px;
                    transition: background-color 0.3s;
                }
                .lesson-link:hover {
                    background-color: #218838;
                }
            </style>
        </head>
        <body>
            <div class="lesson-container">
                <?php if ($lesson): ?>
                    <h1 class="lesson-title">Nội dung bài học</h1>
                    <div class="lesson-content">
                        <?php echo nl2br(htmlspecialchars($lesson['content'])); ?> <!-- Chuyển đổi dòng mới thành <br> -->
                    </div>
                    <?php if (!empty($lesson['link'])): ?>
                        <a href="<?php echo htmlspecialchars($lesson['link']); ?>" class="lesson-link" target="_blank">Xem thêm</a>
                    <?php endif; ?>
                <?php else: ?>
                    <p>Không tìm thấy bài học.</p>
                <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
    } catch (PDOException $e) {
        die("Lỗi truy vấn: " . $e->getMessage());
    }
} else {
    echo 'ID bài học không hợp lệ.';
}
?>
