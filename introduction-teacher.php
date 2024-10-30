<?php
session_start(); 
include 'header.php';
include 'connect.php';

// Truy vấn lấy giảng viên nổi bật
$sql = "SELECT 
            users.id,         -- Lấy ID giảng viên
            users.full_name, 
            users.avatar, 
            courses.name AS subject,   
            COUNT(course_enrollments.student_id) AS registration_count
        FROM 
            users
        JOIN 
            courses ON users.id = courses.teacher_id
        LEFT JOIN 
            course_enrollments ON courses.id = course_enrollments.course_id
        GROUP BY 
            users.id, courses.name  
        ORDER BY 
            registration_count DESC
        LIMIT 5"; // Giới hạn lấy 5 giảng viên

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    // Kiểm tra kết quả và lưu vào biến để hiển thị
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}

// Đóng kết nối
$conn = null;
?>

<section class="introduce">
    <div class="container">
        <div class="inner-wrap">
            <h1>Lời giới thiệu</h1>
            <p>Chúng tôi rất vui mừng chào đón bạn đến với trang web giảng viên của FastLearn – nơi hội tụ của những
                người
                truyền cảm hứng và dẫn dắt học viên trên con đường học tập thành công. Tại đây, bạn sẽ tìm thấy đội ngũ
                giảng
                viên hàng đầu, những người không chỉ có bề dày kinh nghiệm và chuyên môn sâu rộng, mà còn là những người
                đam
                mê
                giáo dục và cam kết mang đến những bài giảng chất lượng nhất.</p>
        </div>
        <h2>Chúng Tôi Là Ai?</h2>
        <p>Chúng tôi tự hào về đội ngũ giảng viên của mình – những chuyên gia, nhà nghiên cứu, và thực hành nổi bật
            trong
            các
            lĩnh vực khác nhau. Họ không chỉ cung cấp kiến thức lý thuyết mà còn chia sẻ những kinh nghiệm thực tiễn quý
            giá,
            giúp học viên có cái nhìn toàn diện và áp dụng kiến thức vào thực tế.</p>
        <h2>Chúng Tôi Cung Cấp Gì?</h2>
        <ul>
            <li>
                Giáo Trình Được Cập Nhật: Các khóa học và chương trình giảng dạy của chúng tôi luôn được cập nhật theo
                xu
                hướng mới nhất và nhu cầu thực tế của thị trường.
            </li>
            <li> Hỗ Trợ Cá Nhân Hóa: Giảng viên của chúng tôi cung cấp sự hỗ trợ tận tâm, giúp bạn giải đáp thắc mắc và
                đạt
                được mục tiêu học tập của mình.</li>
            <li> Kinh Nghiệm Thực Tiễn: Những câu chuyện và bài học từ thực tế mà các giảng viên chia sẻ giúp bạn có cái
                nhìn sâu sắc và áp dụng kiến thức hiệu quả.</li>
        </ul>
        <h2>Tại Sao Chọn Chúng Tôi?</h2>
        <p>
            Chúng tôi không chỉ đào tạo, mà còn đồng hành cùng bạn trên hành trình học tập. Với sự tận tâm và nhiệt
            huyết,
            đội ngũ giảng viên của FastLearn cam kết mang lại trải nghiệm học tập tích cực và hiệu quả, giúp bạn phát
            triển
            toàn
            diện và đạt được thành công trong sự nghiệp.
        </p>
        <p>Khám phá đội ngũ giảng viên của chúng tôi ngay hôm nay và bắt đầu hành trình học tập đầy hứng khởi cùng
            FastLearn!</p>
        <div class="introduce-teacher">
            <h2>Giảng viên nổi bật</h2>
            <div class="introduce-teacher__list">
                <?php
                // Kiểm tra và hiển thị giảng viên nổi bật
                if (!empty($teachers)) {
                    foreach ($teachers as $row) {
                        echo '<a href="about-teacher.php?id=' . htmlspecialchars($row["id"]) . '">'; // Thêm ID vào đường dẫn
                        echo '    <div class="introduce-teacher__item">';
                        echo '        <img src="./admin/' . htmlspecialchars($row["avatar"]) . '" alt="' . htmlspecialchars($row["full_name"]) . '">';
                        echo '        <h3>' . htmlspecialchars($row["full_name"]) . '</h3>';
                        echo '        <p><i class="fas fa-book"></i> Ngành: ' . htmlspecialchars($row["subject"]) . '</p>'; // Hiển thị ngành học
                        echo '    </div>';
                        echo '</a>';
                    }
                } else {
                    echo '<p>Không có giảng viên nổi bật nào được tìm thấy.</p>';
                }
                ?>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
