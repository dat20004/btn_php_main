<?php
ob_start();
session_start();
include 'functions.php';  // File chứa hàm sendVerificationEmail()
include 'connect.php';    // Kết nối cơ sở dữ liệu

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';

    // Kiểm tra email có hợp lệ và không rỗng
    if (empty($email)) {
        $errors['email'] = 'Email không được để trống.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Địa chỉ email không hợp lệ.';
    }

    if (empty($errors)) {
        // Truy vấn người dùng theo email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Tạo mã xác thực ngẫu nhiên (6 chữ số)
            $verification_code = random_int(100000, 999999);

            // Lưu mã xác thực vào cơ sở dữ liệu
            $stmt = $pdo->prepare("UPDATE users SET verification_code = :code WHERE email = :email");
            $stmt->bindParam(':code', $verification_code);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // Gửi email xác thực
            sendVerificationEmail($email, $verification_code);

            // Thông báo thành công và chuyển hướng đến trang xác nhận mã
            $_SESSION['message'] = 'Mã xác thực đã được gửi đến email của bạn.';
            $_SESSION['email'] = $email;  // Lưu email để dùng lại trên trang xác nhận mã
            header('Location: verifyy.php');  // Chuyển hướng đến trang xác nhận mã
            exit();
        } else {
            $errors['email'] = 'Email không tồn tại trong hệ thống.';
        }
    }
}
ob_end_flush();
?>


<!-- Form nhập email -->
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu</title>
    <link rel="stylesheet" href="styles.css">
</head>
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

/* Khung bao quanh nội dung */
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

/* Thông báo lỗi */
.alert {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
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
input[type="email"] {
    padding: 10px;
    font-size: 16px;
    /* width: 100%; */
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 15px;
}

/* Nút gửi mã xác thực */
button {
    background-color: #007bff;
    color: #ffffff;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    width: 100%;
    margin-bottom: 10px;
}

button:hover {
    background-color: #0056b3;
}

</style>
<body>
    <div class="container">
        <!-- Tiêu đề -->
        <h1>Quên mật khẩu</h1>

        <!-- Thông báo lỗi -->
        <?php if (!empty($errors['email'])): ?>
        <div class="alert alert-danger">
            <?php echo $errors['email']; ?>
        </div>
        <?php endif; ?>

        <!-- Form nhập email -->
        <form action="" method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
            <button type="submit">Gửi mã xác thực</button>
        </form>
    </div>
</body>

</html>

