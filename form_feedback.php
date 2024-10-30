<?php
session_start();
include 'connect.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['userID'])) {
    header('Location: login.php'); // Chuyển hướng đến trang đăng nhập nếu chưa đăng nhập
    exit;
}

// Lấy ID của sinh viên từ session
$student_id = $_SESSION['userID'];

// Lấy course_id từ URL
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null; // Lấy course_id từ URL
// Kiểm tra giá trị của course_id


// Kiểm tra ID khóa học hợp lệ
if ($course_id <= 0) { // Kiểm tra xem course_id có hợp lệ không
    echo "Dữ liệu không hợp lệ. Vui lòng chọn khóa học.";
    exit;
}

// Kiểm tra xem form đã được submit chưa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $feedback = trim($_POST['feedback']);
    $rating = intval($_POST['rating']);

    // Kiểm tra xem đã có đánh giá trước đó chưa (để tránh trùng lặp)
    $checkSql = "SELECT * FROM course_feedbacks WHERE student_id = :student_id AND course_id = :course_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $checkStmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        echo "Bạn đã đánh giá khóa học này rồi!";
    } else {
        // Thêm đánh giá vào CSDL
        $sql = "INSERT INTO course_feedbacks (student_id, course_id, feedback, rating, feedback_date)
                VALUES (:student_id, :course_id, :feedback, :rating, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->bindParam(':feedback', $feedback, PDO::PARAM_STR);
        $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Chuyển hướng đến trang chi tiết khóa học
            header('Location: chitietbaihoc.php?id=' . $course_id);
            exit;
        } else {
            echo "Có lỗi xảy ra, vui lòng thử lại.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá khóa học</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        h1 {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .star-rating {
            direction: rtl;
            display: inline-flex;
        }
        .star-rating input[type="radio"] {
            display: none;
        }
        .star-rating label {
            font-size: 2em;
            color: #ccc;
            cursor: pointer;
        }
        .star-rating input[type="radio"]:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffc107;
        }
    </style>
</head>
<body>

<!-- Form đánh giá -->
<h1>Đánh giá khóa học</h1>
<form method="POST">
    <div class="form-group">
        <label for="feedback">Nội dung đánh giá:</label>
        <textarea id="feedback" name="feedback" class="form-control" rows="4" required></textarea>
    </div>

    <div class="form-group">
        <label for="rating">Đánh giá:</label>
        <div class="star-rating">
            <input type="radio" id="star5" name="rating" value="5" required><label for="star5">&#9733;</label>
            <input type="radio" id="star4" name="rating" value="4"><label for="star4">&#9733;</label>
            <input type="radio" id="star3" name="rating" value="3"><label for="star3">&#9733;</label>
            <input type="radio" id="star2" name="rating" value="2"><label for="star2">&#9733;</label>
            <input type="radio" id="star1" name="rating" value="1"><label for="star1">&#9733;</label>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
</form>

</body>
</html>
