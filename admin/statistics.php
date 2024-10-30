<?php 
session_start();
include 'header.php';
include '../connect.php';

try {
    // Lấy tổng số ngành học
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM majors");
    $totalMajors = $stmt->fetchColumn();

    // Lấy tổng số lượt người đăng ký học (dựa trên bảng users)
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role = 'Student'");
    $totalStudents = $stmt->fetchColumn();

    // Lấy tổng số lượt đăng ký khóa học (dựa trên bảng course_enrollments)
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM course_enrollments");
    $totalEnrollments = $stmt->fetchColumn();

    // Lấy tổng số giảng viên
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role = 'Teacher'");
    $totalInstructors = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT SUM(fee) AS total_fee FROM courses");
    $totalFee = $stmt->fetchColumn();

    // Lấy tổng số khóa học được đăng tải (dựa trên bảng courses)
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM courses");
    $totalCourses = $stmt->fetchColumn();
    
    // In kết quả ra
    // echo "<h3>Thông tin tổng quan:</h3>";
    // echo "Tổng số ngành học: $totalMajors<br>";
    // echo "Tổng số người đăng ký học: $totalStudents<br>";
    // echo "Tổng số lượt đăng ký khóa học: $totalEnrollments<br>";
    // echo "Tổng số giảng viên: $totalInstructors<br>";
    // echo "Tổng số khóa học được đăng tải: $totalCourses<br>";

} catch (PDOException $e) {
    echo "Lỗi kết nối: " . $e->getMessage();
    exit;
}
?>

<div class="content-wrapper subject">
    <section class="content-header">
        <h1>
            Thống kê tổng quan
        </h1>

    </section>

    <!-- Main content -->

    <hr class="border border-3 bg-primary">
    <div class="statistics">
        <div class="statistics-list">
            <div class="statistics-item">
                <span>Học viên</span>
                <p><i class="fa-solid fa-user"></i><?php echo $totalStudents; ?></p>
            </div>
            <div class="statistics-item">
                <span>Lượt đăng kí</span>
                <p><i class="fa-solid fa-cart-shopping"></i><?php echo  $totalEnrollments; ?></p>
            </div>
            <div class="statistics-item">
                <span>Doanh thu</span>
                <p><i class="fa-solid fa-tag"></i><?php echo  $totalFee; ?></p>
            </div>
            <div class="statistics-item">
                <span>Giảng viên</span>
                <p><i class="fa-solid fa-camera"></i><?php echo $totalInstructors; ?></p>
            </div>

        </div>

        <h2>Xếp hạng khóa học</h2>
        <form action="" method="POST" role="form">
            <select name="" id="input" required="required">
                <option value="">Theo tổng số đơn hàng</option>
            </select>
        </form>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Khóa học</th>
                    <th>Đơn hàng</th>
                    <th>Doanh thu</th>
                    <th>UserActive</th>
                    <th>Số lần học</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                </tr>
            </tbody>
        </table>

    </div>
</div>


<?php include 'footer.php' ?>