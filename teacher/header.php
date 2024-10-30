<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Chỉ khởi động session nếu chưa có
}
include '../connect.php';  // Kết nối tới cơ sở dữ liệu

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['userID'])) {
    header('Location: ../login.php'); // Chuyển hướng đến trang đăng nhập nếu chưa đăng nhập
    exit;
}

try {
    // Lấy ID người dùng từ session
    $user_id = $_SESSION['userID'];

    // Truy vấn để lấy danh sách câu hỏi liên quan đến khóa học mà giảng viên đang dạy
    $stmt = $pdo->prepare("
        SELECT users.full_name, 
               courses.name AS course_title, 
               course_questions.question, 
               course_questions.create_at
        FROM course_questions
        JOIN users ON course_questions.student_id = users.id
        JOIN courses ON course_questions.course_id = courses.id
        WHERE courses.teacher_id = :teacherId
        ORDER BY course_questions.create_at DESC
    "); 
    $stmt->execute(['teacherId' => $user_id]); // Lấy danh sách câu hỏi dựa theo ID giảng viên
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi khi lấy danh sách câu hỏi: " . $e->getMessage();
}

// Hàm để chuyển đổi thời gian
function timeAgo($datetime) {
    $time_ago = strtotime($datetime);
    $current_time = time();
    $time_difference = $current_time - $time_ago;

    if ($time_difference < 1) return 'Vừa mới';

    $seconds = $time_difference;
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);

    if ($seconds < 60) {
        return "$seconds giây trước";
    } else if ($minutes < 60) {
        return "$minutes phút trước";
    } else if ($hours < 24) {
        return "$hours giờ trước";
    } else if ($days < 7) {
        return "$days ngày trước";
    } else if ($weeks < 4.3) {
        return "$weeks tuần trước";
    } else if ($months < 12) {
        return "$months tháng trước";
    } else {
        return "$years năm trước";
    }
}
$timeout_duration = 300; 

if (isset($_SESSION['last_activity'])) {
    $time_inactive = time() - $_SESSION['last_activity'];
    if ($time_inactive > $timeout_duration) {
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

$_SESSION['last_activity'] = time();
// Truy vấn để lấy thông tin người dùng
$query = "SELECT * FROM users WHERE id = ?"; 
$stmt = $pdo->prepare($query);
$stmt->bindParam(1, $user_id, PDO::PARAM_INT); // Sử dụng PDO để bind param
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC); // Lấy thông tin người dùng
$stmt->closeCursor(); // Đóng cursor để giải phóng kết nối

// Kiểm tra nếu người dùng tồn tại và có ảnh đại diện
$imagePath = '../admin/' . htmlspecialchars($user['avatar']);

// Kiểm tra vai trò của người dùng
if ($user['role'] !== 'Teacher') {  // Thay 'Admin' bằng vai trò bạn muốn kiểm tra
    header('Location: ../login.php'); // Chuyển hướng đến trang không được phép nếu vai trò không đúng
    exit; 
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FastLearn</title>
    <link rel="icon" href="../images/logoweb.png" type="image/png">
    <link rel="stylesheet" href="./css/reset.css">
    <link rel="stylesheet" href="./css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">

    <script src="https://kit.fontawesome.com/533aad8d01.js" crossorigin="anonymous"></script>
    <style>
        /* Định dạng chung cho các mục trong dropdown */
        .avatar-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .dropdown-item {
            padding: 10px 15px;
            font-size: 16px;
            color: #333;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Định dạng icon trong mục "Đăng xuất" */
        .dropdown-item i {
            margin-left: 8px;
            font-size: 18px;
            transition: color 0.3s ease;
        }

        /* Hiệu ứng khi hover vào "Đăng xuất" */
        .dropdown-item:hover {
            background-color: #f0f0f0;
            color: #007bff;
        }

        .dropdown-item:hover i {
            color: #007bff;
        }

        .dropdown-menu {
            max-width: 400px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 10px;
        }

        .text-muted {
            color: #6c757d;
        }

        strong {
            color: #333;
        }
    </style>

</head>
<header class="header-teacher">
    <div class="container">
        <div class="inner-wrap">
            <div class="teacher-img">
                <a href="index.php"><img src="../images/logoweb.png" alt=""></a>
            </div>
            <div class="teacher-search">
                <form action="" method="POST" role="form">
                    <div class="form-group">
                        <input type="text" class="form-control" id="" placeholder="Tìm kiếm">
                    </div>
                    <button type="submit" class=""><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
            </div>
            <div class="teacher-noti">
                <div class="header-buttons d-flex align-items-center" style="gap:1px;">
                    <!-- Icon thông báo -->
                    <div class="dropdown">
                        <button class="btn btn-icon dropdown-toggle" type="button" id="notificationButton"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-bell"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="notificationButton">
                            <?php foreach ($questions as $question): ?>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <strong><?php echo htmlspecialchars($question['full_name']); ?></strong> <br>
                                    Hỏi về bài học: <?php echo htmlspecialchars($question['course_title']); ?> <br>
                                    Câu hỏi: <?php echo htmlspecialchars($question['question']); ?> <br>
                                    <small class="text-muted">
                                        Thời gian gửi: <?php echo timeAgo($question['create_at']); ?>
                                    </small>
                                </a>
                            </li>
                            <?php endforeach; ?>
                            <hr>
                            <li><a class="dropdown-item" href="answerquestion.php">Đọc tất cả thông báo</a></li>
                        </ul>
                    </div>

                    <!-- Dropdown avatar -->
                    <div class="dropdown">
                        <button class="btn btn-avatar dropdown-toggle" type="button" id="dropdownMenuButton"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if ($user && !empty($user['avatar'])): ?>
                            <?php if (file_exists($imagePath)): ?>
                            <img src="<?php echo $imagePath; ?>" alt="" class="avatar-image">
                            <?php else: ?>
                            <p>Hình ảnh không tồn tại.</p>
                            <?php endif; ?>
                            <?php else: ?>
                            <p>Không có hình ảnh nào.</p>
                            <?php endif; ?>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="margin-top: 15px;">
                            <li><a class="dropdown-item" href="logout.php">Đăng xuất <i class="fa-solid fa-sign-out-alt"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<body>
    <script>
    $(document).ready(function() {
        // Khi nhấn vào nút thông báo, hiển thị hoặc ẩn menu
        $('#notificationButton').on('click', function() {
            // Ẩn menu giỏ hàng nếu nó đang mở
            $('#cartButton').next('.dropdown-menu').removeClass('show');
            // Toggle menu thông báo
            $(this).next('.dropdown-menu').toggleClass('show');
        });

        // Khi nhấn ra ngoài các nút thông báo và giỏ hàng, ẩn các menu xổ xuống
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                $('.dropdown-menu').removeClass('show');
            }
        });
    });
    </script>
</body>
</html>
