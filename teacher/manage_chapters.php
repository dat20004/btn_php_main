<?php
include 'header.php';
// Kết nối với cơ sở dữ liệu
include '../connect.php';

// Kiểm tra xem course_id có được truyền vào không
if (isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];

    // Lấy danh sách chương và bài học thuộc về khóa học cụ thể
    $sql = "SELECT chapters.id AS chapter_id, chapters.title AS chapter_title, 
            lessons.id AS lesson_id, lessons.title AS lesson_title
            FROM chapters
            LEFT JOIN lessons ON chapters.id = lessons.chapter_id
            WHERE chapters.course_id = ? -- Lọc theo khóa học
            ORDER BY chapters.id, lessons.id"; // Sắp xếp theo chương và bài học

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$course_id]); // Truyền course_id vào truy vấn
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC); // Lấy tất cả kết quả
    } catch (PDOException $e) {
        die("Câu truy vấn thất bại: " . $e->getMessage());
    }

    $last_chapter_id = null;
} else {
    die("Khóa học không được xác định.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Chương và Bài Học</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> <!-- Thêm Bootstrap -->

    <style>
        body {
            background-color: #f8f9fa;
        }

        h1 {
            background: #CBDDF0;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
        }

        .chapter-container {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 15px;
        }

        .chapter-header {
            cursor: pointer;
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.3s;
        }

        .chapter-header:hover {
            background-color: #e9ecef;
        }

        .button-group {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .btn {
            margin-left: 5px;
        }

        .btn-warning {
            background-color: #9CE6FF; /* Màu nút chỉnh sửa */
            border-color: #9CE6FF; /* Màu viền nút chỉnh sửa */
        }

        .btn-warning:hover {
            background-color: #B2E1FF; /* Màu sáng hơn khi hover */
            border-color: #B2E1FF; /* Màu viền sáng hơn khi hover */
        }

        .btn-danger {
            background-color: #2E8B57; /* Màu nút xóa */
            border-color: #2E8B57; /* Màu viền nút xóa */
        }

        .btn-danger:hover {
            background-color: #3BBE6C; /* Màu sáng hơn khi hover */
            border-color: #3BBE6C; /* Màu viền sáng hơn khi hover */
        }
        
        .lesson-list {
            padding-left: 20px; /* Khoảng cách cho danh sách bài học */
        }

        .lesson-item {
            margin-bottom: 10px;
        }

        .no-data {
            text-align: center;
            color: #6c757d; /* Màu xám nhạt */
        }
    </style>
    
    <script>
        // JavaScript để hiển thị hoặc ẩn danh sách bài học khi nhấn vào chương
        function toggleLessons(chapterId) {
            var lessonList = document.getElementById('lessons_' + chapterId);
            lessonList.style.display = (lessonList.style.display === "none" || lessonList.style.display === "") ? "block" : "none";
        }
    </script>
</head>
<body>

<h1>Danh Sách Chương và Bài Học</h1>

<div class="container">
    <div class="button-container text-center mb-4">
        <a href="addchapter.php?course_id=<?php echo htmlspecialchars($course_id); ?>"><button class="btn btn-primary">Thêm Chương</button></a>
    </div>
    <div class="mb-3">
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay Về Trang Chủ
        </a>
    </div>

    <?php
    if ($result) {
        foreach ($result as $row) {
            // Nếu là chương mới, in ra thông tin chương
            if ($last_chapter_id != $row['chapter_id']) {
                if ($last_chapter_id !== null) {
                    echo "</ul>"; // Kết thúc danh sách bài học của chương trước
                }

                // Nhóm tiêu đề chương và các nút vào một div
                echo "<div class='chapter-header' onclick='toggleLessons(" . $row['chapter_id'] . ")'>";
                echo "<h3>" . htmlspecialchars($row['chapter_title']) . "</h3>";
                
                // Thêm liên kết chỉnh sửa và xóa vào tiêu đề chương
                echo "<div class='chapter-actions'>";
                echo "<a href='editchapter.php?chapter_id=" . $row['chapter_id'] . "' class='btn btn-warning btn-sm'>Chỉnh sửa</a>";
                echo "<a href='deletechapter.php?chapter_id=" . $row['chapter_id'] . "&delete=true' class='btn btn-danger btn-sm' onclick='return confirm(\"Bạn có chắc chắn muốn xóa chương này không?\");'>Xóa</a>";
                echo "</div>";
                
                echo "</div>"; // Kết thúc nhóm tiêu đề và nút
                
                // Mở danh sách bài học cho chương hiện tại
                echo "<ul id='lessons_" . $row['chapter_id'] . "' class='lesson-list' style='display:none;'>"; 
                echo "<li class='lesson-item'><a href='addlesson.php?chapter_id=" . $row['chapter_id'] . "' class='btn btn-success'>Thêm bài học cho chương này</a></li>"; // Nút Thêm bài học cho chương
                
                // Cập nhật ID chương cuối cùng đã in
                $last_chapter_id = $row['chapter_id'];
            }

            // In ra bài học trong chương hiện tại
            if (!empty($row['lesson_title'])) { // Chỉ in ra bài học nếu tồn tại
                echo "<li class='lesson-item'>";
                echo "<a href='viewlesson.php?lesson_id=" . $row['lesson_id'] . "' class='lesson-link'>" . htmlspecialchars($row['lesson_title']) . "</a>";
                
                // Nhóm các nút vào một div
                echo "<div class='button-group'>";
                
                // Nút Chỉnh sửa
                echo "<a href='editlesson.php?lesson_id=" . $row['lesson_id'] . "' class='btn btn-warning btn-sm'>Chỉnh sửa</a>";
                
                // Nút Xóa
                echo "<a href='deletelesson.php?lesson_id=" . $row['lesson_id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Bạn có chắc chắn muốn xóa bài học này không?\");'>Xóa</a>";
                
                echo "</div>"; // Kết thúc nhóm nút
                echo "</li>";
            }
        }
        echo "</ul>"; // Kết thúc danh sách bài học của chương cuối

    } else {
        echo "<p class='no-data'>Không có chương và bài học nào.</p>";
    }
    ?>

</div>

</body>
</html>
