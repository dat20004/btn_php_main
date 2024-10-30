<?php
session_start(); 
include 'header.php';

include '../connect.php';  // Kết nối cơ sở dữ liệu

// Kiểm tra có ID người dùng trong URL hay không
if (isset($_GET['id'])) {
    $userID = $_GET['id'];
} else {
    die("Không tìm thấy thông tin người dùng.");
}

// Truy vấn lấy thông tin người dùng từ cơ sở dữ liệu
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $userID, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu không tìm thấy người dùng
if (!$userData) {
    die("Không tìm thấy người dùng.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $fullName = $_POST['full_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $gender = $_POST['gender'] ?? '';  // Lấy giá trị giới tính từ form
    $isAccountLocked = isset($_POST['lockAccount']);  // Nếu người dùng muốn khóa tài khoản
    $avatarPath = $userData['avatar']; // Giữ avatar hiện tại nếu không thay đổi

    // Kiểm tra trùng tên đăng nhập và email
    $checkQuery = "SELECT * FROM users WHERE (username = :username OR email = :email) AND id != :id";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->bindParam(':username', $username, PDO::PARAM_STR);
    $checkStmt->bindParam(':email', $email, PDO::PARAM_STR);
    $checkStmt->bindParam(':id', $userID, PDO::PARAM_INT);
    $checkStmt->execute();
    $existingUser = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        if ($existingUser['username'] == $username) {
            $error = "Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.";
        }
        if ($existingUser['email'] == $email) {
            $error = "Email đã tồn tại. Vui lòng chọn email khác.";
        }
    } else {
        // Xử lý tải lên hình ảnh đại diện
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $image_name = $_FILES['profile_image']['name'];
            $image_tmp = $_FILES['profile_image']['tmp_name'];
            $image_size = $_FILES['profile_image']['size'];
            $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);

            // Allowed extensions
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array(strtolower($image_ext), $allowed_exts)) {
                $error = "Ảnh phải có định dạng: jpg, jpeg, png, gif.";
            } elseif ($image_size > 100 * 1024 * 1024) {  // Giới hạn ảnh dưới 100MB
                $error = "Ảnh không được vượt quá 100MB.";
            } else {
                // Di chuyển hình ảnh đến thư mục "uploads/"
                $image_dir = 'uploads/';
                if (!is_dir($image_dir)) {
                    mkdir($image_dir, 0777, true);
                }
                $avatarPath = $image_dir . time() . '.' . $image_ext;
                if (!move_uploaded_file($image_tmp, $avatarPath)) {
                    $error = "Không thể lưu ảnh. Vui lòng thử lại.";
                }
            }
        }

        // Cập nhật mật khẩu nếu có thay đổi
        if (!empty($password) && !empty($newPassword)) {
            if (password_verify($password, $userData['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updatePasswordQuery = "UPDATE users SET password = :password WHERE id = :id";
                $updatePasswordStmt = $pdo->prepare($updatePasswordQuery);
                $updatePasswordStmt->bindParam(':password', $hashedPassword);
                $updatePasswordStmt->bindParam(':id', $userID, PDO::PARAM_INT);

                if ($updatePasswordStmt->execute()) {
                    $success = "Cập nhật mật khẩu thành công!";
                } else {
                    $error = "Có lỗi xảy ra khi cập nhật mật khẩu.";
                }
            } else {
                $error = "Mật khẩu cũ không đúng.";
            }
        }

        // Cập nhật thông tin người dùng nếu có thay đổi
        if (empty($error)) {
            $updateFields = [];
            $updateValues = [];

            // Kiểm tra và cập nhật chỉ những trường đã thay đổi
            if ($username != $userData['username']) {
                $updateFields[] = "username = :username";
                $updateValues[':username'] = $username;
            }
            if ($email != $userData['email']) {
                $updateFields[] = "email = :email";
                $updateValues[':email'] = $email;
            }
            if ($phone != $userData['phone_number']) {
                $updateFields[] = "phone_number = :phone";
                $updateValues[':phone'] = $phone;
            }
            if ($address != $userData['address']) {
                $updateFields[] = "address = :address";
                $updateValues[':address'] = $address;
            }
            if ($fullName != $userData['full_name']) {
                $updateFields[] = "full_name = :full_name";
                $updateValues[':full_name'] = $fullName;
            }
            if ($gender != $userData['gender']) {  // Cập nhật giới tính nếu có thay đổi
                $updateFields[] = "gender = :gender";
                $updateValues[':gender'] = $gender;
            }

            if ($avatarPath != $userData['avatar']) { // Cập nhật avatar nếu có thay đổi
                $updateFields[] = "avatar = :avatar";
                $updateValues[':avatar'] = $avatarPath;
            }

            if (!empty($updateFields)) {
                $updateQuery = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = :id";
                $updateStmt = $pdo->prepare($updateQuery);
                $updateValues[':id'] = $userID;  // Thêm ID người dùng vào tham số

                if ($updateStmt->execute($updateValues)) {
                    $success = "Cập nhật thông tin thành công!";
                    header("Location: account.php");
                    exit();
                } else {
                    $error = "Có lỗi xảy ra khi cập nhật thông tin.";
                }
            }
        }

        // Khóa tài khoản nếu có yêu cầu
        if ($isAccountLocked) {
            $lockQuery = "UPDATE users SET state = 'locked' WHERE id = :id";
            $lockStmt = $pdo->prepare($lockQuery);
            $lockStmt->bindParam(':id', $userID, PDO::PARAM_INT);
            if ($lockStmt->execute()) {
                $success = "Tài khoản đã bị khóa!";
                header("Location: account.php");
                exit();
            } else {
                $error = "Có lỗi khi khóa tài khoản.";
            }
        }
    }
}
?>

<!-- Hiển thị form chỉnh sửa thông tin người dùng -->
<div class="content-wrapper subject">
    <div id="courseForm" class="courseForm">
        <h1>Chỉnh sửa thông tin người dùng</h1>

        <!-- Hiển thị thông báo lỗi hoặc thành công -->
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Form chỉnh sửa thông tin người dùng -->
        <form action="" method="POST" enctype="multipart/form-data"> <!-- Chú ý enctype -->
            <div>
                <label for="full_name">Họ và tên</label><br>
                <input class="form-control" type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($userData['full_name']); ?>" required>
            </div>

            <div>
                <label for="email">Email</label><br>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
            </div>

            <div>
                <label for="phone">Số điện thoại</label><br>
                <input type="number" id="phone" name="phone" value="<?php echo htmlspecialchars($userData['phone_number']); ?>" required>
            </div>

            <div>
                <label for="address">Địa chỉ</label><br>
                <input class="form-control" type="text" id="address" name="address" value="<?php echo htmlspecialchars($userData['address']); ?>" required>
            </div>

            <div>
                <label for="username">Tên đăng nhập</label><br>
                <input class="form-control" type="text" id="username" name="username" value="<?php echo htmlspecialchars($userData['username']); ?>" required>
            </div>

            <div>
                <label for="gender">Giới tính</label><br>
                <select id="gender" name="gender" required>
                    <option value="male" <?php echo $userData['gender'] == 'male' ? 'selected' : ''; ?>>Nam</option>
                    <option value="female" <?php echo $userData['gender'] == 'female' ? 'selected' : ''; ?>>Nữ</option>
                    <option value="other" <?php echo $userData['gender'] == 'other' ? 'selected' : ''; ?>>Khác</option>
                </select>
            </div>

            <div>
                <label for="password">Mật khẩu cũ</label><br>
                <input type="password" id="password" name="password" placeholder="Mật khẩu cũ">
            </div>

            <div>
                <label for="newPassword">Mật khẩu mới</label><br>
                <input type="password" id="newPassword" name="newPassword" placeholder="Mật khẩu mới">
            </div>

            <div>
                <label for="profile_image">Ảnh đại diện</label><br>
                <input type="file" id="profile_image" name="profile_image">
                <br>
                <img src="<?php echo htmlspecialchars($userData['avatar']); ?>" alt="Profile Image" style="max-width: 100px; max-height: 100px;">
            </div>

            <div class="btn-key">
                <button type="submit" class="edit-User__btn">Cập nhật</button>
                <button style="margin-right:1100px;" type="button" class="btn btn-secondary back-button" onclick="window.history.back()">Huỷ</button>

                <button type="submit" name="lockAccount" class="edit-User__btn-key edit-User__btn">Khóa tài khoản</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
