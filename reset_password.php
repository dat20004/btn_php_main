<?php
session_start();
include 'connect.php'; // Kết nối cơ sở dữ liệu

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_SESSION['email'] ?? null;  // Lấy email từ session
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra độ mạnh của mật khẩu
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $new_password)) {
        $_SESSION['error'] = "Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt.";
        header("Location: reset_password.php");
        exit();
    }

    // Kiểm tra khớp mật khẩu
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Mật khẩu xác nhận không khớp.";
        header("Location: reset_password.php");
        exit();
    }

    // Mã hóa mật khẩu
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    // Cập nhật mật khẩu vào cơ sở dữ liệu
    try {
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $_SESSION['success'] = "Đặt lại mật khẩu thành công!";
        header("Location: login.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Lỗi: " . $e->getMessage();
        header("Location: reset_password.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Định dạng chung */
body {
    font-family: Arial, sans-serif;
    background-color: #f5f5f5;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

/* Khung chứa nội dung */
.container {
    text-align: center;
    padding: 20px;
    width: 100%;
    max-width: 400px;
    background-color: #ffffff;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

/* Tiêu đề */
h1 {
    color: #333;
    font-size: 24px;
    margin-bottom: 20px;
}

/* Thông báo lỗi và thành công */
.alert {
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
}

/* Form */
form {
    display: flex;
    flex-direction: column;
}

/* Label */
label {
    font-size: 16px;
    color: #333;
    margin-bottom: 5px;
}

/* Input */
input[type="password"] {
    padding: 10px;
    font-size: 16px;
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 15px;
}

/* Nút gửi */
.btn-submit {
    background-color: #007bff;
    color: #ffffff;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    width: 100%;
}

.btn-submit:hover {
    background-color: #0056b3;
}
    
    </style>
</head>

<body>
    <div class="container">
        <!-- Thông báo lỗi -->
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>

        <!-- Thông báo thành công -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>

        <!-- Form đặt lại mật khẩu -->
        <form action="" method="POST">
            <h1>Đặt lại mật khẩu</h1>
            <div class="form-group">
                <label for="new_password">Mật khẩu mới:</label>
                <input type="password" name="new_password" id="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit" class="btn-submit">Đặt lại mật khẩu</button>
        </form>
    </div>
</body>

</html>
