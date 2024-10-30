<?php 
include 'header.php'; 
include 'connect.php';

// Hàm lấy khóa học nổi bật nhất của ngành học
function getTopCourseByMajor($pdo, $majorId) {
    $sql = "
        SELECT 
            courses.id AS course_id,
            courses.name AS course_name,
            courses.description AS course_description,
            users.full_name AS instructor_name,
            courses.fee AS course_fee,
            courses.image AS course_image
        FROM 
            courses
        JOIN 
            users ON courses.teacher_id = users.id
        LEFT JOIN 
            course_enrollments ON courses.id = course_enrollments.course_id
        WHERE 
            courses.major_id = :major_id
        GROUP BY 
            courses.id
        ORDER BY 
            COUNT(course_enrollments.student_id) DESC
        LIMIT 1;
    ";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':major_id', $majorId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Lấy thông tin khóa học
    } catch (PDOException $e) {
        echo "Lỗi: " . $e->getMessage();
    }
}

// Kiểm tra nếu có ID ngành học trong URL
if (isset($_GET['id'])) {
    $majorId = intval($_GET['id']); // Lấy ID ngành học từ URL và chuyển đổi sang số nguyên

    // Lấy tên ngành học từ bảng majors
    $majorSql = "SELECT name FROM majors WHERE id = :id";
    $majorStmt = $pdo->prepare($majorSql);
    $majorStmt->bindParam(':id', $majorId);
    $majorStmt->execute();
    $major = $majorStmt->fetch(PDO::FETCH_ASSOC);
    $majorStmt->closeCursor(); // Đóng con trỏ sau khi lấy tên ngành học

    // Lấy tên ngành học
    $title = !empty($major) ? $major['name'] : "Ngành không xác định"; // Nếu không tìm thấy ngành học

    // Câu lệnh SQL để lấy thông tin các khóa học của ngành học đó
    $courseSql = "
        SELECT 
            courses.id AS course_id,
            courses.name AS course_name,
            users.full_name AS instructor_name,
            courses.fee AS course_fee,
            COUNT(course_enrollments.student_id) AS enrolled_students, -- Tính tổng số học viên đã đăng ký
            COUNT(lessons.id) AS total_lessons, -- Tính tổng số bài học
            courses.start_date,
            courses.end_date,
            courses.image AS course_image,
            TIMESTAMPDIFF(MINUTE, courses.start_date, courses.end_date) AS total_duration_minutes,
            FLOOR(TIMESTAMPDIFF(MINUTE, courses.start_date, courses.end_date) / 60) AS total_hours,
            TIMESTAMPDIFF(MINUTE, courses.start_date, courses.end_date) % 60 AS total_minutes
        FROM 
            courses
        JOIN 
            users ON courses.teacher_id = users.id  -- Kết nối với bảng users để lấy tên giảng viên
        LEFT JOIN 
            course_enrollments ON courses.id = course_enrollments.course_id  -- Kết nối với bảng enrollments để đếm học viên
        LEFT JOIN 
            chapters ON courses.id = chapters.course_id  -- Kết nối với bảng chapters để đếm số chương học
        LEFT JOIN 
            lessons ON chapters.id = lessons.chapter_id  -- Kết nối với bảng lessons để đếm số bài học
        WHERE 
            courses.major_id = :major_id  -- Lọc theo ID ngành học
        GROUP BY 
            courses.id, courses.name, users.full_name, courses.fee, courses.start_date, courses.end_date, courses.image
    ";  

    // Chuẩn bị và thực thi câu lệnh SQL
    $courseStmt = $pdo->prepare($courseSql);
    $courseStmt->bindParam(':major_id', $majorId);
    $courseStmt->execute();

    // Lấy tất cả khóa học vào một mảng
    $courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);
    $courseStmt->closeCursor(); // Đóng con trỏ sau khi lấy danh sách khóa học
    
    // Lấy khóa học nổi bật nhất
    $topCourse = getTopCourseByMajor($pdo, $majorId);

    // Lấy các khóa học phổ biến
    $popularCoursesSql = "
    SELECT 
        courses.id AS course_id,
        courses.name AS course_name,
        COUNT(course_enrollments.student_id) AS enrolled_students
    FROM 
        courses
    LEFT JOIN 
        course_enrollments ON courses.id = course_enrollments.course_id
    WHERE 
        courses.major_id = :major_id
    GROUP BY 
        courses.id, courses.name
    ORDER BY 
        enrolled_students DESC
    LIMIT 6; 
    ";

    // Chuẩn bị và thực thi câu lệnh SQL
    $popularCoursesStmt = $pdo->prepare($popularCoursesSql);
    $popularCoursesStmt->bindParam(':major_id', $majorId);
    $popularCoursesStmt->execute();

    // Lấy các khóa học phổ biến vào một mảng
    $popularCourses = $popularCoursesStmt->fetchAll(PDO::FETCH_ASSOC);
    $popularCoursesStmt->closeCursor(); // Đóng con trỏ sau khi lấy danh sách khóa học phổ biến

    // Lấy giảng viên phổ biến
    $instructorsSql = "
    SELECT 
        users.id AS instructor_id,
        users.full_name AS instructor_name,
        users.avatar AS instructor_image,
        COUNT(DISTINCT courses.id) AS total_courses_taught
    FROM 
        users
    JOIN 
        courses ON users.id = courses.teacher_id
    LEFT JOIN 
        course_enrollments ON courses.id = course_enrollments.course_id
    WHERE 
        courses.major_id = :major_id
    GROUP BY 
        users.id
    ORDER BY 
        total_courses_taught DESC
    LIMIT 3; 
";

// Chuẩn bị và thực thi câu lệnh SQL
$instructorsStmt = $pdo->prepare($instructorsSql);
$instructorsStmt->bindParam(':major_id', $majorId);
$instructorsStmt->execute();

// Lấy giảng viên phổ biến vào một mảng
$instructors = $instructorsStmt->fetchAll(PDO::FETCH_ASSOC);
$instructorsStmt->closeCursor(); // Đóng con trỏ sau khi lấy danh sách giảng viên
} else {
    echo "<p>Không có ngành học nào được chọn.</p>";
    exit; // Dừng thực thi nếu không có ID ngành học
}

// Đóng kết nối (PDO tự động đóng kết nối khi hết phạm vi)
$pdo = null;
?>



<style>
.course-link {
    text-decoration: none;
    color: black;
}
</style>

<div class="details-header">
    <div class="container">
        <div class="inner-wrap">
            <a href="index.php">Trang chủ <i class="fa-solid fa-angle-right"></i></a>
            <a href=""><?php echo htmlspecialchars($title); ?></a>
        </div>
    </div>
</div>

<section class="details-top">
    <div class="container">
        <div class="inner-wrap">
            <h1 class="details-title">Các khoá học về <?php echo htmlspecialchars($title); ?></h1>
            <div class="details-featured__course">
                <h2 class="featured-course__title">Khoá học nổi bật</h2>
                <p class="featured-course__desc">Nhiều học viên thích khóa học được đánh giá cao này vì nội dung hấp dẫn
                    của nó.</p>
                <a href="course-content.php?id=<?php echo htmlspecialchars($topCourse['course_id']); ?>" style="text-decoration:none;">
                    <div class="featured-course__one" >
                        <?php if ($topCourse): ?>
                        <div class="featured-course__left">
                            <img src="./admin/<?php echo htmlspecialchars($topCourse['course_image']); ?>"
                                alt="Ảnh khóa học">
                        </div>
                        <div class="featured-course__right">
                            <h3 class="featured-course__right-title">
                                <?php echo htmlspecialchars($topCourse['course_name']); ?></h3>
                            <p class="featured-course__right-title">
                                <?php echo htmlspecialchars($topCourse['course_description']); ?></p>
                            <p class="featured-course__right-desc">Giảng viên:
                                <?php echo htmlspecialchars($topCourse['instructor_name']); ?></p>
                            <div class="featured-course__buy">
                                <p><?php echo number_format(floatval($topCourse['course_fee']), 0, ',', '.') . 'đ'; ?>
                                </p>
                                <span>Bán chạy</span>
                            </div>
                        </div>
                        <?php else: ?>
                        <p>Không có khóa học nào.</p>
                        <?php endif; ?>
                    </div>
                </a>


            </div>
            <div class="details-popular__course">
                <h1 class="details-popular__title">Khoá học phổ biến</h1>
                <div class="details-popular__link">
                    <?php if ($popularCourses): ?>
                    <?php foreach ($popularCourses as $course): ?>
                    <a href="course-content.php?id=<?php echo htmlspecialchars($course['course_id']); ?>">
                        <?php echo htmlspecialchars($course['course_name']); ?>
                    </a>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <p>Không có khoá học phổ biến nào.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="details-popular__instructors">
    <h1 class="details-instructors__title">Giảng viên phổ biến về <?php echo htmlspecialchars($title); ?></h1>
    <div class="details-instructors__list">
        <?php if ($instructors): ?>
            <?php foreach ($instructors as $instructor): ?>
                <div class="details-instructors__item">
                    <a href="about-teacher.php?id=<?php echo htmlspecialchars($instructor['instructor_id']); ?>">
                        <img src="./admin/<?php echo htmlspecialchars($instructor['instructor_image']); ?>" alt="Giảng viên">
                    </a>
                    <div>
                    <p><?php echo htmlspecialchars($instructor['instructor_name']); ?></p>
                    <p>Tổng số khóa học đang dạy: <?php echo htmlspecialchars($instructor['total_courses_taught']); ?></p> <!-- Hiển thị tổng số khóa học -->
                    </div>
                   

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Không có giảng viên nổi bật nào.</p>
        <?php endif; ?>
    </div>
</div>

        </div>
    </div>
</section>

<section class="details-bottom">
    <div class="container">
        <div class="inner-wrap">
            <div class="all-courses">
                <h1 class="all-courses__title">Tất cả các khoá học</h1>
                <div class="all-courses__list">
                    <!-- Hiển thị các khóa học -->
                    <?php if (!empty($courses)): ?>
                    <?php foreach ($courses as $row): ?>
                    <div class="all-courses__item">
                        <a href="course-content.php?id=<?php echo urlencode($row['course_id']); ?>" class="course-link">
                            <!-- Hình ảnh của khóa học -->
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
                                            <span><?php echo $row['enrolled_students']; ?></span>
                                        </li>

                                        <!-- Số chương học của khóa học -->
                                        <li class="all-courses__number">
                                            <i class="fa-regular fa-newspaper"></i>
                                            <span><?php echo $row['total_lessons']; ?></span>
                                        </li>

                                        <!-- Tổng thời gian khóa học -->
                                        <li class="all-courses__number">
                                            <i class="fa-regular fa-clock"></i>
                                            <span><?php echo $row['total_hours'] . ' giờ ' . $row['total_minutes'] . ' phút'; ?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <p>No courses available.</p>
                    <?php endif; ?>
                </div>

                <!-- Phân trang (nếu cần) -->
                <div class="paginates">
                    <button><i class="fa-solid fa-chevron-left"></i></button>
                    <ul>
                        <li>
                            <a href="#">1</a>
                            <a href="#">2</a>
                            <a href="#">3</a>
                            <a href="#">4</a>
                            <a href="#">5</a>
                        </li>
                    </ul>
                    <button><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>