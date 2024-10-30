<?php
session_start();
include 'header.php';
include 'connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['userID'];

// Truy vấn thông tin người dùng
$query = "SELECT full_name, gender, phone_number, email, address, avatar FROM users WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->bindParam(1, $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

if (!$user) {
    echo "Không tìm thấy thông tin người dùng.";
    exit();
}

// Xử lý tải ảnh đại diện
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $avatar = $_FILES['avatar'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

    if (in_array($avatar['type'], $allowed_types)) {
        $upload_dir = './admin/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $filename = uniqid() . '-' . basename($avatar['name']);
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($avatar['tmp_name'], $target_file)) {
            $sql = "UPDATE users SET avatar = :avatar WHERE id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':avatar', $target_file);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $user['avatar'] = $target_file;
                $upload_status = "Ảnh đại diện đã được cập nhật.";
            } else {
                $upload_status = "Lỗi trong việc cập nhật ảnh đại diện.";
            }
        } else {
            $upload_status = "Không thể di chuyển tệp tải lên.";
        }
    } else {
        $upload_status = "Chỉ cho phép tệp JPEG, PNG hoặc GIF.";
    }
} else {
    if (isset($_FILES['avatar']['error']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_status = "Có lỗi khi tải ảnh lên.";
    }
}
?>

<section class="my-profile">
    <div class="container">
        <div class="inner-wrap">
            <div class="row my-profile__row">
                <!-- Sidebar Menu -->
                <div class="col-md-2 ">
                    <div class="my-profile__img">
                        <img src="<?php echo htmlspecialchars($user['avatar'] ?? './images/avatarmd.png'); ?>"
                            alt="Avatar" class="rounded-circle" />
                    </div>
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="v-pills-account-tab" data-bs-toggle="pill"
                            href="#v-pills-account" role="tab" aria-controls="v-pills-account"
                            aria-selected="true">Thông tin tài khoản</a>
                        <a class="nav-link" id="v-pills-cart-tab" data-bs-toggle="pill" href="#v-pills-cart" role="tab"
                            aria-controls="v-pills-cart" aria-selected="false">Đổi mật khẩu</a>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="col-md-6 my-profile__right">
                    <div class="tab-content" id="v-pills-tabContent">
                        <!-- Account Information -->
                        <div class="tab-pane fade show active" id="v-pills-account" role="tabpanel"
                            aria-labelledby="v-pills-account-tab">
                            <h3 class="my-profile__title">Thông tin tài khoản</h3>
                            <p class="my-profile__info">Thông tin chung</p>
                            <form action="update_profile.php" method="POST" role="form" class="my-profile__form">
                                <div class="form-group">
                                    <label for="full_name">Họ tên</label>
                                    <input type="text" id="full_name" name="full_name"
                                        value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                <div class="form-check">
                                    <label>Giới tính</label>
                                    <div>
                                        <input type="radio" id="male" name="gender" value="male"
                                            <?php if ($user['gender'] == 'Male') echo 'checked'; ?>>
                                        <label for="male">Nam</label>
                                        <input type="radio" id="female" name="gender" value="female"
                                            <?php if ($user['gender'] == 'Female') echo 'checked'; ?>>
                                        <label for="female">Nữ</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Số điện thoại</label>
                                    <input type="text" id="phone" name="phone"
                                        value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email"
                                        value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="address">Địa chỉ cụ thể :</label>
                                    <input type="text" id="address" name="address"
                                        value="<?php echo htmlspecialchars($user['address']); ?>" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Chỉnh sửa</button>
                            </form>

                            <!-- Avatar Update -->
                            <div>
                                <h5>Ảnh đại diện</h5>
                                <form action="" method="POST" enctype="multipart/form-data">
                                    <input type="file" class="form-control mt-2" name="avatar" accept="image/*"
                                        required />
                                    <button type="submit" class="btn btn-primary mt-2">Tải ảnh mới lên</button>
                                </form>
                                <?php if (isset($upload_status)) : ?>
                                <p><?php echo $upload_status; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
             

                <!-- Change Password -->
                <div class="tab-pane fade" id="v-pills-cart" role="tabpanel" aria-labelledby="v-pills-cart-tab">
                    <h3>Đổi mật khẩu</h3>
                    <form action="change_password.php" method="POST">
                        <div class="form-group">
                            <label for="current_password">Mật khẩu hiện tại:</label>
                            <input type="password" class="form-control" id="current_password" name="current_password"
                                required>
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="new_password">Mật khẩu mới:</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="confirm_password">Xác nhận mật khẩu mới:</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                required>
                        </div>
                        <br>
                        <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>