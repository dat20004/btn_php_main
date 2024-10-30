<?php 
session_start();
include 'header.php';
include '../connect.php';

global $pdo;
$errors = [];
$success = "";

// Fetch all majors from the database to populate a dropdown list
try {
    $sql = "SELECT id, name FROM majors";  
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $majors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($majors)) {
        echo "Không có dữ liệu ngành.";
    }
} catch (PDOException $e) {
    echo "Lỗi khi lấy danh sách ngành: " . $e->getMessage();
}

// Fetch all courses from the database to populate a dropdown list
try {
    $sql = "SELECT id, name FROM courses";  
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($courses)) {
        echo "Không có dữ liệu khoá học.";
    }
} catch (PDOException $e) {
    echo "Lỗi khi lấy danh sách khoá học: " . $e->getMessage();
}

// Fetch the teacher's data if `id` is provided in the URL
if (isset($_GET['id'])) {
    $teacher_id = $_GET['id'];
    try {
        $sql = "SELECT * FROM users WHERE id = :id AND role = 'Teacher'";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $teacher_id);
        $stmt->execute();
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$teacher) {
            $_SESSION['error'] = "Giảng viên không tồn tại.";
            header('Location: teacher.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Lỗi khi lấy dữ liệu giảng viên: " . $e->getMessage();
        header('Location: teacher.php');
        exit();
    }
} else {
    $_SESSION['error'] = "Không có giảng viên nào được chỉ định.";
    header('Location: teacher.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Cập nhật thông tin giảng viên
    if (isset($_POST['update_teacher'])) {
        $teacher_name = $_POST['full_name'] ?? '';
        $teacher_email = $_POST['email'] ?? '';
        $teacher_username = $_POST['username'] ?? '';
        $teacher_gender = $_POST['gender'] ?? '';
        $teacher_phone = $_POST['phone'] ?? '';
        $teacher_description = $_POST['description'] ?? '';
        
        // Image upload handling
        $profile_image = $teacher['avatar'];
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $allowed_extensions = ['jpg', 'jpeg', 'png'];
            $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));

            if (!in_array($file_extension, $allowed_extensions)) {
                $errors[] = "Chỉ hỗ trợ upload ảnh với đuôi .jpg, .jpeg hoặc .png.";
            } else {
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $new_file_name = $teacher_id . '-' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                    $profile_image = $upload_path;
                } else {
                    $errors[] = "Lỗi khi tải ảnh lên. Vui lòng thử lại.";
                }
            }
        }

        // Kiểm tra username và email trùng lặp
        if (empty($errors)) {
            try {
                $sql_check = "SELECT COUNT(*) FROM users WHERE username = :username AND id != :id";
                $stmt_check = $pdo->prepare($sql_check);
                $stmt_check->bindParam(':username', $teacher_username);
                $stmt_check->bindParam(':id', $teacher_id);
                $stmt_check->execute();
                $username_exists = $stmt_check->fetchColumn();

                $sql_check_email = "SELECT COUNT(*) FROM users WHERE email = :email AND id != :id";
                $stmt_check_email = $pdo->prepare($sql_check_email);
                $stmt_check_email->bindParam(':email', $teacher_email);
                $stmt_check_email->bindParam(':id', $teacher_id);
                $stmt_check_email->execute();
                $email_exists = $stmt_check_email->fetchColumn();

                if ($username_exists > 0) {
                    $errors[] = "Tên đăng nhập này đã tồn tại.";
                }

                if ($email_exists > 0) {
                    $errors[] = "Email này đã tồn tại.";
                }
            } catch (PDOException $e) {
                $errors[] = "Lỗi khi kiểm tra tên đăng nhập hoặc email: " . $e->getMessage();
            }
        }

        // Cập nhật thông tin giảng viên nếu không có lỗi
        if (empty($errors)) {
            try {
                $sql = "UPDATE users 
                        SET full_name = :full_name, email = :email, username = :username, gender = :gender, 
                            phone_number = :phone, descripteacher = :description, avatar = :avatar
                        WHERE id = :id AND role = 'Teacher'";

                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':full_name', $teacher_name);
                $stmt->bindParam(':email', $teacher_email);
                $stmt->bindParam(':username', $teacher_username);
                $stmt->bindParam(':gender', $teacher_gender);
                $stmt->bindParam(':phone', $teacher_phone);
                $stmt->bindParam(':description', $teacher_description);
                $stmt->bindParam(':avatar', $profile_image);
                $stmt->bindParam(':id', $teacher_id);

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Thông tin giảng viên đã được cập nhật thành công!";
                    header('Location: teacher.php');
                    exit();
                } else {
                    $errors[] = "Có lỗi khi cập nhật thông tin giảng viên. Vui lòng thử lại.";
                }
            } catch (PDOException $e) {
                $errors[] = "Lỗi khi cập nhật: " . $e->getMessage();
            }
        }
    }
    
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

    // Khóa tài khoản giảng viên
    if (isset($_POST['lock_account'])) {
        try {
            $lockQuery = "UPDATE users SET state = 'locked' WHERE id = :id";
            $lockStmt = $pdo->prepare($lockQuery);
            $lockStmt->bindParam(':id', $teacher_id, PDO::PARAM_INT);
            if ($lockStmt->execute()) {
                $_SESSION['success'] = "Tài khoản giảng viên đã bị khóa!";
                header('Location: teacher.php');
                exit();
            } else { if (!empty($password) && !empty($newPassword)) {
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
                $errors[] = "Có lỗi khi khóa tài khoản.";
            }
        } catch (PDOException $e) {
            $errors[] = "Lỗi khi khóa tài khoản: " . $e->getMessage();
        }
    }

    // Display errors if any
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo '<p class="error">' . htmlspecialchars($error) . '</p>';
        }
    }
}
?>

<!-- HTML Form -->
<div class="content-wrapper subject">
    <div id="courseForm edit-courseForm" class="courseForm edit-courseForm">
        <h1>Chỉnh sửa thông tin giảng viên</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <!-- Name -->
            <div>
                <label for="full_name">Tên giảng viên</label><br>
                <input class="form-control" type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($teacher['full_name']); ?>" required>
            </div>

            <!-- Email -->
            <div>
                <label for="email">Email</label><br>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
            </div>

            <!-- Username -->
            <div>
                <label for="username">Tên đăng nhập (Username)</label><br>
                <input class="form-control" type="text" id="username" name="username" value="<?php echo htmlspecialchars($teacher['username']); ?>" required>
            </div>

            <!-- Gender -->
            <div>
                <label for="gender">Giới tính</label><br>
                <select name="gender" id="gender" class="form-control" required>
                    <option value="male" <?php echo ($teacher['gender'] == 'male') ? 'selected' : ''; ?>>Nam</option>
                    <option value="female" <?php echo ($teacher['gender'] == 'female') ? 'selected' : ''; ?>>Nữ</option>
                    <option value="other" <?php echo ($teacher['gender'] == 'other') ? 'selected' : ''; ?>>Khác</option>
                </select>
            </div>

            <!-- Phone Number -->
            <div>
                <label for="phone">Số điện thoại</label><br>
                <input class="form-control" type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($teacher['phone_number']); ?>" required>
            </div>

            <!-- Description -->
            <div>
                <label for="description">Thông tin đầy đủ</label><br>
                <textarea name="description" id="description" class="form-control" required><?php echo htmlspecialchars($teacher['descripteacher']); ?></textarea>
            </div>
            <div>
                <label for="password">Mật khẩu cũ</label><br>
                <input type="password" id="password" name="password" placeholder="Mật khẩu cũ">
            </div>

            <div>
                <label for="newPassword">Mật khẩu mới</label><br>
                <input type="password" id="newPassword" name="newPassword" placeholder="Mật khẩu mới">
            </div>

            <!-- Image Upload -->
            <div>
                <label for="profile_image">Ảnh đại diện</label><br>
                <input type="file" id="profile_image" name="profile_image">
                <br>
                <img src="<?php echo htmlspecialchars($teacher['avatar']); ?>" alt="Profile Image" style="max-width: 100px; max-height: 100px;">
            </div>

            <!-- Buttons -->
            <div class="btn-key">
                <button type="submit" name="update_teacher" class="edit-User__btn">Cập nhật</button>
                <button style="margin-right:1100px;" type="button" class="btn btn-secondary back-button" onclick="window.history.back()">Huỷ</button>

                <button type="submit" name="lock_account" class="edit-User__btn-key edit-User__btn">Khóa tài khoản</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
