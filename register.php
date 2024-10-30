<?php
session_start();

include 'connect.php';  // Giả định file này tạo kết nối PDO $pdo

// Biến để lưu thông báo lỗi
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'] ?? '';  // Bắt trường full_name
    $username = $_POST['username'] ?? '';   // Chỉnh sửa: name -> username
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'Student';  // Mặc định là Student

    // Kiểm tra các trường bắt buộc
    if (empty($full_name)) {
        $error = "Vui lòng nhập họ và tên đầy đủ.";
    } elseif (empty($username)) {
        $error = "Vui lòng nhập tên tài khoản.";
    } elseif (empty($email)) {
        $error = "Vui lòng nhập email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Định dạng email không hợp lệ.";
    } elseif (empty($password)) {
        $error = "Vui lòng nhập mật khẩu.";
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = "Mật khẩu phải có ít nhất 8 ký tự, chứa cả chữ hoa, chữ thường và số.";
    } else {
        try {
            // Tạo kết nối PDO
            global $pdo;

            // Kiểm tra email đã tồn tại chưa
            $sql_check = "SELECT * FROM users WHERE username = :username OR email = :email"; // Chỉnh sửa: name -> username
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute(['username' => $username, 'email' => $email]);
            $result_check = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($result_check) {
                $error = "Email hoặc tên người dùng đã được sử dụng.";
            } else {
                // Nếu không có lỗi, hash mật khẩu
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Tạo mã xác nhận và thời gian hết hạn
                $code = rand(100000, 999999);
                $expires_at = date("Y-m-d H:i:s", strtotime('+5 minutes'));

                // Chuẩn bị câu lệnh SQL để thêm người dùng mới kèm mã xác nhận
                $sql = "INSERT INTO users (username, password, email, role, verification_code, expires_at, full_name) 
                        VALUES (:username, :password, :email, :role, :verification_code, :expires_at, :full_name)"; // Chỉnh sửa: name -> username
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':username', $username);  // Chỉnh sửa: name -> username
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':role', $role);
                $stmt->bindParam(':verification_code', $code);
                $stmt->bindParam(':expires_at', $expires_at);
                $stmt->bindParam(':full_name', $full_name);  // Gán giá trị full_name

                // Thực thi câu lệnh và kiểm tra lỗi
                if ($stmt->execute()) {
                    // Lấy ID của người dùng mới
                    $userId = $pdo->lastInsertId();

                    // Gửi mã xác nhận qua email
                    require 'send_verification_email.php';
                    sendVerificationEmail($email, $code);

                    // Lưu email vào session để xác thực sau khi gửi mã
                    $_SESSION['email'] = $email;

                    echo "<p>Mã xác nhận đã được gửi. Vui lòng kiểm tra email của bạn!</p>";
                    echo "<script>window.location.href = 'verify.php';</script>";
                } else {
                    $error = "Lỗi khi đăng ký: " . implode(", ", $stmt->errorInfo());
                }
            }
        } catch (PDOException $e) {
            $error = "Lỗi khi kết nối cơ sở dữ liệu: " . $e->getMessage();
        }
    }
    $pdo = null; // Đóng kết nối cơ sở dữ liệu
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>
    <link rel="icon" href="./images/logoweb.png" type="image/png">
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://kit.fontawesome.com/533aad8d01.js" crossorigin="anonymous"></script>
    <style>
    .password-field {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }

    .password-field input {
        width: 100%;
        padding: 10px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .eye-icon {
        position: absolute;
        bottom : 27px;
        right: 10px;
        cursor: pointer;
        font-size: 20px;
        z-index: 1;
    }

    .eye-icon i {
        color: #666;
    }

    .password-field input:focus {
        border-color: #5cb85c;
        outline: none;
    }

    .password-field input:focus+.eye-icon i {
        color: #5cb85c;
    }
    </style>
</head>

<body>
    <section class="login-fastLearn">
        <div class="container">
            <div class="inner-wrap">
                <img src="./images/logoweb.png" alt="">
                <h1>Đăng ký tài khoản tại FastLearn</h1>

                <!-- Hiển thị thông báo lỗi nếu có -->
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <form action="" method="POST" role="form">
                    <div class="form-group">
                        <div>
                            <input type="text" name="full_name" placeholder="Họ tên" required>
                        </div>

                        <div>
                            <input type="text" name="username" placeholder="Tên đăng nhập" required> <!-- Chỉnh sửa: name -> username -->
                        </div>
                        <div>
                            <input type="email" name="email" placeholder="Nhập email" required>
                        </div>
                        <div class="password-field">
                            <input type="password" name="password" id="password" placeholder="Mật khẩu" required>
                            <span class="eye-icon" id="toggle-password">
                                <i class="bi bi-eye"></i>
                            </span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Đăng ký</button>
                </form>

                <p>Hoặc</p>
                <div class="login-fastLearn__link">
                    <a href="">
                        <img src="./images/google.png" alt="">
                    </a>
                    <a href="">
                        <img src="./images/facebook.png" alt="">
                    </a>
                    <a href="">
                        <img src="./images/github.png" alt="">
                    </a>
                </div>
                <p>Bạn đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
                <p>Việc bạn sử dụng trang web cũng đồng nghĩa việc bạn đồng ý với điều khoản dịch vụ của chúng tôi.</p>
            </div>
        </div>
    </section>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        
    </script>
    <script>
    // Chức năng ẩn/hiện mật khẩu
    const togglePassword = document.getElementById('toggle-password');
    const passwordField = document.getElementById('password');

    togglePassword.addEventListener('click', function() {
        const type = passwordField.type === 'password' ? 'text' : 'password';
        passwordField.type = type;
        this.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
    });
    </script>
</body>

</html>
