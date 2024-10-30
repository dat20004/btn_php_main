<?php
ob_start();  // Bắt đầu output buffering
session_start();
include 'header.php';
include '../connect.php';

global $pdo;

$sql = "SELECT users.id, users.full_name, users.email, users.role, users.created_at, 
        users.username, users.phone_number, users.state
        FROM users
        WHERE users.role = 'Student'";

$error = '';
$success = '';

// Lấy giá trị tìm kiếm từ form
$search_username = $_POST['search_username'] ?? ''; 
$filter_gender = $_GET['gender'] ?? '';

// Nếu có nhập từ khóa tìm kiếm, thêm điều kiện tìm kiếm vào câu lệnh SQL
if (!empty($search_username)) {
    $sql .= " AND users.full_name LIKE :search_username";
}
if (!empty($filter_gender)) {
    $sql .= " AND users.gender = :filter_gender";
}

// Chuẩn bị câu truy vấn
$stmt = $pdo->prepare($sql);

// Nếu có tìm kiếm, thêm ký tự `%` vào trước và sau giá trị tìm kiếm
if (!empty($search_username)) {
    $search_username = "%" . $search_username . "%";
    $stmt->bindParam(':search_username', $search_username, PDO::PARAM_STR);
}
if (!empty($filter_gender)) {
    $stmt->bindParam(':filter_gender', $filter_gender, PDO::PARAM_STR);
}

// Thực hiện truy vấn
$stmt->execute();

// Lấy tất cả kết quả tìm được
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download'])) {
//     // Chuẩn bị tải về CSV
//     $query = "SELECT id, full_name, email, phone_number, address, password, gender, state FROM users";
//     $stmt = $pdo->prepare($query);
//     $stmt->execute();
//     $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

//     header('Content-Type: text/csv; charset=utf-8');
//     header('Content-Disposition: attachment; filename="students.csv"');

//     $output = fopen('php://output', 'w');
//     fputcsv($output, ['STT', 'Họ và tên', 'Email', 'Số điện thoại', 'Quê quán', 'Giới tính', 'Mật khẩu', 'Trạng thái']);

//     foreach ($students as $index => $student) {
//         fputcsv($output, [
//             $index + 1,
//             $student['full_name'],
//             $student['email'],
//             $student['phone_number'],
//             $student['address'],
//             $student['gender'],
//             $student['password'],
//             $student['state']
//         ]);
//     }

//     fclose($output);
//     exit(); // Ngừng thực thi sau khi tải về
// }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $fullName = $_POST['customerName'] ?? '';
    $email = $_POST['customerEmail'] ?? '';
    $phone = $_POST['customerPhone'] ?? '';
    $username = $_POST['customerUsername'] ?? '';
    $password = $_POST['customerPassword'] ?? '';
    $password2 = $_POST['customerPassword2'] ?? '';
    $gender = $_POST['customerGender'] ?? '';
    $address = $_POST['customerAddress'] ?? '';

    $image = null;
    $errors = []; // Khởi tạo mảng lỗi

    $filter_gender = $_GET['gender'] ?? '';

    // Nếu có lọc theo giới tính
    if (!empty($filter_gender)) {
        $sql .= " AND users.gender = :filter_gender";
    }
    
    // Chuẩn bị truy vấn
    $stmt = $pdo->prepare($sql);
    
    // Nếu có tìm kiếm, thêm ký tự `%` vào trước và sau giá trị tìm kiếm
    if (!empty($filter_gender)) {
        $stmt->bindParam(':filter_gender', $filter_gender, PDO::PARAM_STR);
    }
    
    // Kiểm tra các trường bắt buộc
    if (empty($fullName) || empty($email) || empty($phone) || empty($username) || empty($password) || empty($password2) || empty($gender) || empty($address)) {
        $error = "Vui lòng điền đầy đủ thông tin.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Định dạng email không hợp lệ.";
    } elseif ($password !== $password2) {
        $error = "Mật khẩu và xác nhận mật khẩu không khớp.";
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = "Mật khẩu phải có ít nhất 8 ký tự, chứa cả chữ hoa, chữ thường và số.";
    } 
    elseif (!preg_match('/^\d{10}$/', $phone)) {
        $error = "Số điện thoại phải có đúng 10 số.";
    }
    else {
        try {
            // Kiểm tra tên người dùng hoặc email đã tồn tại chưa
            $sql_check = "SELECT * FROM users WHERE username = :username OR email = :email OR phone_number = :phone";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute(['username' => $username, 'email' => $email, 'phone' => $phone]);
            $existingUser = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                if ($existingUser['username'] == $username) {
                    $error = "Tên người dùng đã được sử dụng.";
                } elseif ($existingUser['email'] == $email) {
                    $error = "Email đã được sử dụng.";
                } elseif ($existingUser['phone_number'] == $phone) {
                    $error = "Số điện thoại đã được sử dụng.";
                }
            } else {
                // Xử lý ảnh
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                    $image_name = $_FILES['profile_image']['name'];
                    $image_tmp = $_FILES['profile_image']['tmp_name'];
                    $image_size = $_FILES['profile_image']['size'];
                    $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

                    // Allowed extensions
                    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
                    if (!in_array($image_ext, $allowed_exts)) {
                        $errors[] = "Ảnh phải có định dạng: jpg, jpeg, png, gif.";
                    }

                    if ($image_size > 100 * 1024 * 1024) {  // Giới hạn ảnh dưới 100MB
                        $errors[] = "Ảnh không được vượt quá 100MB.";
                    }

                    // Nếu hợp lệ, di chuyển ảnh tải lên tới thư mục đích
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

                // Nếu có lỗi ảnh, hiển thị thông báo lỗi
                if (!empty($errors)) {
                    foreach ($errors as $err) {
                        echo "<p style='color:red;'>$err</p>";
                    }
                } else {
                    // Hash mật khẩu
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Thêm người dùng mới vào cơ sở dữ liệu
                    $sql = "INSERT INTO users (full_name, email, username, password, role, state, created_at, gender, phone_number, address, avatar) 
                    VALUES (:full_name, :email, :username, :password, :role, :state, :created_at, :gender, :phone_number, :address, :avatar)";

                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':full_name', $fullName);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':username', $username);
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->bindValue(':role', 'Student');  // Giá trị mặc định là Student
                    $stmt->bindValue(':state', 'active');   // Giá trị mặc định là active
                    $stmt->bindValue(':created_at', date('Y-m-d H:i:s'));  // Thời gian tạo là hiện tại
                    $stmt->bindParam(':gender', $gender);
                    $stmt->bindParam(':phone_number', $phone);
                    $stmt->bindParam(':address', $address);
                    $stmt->bindParam(':avatar', $image); // Đảm bảo $image được gán đúng

                    // Thực hiện lệnh SQL
                    if ($stmt->execute()) {
                        $success = "Tài khoản và thông tin người dùng đã được tạo thành công!";
                        // Chuyển hướng lại trang để làm mới danh sách sau khi tạo tài khoản thành công
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit();  // Đảm bảo script dừng lại ngay sau khi redirect
                    } else {
                        $error = "Lỗi khi thêm tài khoản vào bảng users.";
                    }
                }
            }
        } catch (PDOException $e) {
            $error = "Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage();
        }
    }
    $pdo = null; // Đóng kết nối
}


ob_end_flush();  // Kết thúc output buffering và gửi toàn bộ dữ liệu ra trình duyệt
?>

<div id="createAccountModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span> <!-- Nút Đóng Modal -->
        <h3>Tạo Tài Khoản Mới</h3>

        <form action="" method="POST" enctype="multipart/form-data"> <!-- Thêm enctype để hỗ trợ tải file -->
            <div class="mb-3">
                <label for="customerName" class="form-label">Họ và tên</label>
                <input type="text" class="form-control" id="customerName" name="customerName"
                    placeholder="Nhập họ và tên" required>
            </div>
            <div class="mb-3">
                <label for="customerEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="customerEmail" name="customerEmail"
                    placeholder="Nhập email" required>
            </div>
            <div class="mb-3">
                <label for="customerPhone" class="form-label">Số điện thoại</label>
                <input type="tel" class="form-control" id="customerPhone" name="customerPhone"
                    placeholder="Nhập số điện thoại" required>
            </div>
            <div class="mb-3">
                <label for="customerUsername" class="form-label">Tên đăng nhập</label>
                <input type="text" class="form-control" id="customerUsername" name="customerUsername"
                    placeholder="Nhập tên đăng nhập" required>
            </div>
            <div class="mb-3">
                <label for="customerPassword" class="form-label">Mật khẩu</label>
                <input type="password" class="form-control" id="customerPassword" name="customerPassword"
                    placeholder="Nhập mật khẩu" required>
            </div>
            <div class="mb-3">
                <label for="customerPassword2" class="form-label">Xác nhận mật khẩu</label>
                <input type="password" class="form-control" id="customerPassword2" name="customerPassword2"
                    placeholder="Xác nhận mật khẩu" required>
            </div>
            <div class="mb-3">
                <label for="customerGender" class="form-label">Giới tính</label>
                <select class="form-control" id="customerGender" name="customerGender" required>
                    <option value="">Chọn giới tính</option>
                    <option value="male">Nam</option>
                    <option value="female">Nữ</option>
                    <option value="other">Khác</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="customerAddress" class="form-label">Địa chỉ</label>
                <input type="text" class="form-control" id="customerAddress" name="customerAddress"
                    placeholder="Nhập địa chỉ" required>
            </div>
            <div class="mb-3">
                <label for="profile_image">Ảnh đại diện</label><br>
                <input type="file" id="profile_image" name="profile_image" required>
            </div>
            <button type="submit" class="btn btn-primary">Đăng ký</button>
        </form>
    </div>
</div>


<!-- Display Teacher List -->
<div class="content-wrapper subject">
    <section class="content-header">
        <h1> Quản lý tài khoản</h1>
    </section>
    <div class="account">
        <ul class="nav nav-tabs__account" id="myTab">
            <li class="nav-item__account">
                <button class="nav-link__account active" onclick="showTab('image')">Danh sách</button>
            </li>

        </ul>
        <hr>
        
        <div class="tab-content__account mt-3" id="myTabContent" style="width: 100%;
    font-size: 23px;">
            <div id="image" class="tab-pane__account">
                <!-- Nút mở Modal Tạo tài khoản mới -->
                <li class="nav-item__account">
                    <button id="btnOpenModal" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#createTeacherModal" style="color:#fff">+ Tạo tài khoản</button>
                    <form action="" method="POST" role="form">
                        <div class="form-group">
                            <input type="text" name="search_username" class="" id="" placeholder="Tìm kiếm">
                            <button type="submit" class=""><i class="fa-solid fa-magnifying-glass"></i></button>
                        </div>
                    </form>
                </li>


                <!-- Nút làm mới danh sách -->
                <div class="tab-content__button">
                    <button id="btnRefresh" class="btn btn-secondary">Làm mới</button>
                    <!-- Nút lọc theo giới tính -->
                    <div class="mb-3">
                        <label for="filterGender">Lọc theo giới tính</label>
                        <select id="filterGender">
                            <option value="">Tất cả</option>
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                            <option value="other">Khác</option>
                        </select>
                        <button id="btnFilter" class="btn btn-info " style="color:#fff" >Lọc</button>
                    </div>
                    <form method="POST">
                    <button type="button" name="download" id="downloadBtn" class="btn btn-warning">Tải xuống</button>

</form>
                </div>

                <!-- Bảng danh sách tài khoản -->
                <div class="mt-3">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên tài khoản</th>
                                <th>Email</th>
                                <th>Tên đăng nhập</th>
                                <th>Số điện thoại</th>
                                <th>Ngày tạo tài khoản</th>
                                <th>Trạng thái</th>
                                <th>Cài đặt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                    if ($users) {
                        $index = 1;
                        foreach ($users as $user) { ?>
                            <tr>
                                <td><?php echo $index++; ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($user['state']); ?></td>
                                <td><a href="edit-User.php?id=<?php echo $user['id']; ?>"
                                        class="btn btn-success" style="background-color:black;">Sửa</a></td>
                                <td><a href="delete-user.php?id=<?php echo $user['id']; ?>"
                                        class="btn btn-danger">Xóa</a></td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>Không có dữ liệu giảng viên nào.</td></tr>";
                    }
                    ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.getElementById("btnOpenModal").addEventListener("click", function() {
    var modal = new bootstrap.Modal(document.getElementById('createTeacherModal'));
    modal.show();
});
document.getElementById("btnRefresh").addEventListener("click", function() {
    location.reload(); // Reload trang để cập nhật danh sách
});
document.getElementById('downloadBtn').addEventListener('click', function() {
    let csvContent = "data:text/csv;charset=utf-8,";
    const rows = document.querySelectorAll('table tbody tr');

    rows.forEach(row => {
        const cols = row.querySelectorAll('td');
        const rowData = Array.from(cols).map(col => col.textContent).join(',');
        csvContent += rowData + "\r\n";
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', 'danh_sach_sinh_vien.csv');
    document.body.appendChild(link);

    link.click();
    document.body.removeChild(link);
});
document.getElementById("btnFilter").addEventListener("click", function() {
    const selectedGender = document.getElementById("filterGender").value;
    
    // Tạo URL để gửi dữ liệu
    const url = new URL(window.location.href); // Lấy URL hiện tại
    url.searchParams.set("gender", selectedGender); // Thêm hoặc cập nhật tham số 'gender'
    
    // Chuyển hướng tới URL mới với tham số lọc
    window.location.href = url; // Chuyển hướng
});

</script>

<?php include 'footer.php'; ?>