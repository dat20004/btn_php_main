<?php
session_start();
$message = isset($_SESSION['verificationMessage']) ? $_SESSION['verificationMessage'] : 'Không có thông báo';
$redirectPage = isset($_SESSION['redirectPage']) ? $_SESSION['redirectPage'] : 'login.php';

// Xóa session sau khi sử dụng
unset($_SESSION['verificationMessage']);
unset($_SESSION['redirectPage']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .notification-container {
            background-color: #ffffff;
            border: 1px solid #ccc;
            padding: 30px;
            width: 400px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #4CAF50;
        }

        .message {
            margin: 20px 0;
            font-size: 16px;
            color: #333;
        }

        .redirect-message {
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .loading {
            margin: 10px 0;
            font-size: 18px;
            color: #4CAF50;
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        .fade-out {
            animation: fadeOut 2s ease-out forwards;
        }
    </style>
</head>
<body>

<div class="notification-container">
    <h2><?php echo $message; ?></h2>
    <p class="redirect-message">Bạn sẽ được chuyển hướng đến trang đăng nhập trong vài giây.</p>
    <p class="loading">Đang chuyển hướng...</p>
</div>

<script>
    setTimeout(function() {
        window.location.href = "<?php echo $redirectPage; ?>"; // Chuyển hướng sau 3 giây
    }, 3000);

    document.querySelector('.notification-container').classList.add('fade-out');
</script>

</body>
</html>
