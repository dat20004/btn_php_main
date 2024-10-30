<?php 
session_start();
include 'header.php';
include '../connect.php';

// Lấy danh sách bài đăng và thông tin tác giả
$sql = "SELECT p.id, p.title, p.content, p.created_at, u.full_name 
        FROM posts p 
        JOIN users u ON p.admin_id = u.id";

$stmt = $pdo->query($sql);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);


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

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Thống kê
        </h1>
    </section>
    <div class="col-md-10 content-area" style="width:100%">
        <div class="row mt-4 index">
            <div class="col-md-3">
                <div class="stats-box p-5 rounded thognke" >
                    <h4><?php echo  $totalEnrollments; ?></h4>
                    <p>Lượt mua</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-box bg-success p-5 rounded">
                    <h4><?php echo $totalStudents; ?></h4>
                    <p>Người học</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-box bg-warning p-5 rounded">
                    <h4><?php echo $totalInstructors; ?></h4>
                    <p>Giảng viên</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-box bg-primary p-5 rounded">
                    <h4><?php echo $totalCourses; ?></h4>
                    <p>Khóa học</p>
                </div>
            </div>
        </div>

        <!-- Posts Management -->
        <div class="admin-main-content" style="width:100%;font-size: 20px;">
            <h1 style="font-size: 27px;">Quản lý Bài đăng</h1>
            <?php
// Hiển thị thông báo nếu có
if (isset($_SESSION['message'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['message'] . "</div>";
    unset($_SESSION['message']); // Xóa thông báo sau khi hiển thị
}

if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
    unset($_SESSION['error']); // Xóa thông báo sau khi hiển thị
}
?>

            <!-- Nút thêm bài đăng mới -->
            <a href="add_post.php"><button class="add-post-button">
                    Thêm bài đăng mới
                </button></a>

            <!-- Bảng danh sách bài đăng -->
            <table class="posts-table">
                <thead>
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Tác giả</th>
                        <th>Nội dung</th>
                        <th>Ngày đăng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                        <td><?php echo htmlspecialchars($post['full_name']); ?></td> <!-- Hiển thị tên tác giả -->
                        <td><?php echo htmlspecialchars($post['content']); ?></td>
                        <td><?php echo htmlspecialchars($post['created_at']); ?></td>
                        <td>
                            <a href="update_post.php?id=<?php echo $post['id']; ?>">Sửa</a>
                            <a href="delete_post.php?id=<?php echo $post['id']; ?>"
                                onclick="return confirm('Bạn có chắc chắn muốn xóa bài này?');">Xóa</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p id="responseMessage" style="display: none"></p>
        </div>
    </div>
</div>

<?php include 'footer.php' ?>