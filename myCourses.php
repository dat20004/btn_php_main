<?php
session_start();
include 'header.php';
include 'connect.php';


// Lấy user ID từ session
$user_id = $_SESSION['userID'] ?? null;

// Kiểm tra nếu người dùng chưa đăng nhập
if (!$user_id) {
    echo "Vui lòng đăng nhập để xem các khóa học của bạn.";
    exit;
}

try {
    // Lấy danh sách khóa học của người dùng
    $sql_courses = "
        SELECT courses.*, users.full_name AS instructor_name, 
               IFNULL(course_completion.completion_rate, 0) AS completion_rate
        FROM courses
        JOIN users ON courses.teacher_id = users.id
        LEFT JOIN course_completion ON courses.id = course_completion.course_id AND course_completion.student_id = :user_id
        WHERE courses.id IN (SELECT course_id FROM course_enrollments WHERE student_id = :user_id)";
    
    $stmt_courses = $pdo->prepare($sql_courses);
    $stmt_courses->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_courses->execute();
    $courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khóa Học Của Tôi</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h3>Khóa học của tôi</h3>
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="all-courses-tab" data-bs-toggle="tab" data-bs-target="#all-courses" type="button">Tất cả khóa học</button>
        </li>
        
    </ul>

    <div class="tab-content my-profile__courses mt-3" id="myTabContent">
        <!-- Tab Tất cả khóa học -->
        <div class="tab-pane fade show active" id="all-courses" role="tabpanel">
            <?php foreach ($courses as $course): ?>
                <div class="card course-card mb-4">
                    <div class="row no-gutters">
                        <div class="col-md-4">
                            <img src="./admin/<?php echo htmlspecialchars($course['image']); ?>" class="card-img" alt="Course Image">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($course['name']); ?></h5>
                                <p class="card-text">Giảng viên: <?php echo htmlspecialchars($course['instructor_name']); ?></p>
                                <p class="card-text">Giá: <?php echo number_format((float)$course['fee'], 0, ',', '.'); ?> VNĐ</p>
                                <div class="progress my-3">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $course['completion_rate']; ?>%;" aria-valuenow="<?php echo $course['completion_rate']; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo $course['completion_rate']; ?>%
                                    </div>
                                </div>
                                <a href="chitietbaihoc.php?id=<?php echo $course['id']; ?>" class="btn btn-primary w-100">Bắt đầu khóa học</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Tab Chưa hoàn thành -->
        <div class="tab-pane fade" id="incomplete" role="tabpanel">
            <?php 
            $incompleteCourses = array_filter($courses, fn($course) => $course['completion_rate'] < 100);
            if (empty($incompleteCourses)) {
                echo "<p>Bạn không có khóa học nào chưa hoàn thành.</p>";
            } else {
                foreach ($incompleteCourses as $course) { 
                    include 'course_card_template.php'; // Có thể tách template thẻ khóa học ra file riêng
                }
            } ?>
        </div>

        <!-- Tab Đã hoàn thành -->
        <div class="tab-pane fade" id="completed" role="tabpanel">
            <?php 
            $completedCourses = array_filter($courses, fn($course) => $course['completion_rate'] == 100);
            if (empty($completedCourses)) {
                echo "<p>Bạn không có khóa học nào đã hoàn thành.</p>";
            } else {
                foreach ($completedCourses as $course) { 
                    include 'course_card_template.php';
                }
            } ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
