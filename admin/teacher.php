<?php
session_start();
include 'header.php';
include '../connect.php';

global $pdo;

// Câu lệnh SQL lấy dữ liệu từ bảng `users` và chỉ lọc ra 'Teacher'
$sql = "SELECT users.id, users.full_name, users.email, users.role, users.created_at, 
        users.username,  users.state,users.descripteacher
        FROM users
        WHERE users.role = 'Teacher'";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%"; // Thêm ký tự % để tìm kiếm theo một phần của tên
    $sql .= " AND users.full_name LIKE :search";
}
$stmt = $pdo->prepare($sql);
if (isset($search)) {
    $stmt->bindParam(':search', $search);
}
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$success = "";

try {
    // Fetch all majors from the database to populate a dropdown list
    $sql = "SELECT id, name FROM majors";  
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($fields)) {
        echo "Không có dữ liệu ngành.";
    }
} catch (PDOException $e) {
    echo "Lỗi khi lấy danh sách ngành: " . $e->getMessage();
}

// Form handling logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Form fields
    $teacher_name = $_POST['teacherName'] ?? '';
    $teacher_email = $_POST['teacherEmail'] ?? '';
    $teacher_password = $_POST['teacherPassword'] ? password_hash($_POST['teacherPassword'], PASSWORD_DEFAULT) : '';
    $teacher_phone = $_POST['teacherPhone'] ?? '';
    $teacher_username = $_POST['teacherUsername'] ?? '';
    $teacher_gender = $_POST['teacherGender'] ?? '';
    $teacher_desc = $_POST['teacherDESC'] ?? '';
    $teacher_adress = $_POST['teacherAdress'] ?? ''; // Thêm trường giới thiệu

    if (empty($teacher_name) || empty($teacher_email) || empty($teacher_phone) || empty($teacher_username) || empty($teacher_password) ||  empty($teacher_gender) || empty($teacher_adress)) {
        $error = "Vui lòng điền đầy đủ thông tin.";
    } elseif (!filter_var($teacher_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Định dạng email không hợp lệ.";
    }  elseif (strlen($teacher_password) < 8 || !preg_match('/[A-Z]/', $teacher_password) || !preg_match('/[a-z]/', $teacher_password) || !preg_match('/[0-9]/', $teacher_password)) {
        $error = "Mật khẩu phải có ít nhất 8 ký tự, chứa cả chữ hoa, chữ thường và số.";
    } elseif (!preg_match('/^\d{10}$/', $teacher_phone)) {
        $error = "Số điện thoại phải có đúng 10 số.";
    }
    // Handle file upload (image)
    $image = null;
    if (isset($_FILES['teacherImage']) && $_FILES['teacherImage']['error'] == 0) {
        $image_name = $_FILES['teacherImage']['name'];
        $image_tmp = $_FILES['teacherImage']['tmp_name'];
        $image_size = $_FILES['teacherImage']['size'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);

        // Allowed extensions
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($image_ext), $allowed_exts)) {
            $errors[] = "Ảnh phải có định dạng: jpg, jpeg, png, gif.";
        }

        if ($image_size > 100 * 1024 * 1024) {  // Giới hạn ảnh dưới 100MB
            $errors[] = "Ảnh không được vượt quá 100MB.";
        }

        // If valid, move the uploaded image to the target directory
        if (empty($errors)) {
            $image_dir = 'uploads/';
            if (!is_dir($image_dir)) {
                mkdir($image_dir, 0777, true);
            }

            $image = $image_dir . time() . '.' . $image_ext;
            if (!move_uploaded_file($image_tmp, $image)) {
                $errors[] = "Không thể lưu ảnh. Vui lòng thử lại.";
            }
        }
    }

    // ** Check if the username or email already exists in the database **
    if (empty($errors)) {
        try {
            // Check for existing username
            $sql_check = "SELECT COUNT(*) FROM users WHERE username = :username";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->bindParam(':username', $teacher_username);
            $stmt_check->execute();
            $username_exists = $stmt_check->fetchColumn();

            // Check for existing email
            $sql_check_email = "SELECT COUNT(*) FROM users WHERE email = :email";
            $stmt_check_email = $pdo->prepare($sql_check_email);
            $stmt_check_email->bindParam(':email', $teacher_email);
            $stmt_check_email->execute();
            $email_exists = $stmt_check_email->fetchColumn();

            // If username or email already exists, display an error message
            $sql_check_phone = "SELECT COUNT(*) FROM users WHERE phone_number = :phone_number";
$stmt_check_phone = $pdo->prepare($sql_check_phone);
$stmt_check_phone->bindParam(':phone_number', $teacher_phone);
$stmt_check_phone->execute();
$phone_exists = $stmt_check_phone->fetchColumn();

// If username, email, or phone already exists, display an error message
if ($username_exists > 0) {
    $errors[] = "Tên đăng nhập này đã tồn tại.";
}
if ($email_exists > 0) {
    $errors[] = "Email này đã tồn tại.";
}
if ($phone_exists > 0) {
    $errors[] = "Số điện thoại này đã tồn tại.";
}

        } catch (PDOException $e) {
            $errors[] = "Lỗi khi kiểm tra tên đăng nhập hoặc email: " . $e->getMessage();
        }
    }

    // If no errors, proceed with inserting the new teacher into the database
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO users (full_name, email, username, password, role, state, created_at, gender, phone_number, address, avatar, descripteacher) 
                    VALUES (:full_name, :email, :username, :password, :role, :state, :created_at, :gender, :phone_number, :address, :avatar, :descripteacher)";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':full_name', $teacher_name);
            $stmt->bindParam(':email', $teacher_email);
            $stmt->bindParam(':username', $teacher_username);
            $stmt->bindParam(':password', $teacher_password);
            $stmt->bindValue(':role', 'Teacher');
            $stmt->bindValue(':state', 'active');
            $stmt->bindValue(':created_at', date('Y-m-d H:i:s'));
            $stmt->bindParam(':gender', $teacher_gender);
            $stmt->bindParam(':phone_number', $teacher_phone);
            $stmt->bindValue(':address', $teacher_adress);
            $stmt->bindParam(':avatar', $image);
            $stmt->bindParam(':descripteacher', $teacher_desc); // Bind trường giới thiệu

            if ($stmt->execute()) {
                $_SESSION['success'] = "Giảng viên mới đã được tạo thành công!";
                header('Location: teacher.php');
                exit;
            } else {
                $errors[] = "Có lỗi khi thêm giảng viên mới. Vui lòng thử lại sau.";
            }
        } catch (PDOException $e) {
            $errors[] = "Lỗi: " . $e->getMessage();
        }
    }

    // Show errors if any
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo '<p class="error">' . htmlspecialchars($error) . '</p>';
        }
    }
}
// if (isset($_SESSION['success'])) {
//     echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
//     unset($_SESSION['success']); // Xóa thông báo sau khi hiển thị
// }

// if (isset($_SESSION['error'])) {
//     echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
//     unset($_SESSION['error']); // Xóa thông báo sau khi hiển thị
// }
?>

<div class="content-wrapper teacher">
    <!-- Page Header -->
    <section class="content-header d-flex justify-content-between">
        <h1>Teacher</h1>
        <a class="btn btn-primary" id="btnOpenModal">+ Teacher</a>
    </section>

    <!-- Teacher Form Modal -->
    <div id="createAccountModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Tạo Tài Khoản Mới</h3>
            <form id="createAccountForm" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="teacherName" class="form-label">Họ và tên</label>
                    <input type="text" class="form-control" id="teacherName" name="teacherName"
                        placeholder="Nhập tên giảng viên" required>
                </div>
                <div class="mb-3">
                    <label for="teacherEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="teacherEmail" name="teacherEmail"
                        placeholder="Nhập email giảng viên" required>
                </div>
                <div class="mb-3">
                    <label for="teacherPassword" class="form-label">Mật khẩu</label>
                    <input type="password" class="form-control" id="teacherPassword" name="teacherPassword"
                        placeholder="Nhập mật khẩu" required>
                </div>
                <div class="mb-3">
                    <label for="teacherPhone" class="form-label">Số điện thoại</label>
                    <input type="phone" class="form-control" id="teacherPhone" name="teacherPhone"
                        placeholder="Nhập số điện thoại" required>
                </div>
                <div class="mb-3">
                    <label for="teacherUsername" class="form-label">Tên đăng nhập</label>
                    <input type="text" class="form-control" id="teacherUsername" name="teacherUsername"
                        placeholder="Nhập tên đăng nhập" required>
                </div>
                <div class="mb-3">
                    <label for="teacherAdress" class="form-label">Địa chỉ</label>
                    <input type="text" class="form-control" id="teacherAdress" name="teacherAdress"
                        placeholder="Nhập địa chỉ" required>
                </div>
                <div class="mb-3">
                    <label for="teacherDESC" class="form-label">Giới thiệu</label>
                    <input type="text" class="form-control" id="teacherDESC" name="teacherDESC"
                        placeholder="Nhập lời giới thiệu" required>
                </div>
                <div class="mb-3">
                    <label for="teacherSpecialty">Ngành:</label>
                    <select name="teacherSpecialty" id="teacherSpecialty" class="form-control" required>
                        <option value="">Chọn ngành</option>
                        <?php foreach ($fields as $field) { ?>
                        <option value="<?php echo $field['id']; ?>"><?php echo $field['name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="teacherImage" class="form-label">Hình ảnh</label>
                    <input type="file" class="form-control" id="teacherImage" name="teacherImage">
                </div>
                <div class="mb-3">
                    <label for="teacherGender">Giới tính</label>
                    <select class="form-control" id="teacherGender" name="teacherGender" required>
                        <option value="">Chọn giới tính</option>
                        <option value="male">Nam</option>
                        <option value="female">Nữ</option>
                        <option value="other">Khác</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Tạo tài khoản</button>
            </form>
        </div>
    </div>

    <!-- Teacher Table -->
    <section class="content-header d-flex justify-content-between">
        <div class="teacher-input">
            <form action="" method="GET" class="form-inline">
                <label for="">Tìm kiếm</label>
                <input type="text" name="search" id="search">
                <button><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
            <a href="teacher.php" type="button" id="btnRefresh" class="btn btn-secondary">Làm mới</a>
            <button id=" btnRefresh" class="btn btn-warning">Import</button>
        </div>

    </section>
    <hr>
    <section class="content">
        <table class="table table-hover">
            <?php
            if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']); // Xóa thông báo sau khi hiển thị
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']); // Xóa thông báo sau khi hiển thị
}
            ?>
            <?php
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo '<script>alert("' . htmlspecialchars($error) . '");</script>';
    }
}
?>
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Họ tên</th>

                    <th>Email</th>
                    <th>Tên đăng nhập</th>

                    <th>Ngày tạo</th>
                    <th>Trạng thái</th>
                    <th>Giới thiệu</th> <!-- Thêm cột Giới thiệu -->
                    <th>Cài đặt</th>
                </tr>
            </thead>
            <tbody>
                <?php
        if ($users) {
            $index = 1;
            foreach ($users as $user) {
                echo "<tr>
                        <td>{$index}</td>
                        <td>{$user['full_name']}</td>
                        
                        <td>{$user['email']}</td>
                        <td>{$user['username']}</td>
                       
                        <td>" . date('d/m/Y', strtotime($user['created_at'])) . "</td>
                        <td>{$user['state']}</td>
                        <td>{$user['descripteacher']}</td> <!-- Hiển thị giới thiệu -->
                        <td><a href='edit-Teacher.php?id={$user['id']}' class='btn btn-success'>Sửa</a></td>
                        <td><a href='delete-teacher.php?id={$user['id']}' class='btn btn-danger'>Xóa</a></td>
                    </tr>";
                $index++;
            }
        } else {
            echo "<tr><td colspan='10' class='text-center'>Không có dữ liệu giảng viên nào.</td></tr>";
        }
        ?>
            </tbody>
        </table>

    </section>
</div>
<script>
const modal = document.getElementById('createAccountModal');
const btnOpen = document.getElementById('btnOpenModal');
const btnClose = document.getElementsByClassName('close')[0];

btnOpen.onclick = function() {
    modal.style.display = 'block';
}

btnClose.onclick = function() {
    modal.style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>
<?php include 'footer.php'; ?>