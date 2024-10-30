<?php 
session_start();
include 'header.php';
include '../connect.php';

// Kiểm tra nếu ID người dùng đã được gửi
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Optional: Verify that the ID matches the logged-in user for security
    if ($user_id !== $_SESSION['userID']) {
        die("Bạn không có quyền truy cập trang này.");
    }

    // Fetch user data if needed
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            die("Người dùng không tồn tại.");
        }
    } catch (PDOException $e) {
        die("Lỗi: " . $e->getMessage());
    }
} else {
    die("ID người dùng không hợp lệ.");
}

$errorMessage = ""; // Khởi tạo biến để lưu thông báo lỗi

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['userID']; // Lấy ID người dùng từ session
    $oldPassword = $_POST['oldPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    try {
        // Truy vấn để lấy mật khẩu hiện tại của người dùng
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra mật khẩu hiện tại
        if ($user && password_verify($oldPassword, $user['password'])) {
            // Kiểm tra xem mật khẩu mới và xác nhận mật khẩu có trùng khớp không
            if ($newPassword === $confirmPassword) {
                // Mã hóa mật khẩu mới
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Cập nhật mật khẩu mới vào cơ sở dữ liệu
                $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                $updateStmt->execute(['password' => $hashedPassword, 'id' => $user_id]);

                // Thông báo thành công
                echo "<div class='alert alert-success'>Đổi mật khẩu thành công!</div>";

                // Redirect to login page
                header("Location: ../login.php");
                exit; // Ensure script stops executing after redirect
            } else {
                $errorMessage = "Mật khẩu mới và xác nhận mật khẩu không trùng khớp.";
            }
        } else {
            $errorMessage = "Mật khẩu hiện tại không đúng.";
        }
    } catch (PDOException $e) {
        $errorMessage = "Lỗi khi đổi mật khẩu: " . $e->getMessage();
    }
}

?>
<style>
/* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    color: #333;
}

.content-section {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    max-width: 500px;
    margin: 40px auto;
    padding: 20px;
}

/* Form Styles */
h2 {
    text-align: center;
    margin-bottom: 20px;
}

form {
    display: flex;
    flex-direction: column;
}

/* Input Styles */
.mb-3 {
    margin-bottom: 15px;
    position: relative; /* Make sure this is here for positioning the icon */
}

.form-label {
    margin-bottom: 5px;
    font-weight: bold;
}

input[type="password"] {
    padding: 10px 40px 10px 10px; /* Add padding to the left for the icon */
    border: 1px solid #ddd;
    border-radius: 5px;
    transition: border-color 0.3s;
    width: 100%; /* Ensure full width of input */
}

input[type="password"]:focus {
    border-color: #007bff;
    outline: none;
}

/* Button Styles */
.btn {
    padding: 10px;
    border: none;
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-danger {
    background-color: #dc3545;
    color: #fff;
}

.btn-danger:hover {
    background-color: #c82333;
}

.btn-primary {
    background-color: #007bff;
    color: #fff;
}

.btn-primary:hover {
    background-color: #0069d9;
}

/* Alert Styles */
.alert {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Icon Styles */
i.fa-regular {
    cursor: pointer;
    position: absolute;
    right: 10px;
    top: 35px; /* Adjust this value to align with input height */
    color: #888;
    pointer-events: none; /* Prevent click events on icon */
}

/* Media Queries */
@media (max-width: 600px) {
    .content-section {
        margin: 20px;
        padding: 15px;
    }

    h2 {
        font-size: 1.5em;
    }

    .btn {
        font-size: 0.9em;
    }
}

</style>
<head>
    <link rel="stylesheet" href="path/to/font-awesome.css"> <!-- Ensure Font Awesome is included -->
    <style>
        /* Paste the CSS styles here */
    </style>
</head>
<div id="password" class="content-section" style="display: block">
    <h2>Đổi mật khẩu</h2>
    <?php if (!empty($errorMessage)) : ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>

    <form class="change-password" method="POST" action="">
        <div class="mb-3">
            <label for="oldPassword" class="form-label">Mật khẩu hiện tại</label>
            <input type="password" name="oldPassword" id="oldPassword" required />
            <i class="fa-regular fa-eye-slash" id="toggleCurrentPassword"></i>
        </div>
        <div class="mb-3">
            <label for="newPassword" class="form-label">Mật khẩu mới</label>
            <input type="password" name="newPassword" id="newPassword" required />
            <i class="fa-regular fa-eye-slash" id="toggleNewPassword"></i>
        </div>
        <div class="mb-3">
            <label for="confirmPassword" class="form-label">Xác nhận mật khẩu</label>
            <input type="password" name="confirmPassword" id="confirmPassword" required />
            <i class="fa-regular fa-eye-slash" id="toggleCheckPassword"></i>
        </div>
        <div>
            <button type="button" class="btn btn-danger" onclick="resetForm()">Hủy</button>
        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        </div>
        
    </form>
</div>



<script>
// Hàm để reset form (nếu cần)
function resetForm() {
    document.querySelector('.change-password').reset();
}

// Ví dụ cho toggle mật khẩu (hiện/ẩn)
document.getElementById('toggleCurrentPassword').addEventListener('click', function() {
    const currentPasswordInput = document.getElementById('oldPassword');
    currentPasswordInput.type = currentPasswordInput.type === 'password' ? 'text' : 'password';
});

document.getElementById('toggleNewPassword').addEventListener('click', function() {
    const newPasswordInput = document.getElementById('newPassword');
    newPasswordInput.type = newPasswordInput.type === 'password' ? 'text' : 'password';
});

document.getElementById('toggleCheckPassword').addEventListener('click', function() {
    const confirmPasswordInput = document.getElementById('confirmPassword');
    confirmPasswordInput.type = confirmPasswordInput.type === 'password' ? 'text' : 'password';
});
</script>

<?php include 'footer.php'; ?>
