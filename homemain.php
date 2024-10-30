<?php

include 'header.php';
include 'connect.php';

if (!isset($_SESSION['email'])) {
    
    header('Location: login.php'); 
    exit();
}

try {
    // Truy vấn để lấy danh sách ngành học yêu thích
    $stmt = $pdo->query("
        SELECT majors.id, majors.name, majors.img, COUNT(course_enrollments.student_id) AS enrollment_count
        FROM majors
        LEFT JOIN courses ON majors.id = courses.major_id
        LEFT JOIN course_enrollments ON courses.id = course_enrollments.course_id
        GROUP BY majors.id, majors.name, majors.img
        ORDER BY enrollment_count DESC
        LIMIT 4
    ");

    $favoriteMajors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    echo "Lỗi kết nối: " . $e->getMessage();
    exit;
}

try {
    // Truy vấn để lấy danh sách khóa học yêu thích
    $stmt = $pdo->query("
        SELECT courses.id, courses.name, courses.image, COUNT(course_enrollments.student_id) AS enrollment_count
        FROM courses
        LEFT JOIN course_enrollments ON courses.id = course_enrollments.course_id
        GROUP BY courses.id, courses.name, courses.image
        ORDER BY enrollment_count DESC
        LIMIT 4
    ");

    $favoriteCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    echo "Lỗi kết nối: " . $e->getMessage();
    exit;
}

$sql = "SELECT users.full_name, users.avatar, course_completion.completion_rate
FROM users
JOIN course_completion ON users.id = course_completion.student_id
WHERE course_completion.completion_rate >= 90";

$stmt = $pdo->prepare($sql);
$stmt->execute();

// Lưu danh sách sinh viên xuất sắc vào mảng
$excellentStudents = $stmt->fetchAll(PDO::FETCH_ASSOC); 
?>
<style>

</style>
<section>
    <div class="slogan">
        <img class="slogan-img__one" src="./images/anh1.png" alt="">
        <h1 class="slogan-title">FastLearn</h1>
        <h3 class="slogan-title__two">Nhanh chóng, Hiệu quả, Vươn xa</h3>
        <p class="slogan-desc">Tiệm cận với chi thức là cách để thành công</p>
        <a class="btn btn-primary mt-5" href="all-courses.php">Khám phá các khóa học </a>
        <img class="slogan-img__two" src="./images/anh2.png" alt="">
    </div>
</section>
<section class="course">
    <div class="inner-wrap">
        <div class="container">
            <h1 class="course-title">Các ngành được yêu thích</h1>
            <div class="course-list">
                <?php foreach ($favoriteMajors as $major): ?>
                <div class="course-item">
                    <a href="chitietcourses.php?id=<?= $major['id'] ?>">
                        <!-- <img src="<?= $major['img'] ?>" alt="<?= $major['name'] ?>" style="width: 200px; height: auto;"> -->
                        <?php if (!empty( $major['img'])): ?>
                        <?php 
                                    $imagePath = './admin/' . htmlspecialchars( $major['img']); 
                                    if (file_exists($imagePath)): // Kiểm tra tồn tại của tệp hình ảnh
                                    ?>
                        <img src="<?php echo $imagePath; ?>" alt="Hình ảnh khóa học">
                        <?php else: ?>
                        <p>Hình ảnh không tồn tại.</p>
                        <?php endif; ?>
                        <?php else: ?>
                        <p>Không có hình ảnh nào.</p>
                        <?php endif; ?>
                        <p class="course-desc"><?= $major['name'] ?></p>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <a href="all-courses.php" class="course-all">Xem tất cả các ngành </a>
        </div>
    </div>
</section>
<section class="course-favorite">
    <div class="container">
        <h1>Khóa học nổi bật</h1>
        <div id="featuredCoursesCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="row">
                        <?php foreach ($favoriteCourses as $course): ?>
                        <div class="col-md-3 col-sm-6">
                            <a href="course-content.php?id=<?= $course['id'] ?>">
                                <?php if (!empty( $course['image'])): ?>
                                <?php 
                $imagePath = './admin/' . htmlspecialchars( $course['image']); 
                if (file_exists($imagePath)): // Kiểm tra tồn tại của tệp hình ảnh
                ?>
                                <img src="<?php echo $imagePath; ?>" alt="Hình ảnh khóa học" class="img-fluid">
                                <?php else: ?>
                                <p>Hình ảnh không tồn tại.</p>
                                <?php endif; ?>
                                <?php else: ?>
                                <p>Không có hình ảnh nào.</p>
                                <?php endif; ?>
                            </a>
                            <p><?= $course['name'] ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>

                </div>

            </div>

            <!-- Controls for the carousel -->
            <button class="carousel-control-prev" type="button" data-bs-target="#featuredCoursesCarousel"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#featuredCoursesCarousel"
                data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>

    <div class="container"></div>
</section>
<section class="banner">
    <div class="inner-wrap">
        <img class="banner-img" src="./images/banner.jpg" alt="">
        <p>SALE 30%</p>
        <span>4/10-20/10</span>
    </div>
</section>
<section class="achievement">
    <div class="inner-wrap">
        <div class="container">
            <div class="achievement-top">
                <h1 class="achievement-top__title">Bạn nhận được khi học ở FastLearn</h1>
                <div class="achievement-top__img">
                    <img src="./images/hoc-tap.jpg" alt="">
                    <div class="achievement-top__desc achievement-top__desc-1">Tiến trình theo dõi và đánh giá</div>
                    <div class="achievement-top__desc achievement-top__desc-2">Kiến thức và kỹ năng thực chiến</div>
                    <div class="achievement-top__desc achievement-top__desc-3">Thời gian học tập linh hoạt</div>
                    <div class="achievement-top__desc achievement-top__desc-4">Phương pháp học tiên tiến</div>
                </div>
            </div>
            <div class="achievement-bottom">
                <h1 class="achievement-bottom__title">Các học viên xuất sắc</h1>
                <div class="achievement-bottom__info">
                    <div class="achievement-left">
                        <p>Thành <span>tích</span></p>
                        <p class="achievement-left__desc">Với sự cố gắng không ngừng bạn đã đạt thành tích xuất sắc! Sự
                            nỗ lực của bạn là tấm gương sáng cho mọi người noi theo. Tiếp tục phát huy nhé!</p>
                        <div class="achievement-left__bottom">
                            <div class="achievement-bottom__left">
                                <span>365+</span>
                                <p>
                                    Học viên tốt nghiệp với
                                    thành tích <span>xuất sắc</span></p>
                            </div>
                            <div class="achievement-bottom__right">
                                <span>700+</span>
                                <p>
                                    Học viên tốt nghiệp với
                                    thành tích <span>giỏi</span> trở lên</p>
                            </div>
                        </div>
                    </div>
                    <div class="achievement-right">
                        <?php
    // Nếu có sinh viên xuất sắc
    if (!empty($excellentStudents)) {
        // Duyệt qua từng sinh viên
        foreach ($excellentStudents as $student) {
            echo '<img src="./admin/' . htmlspecialchars($student["avatar"]) . '" alt="' . htmlspecialchars($student["avatar"]) . '">';
        }
    } else {
        echo "Không có sinh viên nào đạt tiêu chí.";
    }
    ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include('footer.php')?>