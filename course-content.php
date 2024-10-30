<?php 
ob_start(); // Bắt đầu output buffering
session_start();    
include 'header.php';
include 'connect.php';  // Ensure this file connects to the database using PDO

// Get the course ID from the URL
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$course_enrolled = false;

try {
    $check_enrollment_stmt = $pdo->prepare("SELECT * FROM course_enrollments WHERE student_id = :user_id AND course_id = :course_id");
    $check_enrollment_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $check_enrollment_stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $check_enrollment_stmt->execute();

    if ($check_enrollment_stmt->rowCount() > 0) {
        $course_enrolled = true; // Đã có khóa học trong "Khóa học của tôi"
    }
} catch (PDOException $e) {
    echo "Lỗi kiểm tra đăng ký: " . $e->getMessage();
}

// Kiểm tra xem có yêu cầu thêm vào giỏ hàng không
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    // Lấy dữ liệu từ form
    $course_id = $_POST['course_id'];

    try {
        // Lấy thông tin khóa học từ bảng courses
        $course_stmt = $pdo->prepare("SELECT name AS course_name, fee AS course_fee, image AS course_image FROM courses WHERE id = :course_id");
        $course_stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $course_stmt->execute();

        // Kiểm tra xem khóa học có tồn tại không
        if ($course_stmt->rowCount() > 0) {
            $course = $course_stmt->fetch(PDO::FETCH_ASSOC);
            $course_name = $course['course_name'];
            $course_fee = $course['course_fee'];
            $course_image = $course['course_image'];

            // Kiểm tra nếu khóa học đã tồn tại trong giỏ hàng
            $stmt = $pdo->prepare("SELECT * FROM carts WHERE user_id = :user_id AND course_id = :course_id");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT); // Giả sử $user_id đã được xác định từ session hoặc thông tin đăng nhập
            $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Khóa học đã tồn tại trong giỏ hàng, không làm gì
                // Chuyển hướng về trang trước đó với thông báo rằng khóa học đã có trong giỏ hàng
                header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=1'); // Có thể thêm thông báo lỗi
                exit;
            } else {
                // Nếu khóa học chưa có trong giỏ hàng, thêm mới
                $insert_stmt = $pdo->prepare("INSERT INTO carts (user_id, course_id, quantity, created_at, updated_at) VALUES (:user_id, :course_id, 1, NOW(), NOW())");
                $insert_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $insert_stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
                $insert_stmt->execute();

                // Chuyển hướng về trang trước đó với thông báo thành công
                header('Location: ' . $_SERVER['HTTP_REFERER'] . '?success=1');
                exit;
            }

        } else {
            echo "Khóa học không tồn tại.";
        }

    } catch (PDOException $e) {
        echo "Lỗi: " . $e->getMessage();
    }
}




// Check if the ID is valid
if ($course_id > 0) {
    // SQL query to get course details including instructor, creation time, ratings, and major ID
    $sql = "
    SELECT 
        courses.id, 
        courses.name, 
        courses.description, 
        courses.start_date, 
        courses.fee AS course_fee,
        users.full_name AS instructor_name, 
        users.id AS teacher_id,
        courses.image AS course_image,
        users.avatar AS instructor_avatar,
        majors.id AS major_id, 
        majors.name AS major_name,
        COUNT(DISTINCT chapters.id) AS total_chapters, 
        COUNT(DISTINCT lessons.id) AS total_lessons, 
        SUM(lessons.duration) AS total_duration_minutes, 
        FLOOR(SUM(lessons.duration) / 60) AS total_hours, 
        SUM(lessons.duration) % 60 AS total_minutes, 
        AVG(course_feedbacks.rating) AS average_rating,  
        COUNT(course_feedbacks.id) AS total_reviews,     
        COUNT(course_enrollments.student_id) AS total_enrolled_students,
        COUNT(DISTINCT courses.id) AS total_courses_taught
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
        courses.id ";  

    $stmt = $pdo->prepare($sql);  
    $stmt->bindParam(':id', $course_id, PDO::PARAM_INT);
    $stmt->execute();
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if the course exists
    if (!$course) {
        echo "<p>Khóa học không tồn tại hoặc ID không hợp lệ.</p>";
        exit;
    }

    // Get the instructor ID from the query result
    $giangvien_id = $course['teacher_id'];

    // Query to get instructor's description
    $sql_teacher = "SELECT descripteacher FROM users WHERE id = :id";
    $stmt_teacher = $pdo->prepare($sql_teacher);
    $stmt_teacher->bindParam(':id', $giangvien_id, PDO::PARAM_INT);
    $stmt_teacher->execute();
    $teacher = $stmt_teacher->fetch(PDO::FETCH_ASSOC);
    
    // Calculate total hours and minutes from total duration
    $total_duration_minutes = isset($course['total_duration_minutes']) ? $course['total_duration_minutes'] : 0; 
    $total_hours = floor($total_duration_minutes / 60); 
    $total_minutes = $total_duration_minutes % 60; 

    // Get the list of chapters for the course
    $sql_chapters = "SELECT id, title FROM chapters WHERE course_id = :course_id"; 
    $stmt_chapters = $pdo->prepare($sql_chapters);
    $stmt_chapters->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt_chapters->execute();
    $chapters = $stmt_chapters->fetchAll(PDO::FETCH_ASSOC);

    // Get the lesson content from the lessons table
    $sql_lessons = "SELECT content FROM lessons 
                    WHERE chapter_id IN (SELECT id FROM chapters WHERE course_id = :course_id)"; 
    $stmt_lessons = $pdo->prepare($sql_lessons);
    $stmt_lessons->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt_lessons->execute();
    $lessons = $stmt_lessons->fetchAll(PDO::FETCH_ASSOC);

    // Get content from the lessons array
    $contents = array_column($lessons, 'content');

    // Query to get course reviews
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
        cf.feedback_date DESC";

    $stmt_reviews = $pdo->prepare($sql_reviews);
    $stmt_reviews->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt_reviews->execute();
    $reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);
    
    // Now fetch related courses based on major ID
    $major_id = $course['major_id']; // Use major_id from the course data
    $sql_related_courses = "
    SELECT 
        courses.id AS course_id,
        courses.name AS course_name,
        courses.fee AS course_fee,
        users.full_name AS instructor_name,
        courses.image AS course_image,
        COUNT(course_enrollments.student_id) AS enrolled_students,
        COUNT(DISTINCT lessons.id) AS total_lessons,
        SUM(lessons.duration) AS total_duration_minutes,
        FLOOR(SUM(lessons.duration) / 60) AS total_hours,
        SUM(lessons.duration) % 60 AS total_minutes
    FROM 
        courses
    LEFT JOIN 
        users ON courses.teacher_id = users.id 
    LEFT JOIN 
        chapters ON courses.id = chapters.course_id  
    LEFT JOIN 
        lessons ON chapters.id = lessons.chapter_id  
    LEFT JOIN 
        course_enrollments ON courses.id = course_enrollments.course_id 
    WHERE 
        courses.major_id = :major_id AND courses.id != :course_id
    GROUP BY 
        courses.id
    LIMIT 6";  // Limit the number of courses displayed

    $stmt_related_courses = $pdo->prepare($sql_related_courses);
    $stmt_related_courses->bindParam(':major_id', $major_id, PDO::PARAM_INT);
    $stmt_related_courses->bindParam(':course_id', $course_id, PDO::PARAM_INT); // Exclude the current course
    $stmt_related_courses->execute();
    $courses = $stmt_related_courses->fetchAll(PDO::FETCH_ASSOC);

} else {
    echo "<p>ID khóa học không hợp lệ.</p>";
}

ob_end_flush(); // Gửi tất cả dữ liệu đã buffered ra trình duyệt
?>


<section class="course-content__top">
    <div class="container">
        <div class="inner-wrap">
            <div class="course-content__link">
                <a href="index.php">Trang chủ <i class="fa-solid fa-angle-right"></i></a>
                <a href="chitietcourses.php?id=<?php echo htmlspecialchars($course['major_id']); ?>">
                    <?php echo htmlspecialchars($course['major_name']); ?>
                    <i class="fa-solid fa-angle-right"></i>
                </a>
                <a href=""><?php echo htmlspecialchars($course['name']); ?></a>
            </div>

            <h1 class="course-content__title"><?php echo htmlspecialchars($course['name']); ?></h1>
            <p class="course-content__desc"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
            <p class="course-content__desc">Giảng viên:
                <strong><?php echo htmlspecialchars($course['instructor_name']); ?></strong>
            </p>
            <p class="course-content__desc"><i class="fa-solid fa-circle-exclamation"></i> Thời gian cập nhật khóa học:
                <strong><?php echo date('d-m-Y', strtotime($course['start_date'])); ?></strong>
            </p>
            <div class="course-content__evaluation">
                <p><?php 
                    $average_rating = $course['average_rating'] ? $course['average_rating'] : 0; 
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $average_rating) {
                            echo "<i class='fa-solid fa-star' style='color: gold;'></i>"; // Full star
                        } else {
                            echo "<i class='fa-solid fa-star' style='color: lightgray;'></i>"; // Empty star
                        }
                    }
                ?></p>
                <p class="course-evaluation__second">
                    <?php echo "(" . ($course['total_reviews'] ? $course['total_reviews'] : 0) . " lượt đánh giá)"; ?>
                </p>
                <p class="course-evaluation__third">
                    <?php echo number_format($course['total_enrolled_students'], 0, ',', '.') . " sinh viên đăng ký"; ?>
                </p>
            </div>
            <div class="course-content__info" style="width: 400px;">
                <?php if (!empty( $course['course_image'])): ?>
                <?php 
                    $imagePath = './admin/' . htmlspecialchars( $course['course_image']); 
                    if (file_exists($imagePath)): // Kiểm tra tồn tại của tệp hình ảnh
                ?>
                <img src="<?php echo $imagePath; ?>" alt="Hình ảnh khóa học">
                <?php else: ?>
                <p>Hình ảnh không tồn tại.</p>
                <?php endif; ?>
                <?php else: ?>
                <p>Không có hình ảnh nào.</p>
                <?php endif; ?>

                <div>
    <h2><?php echo number_format(floatval($course['course_fee']), 0, ',', '.') . 'đ'; ?></h2>
    <p>Khoá học bao gồm :</p>
    <ul>
        <li><i class="fa-solid fa-check"></i>
            <p>Tổng thời gian học: <?php echo $total_hours . ' giờ ' . $total_minutes . ' phút'; ?></p>
        </li>
        <li><i class="fa-solid fa-check"></i>
            <p>Tổng số bài học: <?php echo htmlspecialchars($course['total_lessons']); ?></p>
        </li>
        <li><i class="fa-solid fa-check"></i> Hỗ trợ điện thoại, máy tính bảng và desktop</li>
    </ul>
    <div class="course-info__btn">
        <form action="" method="POST">
            <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
            <input type="hidden" name="course_name" value="<?php echo htmlspecialchars($course['name']); ?>">
            <input type="hidden" name="course_fee" value="<?php echo htmlspecialchars($course['course_fee']); ?>">
            <input type="hidden" name="course_image" value="<?php echo htmlspecialchars($course['course_image']); ?>">
            <button type="submit" name="add_to_cart" class="btn btn-primary">Thêm vào giỏ hàng</button>
        </form>
        <?php if ($course_enrolled): ?>
        <!-- Nếu khóa học đã tồn tại, hiển thị thông báo và vô hiệu hóa nút Mua ngay -->
        <button class="btn-register" disabled>Khóa học này đã có trong Khóa học của tôi</button>
    <?php else: ?>
        <!-- Nếu khóa học chưa có, cho phép mua -->
        <a href="thanhtoan.php?id=<?php echo htmlspecialchars($course['id']); ?>">
            <button class="btn-register">Mua ngay</button>
        </a>
    <?php endif; ?>
    </div>
</div>
            </div>
        </div>
    </div>
</section>


<section class="course-content__mid">
    <div class="container">
        <div class="inner-wrap">
            <div class="course-mid__study">
                <h2>Bạn sẽ học được gì</h2>
                <ul>
                    <?php if (!empty($contents)): ?>
                    <?php foreach ($contents as $content): ?>
                    <li><i class="fa-regular fa-circle-check"></i> <?php echo htmlspecialchars($content); ?></li>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <li>Không có nội dung nào cho khóa học này.</li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="course-mid__content">
                <h2>Nội dung khoá học</h2>
                <ul class="ul-li">
                    <li>
                        <i class="fa-regular fa-folder"></i> |
                        <strong><?php 
                       
                        echo htmlspecialchars($course['total_chapters']); ?></strong> Chương
                    </li>
                    <li>
                        <i class="fa-regular fa-book"></i> |
                        <strong><?php echo htmlspecialchars($course['total_lessons']); ?></strong> Bài giảng
                    </li>
                    <li>
                        <strong> <i class="fa-regular fa-clock"></i> |</strong>
                        <?php echo $total_hours . ' giờ ' . $total_minutes . ' phút'; ?>
                    </li>
                </ul>
                <div class="course-content__video">
                    <div class="accordion" id="courseAccordion">
                        <?php
    // Hiển thị thông tin từng chương
    foreach ($chapters as $chapter) {
        // Lấy tổng số bài giảng cho chương
        $sql_total_lessons = "SELECT COUNT(*) AS total_lessons, SUM(duration) AS total_duration FROM lessons WHERE chapter_id = :chapter_id";
        $stmt_total_lessons = $pdo->prepare($sql_total_lessons);
        $stmt_total_lessons->bindParam(':chapter_id', $chapter['id'], PDO::PARAM_INT);
        $stmt_total_lessons->execute();
        $lesson_info = $stmt_total_lessons->fetch(PDO::FETCH_ASSOC);
        
        $total_lessons = isset($lesson_info['total_lessons']) ? $lesson_info['total_lessons'] : 0;
        $total_duration = isset($lesson_info['total_duration']) ? $lesson_info['total_duration'] : 0;
        ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingIntro<?php echo $chapter['id']; ?>">
                                <button type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseIntro<?php echo $chapter['id']; ?>" aria-expanded="true"
                                    aria-controls="collapseIntro<?php echo $chapter['id']; ?>"
                                    class="course-video__btn">
                                    <i class="fa-solid fa-chevron-down"></i>
                                    <?php echo htmlspecialchars($chapter['title']); ?>
                                </button>
                                <?php echo $total_lessons; ?> Bài giảng •
                                <?php echo floor($total_duration / 60) . ' Giờ ' . ($total_duration % 60) . ' Phút'; ?>
                            </h2>
                            <div id="collapseIntro<?php echo $chapter['id']; ?>" class="accordion-collapse collapse"
                                aria-labelledby="headingIntro<?php echo $chapter['id']; ?>"
                                data-bs-parent="#courseAccordion">
                                <div class="accordion-body">
                                    <div id="introLectures<?php echo $chapter['id']; ?>"
                                        class="ms-3 mt-2 course-video__lectures">
                                        <!-- Hiển thị danh sách bài giảng dưới dạng <ul> -->
                                        <ul class="list-unstyled">
                                            <?php
                            // Lấy danh sách bài giảng cho chương học này
                            $sql_lessons = "SELECT * FROM lessons WHERE chapter_id = :chapter_id";
                            $stmt_lessons = $pdo->prepare($sql_lessons);
                            $stmt_lessons->bindParam(':chapter_id', $chapter['id'], PDO::PARAM_INT);
                            $stmt_lessons->execute();
                            $lessons = $stmt_lessons->fetchAll(PDO::FETCH_ASSOC);
                            
echo '<ul>'; // Mở thẻ <ul> đúng cách
  
// Vòng lặp để hiển thị danh sách bài học
foreach ($lessons as $index => $lesson): ?>
                                            <li class="lesson-item d-flex align-items-center">
                                                <!-- Checkbox để đánh dấu bài học đã hoàn thành -->


                                                <!-- Tiêu đề bài học và thời gian -->
                                                <span><?= ($index + 1) . '. ' . htmlspecialchars($lesson['title']); ?>
                                                    </span>

                                                <!-- Thời lượng của bài học -->
                                                <span
                                                    class="lesson-duration ms-auto"><?= gmdate("i:s", $lesson['duration']); ?></span>
                                            </li>
                                            <?php endforeach; // Kết thúc vòng lặp ?>

                                        </ul> <!-- Đóng thẻ <ul> đúng cách -->

                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
    }
    ?>
                    </div>


                </div>

            </div>
            <div class="course-mid__request">
                <h2>Yêu cầu</h2>
                <ul>
                    <li>Khóa học này yêu cầu bạn phải có một số kiến thức cơ bản về HTML.</li>
                </ul>
                <h2>Mô tả khoá học</h2>
                <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>

            </div>
            <div class="course-mid__teacher">
                <h2>Giảng viên</h2>
                <p><strong><?php echo htmlspecialchars($course['instructor_name']); ?></strong></p>
                <p>
                    <?php
    if ($teacher) {
        echo htmlspecialchars($teacher['descripteacher']);
    } else {
        echo 'Mô tả của giảng viên chưa có sẵn.';
    }
    ?>
                </p>

                <?php
                echo '<div class="course-teacher__info">';
                if ($course && !empty($course['instructor_avatar'])): ?>
                <?php 
                    $imagePath = './admin/' . htmlspecialchars($course['instructor_avatar']); 
                    if (file_exists($imagePath)): // Kiểm tra tồn tại của tệp hình ảnh
                    ?>
                <img src="<?php echo $imagePath; ?>" alt="Avatar của giảng viên" class="instructor-avatar">
                <?php else: ?>
                <p>Hình ảnh không tồn tại.</p>
                <?php endif; ?>
                <?php else: ?>
                <p>Không có hình ảnh nào.</p>
                <?php endif; ?>
                <?php
                echo '<ul>';
                echo '<li>' . htmlspecialchars($course['total_reviews']) . ' đánh giá</li>';
                echo '<li>' . htmlspecialchars($course['total_enrolled_students']) . ' học viên</li>';
                echo '<li>' . htmlspecialchars($course['total_courses_taught']) . ' khóa học</li>';
                echo '</ul>';
                echo '</div>';
                ?>


                <div class="course-teacher__example">
                    <span>Đánh giá khoá học</span>
                    <span><?php 
                    // Giả sử average_rating là số sao đánh giá trung bình
                    $average_rating = $course['average_rating'] ? $course['average_rating'] : 0; 
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $average_rating) {
                            echo "<i class='fa-solid fa-star' style='color: gold;'></i>"; // Sao đầy
                        } else {
                            echo "<i class='fa-solid fa-star' style='color: lightgray;'></i>"; // Sao rỗng
                        }
                    }
                    ?></span>

                </div>
            </div>
            <div class="course-mid__feedback">
                <div class="course-reviews">
                    <h3>Đánh giá</h3>
                    <?php if (!empty($reviews)): ?>
                    <ul>
                        <?php foreach ($reviews as $review): ?>
                        <li>
                            <div class="review-avatar">
                                <?php
                if ( $review && !empty($review['student_avatar'])): ?>
                                <?php 
                    $imagePath = './admin/' . htmlspecialchars( $review['student_avatar']); 
                    if (file_exists($imagePath)): // Kiểm tra tồn tại của tệp hình ảnh
                    ?>
                                <img src="<?php echo $imagePath; ?>" alt="Avatar của giảng viên"
                                    class="instructor-avatar"
                                    style="width:40px;height:40px;border-radius:50%;object-fit: cover;">
                                <?php else: ?>
                                <p>Hình ảnh không tồn tại.</p>
                                <?php endif; ?>
                                <?php else: ?>
                                <p>Không có hình ảnh nào.</p>
                                <?php endif; ?>

                            </div>
                            <div class="review-content">
                                <p><strong><?php echo htmlspecialchars($review['student_name']); ?></strong>
                                    <?php echo date('d-m-Y', strtotime($review['feedback_date'])); ?></p>
                                <p><?php echo nl2br(htmlspecialchars($review['feedback'])); ?></p>
                                <p>
                                    <?php 
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $review['rating']) {
                                                echo "<i class='fa-solid fa-star' style='color: gold;'></i>";
                                            } else {
                                                echo "<i class='fa-solid fa-star' style='color: lightgray;'></i>";
                                            }
                                        }
                                        ?>
                                </p>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <p>Chưa có đánh giá cho khóa học này.</p>
                    <?php endif; ?>
                </div>

                <form method="POST">
                    <button class="btn btn-primary mt-3" type="submit" name="show_all_feedback">Hiển thị tất cả đánh
                        giá</button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="course-content__last">
    <div class="container">
        <div class="inner-wrap">
            <h1>Các khóa học khác của <?php echo htmlspecialchars($course['major_name']); ?></h1>
            <div class="all-courses__list">
                <!-- Hiển thị các khóa học -->
                <?php if (!empty($courses)): ?>
                <?php foreach ($courses as $row): ?>
                <div class="all-courses__item">
                    <a href="course-content.php?id=<?php echo urlencode($row['course_id']); ?>" class="course-link"
                        style="text-decoration: none;">
                        <!-- Hình ảnh của khóa học -->
                        <?php if (!empty($row['course_image'])): ?>
                        <?php 
                                    $imagePath = './admin/' . htmlspecialchars($row['course_image']); 
                                    if (file_exists($imagePath)): // Kiểm tra tồn tại của tệp hình ảnh
                                    ?>
                        <img src="<?php echo $imagePath; ?>" alt="Hình ảnh khóa học">
                        <?php else: ?>
                        <p>Hình ảnh không tồn tại.</p>
                        <?php endif; ?>
                        <?php else: ?>
                        <p>Không có hình ảnh nào.</p>
                        <?php endif; ?>

                        <!-- Tên khóa học -->
                        <h2><?php echo htmlspecialchars($row['course_name']); ?></h2>

                        <!-- Tên giảng viên -->
                        <p><?php echo htmlspecialchars($row['instructor_name']); ?></p>

                        <!-- Giá khóa học -->
                        <span class="all-courses__price">
                            <?php echo number_format(floatval($row['course_fee']), 0, ',', '.') . 'đ'; ?>
                        </span>

                        <div class="all-courses__bottom">
                            <div class="all-courses__numbers">
                                <ul>
                                    <!-- Số học viên đã đăng ký khóa học -->
                                    <li class="all-courses__number">
                                        <i class="fa-solid fa-users"></i>
                                        <span><?php echo htmlspecialchars($row['enrolled_students']); ?></span>
                                    </li>

                                    <!-- Số chương học của khóa học -->
                                    <li class="all-courses__number">
                                        <i class="fa-regular fa-newspaper"></i>
                                        <span><?php echo htmlspecialchars($row['total_lessons']); ?></span>
                                    </li>

                                    <!-- Tổng thời gian khóa học -->
                                    <li class="all-courses__number">
                                        <i class="fa-regular fa-clock"></i>
                                        <span><?php echo htmlspecialchars($row['total_hours']) . ' giờ ' . htmlspecialchars($row['total_minutes']) . ' phút'; ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </a> <!-- Closing course-link anchor tag -->
                </div> <!-- Closing all-courses__item div -->
                <?php endforeach; ?>
                <?php else: ?>
                <p>Không có khoá học nào.</p>
                <?php endif; ?>
            </div> <!-- Closing all-courses__list div -->
        </div> <!-- Closing inner-wrap div -->
    </div> <!-- Closing container div -->
</section>
<?php include 'footer.php';  ?>