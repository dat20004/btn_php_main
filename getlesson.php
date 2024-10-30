<?php
session_start();
include 'connect.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question = $_POST['question'];
    $teacher_id = $_POST['teacher_id'];
    $student_id = $_SESSION['user_id']; // Lấy ID người dùng từ session

    // Thực hiện truy vấn để lưu câu hỏi vào cơ sở dữ liệu
    $sql = "INSERT INTO questions (student_id, teacher_id, question, created_at) VALUES (:student_id, :teacher_id, :question, NOW())";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
        $stmt->bindParam(':question', $question, PDO::PARAM_STR);
        $stmt->execute();
        
        echo "Câu hỏi của bạn đã được gửi thành công.";
    } catch (PDOException $e) {
        die("Lỗi: " . $e->getMessage());
    }
} else {
    echo "Yêu cầu không hợp lệ.";
}
?>
