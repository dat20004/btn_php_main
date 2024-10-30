<?php 
include 'header.php';
include 'connect.php';  // Đảm bảo tệp kết nối PDO

// Lấy ID của khóa học từ URL
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Kiểm tra nếu ID hợp lệ
if ($course_id > 0) {
    // Câu lệnh SQL để lấy chi tiết khóa học bao gồm giảng viên, thời gian tạo và đánh giá
    $sql = "
    SELECT 
        courses.id, 
        courses.name, 
        courses.description, 
        courses.start_date, 
        users.full_name AS instructor_name, 
        users.id AS teacher_id,
        users.avatar AS instructor_avatar,  -- Lấy ảnh đại diện giảng viên
        majors.id AS major_id, 
        majors.name AS major_name,
        COUNT(DISTINCT chapters.id) AS total_chapters, 
        COUNT(DISTINCT lessons.id) AS total_lessons, 
        SUM(lessons.duration) AS total_duration_minutes, 
        FLOOR(SUM(lessons.duration) / 60) AS total_hours, 
        SUM(lessons.duration) % 60 AS total_minutes, 
        AVG(course_feedbacks.rating) AS average_rating,  
        COUNT(course_feedbacks.id) AS total_reviews,     
        COUNT(course_enrollments.student_id) AS total_enrolled_students, -- Tính tổng số sinh viên đăng ký
        COUNT(DISTINCT courses.id) AS total_courses_taught -- Tính tổng số khóa học giảng viên dạy
    FROM 
        courses
    JOIN 
        majors ON courses.major_id = majors.id
    LEFT JOIN 
        users ON courses.teacher_id = users.id 
    LEFT JOIN 
        course_feedbacks ON courses.id = course_feedbacks.course_id 
    LEFT JOIN 
        chapters ON courses.id = chapters.course_id  
    LEFT JOIN 
        lessons ON chapters.id = lessons.chapter_id  
    LEFT JOIN 
        course_enrollments ON courses.id = course_enrollments.course_id 
    WHERE 
        courses.id = :id
    GROUP BY 
        courses.id";  

    $stmt = $pdo->prepare($sql);  
    $stmt->bindParam(':id', $course_id, PDO::PARAM_INT);
    $stmt->execute();
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Kiểm tra xem khóa học có tồn tại không
    if (!$course) {
        echo "<p>Khóa học không tồn tại hoặc ID không hợp lệ.</p>";
        exit;
    }

    // Lấy ID giảng viên từ kết quả truy vấn
    $giangvien_id = $course['teacher_id'];

    // Truy vấn để lấy mô tả giảng viên
    $sql_teacher = "SELECT descripteacher FROM users WHERE id = :id";
    $stmt_teacher = $pdo->prepare($sql_teacher);
    $stmt_teacher->bindParam(':id', $giangvien_id, PDO::PARAM_INT);
    $stmt_teacher->execute();
    $teacher = $stmt_teacher->fetch(PDO::FETCH_ASSOC);
    
    // Tính giờ và phút từ tổng thời gian
    $total_duration_minutes = isset($course['total_duration_minutes']) ? $course['total_duration_minutes'] : 0; 
    $total_hours = floor($total_duration_minutes / 60); 
    $total_minutes = $total_duration_minutes % 60; 

    // Lấy danh sách chương cho khóa học
    $sql_chapters = "SELECT id, title FROM chapters WHERE course_id = :course_id"; 
    $stmt_chapters = $pdo->prepare($sql_chapters);
    $stmt_chapters->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt_chapters->execute();
    $chapters = $stmt_chapters->fetchAll(PDO::FETCH_ASSOC);

    // Lấy nội dung bài học từ bảng lessons
    $sql_lessons = "SELECT content FROM lessons 
                    WHERE chapter_id IN (SELECT id FROM chapters WHERE course_id = :course_id)"; 
    $stmt_lessons = $pdo->prepare($sql_lessons);
    $stmt_lessons->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt_lessons->execute();
    $lessons = $stmt_lessons->fetchAll(PDO::FETCH_ASSOC);

    // Lấy nội dung từ mảng bài học
    $contents = array_column($lessons, 'content');

    // Truy vấn để lấy đánh giá của khóa học
    $sql_reviews = "
    SELECT 
        cf.feedback,
        cf.rating,
        cf.feedback_date,
        u.full_name AS student_name,
        u.avatar AS student_avatar
    FROM 
        course_feedbacks cf
    JOIN 
        users u ON cf.student_id = u.id
    WHERE 
        cf.course_id = :course_id
    ORDER BY 
        cf.feedback_date DESC"; // Sắp xếp theo thời gian tạo gần nhất

    $stmt_reviews = $pdo->prepare($sql_reviews);
    $stmt_reviews->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt_reviews->execute();
    $reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);

    // Hiển thị thông tin khóa học
    ?>
    <div class="course-details">
        <h2><?php echo htmlspecialchars($course['name']); ?></h2>
        <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
        <p>Giảng viên: <strong><?php echo htmlspecialchars($course['instructor_name']); ?></strong></p>
        <?php if (!empty($course['instructor_avatar'])): ?>
            <img src="<?php echo htmlspecialchars('./uploads/' . $course['instructor_avatar']); ?>" alt="Instructor Avatar" class="rounded-circle">
        <?php else: ?>
            <img src="https://via.placeholder.com/100" alt="Instructor Avatar" class="rounded-circle">
        <?php endif; ?>
        <p>Thời gian khóa học: <?php echo $total_hours . " giờ " . $total_minutes . " phút"; ?></p>
        <p>Tổng số chương: <?php echo $course['total_chapters']; ?></p>
        <p>Tổng số bài học: <?php echo $course['total_lessons']; ?></p>
        <p>Đánh giá trung bình: <?php echo round($course['average_rating'], 1); ?></p>
        <p>Tổng số đánh giá: <?php echo $course['total_reviews']; ?></p>
        <p>Tổng số sinh viên đăng ký: <?php echo $course['total_enrolled_students']; ?></p>
    </div>

    <div class="reviews">
        <h3>Đánh giá từ sinh viên</h3>
        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <div class="d-flex justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="mr-2">
                                <img src="<?php echo !empty($review['student_avatar']) ? htmlspecialchars('./uploads/' . $review['student_avatar']) : 'https://via.placeholder.com/40'; ?>" 
                                     alt="Avatar" class="rounded-circle" />
                            </div>
                            <div>
                                <strong><?php echo htmlspecialchars($review['student_name']); ?></strong>
                                <span class="text-muted">• <?php echo date('d/m/Y', strtotime($review['feedback_date'])); ?></span>
                            </div>
                        </div>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($review['feedback'])); ?></p>
                    <p>Đánh giá: <?php echo str_repeat('⭐', $review['rating']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Chưa có đánh giá nào cho khóa học này.</p>
        <?php endif; ?>
    </div>

    <?php
} 
?>
