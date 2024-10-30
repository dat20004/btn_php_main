<?php
ob_start();
session_start();
include '../connect.php';

try {
    if (isset($_SESSION['userID'])) {
        $replierId = $_SESSION['userID'];

        if (empty($replierId)) {
            echo "<script>alert('Bạn cần đăng nhập để trả lời câu hỏi.'); window.location.href='login.php';</script>";
            exit;
        }

        if (isset($_GET['question_id'])) {
            $question_id = $_GET['question_id'];

            // Lấy tất cả câu trả lời cho câu hỏi
            $stmt = $pdo->prepare("
                SELECT a.answer, a.replier_id 
                FROM answers a 
                WHERE a.question_id = :question_id AND a.replier_id = :replier_id
            ");
            $stmt->execute([':question_id' => $question_id, ':replier_id' => $replierId]);
            $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Kiểm tra nếu không có câu trả lời
            if (!$answers) {
                echo "<script>alert('Không tìm thấy câu trả lời cho câu hỏi này.'); window.location.href='answerquestion.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('ID câu hỏi không hợp lệ.'); window.location.href='answerquestion.php';</script>";
            exit();
        }
    } elseif (isset($_POST['question_id']) && isset($_POST['updated_answer']) && isset($_POST['replier_id'])) {
        // Khi form được gửi để cập nhật
        $question_id = $_POST['question_id'];
        $updated_answer = $_POST['updated_answer'];
        $replier_id = $_POST['replier_id'];

        // Kiểm tra xem câu trả lời có tồn tại không
        $stmt = $pdo->prepare("
            SELECT * FROM answers 
            WHERE question_id = :question_id AND replier_id = :replier_id
        ");
        $stmt->execute([':question_id' => $question_id, ':replier_id' => $replier_id]);
        $existingAnswer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingAnswer) {
            // Cập nhật câu trả lời trong cơ sở dữ liệu
            $stmt = $pdo->prepare("
                UPDATE answers 
                SET answer = :answer 
                WHERE question_id = :question_id AND replier_id = :replier_id
            ");
            $stmt->execute([
                ':answer' => $updated_answer,
                ':question_id' => $question_id,
                ':replier_id' => $replier_id
            ]);

            if ($stmt->rowCount() > 0) {
                echo "<script>alert('Câu trả lời đã được cập nhật thành công.'); window.location.href='answerquestion.php';</script>";
                exit();
            } else {
                echo "<script>alert('Không có thay đổi nào được thực hiện.');</script>";
            }
        } else {
            echo "<script>alert('Không tìm thấy câu trả lời cần cập nhật.'); window.location.href='answerquestion.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Dữ liệu không hợp lệ.'); window.location.href='answerquestion.php';</script>";
        exit();
    }
} catch (PDOException $e) {
    echo "<script>alert('Kết nối không thành công: " . $e->getMessage() . "');</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa Câu Trả Lời</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h1 class="mb-4">Chỉnh Sửa Câu Trả Lời</h1>

    <?php if (isset($answers) && !empty($answers)): ?>
        <form method="POST" action="update_answer.php">
            <input type="hidden" name="question_id" value="<?php echo htmlspecialchars($question_id); ?>">

            <?php foreach ($answers as $answer): ?>
                <div class="mb-3">
                    <label class="form-label">Câu Trả Lời (Replier ID: <?php echo htmlspecialchars($answer['replier_id']); ?>)</label>
                    <textarea class="form-control" name="updated_answer" rows="5" required><?php echo htmlspecialchars($answer['answer']); ?></textarea>
                    <input type="hidden" name="replier_id" value="<?php echo htmlspecialchars($answer['replier_id']); ?>">
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary">Lưu</button>
            <a href="answerquestion.php" class="btn btn-secondary">Hủy</a>
        </form>
    <?php else: ?>
        <div class="alert alert-warning">Không tìm thấy câu trả lời nào để chỉnh sửa.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
