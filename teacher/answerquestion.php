<?php 
session_start();
include 'header.php'; // Nếu có header để bao gồm
require_once('../connect.php');

// Kiểm tra nếu người dùng đã đăng nhập
if (isset($_SESSION['userID'])) {
    $replierId = $_SESSION['userID']; // Lấy ID người dùng từ session
} 

// Biến chứa câu truy vấn để lấy danh sách chương và bài học
$sql_chapters_lessons = "
    SELECT chapters.id AS chapter_id, chapters.title AS chapter_title, 
           lessons.id AS lesson_id, lessons.title AS lesson_title
    FROM chapters
    LEFT JOIN lessons ON chapters.id = lessons.chapter_id
    ORDER BY chapters.id, lessons.id
";
$questions = [];

// Kiểm tra xem có yêu cầu tìm kiếm không
$searchQueryRequest = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['searchQueryRequest'])) {
    $searchQueryRequest = $_POST['searchQueryRequest'];

    // Câu truy vấn với điều kiện tìm kiếm
    $stmt_questions = $pdo->prepare("
    SELECT cq.id AS question_id, 
           u.full_name AS student_name, 
           u.email AS student_email, 
           cq.question, 
           cq.state, 
           cq.create_at AS question_create_at,
           c.name AS course_title,
           a.answer AS answer_content,
           r.full_name AS replier_name,
           (SELECT MAX(ans.create_at) FROM answers ans WHERE ans.question_id = cq.id) AS last_reply_date
    FROM course_questions cq
    INNER JOIN users u ON cq.student_id = u.id
    INNER JOIN courses c ON cq.course_id = c.id
    LEFT JOIN answers a ON a.question_id = cq.id
    LEFT JOIN users r ON a.replier_id = r.id
    WHERE u.full_name LIKE :searchQuery -- Thêm điều kiện tìm kiếm
    ORDER BY cq.create_at DESC
    ");
    $stmt_questions->execute(['searchQuery' => '%' . $searchQueryRequest . '%']);
    $questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC); // Lấy tất cả câu hỏi
} else {
    // Nếu không tìm kiếm, lấy tất cả câu hỏi
    $stmt_questions = $pdo->prepare("
    SELECT cq.id AS question_id, 
           u.full_name AS student_name, 
           u.email AS student_email, 
           cq.question, 
           cq.state, 
           cq.create_at AS question_create_at,
           c.name AS course_title,
           a.answer AS answer_content,
           r.full_name AS replier_name,
           (SELECT MAX(ans.create_at) FROM answers ans WHERE ans.question_id = cq.id) AS last_reply_date
    FROM course_questions cq
    INNER JOIN users u ON cq.student_id = u.id
    INNER JOIN courses c ON cq.course_id = c.id
    LEFT JOIN answers a ON a.question_id = cq.id
    LEFT JOIN users r ON a.replier_id = r.id
    ORDER BY cq.create_at DESC
    ");
    $stmt_questions->execute();
    $questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC); // Lấy tất cả câu hỏi
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Câu Hỏi</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .mt-4 {
            background-color: #CBDDF0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="mt-4">Danh Sách Câu Hỏi</h2>
        <div id="students" class="mb-3 ">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay Về Trang Chủ
            </a>
            <form method="POST">
                <div class="student-search">
                    <input type="text" name="searchQueryRequest" placeholder="Tìm kiếm sinh viên">
                    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </form>
        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>STT</th> <!-- Cột số thứ tự -->
                    <th>Tên Học Sinh</th>
                    <th>Email</th>
                    <th>Câu Hỏi</th>
                    <th>Tên Khóa Học</th>
                    <th>Câu Trả Lời</th> <!-- Hiển thị câu trả lời -->
                    <th>Trạng Thái</th>
                    <th>Cài Đặt</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Biến đếm số thứ tự
                $index = 1; 
                foreach ($questions as $question): ?>
                <tr>
                    <td><?= $index++ ?></td> <!-- Hiển thị số thứ tự tự động tăng -->
                    <td><?= htmlspecialchars($question['student_name']) ?></td>
                    <td><?= htmlspecialchars($question['student_email']) ?></td>
                    <td><?= htmlspecialchars($question['question']) ?></td>
                    <td><?= htmlspecialchars($question['course_title']) ?></td>

                    <!-- Hiển thị nội dung câu trả lời, nếu chưa có sẽ hiển thị thông báo -->
                    <td><?= isset($question['answer_content']) ? htmlspecialchars($question['answer_content']) : 'Chưa có câu trả lời' ?></td>

                    <td><?= htmlspecialchars($question['state']) ?></td>

                    <td class="action-buttons">
                        <!-- Nút trả lời -->
                        <form method="POST" action="reply_question.php" style="display:inline;">
                            <input type="hidden" name="question_id" value="<?= $question['question_id'] ?>">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-reply"></i> Trả Lời
                            </button>
                        </form>

                        <!-- Nút xóa -->
                        <form method="POST" action="delete_question.php" style="display:inline;">
                            <input type="hidden" name="question_id" value="<?= $question['question_id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Bạn có chắc muốn xóa câu hỏi này?')">
                                <i class="fas fa-trash-alt"></i> Xóa
                            </button>
                        </form>

                        <!-- Cập nhật câu trả lời -->
                        <form method="GET" action="update_answer.php" style="display:inline;">
                            <input type="hidden" name="question_id" value="<?= $question['question_id'] ?>">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Cập Nhật
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Thư viện Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
