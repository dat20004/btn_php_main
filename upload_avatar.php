<?php
session_start();
include 'connect.php';  // Kết nối tới cơ sở dữ liệu

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['userID'])) {
    header('Location: login.php'); // Chuyển hướng đến trang đăng nhập nếu chưa đăng nhập
    exit;
}

// Lấy ID người dùng từ session
$user_id = $_SESSION['userID']; 

// Kiểm tra xem có tệp nào được tải lên không
if (isset($_FILES['avatar'])) {
    $avatar = $_FILES['avatar'];
    
    // Kiểm tra lỗi tải lên
    if ($avatar['error'] !== UPLOAD_ERR_OK) {
        echo "Có lỗi khi tải ảnh lên.";
        exit;
    }

    // Kiểm tra loại tệp
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($avatar['type'], $allowed_types)) {
        echo "Chỉ cho phép tải lên tệp JPEG, PNG hoặc GIF.";
        exit;
    }

    // Đường dẫn để lưu ảnh
    $upload_dir = './admin/uploads/';  // Thư mục chứa ảnh
    $filename = uniqid() . '-' . basename($avatar['name']); // Tạo tên tệp duy nhất
    $target_file = $upload_dir . $filename;

    // Di chuyển tệp tải lên vào thư mục mong muốn
    if (move_uploaded_file($avatar['tmp_name'], $target_file)) {
        // Cập nhật đường dẫn ảnh vào cơ sở dữ liệu
        $sql = "UPDATE users SET avatar = :avatar WHERE id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':avatar', $target_file);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
           
            header('Location: myProfile.php'); // Chuyển hướng về trang hồ sơ cá nhân
            exit;
        } else {
            echo "Có lỗi trong việc cập nhật ảnh đại diện.";
        }
    } else {
        echo "Không thể di chuyển tệp tải lên.";
    }
} else {
    echo "Không có tệp nào được tải lên.";
}
?>
