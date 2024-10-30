<?php
session_start();
include '../connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng đã đăng nhập
// Kiểm tra nếu người dùng đã đăng nhập
if (isset($_SESSION['userID'])) {
    $replierId = $_SESSION['userID']; // Lấy ID người dùng từ session
     // Lấy tên người dùng từ session
} else {
    echo "Bạn cần đăng nhập để trả lời câu hỏi.";
    exit;
}

// Kiểm tra xem $replierId có giá trị hợp lệ không
if (empty($replierId)) {
    echo "ID người trả lời không hợp lệ.";
    exit;
}

// Khởi tạo biến $questionId
$questionId = null;

// Lấy giá trị question_id từ POST hoặc GET
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_id'])) {
    $questionId = $_POST['question_id'];
} elseif (isset($_GET['question_id'])) {
    $questionId = $_GET['question_id'];
} else {
    echo "Không tìm thấy ID câu hỏi.";
    exit;
}

try {
    // Lấy câu hỏi từ cơ sở dữ liệu
    $stmt = $pdo->prepare("SELECT question FROM course_questions WHERE id = :id");
    $stmt->bindParam(':id', $questionId, PDO::PARAM_INT);
    $stmt->execute();
    $question = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra nếu không tìm thấy câu hỏi
    if (!$question) {
        echo "Không tìm thấy câu hỏi.";
        exit;
    }

    // Xử lý trả lời câu hỏi
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_text'])) {
        $newReply = trim($_POST['reply_text']);

        // Kiểm tra nếu câu trả lời trống
        if (empty($newReply)) {
            echo "Câu trả lời không được để trống.";
            exit;
        }

        // Thêm câu trả lời vào bảng answers
        $insertStmt = $pdo->prepare("INSERT INTO answers (replier_id, answer, question_id) VALUES (:replier_id, :answer, :question_id)");
        $insertStmt->bindParam(':replier_id', $replierId, PDO::PARAM_INT);
       
        $insertStmt->bindParam(':answer', $newReply, PDO::PARAM_STR);
        $insertStmt->bindParam(':question_id', $questionId, PDO::PARAM_INT);

        $pdo->beginTransaction(); // Bắt đầu transaction

        try {
            $insertStmt->execute();

            // Cập nhật trạng thái câu hỏi thành 'Closed'
            $updateStmt = $pdo->prepare("UPDATE course_questions SET state = 'Closed' WHERE id = :id");
            $updateStmt->bindParam(':id', $questionId, PDO::PARAM_INT);
            $updateStmt->execute();

            $pdo->commit(); // Xác nhận transaction
            header("Location: answerquestion.php"); // Chuyển hướng về trang câu hỏi
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack(); // Hoàn tác nếu có lỗi
            echo "Lỗi khi thực hiện câu lệnh: " . $e->getMessage();
            exit;
        }
    }
} catch (PDOException $e) {
    echo "Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trả Lời Câu Hỏi</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-4">Trả Lời Câu Hỏi</h2>
        <div class="mb-3">
            <strong>Câu hỏi:</strong>
            <p><?= htmlspecialchars($question['question']) ?></p>
        </div>
        
        <form method="POST" action="">
            <input type="hidden" name="question_id" value="<?= htmlspecialchars($questionId) ?>">
            <div class="form-group">
                <label for="reply_text">Câu trả lời:</label>
                <textarea id="reply_text" name="reply_text" class="form-control" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Gửi Câu Trả Lời</button>
        </form>
    </div>

    <!-- Thư viện Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
