



<?php 
session_start();
include 'header.php'; 

// Kết nối với cơ sở dữ liệu
require_once('../connect.php');

// Kiểm tra nếu người dùng đã đăng nhập
if (isset($_SESSION['userID'])) {
    $teacherId = $_SESSION['userID']; // ID giảng viên từ session

    try {
        // Truy vấn tổng số khóa học mà giảng viên đã dạy
        $stmt_courses = $pdo->prepare("SELECT COUNT(*) AS total_courses FROM courses WHERE teacher_id = :teacher_id");
        $stmt_courses->execute(['teacher_id' => $teacherId]);
        $total_courses = $stmt_courses->fetchColumn();

        // Truy vấn tổng số học sinh đã đăng ký khóa học do giảng viên dạy
        $stmt_students = $pdo->prepare("
            SELECT COUNT(DISTINCT r.student_id) AS total_students
            FROM course_enrollments r
            INNER JOIN courses c ON r.course_id = c.id
            WHERE c.teacher_id = :teacher_id
        ");
        $stmt_students->execute(['teacher_id' => $teacherId]);
        $total_students = $stmt_students->fetchColumn();
    } catch (PDOException $e) {
        die("Câu truy vấn thất bại: " . $e->getMessage());
    }
} else {
    echo "Vui lòng đăng nhập để xem thông tin thống kê.";
    exit;
}

// Lấy danh sách chương và bài học
$sql_chapters_lessons = "
    SELECT chapters.id AS chapter_id, chapters.title AS chapter_title, 
           lessons.id AS lesson_id, lessons.title AS lesson_title
    FROM chapters
    LEFT JOIN lessons ON chapters.id = lessons.chapter_id
    ORDER BY chapters.id, lessons.id
";

try {
    // Truy vấn danh sách chương và bài học
    $stmt_chapters = $pdo->prepare($sql_chapters_lessons);
    $stmt_chapters->execute();
    $result_chapters = $stmt_chapters->fetchAll(PDO::FETCH_ASSOC); // Lấy tất cả kết quả về chương và bài học

    // Truy vấn câu hỏi
    $stmt_questions = $pdo->prepare("
    SELECT cq.id, u.full_name AS student_name, u.email, cq.question, cq.state, 
           cq.create_at, 
           c.name AS course_title,  -- Lấy tên khóa học
           (SELECT MAX(a.create_at) FROM answers a WHERE a.question_id = cq.id) AS last_reply_date
    FROM course_questions cq
    INNER JOIN users u ON cq.student_id = u.id
    INNER JOIN courses c ON cq.course_id = c.id  -- Kết hợp với bảng courses
    ORDER BY cq.create_at DESC
    ");
    $stmt_questions->execute();
    $questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC); // Lấy tất cả câu hỏi
} catch (PDOException $e) {
    die("Câu truy vấn thất bại: " . $e->getMessage());
}

// Lấy thông tin người dùng
$user_id = $_SESSION['userID']; // Thêm biến user_id từ session

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        die("Người dùng không tồn tại.");
    }
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}

// Đường dẫn đến ảnh đại diện
$imagePath = '../admin/' . htmlspecialchars($user['avatar']);

// Truy vấn danh sách sinh viên
$sql = "SELECT id, full_name, gender, role, state, phone_number FROM users WHERE role = 'Student'";

// Thực thi câu truy vấn
$stmt = $pdo->prepare($sql);
$stmt->execute();

// Lấy dữ liệu sinh viên
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hàm kiểm tra tính hợp lệ của mật khẩu
function isValidPassword($password) {
    if (strlen($password) < 8) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        return false;
    }
    return true;
}

// Lấy thông tin chi tiết của người dùng
$stmt = $pdo->prepare("SELECT full_name, email, phone_number, address, descripteacher FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Kiểm tra xem có dữ liệu người dùng không
if (!$user) {
    echo "Không tìm thấy thông tin người dùng.";
    exit;
}

// Xử lý thay đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['userID'];
    $oldPassword = $_POST['oldPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Kiểm tra mật khẩu mới có khớp nhau không
    if ($newPassword !== $confirmPassword) {
        $errorMessage = "Mật khẩu mới không khớp.";
    } elseif ($oldPassword === $newPassword) {
        $errorMessage = "Mật khẩu mới không được trùng với mật khẩu cũ.";
    } else {
        // Xác minh mật khẩu cũ
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($oldPassword, $user['password'])) {
            $errorMessage = "Mật khẩu cũ không đúng.";
        } else {
            // Cập nhật mật khẩu mới
            $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bindValue(1, $hashedNewPassword);
            $stmt->bindValue(2, $user_id, PDO::PARAM_INT);
            $stmt->execute();

            // Chuyển hướng về trang đăng nhập sau khi đổi mật khẩu thành công
            header('Location: ../login.php?message=Đổi mật khẩu thành công! Hãy đăng nhập lại.');
            exit;
        }
    }
}

// Biến để theo dõi chương hiện tại (nếu cần trong phần hiển thị chương và bài học)
$last_chapter_id = null; 
?>

<style>
.card {
    background-color: #f8f9fa;
    /* Màu nền nhạt */
    border: 1px solid #007bff;
    /* Viền màu xanh */
    border-radius: 8px;
    /* Bo góc */
}

.card h5 {
    color: #007bff;
    /* Màu tiêu đề */
}

.card h2 {
    color: #333;
    /* Màu số liệu */
    font-weight: bold;
    /* In đậm */
}
</style>
<section class="teacher-main">
    <div class="container">
        <div class="inner-wrap">
            <div class="d-flex">
                <!-- Sidebar -->
                <div class="sidebar p-3">
                    <div class="menu-item p-2" onclick="showContent('personal-management')">
                        <i class="bi bi-person"></i> Quản Lý Cá Nhân
                    </div>
                    <div class="menu-item p-2" onclick="toggleAccountMenu(),showContent('info')">
                        <i class=" bi bi-person"></i> Tài Khoản
                    </div>
                    <ul class="list-group" id="account-submenu" style="display: none;">
                        <li class="list-group-item" onclick="showContent('info')">
                            Thông tin cá nhân
                        </li>
                        <li class="list-group-item" onclick="showContent('password')">
                            Đổi mật khẩu
                        </li>
                        <li class="list-group-item" onclick="showContent('logout')">
                            Đăng xuất
                        </li>
                    </ul>
                </div>

                <!-- Main content -->
                <div class="" style="flex-grow: 1">
                    <!-- Quản lý cá nhân -->
                    <div id="personal-management" class="content-section">
                        <!-- <h2 style="background-color: #69aaef;">Quản Lý Cá Nhân</h2> -->
                        <div class="row">
                            <div class="col-md-3">
                                <button class="btn btn-secondary" onclick="showSubContent('student-exchange')">
                                    Sinh Viên
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-secondary" onclick="showSubContent('documents')">
                                    Tài Liệu
                                </button>
                            </div>


                            <div class="col-md-3">
                                <button class="btn btn-secondary" onclick="showSubContent('students')">
                                    Trao đổi Học Viên
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-secondary" onclick="showSubContent('tests')">
                                    Thống kê </button>
                            </div>
                        </div>
                        <script>
                        function showSubContent(sectionId) {
                            // Lấy tất cả các phần sub-content
                            const sections = document.querySelectorAll('.sub-content');

                            // Ẩn tất cả các phần sub-content
                            sections.forEach(function(section) {
                                section.style.display = 'none';
                            });

                            // Hiển thị phần có id tương ứng với sectionId
                            const targetSection = document.getElementById(sectionId);
                            if (targetSection) {
                                targetSection.style.display = 'block';
                            }
                        }
                        </script>
                        <!-- Phần nội dung phụ -->
                        <!-- Phần tài liệu -->
                        <div id="documents" class="sub-content mt-4" style="display:none;">
                            <h2
                                style="text-align: center; padding: 20px; background-color: #69aaef; color: #fff; margin: 0;">
                                Danh Sách Chương và Bài Học
                            </h2>
                            <a href="manage_chapters.php" class="btn btn-success"
                                style="margin-top: 10px; text-decoration: none;">
                                Chỉnh sửa
                            </a>

                            <?php
    if ($result_chapters) {
        $last_chapter_id = null; // Khởi tạo biến lưu chương hiện tại

        foreach ($result_chapters as $row) {
            // Kiểm tra nếu là chương mới
            if ($last_chapter_id != $row['chapter_id']) {
                // Đóng danh sách bài học của chương trước nếu có
                if ($last_chapter_id !== null) {
                    echo "</ul>"; // Kết thúc danh sách bài học của chương trước
                }

                // Hiển thị tiêu đề chương và thêm sự kiện nhấn vào để mở/đóng bài học
                echo "<h3 style='cursor: pointer;' onclick='toggleLessons(" . $row['chapter_id'] . ")'>" . htmlspecialchars($row['chapter_title']) . "</h3>";

                // Mở danh sách bài học cho chương hiện tại và mặc định ẩn nó
                echo "<ul id='lessons_" . $row['chapter_id'] . "' class='lesson-list' style='display: none;'>";

                // Cập nhật ID chương cuối cùng đã in
                $last_chapter_id = $row['chapter_id'];
            }

            // In ra các bài học trong chương hiện tại
            if (!empty($row['lesson_title'])) { // Chỉ in ra bài học nếu có
                echo "<li>" . htmlspecialchars($row['lesson_title']) . "</li>";
            }
        }

        // Đóng danh sách bài học của chương cuối cùng
        echo "</ul>";
    } else {
        echo "<p>Không có chương và bài học nào.</p>";
    }
    ?>
                        </div>

                        <script>
                        // JavaScript để hiển thị hoặc ẩn danh sách bài học khi nhấn vào tên chương
                        function toggleLessons(chapterId) {
                            var lessonList = document.getElementById('lessons_' + chapterId);

                            // Kiểm tra trạng thái hiện tại và thay đổi display
                            if (lessonList.style.display === "none" || lessonList.style.display === "") {
                                lessonList.style.display = "block"; // Hiển thị danh sách bài học
                            } else {
                                lessonList.style.display = "none"; // Ẩn danh sách bài học khi nhấn lại
                            }
                        }
                        </script>

                        <!-- Phần hiển thị -->
                        <div id="student-exchange" class="sub-content mt-4">
                            <div class="student-list">
                                <!-- <div class="student-limit">
                                    <p>Hiển thị</p>
                                    <select name="" id="input" required="required">
                                        <option value="">1</option>
                                        <option value="">2</option>
                                        <option value="">3</option>
                                        <option value="">4</option>
                                        <option value="">5</option>
                                    </select>
                                </div> -->
                                <form method="POST">
                                    <div class="student-search">
                                        <input type="text" name="searchQuery" placeholder="Tìm kiếm sinh viên">
                                        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                                    </div>
                                </form>
                            </div>
                            <table class="table table-bordered mt-4">
                                <thead>
                                    <?php
if (count($results) > 0) {
    // Hiển thị bảng với kẻ khung
    echo "<style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>";

    
    echo "<tr><th>STT</th><th>Họ và Tên</th><th>Giới Tính</th><th>Lớp Theo Học</th><th>Trạng Thái</th><th>SDT</th></tr>";
    
    foreach ($results as $row) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["full_name"] . "</td>";
        echo "<td>" . $row["gender"] . "</td>";
        echo "<td>" . $row["role"] . "</td>";
        echo "<td>" . $row["state"] . "</td>";
        echo "<td>" . $row["phone_number"] . "</td>";
        echo "</tr>";
    }
    
    
} else {
    echo "Không có sinh viên nào.";
}
?>

                            </table>
                        </div>


                        <div id="students" class="sub-content mt-4" style="display: none">
                            <table class="table table-bordered">
                                <a href="answerquestion.php" class="btn btn-success"
                                    style="margin-top: 10px; text-decoration: none;margin-bottom: 20px;">
                                    Chỉnh sửa
                                </a>
                                <thead>
                                    <tr>
                                        <th>Tên Sinh Viên</th>
                                        <th>Email</th>
                                        <th>Nội Dung Câu Hỏi</th>
                                        <th>Khóa Học</th>
                                        <th>Trạng Thái</th>
                                        <th>Thời gian</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($questions)) : ?>
                                    <?php foreach ($questions as $question) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($question['student_name']) ?></td>
                                        <td><?= htmlspecialchars($question['email']) ?></td>
                                        <td><?= htmlspecialchars($question['question']) ?></td>
                                        <td><?= htmlspecialchars($question['course_title']) ?></td>
                                        <!-- Hiển thị khóa học -->
                                        <td><?= htmlspecialchars($question['state'] === 'Closed' ? 'Đã Đóng' : 'Mở') ?>
                                        </td>
                                        <td><?= htmlspecialchars($question['last_reply_date'] ?? 'Chưa trả lời') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php else : ?>
                                    <tr>
                                        <td colspan="7">Không có câu hỏi nào được tìm thấy.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>


                        <div id="tests" class="sub-content mt-4" style="display: none">
                            <h3>Thống kê</h3>
                            <div class="container mt-4">
                                <h2 style="background-color: #69aaef;color:#fff;">Thống Kê Giảng Viên</h2>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="card text-center">
                                            <div class="card-body">
                                                <h5>Tổng số khóa học đã dạy</h5>
                                                <h2 style="background-color: #69aaef;">
                                                    <strong><?= $total_courses ?></strong>
                                                </h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card text-center">
                                            <div class="card-body">
                                                <h5>Tổng số học sinh đã đăng ký</h5>
                                                <h2 style="background-color: #69aaef;">
                                                    <strong><?= $total_students ?></strong>
                                                </h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Bảng dữ liệu học viên -->
                    </div>

                    <!-- Quản lý tài khoản -->
                    <div id="account-management" class="content-section" style="display: none"></div>

                    <!-- Thông tin cá nhân -->
                    <div id="info" class="content-section teacher-content__section" style="display: none;">
    <h2>Thông tin cá nhân</h2>
    
    <div id="info" class="content-section teacher-content__section" style="display: none;">
    <h2>Thông tin cá nhân</h2>
    
    <div class="info-main">
        <!-- Kiểm tra và hiển thị ảnh đại diện -->
        <?php if ($user): ?>
            <?php if (!empty($user['avatar'])): ?>
                <?php 
                    $imagePath = '../admin/' . htmlspecialchars($user['avatar']); // Đường dẫn đầy đủ đến ảnh
                ?>
                <?php if (file_exists($imagePath)): // Kiểm tra nếu tệp hình ảnh tồn tại ?>
                    <img src="<?php echo $imagePath; ?>" class="avatar-teacher" alt="Avatar của giảng viên" style="width: 150px; height: 150px; border-radius: 50%;">
                <?php else: ?>
                    <p>Hình ảnh không tồn tại.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>Không có hình ảnh nào.</p>
            <?php endif; ?>

            <!-- Hiển thị thông tin cá nhân -->
            <form action="" method="POST" role="form">
                <div class="form-group">
                    <label for="name">Tên: </label>
                    <div class="form-control" style="border: 1px solid #ced4da; padding: 10px;">
                        <?php echo htmlspecialchars($user['full_name']); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email: </label>
                    <div class="form-control" style="border: 1px solid #ced4da; padding: 10px;">
                        <?php echo htmlspecialchars($user['email']); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Điện thoại: </label>
                    <div class="form-control" style="border: 1px solid #ced4da; padding: 10px;">
                        <?php echo htmlspecialchars($user['phone_number']); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Địa chỉ: </label>
                    <div class="form-control" style="border: 1px solid #ced4da; padding: 10px;">
                        <?php echo htmlspecialchars($user['address']); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Mô tả giảng viên: </label>
                    <div class="form-control" style="border: 1px solid #ced4da; padding: 10px;">
                        <?php echo isset($user['descripteacher']) ? nl2br(htmlspecialchars($user['descripteacher'])) : 'Chưa có mô tả.'; ?>
                    </div>
                </div>
                
                <!-- Nút chỉnh sửa -->
                <a href="updateprofile.php" class="btn btn-success" style="margin-top: 10px; text-decoration: none;">
                    Chỉnh sửa
                </a>
            </form>
        <?php else: ?>
            <p>Không có thông tin giảng viên.</p>
        <?php endif; ?>
    </div>
</div>






                <!-- Đổi mật khẩu -->
                <div id="password" class="content-section" style="display: none">
                    <h2>Đổi mật khẩu</h2>
                    <form class="change-password" method="POST" action="">
                        <div class="mb-3">
                            <label for="oldPassword" class="form-label">Mật khẩu hiện tại</label>
                            <div>
                                <input type="password" name="oldPassword" id="oldPassword" required />
                                <i class="fa-regular fa-eye-slash" id="toggleCurrentPassword"></i>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">Mật khẩu mới</label>
                            <div>
                                <input type="password" name="newPassword" id="newPassword" required />
                                <i class="fa-regular fa-eye-slash" id="toggleNewPassword"></i>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Xác nhận mật khẩu</label>
                            <div>
                                <input type="password" name="confirmPassword" id="confirmPassword" required />
                                <i class="fa-regular fa-eye-slash" id="toggleCheckPassword"></i>
                            </div>
                        </div>
                        <button type="button" class="btn btn-danger" onclick="resetForm()">
                            Hủy
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Lưu thay đổi
                        </button>
                    </form>

                    <!-- Hiển thị thông báo lỗi nếu có -->
                    <!-- <?php if (!empty($errorMessage)) : ?>
                    <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
                    <?php endif; ?> -->
                </div>




                <!-- Đăng xuất -->
                <div id="logout" class="content-section" style="display: none">
                    <h2>Đăng xuất</h2>
                    <p>Bạn có chắc chắn muốn đăng xuất?</p>
                    <button class="btn btn-danger">Đăng xuất</button>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>

<?php include 'footer.php'; ?>