<?php
ob_start(); // Bắt đầu buffer đầu ra
session_start();

include 'connect.php';  // Kết nối cơ sở dữ liệu


$errors = [];  // Tạo biến để lưu thông báo lỗi

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';  // Nhận email từ form
    $password = $_POST['password'] ?? '';  // Nhận mật khẩu từ form
    $role = $_POST['role'] ?? '';  // Nhận vai trò từ form

    // Kiểm tra thông tin đăng nhập
    if (empty($email)) {
        $errors['email'] = 'Email không được để trống.';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Mật khẩu không được để trống.';
    }
    
    if (empty($role)) {
        $errors['role'] = 'Vui lòng chọn vai trò.';
    }

    // Nếu không có lỗi thì tiếp tục kiểm tra đăng nhập
    if (empty($errors)) {
        // Truy vấn lấy thông tin người dùng dựa trên email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra email và mật khẩu
        if ($user && password_verify($password, $user['password'])) {
            // Kiểm tra trạng thái người dùng
            if ($user['state'] == 'Active') {
                // Nếu người dùng có trạng thái 'Active', cho phép đăng nhập
                
                // Kiểm tra vai trò của người dùng
                if ($user['role'] === $role) {  // So sánh vai trò
                    $_SESSION['email'] = $email;
                    $_SESSION['user_login'] = $user; // Lưu thông tin người dùng vào session
                    $_SESSION['userID'] = $user['id']; // Lưu ID người dùng vào session
                    $_SESSION['user_avatar'] = $user['avatar'];
                    $_SESSION['full_name'] = $user['full_name']; // Lưu đường dẫn đến avatar
                    
                    // Chuyển hướng dựa trên vai trò
                    switch ($user['role']) {
                        case 'Admin':
                            header('Location: ./admin/index.php');
                            exit();
                        case 'Teacher':
                            header('Location: ./teacher/index.php');
                            exit();
                        case 'Student':
                            header('Location: homemain.php');
                            exit();
                        default:
                            $errors['failed'] = 'Vai trò không hợp lệ.';
                            break;
                    }
                } else {
                    $errors['failed'] = 'Vai trò không chính xác cho tài khoản này.';
                }
            } else {
                $errors['failed'] = $user['state'] == 'Inactive' ? 'Tài khoản chưa được xác minh!' : 'Tài khoản đã bị xóa hoặc bị khóa!';
            }
        } else {
            $errors['failed'] = 'Email hoặc mật khẩu không đúng!';
        }
    }
}

ob_end_flush(); // Kết thúc buffer đầu ra
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link rel="icon" href="./images/logoweb.png" type="image/png">
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://kit.fontawesome.com/533aad8d01.js" crossorigin="anonymous"></script>
    <style>
    /* Tạo kiểu cho select */




    .password-field {
        position: relative;
        display: flex;
        /* Sử dụng Flexbox để căn chỉnh phần tử con */
        align-items: center;
        /* Căn giữa theo chiều dọc */
        width: 100%;
        /* Đảm bảo chiều rộng đủ để chứa cả input và icon */
    }

    .password-field input {
        width: 100%;
        /* Làm cho input chiếm hết không gian có sẵn */
        padding: 10px;
        /* Padding cho input */
        font-size: 16px;
        /* Kích thước font của input */
        border: 1px solid #ccc;
        /* Đường viền của input */
        border-radius: 5px;
        /* Bo góc của input */
    }

    .eye-icon {
        position: absolute;
        /* Đặt mắt vào vị trí tuyệt đối */
        bottom : 27px;
        right: 10px;
        /* Căn phải cho biểu tượng con mắt */
        cursor: pointer;
        /* Khi hover lên mắt sẽ có con trỏ pointer */
        font-size: 20px;
        /* Kích thước của biểu tượng con mắt */
        z-index: 1;
        /* Đảm bảo biểu tượng mắt nằm trên input */
    }

    .eye-icon i {
        color: #666;
        /* Màu cho biểu tượng con mắt */
    }

    .password-field input:focus {
        border-color: #5cb85c;
        /* Màu đường viền khi input được focus */
        outline: none;
        /* Loại bỏ outline mặc định khi focus */
    }

    .password-field input:focus+.eye-icon i {
        color: #5cb85c;
        /* Thay đổi màu mắt khi input được focus */
    }

    .alert {
        position: relative;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 5px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .alert-dismissible .btn-close {
        position: absolute;
        top: 10px;
        right: 10px;
        opacity: 0.8;
    }

    .alert-dismissible .btn-close:hover {
        opacity: 1;
    }

    .alert-danger {
        border-left: 5px solid #f44336;
    }
    </style>
</head>

<body>

    <section class="login-fastLearn">
        <div class="container">
            <div class="inner-wrap">
                <img src="./images/logoweb.png" alt="">
                <h1>Đăng nhập tài khoản tại FastLearn</h1>

                <?php if (!empty($errors['failed'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $errors['failed']; ?>
                    </div>
                    <?php endif; ?>

                <!-- Form đăng nhập -->
                <form action="" method="POST" role="form">
                    <div class="container mt-5">

                        <label for="role" class="form-label">Vai trò</label>
                        <select id="role" name="role" class="form-select" required>
                            <option value="" disabled selected>-- Chọn vai trò --</option>
                            <option value="Admin">Admin</option>
                            <option value="Teacher">Teacher</option>
                            <option value="Student">Student</option>
                        </select>
                    </div>

                    <!-- Hiển thị lỗi vai trò -->
                    <?php if (!empty($errors['role'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $errors['role']; ?>
                    </div>
                    <?php endif; ?>
                    <?php //if (!empty($errors['failed'])): ?>
                    <!-- <div class="alert alert-danger">
                        <?php //echo $errors['failed']; ?>
                    </div> -->
                    <?php //endif; ?>
                       <!-- Hiển thị lỗi nếu có lỗi đăng nhập -->
                   
                    <div class="form-group">
                        <div>
                            <input type="email" name="email" placeholder="Nhập email" required>
                        </div>
                        <!-- Hiển thị lỗi email -->
                        <?php if (!empty($errors['email'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $errors['email']; ?>
                        </div>
                        <?php endif; ?>
                        <div class="password-field">
                            <input type="password" name="password" id="password" placeholder="Mật khẩu" required>
                            <span class="eye-icon" id="toggle-password">
                                <i class="bi bi-eye"></i>
                            </span>
                        </div>
                        <!-- Hiển thị lỗi mật khẩu -->
                        <?php if (!empty($errors['password'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $errors['password']; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                 
                    
                    <a href="LoginForgetPass.php" class="text-decoration-none">Quên mật khẩu?</a>

                    <button type="submit" class="btn btn-primary">Đăng nhập</button>
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
                <p>Bạn chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
                <p>Việc bạn sử dụng trang web cũng đồng nghĩa với việc bạn đồng ý với điều khoản dịch vụ của chúng tôi.
                </p>
            </div>
        </div>
    </section>
    <script>
    document.getElementById('role').addEventListener('change', function() {
        var defaultOption = this.querySelector('option[value=""]'); // Lấy option mặc định
        if (this.value !== "") {
            defaultOption.style.display = 'none'; // Ẩn option mặc định khi đã chọn giá trị
        } else {
            defaultOption.style.display = 'block'; // Hiển thị lại nếu không có giá trị nào được chọn
        }
    });

    // Lấy các biểu tượng mắt và trường mật khẩu
    const togglePassword = document.getElementById('toggle-password');
    const passwordField = document.getElementById('password');
    const toggleConfirmPassword = document.getElementById('toggle-confirm-password');
    const confirmPasswordField = document.getElementById('confirm-password');

    // Chức năng để ẩn/hiển thị mật khẩu
    togglePassword.addEventListener('click', function() {
        const type = passwordField.type === 'password' ? 'text' : 'password';
        passwordField.type = type;
        this.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
    });

    toggleConfirmPassword.addEventListener('click', function() {
        const type = confirmPasswordField.type === 'password' ? 'text' : 'password';
        confirmPasswordField.type = type;
        this.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>


