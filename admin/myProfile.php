<?php 
ob_start();
session_start();
include 'header.php';
include '../connect.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['userID'])) {
    header('Location: login.php'); // Chuyển hướng đến trang đăng nhập nếu chưa đăng nhập
    exit;
}

// Lấy ID người dùng từ session
$user_id = $_SESSION['userID']; 

// Truy vấn để lấy thông tin người dùng
$sql_user_info = "SELECT full_name, email, avatar FROM users WHERE id = :user_id";
$stmt_user_info = $pdo->prepare($sql_user_info);
$stmt_user_info->execute([':user_id' => $user_id]);
$user = $stmt_user_info->fetch(PDO::FETCH_ASSOC);

// Kiểm tra xem người dùng có tồn tại không
if (!$user) {
    echo "Không tìm thấy thông tin người dùng.";
    exit;
}

// Xử lý cập nhật thông tin tài khoản
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $old_password = trim($_POST['old-password']);
    $new_password = trim($_POST['new-password']);
    $confirm_password = trim($_POST['confirm-password']);

    // Kiểm tra mật khẩu cũ
    $sql_password_check = "SELECT password FROM users WHERE id = :user_id";
    $stmt_password_check = $pdo->prepare($sql_password_check);
    $stmt_password_check->execute([':user_id' => $user_id]);
    $current_password_hash = $stmt_password_check->fetchColumn();

    // Xác minh mật khẩu cũ
    if (password_verify($old_password, $current_password_hash)) {
        // Cập nhật tên và email
        $sql_update_user = "UPDATE users SET full_name = :name, email = :email WHERE id = :user_id";
        $stmt_update_user = $pdo->prepare($sql_update_user);
        $stmt_update_user->execute([
            ':name' => $name,
            ':email' => $email,
            ':user_id' => $user_id
        ]);

        // Nếu có thay đổi mật khẩu, cập nhật mật khẩu mới
        if (!empty($new_password) && $new_password === $confirm_password) {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $sql_update_password = "UPDATE users SET password = :new_password WHERE id = :user_id";
            $stmt_update_password = $pdo->prepare($sql_update_password);
            $stmt_update_password->execute([
                ':new_password' => $new_password_hash,
                ':user_id' => $user_id
            ]);
        }

        echo "<p>Cập nhật thông tin thành công!</p>";
        // Reload user info after update
        $stmt_user_info->execute([':user_id' => $user_id]);
        $user = $stmt_user_info->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "<p>Mật khẩu cũ không đúng.</p>";
    }
}
?>

<div class="content-wrapper subject">
    <div class="myProfile">
        <div class="container">
            <div class="myProfile-container">
                <div class="myProfile-left">
                    <div class="myProfile-left__logo"><img src="../images/logoweb.png" alt=""></div>
                    <div class="myProfile-left__ava">
                        <img src="./<?php echo htmlspecialchars($user['avatar']); ?>" alt="User Avatar">
                    </div>
                    <div class="myProfile-left__exit">
                        <form action="logout.php" method="POST">
                            <button type="submit">Đăng xuất</button>
                        </form>
                    </div>
                </div>
                <div class="myProfile-right">
                    <h1>Tài khoản</h1>
                    <p>Thông tin cá nhân</p>
                    <div class="account-form">
                        <h2>Tài khoản cá nhân</h2>
                        <form action="" method="POST">
                            <label for="name">Tên:</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['full_name']); ?>" >

                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" >

                            

                            <button type="submit">Cập nhật</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
