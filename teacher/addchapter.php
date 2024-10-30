<?php

// Kết nối với cơ sở dữ liệu
include '../connect.php'; // Đảm bảo bạn đã có file kết nối DB

// Kiểm tra course_id từ GET hoặc POST
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;
$course_name = '';

// Kiểm tra và lấy tên khóa học từ DB nếu course_id hợp lệ
if ($course_id) {
    try {
        $stmt = $pdo->prepare("SELECT name FROM courses WHERE id = :course_id");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($course) {
            $course_name = $course['name'];
        } else {
            die("Không tìm thấy khóa học!");
        }
    } catch (PDOException $e) {
        die("Lỗi: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $chapter_title = isset($_POST['chapter_title']) ? $_POST['chapter_title'] : null;

    // Kiểm tra nếu các trường không rỗng
    if (!empty($chapter_title) && !empty($course_id)) {
        try {
            // Chuẩn bị câu lệnh SQL để thêm chương với khóa học
            $stmt = $pdo->prepare("INSERT INTO chapters (title, course_id) VALUES (:title, :course_id)");
            $stmt->bindParam(':title', $chapter_title);
            $stmt->bindParam(':course_id', $course_id);
            $stmt->execute();

            header("Location: manage_chapters.php?course_id=" . urlencode($course_id));

            exit();
        } catch (PDOException $e) {
            die("Lỗi: " . $e->getMessage());
        }
    } else {
        echo "Tiêu đề chương và khóa học không thể trống!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Chương</title>
</head>
<style>
    /* Global Styles */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
    
    height: 100vh;
}
.form-main{
    display: flex;
    justify-content: center;
    align-items: center;
}
h1 {
    text-align: center;
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
}

/* Form Container */
form {
    background-color: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    display: flex;
    flex-direction: column;
    
}

/* Label Styling */
label {
    font-size: 16px;
    margin-bottom: 8px;
    color: #555;
}

/* Input and Select Styling */
input[type="text"],
select {
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 4px;
    border: 1px solid #ccc;
    font-size: 16px;
    width: 100%;
    box-sizing: border-box;
    transition: border-color 0.3s ease;
}

/* Focus state for inputs */
input[type="text"]:focus,
select:focus {
    border-color: #4CAF50;
    outline: none;
}

/* Button Styling */
button {
    padding: 12px;
    font-size: 16px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #45a049;
}

/* Responsive Design */
@media (max-width: 600px) {
    form {
        width: 90%;
    }
}

</style>
<body>

<h1>Thêm Chương Mới</h1>
    <div class="form-main">
        <form action="addchapter.php?course_id=<?php echo htmlspecialchars($course_id); ?>" method="POST">
            <label for="chapter_title">Tiêu đề chương:</label>
            <input type="text" name="chapter_title" id="chapter_title" required>

            <!-- Hiển thị khóa học đã chọn -->
            <label>Khóa Học:</label>
            <input type="text" value="<?php echo htmlspecialchars($course_name); ?>" disabled>
            <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id); ?>">

            <button type="submit">Thêm Chương</button>
            <button type="button" class="btn btn-secondary back-button" onclick="window.history.back()">Huỷ</button>
        </form>
    </div>
    
</body>
</html>

