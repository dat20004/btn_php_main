<?php
// // delete_question.php

// // Kết nối đến cơ sở dữ liệu với PDO
// $host = 'localhost';
// $db = 'btl'; // Thay đổi với tên cơ sở dữ liệu của bạn
// $user = 'root'; // Thay đổi với tên người dùng của bạn
// $pass = ''; // Thay đổi với mật khẩu của bạn

// try {
//     $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//     // Xử lý xóa câu hỏi
//     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//         $questionId = $_POST['question_id'];
//         $deleteStmt = $pdo->prepare("DELETE FROM course_questions WHERE id = :id");
//         $deleteStmt->bindParam(':id', $questionId, PDO::PARAM_INT);
//         $deleteStmt->execute();

//         header("Location: view_questions.php"); // Quay lại trang hiển thị câu hỏi
//         exit();
//     }

// } catch (PDOException $e) {
//     echo "Kết nối không thành công: " . $e->getMessage();
// }
// ?>
<?php
session_start(); // Khởi động phiên
include '../connect';
// Giả định rằng người dùng đã đăng nhập thành công
// Thay thế mã này bằng mã xác thực của bạn
// $_SESSION['user_id'] = 1; // ID của người dùng đã đăng nhập

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    // Chuyển hướng đến trang đăng nhập nếu người dùng chưa đăng nhập
    header("Location: index.php?students");
    exit();
}

// // Kết nối đến cơ sở dữ liệu với PDO
// $host = 'localhost';
// $db = 'btl'; // Thay đổi với tên cơ sở dữ liệu của bạn
// $user = 'root'; // Thay đổi với tên người dùng của bạn
// $pass = ''; // Thay đổi với mật khẩu của bạn

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Xử lý xóa câu hỏi
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $questionId = $_POST['question_id'];

        // Kiểm tra nếu $questionId hợp lệ
        if (!empty($questionId) && is_numeric($questionId)) {
            $deleteStmt = $pdo->prepare("DELETE FROM course_questions WHERE id = :id");
            $deleteStmt->bindParam(':id', $questionId, PDO::PARAM_INT);
            $deleteStmt->execute();

            header("Location: answerquestion.php"); // Quay lại trang hiển thị câu hỏi
            exit();
        } else {
            echo "ID câu hỏi không hợp lệ.";
            exit();
        }
    }

} catch (PDOException $e) {
    echo "Kết nối không thành công: " . $e->getMessage();
}
?>
