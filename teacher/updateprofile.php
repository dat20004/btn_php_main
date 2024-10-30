<?php 
ob_start();
session_start();
// include 'header.php';
include '../connect.php';

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php"); // Chuyển hướng đến trang đăng nhập nếu chưa đăng nhập
    exit();
}

// Lấy ID người dùng từ session
$userId = $_SESSION['userID'];

// Truy vấn thông tin người dùng
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $userId);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Kiểm tra xem người dùng có tồn tại không
if (!$user) {
    $_SESSION['error'] = "Người dùng không tồn tại.";
    header("Location: updateprofile.php");
    exit();
}

// Kiểm tra xem người dùng đã nhấn nút submit chưa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $phone_number = $_POST['phone'];
    $address = $_POST['address'];
    $description = $_POST['description'];

    // Kiểm tra định dạng email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email không đúng định dạng.";
        header("Location: updateprofile.php");
        exit();
    }

    // Kiểm tra định dạng số điện thoại
    if (!preg_match('/^0[0-9]{9,10}$/', $phone_number)) {
        $_SESSION['error'] = "Số điện thoại không đúng định dạng.";
        header("Location: updateprofile.php");
        exit();
    }

    // Kiểm tra trùng email, số điện thoại, tên đăng nhập
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE (email = :email OR phone_number = :phone_number OR username = :username) 
            AND id != :id
        ");
        
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();

        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            if ($existingUser['email'] == $email) {
                $_SESSION['error'] = "Email đã tồn tại.";
            } elseif ($existingUser['phone_number'] == $phone_number) {
                $_SESSION['error'] = "Số điện thoại đã tồn tại.";
            } elseif ($existingUser['username'] == $username) {
                $_SESSION['error'] = "Tên đăng nhập đã tồn tại.";
            }
            header("Location: updateprofile.php");
            exit();
        }

        // Xử lý file avatar
        $avatar = $user['avatar']; // Giữ avatar cũ nếu không có file mới
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
            $avatar = $_FILES['avatar']['name']; // Tên file gốc
            $avatar_tmp = $_FILES['avatar']['tmp_name']; // Đường dẫn tạm thời của file

            $target_dir = "../admin/uploads";
            $target_file = $target_dir . basename($avatar);

            // Chỉ cho phép upload file hình ảnh
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                if (!move_uploaded_file($avatar_tmp, $target_file)) {
                    $_SESSION['error'] = "Lỗi khi tải lên ảnh đại diện.";
                    header("Location: updateprofile.php");
                    exit();
                }
                $avatar = $target_file; // Cập nhật đường dẫn ảnh đại diện mới
            } else {
                $_SESSION['error'] = "Chỉ cho phép tải lên các file hình ảnh (jpg, jpeg, png, gif).";
                header("Location: updateprofile.php");
                exit();
            }
        }

        // Cập nhật dữ liệu vào cơ sở dữ liệu
        $stmt = $pdo->prepare("
            UPDATE users 
            SET 
                username = :username, 
                full_name = :full_name, 
                email = :email, 
                gender = :gender, 
                phone_number = :phone_number, 
                address = :address, 
                avatar = :avatar, 
                descripteacher = :description
            WHERE id = :id
        ");

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':avatar', $avatar);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':id', $userId);

        $stmt->execute();

        $_SESSION['success'] = "Cập nhật thông tin người dùng thành công!";
        header("Location: index.php");
        exit();

    } catch (PDOException $e) {
        // Thông báo lỗi
        $_SESSION['error'] = "Lỗi: " . $e->getMessage();
        header("Location: updateprofile.php");
        exit();
    }
}
ob_end_flush();
?>

<form action="" method="POST" role="form" enctype="multipart/form-data">
    <!-- ID người dùng cần cập nhật (ẩn đi) -->
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">

    <div class="form-group">
        <label for="username">Tên đăng nhập: </label>
        <input type="text" name="username" id="username" class="form-control"
            placeholder="Tên đăng nhập"
            value="<?php echo htmlspecialchars($user['username']); ?>" required>
    </div>

    <div class="form-group">
        <label for="full_name">Họ Tên: </label>
        <input type="text" name="full_name" id="full_name" class="form-control"
            placeholder="Nguyễn Quốc Dũng"
            value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
    </div>

    <div class="form-group">
        <label for="email">Email: </label>
        <input type="email" name="email" id="email" class="form-control"
            placeholder="dung211224@gmail.com"
            value="<?php echo htmlspecialchars($user['email']); ?>" required>
    </div>

    <div class="form-group">
        <label for="gender">Giới tính: </label>
        <select name="gender" id="gender" class="form-control" required>
            <option value="Male" <?php if ($user['gender'] == 'Male') echo 'selected'; ?>>Nam</option>
            <option value="Female" <?php if ($user['gender'] == 'Female') echo 'selected'; ?>>Nữ</option>
        </select>
    </div>

    <div class="form-group">
        <label for="phone">Điện thoại: </label>
        <input type="text" name="phone" id="phone" class="form-control"
            placeholder="0392392938"
            value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
    </div>

    <div class="form-group">
        <label for="address">Địa chỉ: </label>
        <input type="text" name="address" id="address" class="form-control"
            placeholder="123 Đường XYZ"
            value="<?php echo htmlspecialchars($user['address']); ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Mô tả giảng viên: </label>
        <textarea name="description" id="description" class="form-control"
            placeholder="Mô tả về giảng viên"
            required><?php echo htmlspecialchars($user['descripteacher']); ?></textarea>
    </div>

    <div class="form-group">
        <label for="avatar">Ảnh đại diện: </label>
        <input type="file" name="avatar" id="avatar" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary">Cập nhật</button>
    <button type="button" class="btn btn-secondary back-button" onclick="window.history.back()">Huỷ</button>
</form>
