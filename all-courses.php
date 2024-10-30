<?php 
include 'header.php'; 
include 'connect.php';

// Lấy danh sách ngành học
$stmt = $pdo->prepare("SELECT id, name, img FROM majors");
$stmt->execute();

// Lấy tất cả dữ liệu ngành học
$majors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kiểm tra dữ liệu ngành học
if (empty($majors)) {
    echo "<p>Không có ngành học nào.</p>";
}
?>

<section class="all-courses">
    <div class="container">
        <div class="inner-warp">
            <h1 class="all-courses__title">Khám phá tất cả các ngành</h1>
            <div class="all-courses__list">
                <?php 
                foreach ($majors as $major): ?>
                    <div class="all-courses-item">
                    <a href="chitietcourses.php?id=<?php echo htmlspecialchars($major['id']); ?>" style="text-decoration: none;">
                            <?php 
                            // Tạo đường dẫn hình ảnh từ thư mục uploads
                            $imgPath = './admin/' . htmlspecialchars($major['img']);
                            
                            // Kiểm tra nếu hình ảnh hợp lệ
                            if (!empty($imgPath) && @getimagesize($imgPath)) {
                                echo "<img src='{$imgPath}' alt='" . htmlspecialchars($major['name']) . "' />";
                            } else {
                                echo "<img src='path/to/default-image.jpg' alt='Hình ảnh không khả dụng' />";
                            }
                            ?>
                            <h2>
                                <?php echo htmlspecialchars($major['name']); ?>
                                <i class="fa-solid fa-angle-right"></i>
                            </h2>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="all-courses--desc">
    <div class="container">
        <div class="inner-wrap">
            <div class="courses-desc__left">
                <p><i class="fas fa-star"></i> Tham gia FastLearn ngay hôm nay</p>
            </div>
            <div class="courses-desc__right">
                <p><i class="fas fa-envelope"></i> Đăng ký tài khoản FutureLearn để nhận các đề xuất khóa học và ưu đãi được cá nhân hóa gửi thẳng đến hộp thư của bạn.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
