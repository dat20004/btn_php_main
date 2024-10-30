<?php
session_start();

include 'header.php';
include 'connect.php';

// Truy vấn lấy danh sách học viên xuất sắc
$sql = "SELECT users.full_name, users.avatar, course_completion.completion_rate
        FROM users
        JOIN course_completion ON users.id = course_completion.student_id
        WHERE course_completion.completion_rate >= 90";

$stmt = $pdo->prepare($sql);
$stmt->execute();

// Lưu danh sách sinh viên xuất sắc vào mảng
$excellentStudents = $stmt->fetchAll(PDO::FETCH_ASSOC); 

// Truy vấn lấy đánh giá từ học viên
$feedback_query = "
SELECT 
    users.full_name AS reviewer_name,
    users.avatar AS reviewer_avatar,  -- Lấy ảnh của người đánh giá
    courses.name AS course_name,
    course_feedbacks.feedback AS feedback_content
FROM 
    course_feedbacks
JOIN 
    users ON course_feedbacks.student_id = users.id
JOIN 
    courses ON course_feedbacks.course_id = courses.id
";

try {
    $stmt = $pdo->prepare($feedback_query);
    $stmt->execute();
    
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>

<section class="achievement">
    <div class="inner-wrap">
        <div class="container">
            <div class="achievement-bottom">
                <h1 class="achievement-bottom__title">Các học viên xuất sắc</h1>
                <div class="achievement-bottom__info">
                    <div class="achievement-left">
                        <p>Thành <span>tích</span></p>
                        <p class="achievement-left__desc">Với sự cố gắng không ngừng bạn đã đạt thành tích xuất sắc! Sự nỗ lực của bạn là tấm gương sáng cho mọi người noi theo. Tiếp tục phát huy nhé!</p>
                        <div class="achievement-left__bottom">
                            <div class="achievement-bottom__left">
                                <span>365+</span>
                                <p>Học viên tốt nghiệp với thành tích <span>xuất sắc</span></p>
                            </div>
                            <div class="achievement-bottom__right">
                                <span>700+</span>
                                <p>Học viên tốt nghiệp với thành tích <span>giỏi</span> trở lên</p>
                            </div>
                        </div>
                    </div>
                    <div class="achievement-right">
                        <?php
                        // Nếu có sinh viên xuất sắc
                        if (!empty($excellentStudents)) {
                            // Duyệt qua từng sinh viên
                            foreach ($excellentStudents as $student) {
                                echo '<img src="./admin/' . htmlspecialchars($student["avatar"]) . '" alt="' . htmlspecialchars($student["full_name"]) . '">';
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

<section class="feel-student">
    <div class="container">
        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
            <!-- Các nút chuyển slide -->
            <div class="carousel-indicators">
                <?php if (!empty($feedbacks)): ?>
                    <?php foreach ($feedbacks as $index => $feedback): ?>
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Nội dung các slide -->
            <div class="carousel-inner">
                <?php if (!empty($feedbacks)): ?>
                    <?php foreach ($feedbacks as $index => $feedback): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <div class="testimonial">
                                <div class="testimonial-img">
                                    <img src="./admin/<?php echo htmlspecialchars($feedback["reviewer_avatar"]); ?>" alt="<?php echo htmlspecialchars($feedback["reviewer_name"]); ?>">
                                </div>
                                <div class="testimonial-content">
                                    <h3><?php echo htmlspecialchars($feedback['reviewer_name']) . " - " . htmlspecialchars($feedback['course_name']); ?></h3>
                                    <p>"<?php echo htmlspecialchars($feedback['feedback_content']); ?>"</p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="carousel-item active">
                        <p>Không có đánh giá nào.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Nút điều khiển trái và phải -->
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
