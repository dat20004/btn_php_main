<?php 
session_start(); 
include 'header.php'; 
include 'connect.php'; 

$teacher_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Truy vấn lấy thông tin giảng viên và khóa học
$sql = "SELECT 
            users.full_name, 
            users.avatar, 
            courses.name AS course_name, 
            COUNT(course_enrollments.student_id) AS total_students, 
            AVG(course_feedbacks.rating) AS average_rating, 
            users.descripteacher  
        FROM 
            users
        LEFT JOIN 
            courses ON users.id = courses.teacher_id
        LEFT JOIN 
            course_enrollments ON courses.id = course_enrollments.course_id
        LEFT JOIN 
            course_feedbacks ON courses.id = course_feedbacks.course_id  
        WHERE 
            users.id = :id
        GROUP BY 
            users.id, courses.name";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $teacher_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $teacher_info = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}

// Truy vấn lấy danh sách các khóa học của giảng viên
$sql_courses = "
SELECT 
    courses.id AS course_id,
    courses.name AS course_name,
    users.full_name AS instructor_name,
    courses.fee AS course_fee,
    COUNT(course_enrollments.student_id) AS enrolled_students,
    COUNT(lessons.id) AS total_lessons,
    courses.start_date,
    courses.end_date,
    courses.image AS course_image,
    -- Tính tổng thời gian giữa start_date và end_date
    TIMESTAMPDIFF(MINUTE, courses.start_date, courses.end_date) AS total_duration_minutes,
    -- Chia số phút ra thành giờ và phút
    FLOOR(TIMESTAMPDIFF(MINUTE, courses.start_date, courses.end_date) / 60) AS total_hours,
    TIMESTAMPDIFF(MINUTE, courses.start_date, courses.end_date) % 60 AS total_minutes
FROM 
    courses
JOIN 
    users ON courses.teacher_id = users.id
LEFT JOIN 
    course_enrollments ON courses.id = course_enrollments.course_id
LEFT JOIN 
    chapters ON courses.id = chapters.course_id
LEFT JOIN 
    lessons ON chapters.id = lessons.chapter_id
WHERE 
    courses.teacher_id = :teacher_id  -- Lọc theo ID giảng viên
GROUP BY 
    courses.id, courses.name, users.full_name, courses.fee, courses.start_date, courses.end_date, courses.image
";  

try {
    $stmt_courses = $pdo->prepare($sql_courses);
    $stmt_courses->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
    $stmt_courses->execute();
    
    $courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}

// Đóng kết nối
$conn = null;
?>
<style>
.about-teacher__right_center {
    display: flex;
}
.about-teacher__right_left{
    width:60%;
}

.about-teacher__right_right img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
}
.all-courses__list {
    display: flex;
    flex-wrap: wrap;
}
.all-courses__item {
    border: 1px solid #ccc;
    border-radius: 5px;
    margin: 10px;
    padding: 10px;
    width: calc(33.333% - 20px); /* Để 3 khóa học mỗi hàng */
}
.all-courses__item img {
    max-width: 100%;
    height: auto;
}
</style>
<section class="about-teacher">
    <div class="container">
        <div class="inner-wrap">
            <div class="about-teacher__right">
                <h1>Giảng viên</h1>
                <div class="about-teacher__right_center">
                    <div class="about-teacher__right_left">
                        <?php if ($teacher_info): ?>
                            <p><?php echo htmlspecialchars($teacher_info['full_name']); ?></p>
                            <p><?php echo htmlspecialchars($teacher_info['course_name']); ?></p>
                            <ul>
                                <li>Tổng số học sinh: <?php echo htmlspecialchars($teacher_info['total_students']); ?></li>
                                <li>Đánh giá: <?php echo htmlspecialchars(number_format($teacher_info['average_rating'], 1)); ?></li>
                            </ul>
                            <p><?php echo nl2br(htmlspecialchars($teacher_info['descripteacher'])); ?></p>
                        <?php else: ?>
                            <p>Không tìm thấy thông tin giảng viên.</p>
                        <?php endif; ?>
                    </div>
                    <div class="about-teacher__right_right">
                        <?php if ($teacher_info): ?>
                            <img src="./admin/<?php echo htmlspecialchars($teacher_info['avatar']); ?>" alt="<?php echo htmlspecialchars($teacher_info['full_name']); ?>">
                        <?php endif; ?>
                    </div>
                </div>

                <h2>Các khóa học liên quan</h2>
                <div class="all-courses__list">
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $row): ?>
                            <div class="all-courses__item">
                                <a href="course-content.php?id=<?php echo urlencode($row['course_id']); ?>" class="course-link">
                                    <?php if ($row && !empty($row['course_image'])): ?>
                                        <?php 
                                        $imagePath = './admin/' . htmlspecialchars($row['course_image']); 
                                        if (file_exists($imagePath)): // Kiểm tra tồn tại của tệp hình ảnh
                                        ?>
                                            <img src="<?php echo $imagePath; ?>" alt="Course Image">
                                        <?php else: ?>
                                            <p>Hình ảnh không tồn tại.</p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p>Không có hình ảnh nào.</p>
                                    <?php endif; ?>

                                    <h2><?php echo htmlspecialchars($row['course_name']); ?></h2>
                                    <p><?php echo htmlspecialchars($row['instructor_name']); ?></p>
                                    <span class="all-courses__price">
                                        <?php echo number_format(floatval($row['course_fee']), 0, ',', '.') . 'đ'; ?>
                                    </span>

                                    <div class="all-courses__bottom">
                                        <div class="all-courses__numbers">
                                            <ul>
                                                <li class="all-courses__number">
                                                    <i class="fa-solid fa-users"></i>
                                                    <span><?php echo htmlspecialchars($row['enrolled_students']); ?></span>
                                                </li>
                                                <li class="all-courses__number">
                                                    <i class="fa-regular fa-newspaper"></i>
                                                    <span><?php echo htmlspecialchars($row['total_lessons']); ?></span>
                                                </li>
                                                <li class="all-courses__number">
                                                    <i class="fa-regular fa-clock"></i>
                                                    <span><?php echo htmlspecialchars($row['total_hours']); ?> giờ</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Không có khóa học nào liên quan.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="about-teacher__left"></div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
