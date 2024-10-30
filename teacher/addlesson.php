<?php
// Kết nối với cơ sở dữ liệu
require_once('../connect.php');

// Kiểm tra nếu có chapter_id được truyền qua URL
if (!isset($_GET['chapter_id'])) {
    die("ID chương không hợp lệ.");
}

$chapter_id = $_GET['chapter_id'];
$course_id = null;

// Lấy course_id từ chapter_id
try {
    $stmt = $pdo->prepare("SELECT course_id FROM chapters WHERE id = :chapter_id");
    $stmt->bindParam(':chapter_id', $chapter_id);
    $stmt->execute();
    $chapter = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($chapter) {
        $course_id = $chapter['course_id'];
    } else {
        die("Không tìm thấy chương!");
    }
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lesson_title = $_POST['lesson_title'];
    $lesson_content = $_POST['lesson_content'];
    $lesson_link = $_POST['lesson_link'];  // Thêm trường link
    $lesson_duration = $_POST['lesson_duration']; // Thêm trường duration

    // Kiểm tra nếu tiêu đề, nội dung, link và thời gian không trống
    if (!empty($lesson_title) && !empty($lesson_content) && !empty($lesson_link) && !empty($lesson_duration)) {
        try {
            // Câu truy vấn thêm bài học vào cơ sở dữ liệu, bao gồm cả link và thời gian học
            $stmt = $pdo->prepare("INSERT INTO lessons (chapter_id, title, content, link, duration) VALUES (:chapter_id, :title, :content, :link, :duration)");
            $stmt->bindParam(':chapter_id', $chapter_id);
            $stmt->bindParam(':title', $lesson_title);
            $stmt->bindParam(':content', $lesson_content);
            $stmt->bindParam(':link', $lesson_link); // Thêm link vào câu truy vấn
            $stmt->bindParam(':duration', $lesson_duration); // Thêm duration vào câu truy vấn
            $stmt->execute();
            
            // Chuyển hướng về trang manage_chapters.php với course_id cụ thể
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
    <title>Thêm Bài Học</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            height: 100vh;
        }
        .form-lesson{
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
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
            max-width: 500px;
            margin: auto;
            display: flex;
            flex-direction: column;
        }

        /* Form Label Styling */
        label {
            font-size: 16px;
            margin-bottom: 5px;
            color: #333;
        }

        /* Input and Textarea Styling */
        input[type="text"],
        input[type="url"],
        input[type="number"],
        textarea {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        /* Focus States for Inputs and Textareas */
        input[type="text"]:focus,
        input[type="url"]:focus,
        input[type="number"]:focus,
        textarea:focus {
            border-color: #4CAF50;
            outline: none;
        }

        /* Textarea Sizing */
        textarea {
            height: 150px;
            resize: vertical;
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

        /* Center button */
        button {
            align-self: center;
            width: 100%;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            form {
                padding: 15px;
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <h1>Thêm Bài Học vào Chương</h1>
    <div class= "form-lesson">
        <form action="addlesson.php?chapter_id=<?php echo $chapter_id; ?>" method="POST">
            <label for="lesson_title">Tiêu đề bài học:</label>
            <input type="text" name="lesson_title" id="lesson_title" required>
            <br><br>

            <label for="lesson_content">Nội dung bài học:</label>
            <textarea name="lesson_content" id="lesson_content" required></textarea>
            <br><br>

            <label for="lesson_link">Link bài học:</label>
            <input type="url" name="lesson_link" id="lesson_link" placeholder="https://example.com" required>
            <br><br>

            <label for="lesson_duration">Thời gian học (phút):</label>
            <input type="number" name="lesson_duration" id="lesson_duration" required min="1" placeholder="Nhập thời gian học bằng phút">
            <br><br>

            <button type="submit">Thêm Bài Học</button>
            <button type="button" class="btn btn-secondary back-button" onclick="window.history.back()">Huỷ</button>

        </form>
    </div>
    
</body>
</html>
