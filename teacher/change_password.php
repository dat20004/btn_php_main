<?php
session_start(); // Bắt đầu phiên làm việc
require 'connect.php'; // Tệp chứa mã kết nối cơ sở dữ liệu

$message = ""; // Biến lưu thông báo

// Hàm kiểm tra định dạng mật khẩu
function isValidPassword($password) {
    // Kiểm tra độ dài mật khẩu
    if (strlen($password) < 8) {
        return false;
    }
    // Kiểm tra có ít nhất một chữ hoa, một chữ thường và một chữ số
    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        return false;
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $checkPassword = $_POST['checkPassword'];

    // Kiểm tra xem mật khẩu mới và xác nhận mật khẩu có khớp không
    if ($newPassword !== $checkPassword) {
        $message = "Mật khẩu mới không khớp.";
    } elseif (!isValidPassword($newPassword)) {
        $message = "Mật khẩu mới phải có ít nhất 8 ký tự, chứa cả chữ hoa, chữ thường và số.";
    } else {
        // Lấy ID người dùng từ session
        $userId = $_SESSION['user_id'];

        // Truy vấn mật khẩu hiện tại từ cơ sở dữ liệu
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Kiểm tra mật khẩu hiện tại
        if (!password_verify($currentPassword, $user['password'])) {
            $message = "Mật khẩu hiện tại không chính xác.";
        } else {
            // Mã hóa mật khẩu mới
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Cập nhật mật khẩu mới vào cơ sở dữ liệu
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedPassword, $userId);
            if ($stmt->execute()) {
                $message = "Mật khẩu đã được cập nhật thành công.";
            } else {
                $message = "Có lỗi xảy ra. Vui lòng thử lại.";
            }
        }
    }
}
?>

<!-- Hiển thị thông báo -->
<?php if (!empty($message)) : ?>
    <div class="alert alert-info"><?php echo $message; ?></div>
<?php endif; ?>
