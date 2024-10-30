<?php 
session_start();
include 'header.php'; 
include '../connect.php';

// Kiểm tra nếu giảng viên đã đăng nhập
if (!isset($_SESSION['userID'])) {
    echo "Vui lòng đăng nhập để xem thông tin.";
    exit;
}

$teacherId = $_SESSION['userID'];

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

// Xử lý tìm kiếm sinh viên
if (isset($_POST['searchQuery'])) {
    $searchQuery = $_POST['searchQuery'];

    // Truy vấn danh sách sinh viên dựa theo từ khóa tìm kiếm và ID giảng viên
    $sql = "SELECT u.id, u.full_name, u.gender, u.state, u.phone_number 
            FROM users u
            JOIN course_enrollments ce ON u.id = ce.student_id
            JOIN courses c ON ce.course_id = c.id
            WHERE u.role = 'Student' 
              AND c.teacher_id = :teacherId 
              AND u.full_name LIKE :searchQuery";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'teacherId' => $teacherId,
        'searchQuery' => '%' . $searchQuery . '%'
    ]);
} else {
    // Hiển thị tất cả sinh viên đăng ký khóa học của giảng viên
    $sql = "SELECT u.id, u.full_name, u.gender, u.state, u.phone_number 
            FROM users u
            JOIN course_enrollments ce ON u.id = ce.student_id
            JOIN courses c ON ce.course_id = c.id
            WHERE u.role = 'Student' 
              AND c.teacher_id = :teacherId";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['teacherId' => $teacherId]);
}

// Lấy danh sách sinh viên từ kết quả truy vấn
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Truy vấn danh sách khóa học
$sql_courses = "SELECT id AS course_id, name AS course_name FROM courses WHERE teacher_id = :teacher_id";
$stmt_courses = $pdo->prepare($sql_courses);
$stmt_courses->bindParam(':teacher_id', $teacherId, PDO::PARAM_INT);
$stmt_courses->execute();
$result_courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);

// Xử lý tìm kiếm câu hỏi
if (isset($_POST['searchQueryRequest'])) {
    $searchQueryRequest = $_POST['searchQueryRequest'];
    // Truy vấn danh sách câu hỏi dựa theo tên sinh viên
    $stmt_questions = $pdo->prepare("
        SELECT cq.id, 
               u.full_name AS student_name, 
               u.email, 
               cq.question, 
               cq.state, 
               cq.create_at, 
               c.name AS course_title, 
               (SELECT MAX(a.create_at) FROM answers a WHERE a.question_id = cq.id) AS last_reply_date
        FROM course_questions cq
        INNER JOIN users u ON cq.student_id = u.id
        INNER JOIN courses c ON cq.course_id = c.id
        WHERE c.teacher_id = :teacherId
          AND u.full_name LIKE :searchQuery
        ORDER BY cq.create_at DESC
    ");
    $stmt_questions->execute([
        'teacherId' => $teacherId,
        'searchQuery' => '%' . $searchQueryRequest . '%'
    ]);
} else {
    // Hiển thị tất cả câu hỏi
    $stmt_questions = $pdo->prepare("
        SELECT cq.id, 
               u.full_name AS student_name, 
               u.email, 
               cq.question, 
               cq.state, 
               cq.create_at, 
               c.name AS course_title,  
               (SELECT MAX(a.create_at) FROM answers a WHERE a.question_id = cq.id) AS last_reply_date
        FROM course_questions cq
        INNER JOIN users u ON cq.student_id = u.id
        INNER JOIN courses c ON cq.course_id = c.id
        WHERE c.teacher_id = :teacherId
        ORDER BY cq.create_at DESC
    ");
    $stmt_questions->execute(['teacherId' => $teacherId]);
}

// Lấy danh sách câu hỏi từ kết quả truy vấn
$questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC);
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
    <a href="settingPassword.php?id=<?php echo htmlspecialchars($teacherId); ?>">Đổi mật khẩu</a>
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
                                    Học Viên
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
                            

                        <?php
// Giả sử $result_courses chứa danh sách các khóa học mà giảng viên dạy
if ($result_courses) {
    echo "<ul class='course-list'>"; // Bắt đầu danh sách khóa học

    foreach ($result_courses as $row) {
        // Tạo liên kết xem bài học cho mỗi khóa học
        echo "<li><a href='manage_chapters.php?course_id=" . htmlspecialchars($row['course_id']) . "'>" . htmlspecialchars($row['course_name']) . "</a></li>";
    }

    echo "</ul>"; // Kết thúc danh sách khóa học
} else {
    echo "<p>Không có khóa học nào.</p>";
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
        <!-- Form tìm kiếm sinh viên -->
        <form method="POST">
            <div class="student-search">
                <input type="text" name="searchQuery" placeholder="Tìm kiếm học viên">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
        </form>
    </div>

    <!-- Bảng danh sách sinh viên -->
    <table class="table table-bordered mt-4">
        <thead>
            <?php
            if (!empty($students)) { // Kiểm tra nếu có kết quả
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

                // Thêm tiêu đề bảng
                echo "<tr><th>STT</th><th>Họ và Tên</th><th>Giới Tính</th><th>Trạng Thái</th><th>SDT</th></tr>";

                // Thêm nội dung bảng
                foreach ($students as $index => $row) {
                    echo "<tr>";
                    echo "<td>" . ($index + 1) . "</td>"; // Hiển thị STT tự động
                    echo "<td>" . htmlspecialchars($row["full_name"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["gender"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["state"]) . "</td>"; // Hiển thị Trạng Thái
                    echo "<td>" . htmlspecialchars($row["phone_number"]) . "</td>";
                    echo "</tr>";
                }
            } else {
                // Nếu không có dữ liệu, hiển thị hàng thông báo
                echo "<tr><td colspan='5'>Không có học viên nào đăng ký khóa học này.</td></tr>";
            }
            ?>
        </thead>
    </table>
</div>


                        <div id="students" class="sub-content mt-4" style="display: none">
                            <table class="table table-bordered">
                                <a href="answerquestion.php" class="btn btn-success"
                                    style="margin-top: 10px; text-decoration: none;margin-bottom: 20px;">
                                    Chỉnh sửa
                                </a>
                                <form method="POST">
                                    <div class="student-search">
                                        <input type="text" name="searchQueryRequest" placeholder="Tìm kiếm học viên">
                                        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                                    </div>
                                </form>
                                <thead>
                                    <tr>
                                        <th>Tên Học Viên</th>
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
                    <div id="info" class="content-section teacher-content__section" style="display: none">
                        <h2>Thông tin cá nhân</h2>
                        <div class="info-main">
                            <img src=" <?php if ($user && !empty($user['avatar'])): ?>
                                    <?php if (file_exists($imagePath)): // Kiểm tra tệp hình ảnh có tồn tại ?>
                                    <?php echo $imagePath; ?>" class="avatar-teacher" alt="">
                            <?php else: ?>
                            <p>Hình ảnh không tồn tại.</p>
                            <?php endif; ?>
                            <?php else: ?>
                            <p>Không có hình ảnh nào.</p>
                            <?php endif; ?>

                            <form action="" method="POST" role="form">
                                <div class="form-group">
                                    <label for="name">Tên: </label>
                                    <input type="text" name="name" id="name" class="form-control"
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
                                <a href="updateprofile.php" class="btn btn-success"
                                    style="margin-top: 10px; text-decoration: none;">
                                    Chỉnh sửa
                                </a>
                            </form>
                        </div>
                    </div>

                    <!-- Đổi mật khẩu -->
                    <div id="password" class="content-section" style="display: none">
                        <h2>Đổi mật khẩu</h2>
                       

                        

                    </div> 

                    <script>
                    // Hàm để reset form (nếu cần)
                    function resetForm() {
                        document.querySelector('.change-password').reset();
                    }

                    
                    </script>


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