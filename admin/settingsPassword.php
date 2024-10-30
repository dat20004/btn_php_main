<?php 
ob_start();
session_start();
include 'header.php';
include '../connect.php';

if (!isset($_SESSION['userID'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit;
}

// Retrieve user ID from the session
$user_id = $_SESSION['userID']; 
$errors = [];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    // Validate inputs
    if (empty($old_password)) {
        $errors['old_password'] = 'Bạn phải nhập mật khẩu cũ';
    }
    if (empty($new_password)) {
        $errors['new_password'] = 'Bạn phải nhập mật khẩu mới';
    }
    if (empty($confirm_new_password)) {
        $errors['confirm_new_password'] = 'Bạn phải xác nhận mật khẩu mới';
    } else if ($new_password !== $confirm_new_password) {
        $errors['confirm_new_password'] = 'Xác nhận mật khẩu không chính xác';
    }

    // Proceed if there are no errors
    if (empty($errors)) {
        // Get the current password hash from the database
        $sqlCheck = "SELECT password FROM users WHERE id = :user_id";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([':user_id' => $user_id]);
        $current_password_hash = $stmtCheck->fetchColumn();

        // Verify the old password
        if (!password_verify($old_password, $current_password_hash)) {
            $errors['failed'] = 'Mật khẩu cũ không chính xác';
        } else {
            // Hash the new password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $sqlUpdate = "UPDATE users SET password = :new_password WHERE id = :user_id";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([':new_password' => $new_password_hash, ':user_id' => $user_id]);

            // Clear session and redirect to login page
            unset($_SESSION['user_login']);
            header('Location: ../login.php');
            exit; // Ensure no further code is executed
        }
    }
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Thay đổi mật khẩu
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box">
            <div class="box-body">
                <?php if (!empty($errors)) : ?>
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form action="" method="POST" role="form">
                    <div class="form-group">
                        <label for="">Mật khẩu cũ</label>
                        <input type="password" name="old_password" class="form-control" placeholder="Current Password" required>
                    </div>

                    <div class="form-group">
                        <label for="">Mật khẩu mới</label>
                        <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
                    </div>
                    <div class="form-group">
                        <label for="">Xác nhận mật khẩu</label>
                        <input type="password" name="confirm_new_password" class="form-control" placeholder="Confirm New Password" required>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Change Password</button>
                </form>
            </div>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>
