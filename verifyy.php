<?php
session_start();
require 'connect.php';
require 'functions.php';

$errors = [];
$attempts = $_SESSION['attempts'] ?? 0;
$lockout_time = $_SESSION['lockout_time'] ?? 0;

if ($lockout_time > time()) {
    $errors['verification'] = "Tài khoản của bạn đã bị khóa tạm thời. Vui lòng thử lại sau " . ceil(($lockout_time - time()) / 60) . " phút.";
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['resend'])) {
            if (isset($_SESSION['email'])) {
                $email = $_SESSION['email'];

                // Tạo mã xác thực ngẫu nhiên
                $verification_code = random_int(100000, 999999);
                sendVerificationEmail($email, $verification_code);

                // Cập nhật mã xác thực vào cơ sở dữ liệu
                $stmt = $pdo->prepare("UPDATE users SET verification_code = :code, expires_at = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE email = :email");
                $stmt->bindParam(':code', $verification_code);
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                $_SESSION['message'] = 'Mã xác thực mới đã được gửi đến email của bạn.';
                header("Location: reset_password.php?email=" . urlencode($email) . "&verification_code=" . urlencode($verification_code));
                exit();
            } else {
                $errors['verification'] = "Không tìm thấy email.";
            }
        } else {
            $code = $_POST['code'] ?? '';

            if (isset($_SESSION['email'])) {
                $email = $_SESSION['email'];
            } else {
                $errors['verification'] = "Không tìm thấy email.";
            }

            if (empty($errors)) {
                $stmt = $pdo->prepare("SELECT verification_code, expires_at FROM users WHERE email = :email ORDER BY created_at DESC LIMIT 1");
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                $verification = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($verification) {
                    if (trim($verification['verification_code']) == trim($code) && strtotime($verification['expires_at']) > time()) {
                        $_SESSION['verified'] = true;
                        header("Location: reset_password.php");
                        exit();
                    } else {
                        $attempts++;
                        $_SESSION['attempts'] = $attempts;
                        if ($attempts >= 3) {
                            $_SESSION['lockout_time'] = time() + 15 * 60;
                            $errors['verification'] = "Bạn đã nhập sai mã quá 3 lần. Tài khoản của bạn đã bị khóa tạm thời. Vui lòng thử lại sau 15 phút.";
                        } else {
                            $errors['verification'] = "Mã xác nhận không hợp lệ hoặc đã hết hạn. Bạn còn " . (3 - $attempts) . " lần thử.";
                        }
                    }
                } else {
                    $errors['verification'] = "Không tìm thấy mã xác nhận cho email này.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhập mã xác nhận</title>
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
        max-width: 500px;
        background-color: #ffffff;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    /* Logo */
    .logo {
        width: 80px;
        height: auto;
        margin-bottom: 20px;
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

    /* Khung nhập mã xác nhận */
    .code-inputs {
        display: flex;
        justify-content: space-between;
        gap: 10px;
    }

    .code-input {
        padding: 10px;
        font-size: 20px;
        width: 50px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    /* Nút xác nhận */
    .submit-btn, .resend-btn {
        background-color: #007bff;
        color: #ffffff;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        width: 100%;
        margin-top: 20px;
    }

    .submit-btn:hover {
        background-color: #0056b3;
    }

    /* Nút gửi lại mã */
    .resend-btn {
        background-color: #6c757d;
        margin-top: 10px;
    }

    .resend-btn:hover {
        background-color: #5a6268;
    }
</style>

<body>
    <div class="container">
        <!-- Logo -->
        <img src="logo.png" alt="Logo" class="logo">

        <!-- Tiêu đề -->
        <h1>Nhập mã xác nhận</h1>

        <!-- Thông báo lỗi -->
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Form nhập mã xác nhận -->
        <?php if ($lockout_time <= time()): ?>
        <form method="POST" class="verification-form">
            <div class="code-inputs">
                <input type="text" name="code[]" maxlength="1" class="code-input" required oninput="moveNext(this, 'code-input2')">
                <input type="text" name="code[]" maxlength="1" class="code-input" id="code-input2" required oninput="moveNext(this, 'code-input3')">
                <input type="text" name="code[]" maxlength="1" class="code-input" id="code-input3" required oninput="moveNext(this, 'code-input4')">
                <input type="text" name="code[]" maxlength="1" class="code-input" id="code-input4" required oninput="moveNext(this, 'code-input5')">
                <input type="text" name="code[]" maxlength="1" class="code-input" id="code-input5" required oninput="moveNext(this, 'code-input6')">
                <input type="text" name="code[]" maxlength="1" class="code-input" id="code-input6" required>
            </div>
            <button type="submit" class="submit-btn">Xác nhận</button>
        </form>
        <?php endif; ?>

        <!-- Nút gửi lại mã xác nhận -->
        <form method="POST" class="resend-form">
            <input type="hidden" name="resend" value="1">
            <button type="submit" class="resend-btn">Gửi lại mã xác nhận</button>
        </form>
    </div>

    <script>
        function moveNext(current, nextId) {
            if (current.value.length >= current.maxLength) {
                document.getElementById(nextId).focus();
            }
        }
    </script>
</body>

</html>

