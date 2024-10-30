<?php 
include 'header.php'; 
include '../connect.php'; // Kết nối cơ sở dữ liệu
$searchKey = $_GET['search_key'] ?? '';
// Lấy danh sách khóa học từ cơ sở dữ liệu
try {
    $sql = "SELECT courses.id, courses.name, courses.description, courses.fee, users.full_name AS teacher, majors.name AS major
    FROM courses
    LEFT JOIN users ON courses.teacher_id = users.id
    LEFT JOIN majors ON courses.major_id = majors.id";  
    if ($searchKey) {
            $sql .= " WHERE courses.name LIKE :search_key";
            }
    $stmt = $pdo->prepare($sql);
    if ($searchKey) {
        $stmt->bindValue(':search_key', '%' . $searchKey . '%');
    }
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi khi lấy danh sách khóa học: " . $e->getMessage();
}
?>
<style>
    .table {
        width: 100%;
        table-layout: fixed; 
    }

    .table th {
        width: 30%; /* Gần 1/7 cho mỗi cột */
        /* text-align: center; */
    }
</style>
<div class="content-wrapper subject">
    <section class="content-header">
        <h1>
            Khóa học
        </h1>
    </section>

    <section class="content-header d-flex justify-content-between subject-head">
        <h1></h1>
        <a class="btn btn-primary" href="addCourse.php" id="addCourseBtn">+ Thêm mới khóa học</a>
    </section>

    <!-- Main content -->
    <section class="content subject">
        <div class="box">
            <div class="box-body" style="font-size:20px;">
                <form action="" method="GET" class="form-inline">
                    <div class="radio">
                        <label>
                            <input type="radio" name="" id="input" value="" checked="checked">
                            Tất cả
                        </label>
                    </div>

                    <form action="" method="GET">
                        <div class="form-group">
                            <input type="text" name="search_key" class="form-control" placeholder="Tìm kiếm">
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                    </form>

                </form>
                <button id="btnRefresh" class="btn btn-secondary" onclick="resetSearch()">Làm mới</button>
                <button id="btnRefresh" class="btn btn-warning">Import</button>
                <p>Danh sách</p>

                <!-- Hiển thị thông báo thành công và lỗi -->
                <?php
                if (isset($_SESSION['success'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                    unset($_SESSION['success']); // Xóa thông báo sau khi hiển thị
                }
                if (isset($_SESSION['error'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                    unset($_SESSION['error']); // Xóa thông báo sau khi hiển thị
                }
                ?>

                <!-- Bắt đầu bảng danh sách khóa học -->
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Tên khóa học</th>
                            <th>Mô tả</th>
                            <th>Ngành học</th>
                            <th>Học phí</th>
                            <th>Giảng viên</th>
                            <th>Cài đặt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($courses)) {
                            $index = 1;
                            foreach ($courses as $course) {
                                echo "<tr>
                                    <td>{$index}</td>
                                    <td>{$course['name']}</td>
                                    <td>{$course['description']}</td>
                                    <td>{$course['major']}</td>
                                    <td>{$course['fee']}</td>
                                    <td>{$course['teacher']}</td>
                                    <td>
                                        <a href='Edit-course.php?id={$course['id']}' class='btn btn-success'>Sửa</a>
                                        <a href='Delete-course.php?id={$course['id']}' class='btn btn-danger' onclick=\"return confirm('Bạn có chắc chắn muốn xóa khóa học này?');\">Xóa</a>
                                    </td>
                                </tr>";
                                $index++;
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>Không có khóa học nào.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <!-- Kết thúc bảng danh sách khóa học -->

            </div>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>