<?php
session_start(); // Đặt ở dòng đầu tiên của file PHP

include '../connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra xem có ID người dùng trong URL hay không
if (isset($_GET['id'])) {
    $userID = $_GET['id'];
    
    // Kiểm tra xem người dùng có tồn tại trong cơ sở dữ liệu hay không
    $checkUserQuery = "SELECT * FROM users WHERE id = :id";
    $stmt = $pdo->prepare($checkUserQuery);
    $stmt->bindParam(':id', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Thực hiện xóa người dùng
        $deleteQuery = "DELETE FROM users WHERE id = :id";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $deleteStmt->bindParam(':id', $userID, PDO::PARAM_INT);
        
        if ($deleteStmt->execute()) {
            // Thành công, chuyển hướng về trang quản lý với thông báo
            $_SESSION['success'] = "Xóa người dùng thành công!";
            header('Location: account.php');
            exit();
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi xóa người dùng.";
        }
    } else {
        $_SESSION['error'] = "Không tìm thấy người dùng.";
    }
} else {
    $_SESSION['error'] = "Không có ID người dùng.";
}

// Nếu có lỗi, chuyển hướng về trang quản lý
header('Location: account.php');
exit();


?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php elseif (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

