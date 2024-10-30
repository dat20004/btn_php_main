<?php
include '../connect.php';  // Kết nối với cơ sở dữ liệu

// Kiểm tra nếu có từ khóa tìm kiếm từ form
if (isset($_GET['search_key']) && !empty($_GET['search_key'])) {
    $search_key = trim($_GET['search_key']);  // Lấy từ khóa tìm kiếm và loại bỏ khoảng trắng

    // Truy vấn cơ sở dữ liệu với từ khóa tìm kiếm
    $query = "SELECT * FROM courses WHERE name LIKE :search_key";
    $stmt = $conn->prepare($query);
    $search_key = '%' . $search_key . '%';  // Thêm dấu "%" để tìm kiếm chứa từ khóa
    $stmt->bindParam(':search_key', $search_key);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Nếu không có từ khóa tìm kiếm, có thể hiển thị thông báo hoặc trả về danh sách tất cả khóa học
    $results = [];
}
?>

<!-- Hiển thị kết quả tìm kiếm -->
<div class="content-wrapper">
    <h1>Kết quả tìm kiếm</h1>

    <?php if (!empty($results)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên khóa học</th>
                <th>Mô tả</th>
                <th>Giá</th>
                <th>Ngành</th>
                <th>Giảng viên</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $course): ?>
            <tr>
                <td><?php echo htmlspecialchars($course['id']); ?></td>
                <td><?php echo htmlspecialchars($course['name']); ?></td>
                <td><?php echo htmlspecialchars($course['description']); ?></td>
                <td><?php echo number_format($course['fee']); ?> VND</td>
                <td>
                    <?php
                            // Lấy ngành của khóa học
                            $field_id = $course['major_id'];
                            $field_query = "SELECT name FROM majors WHERE id = :field_id";
                            $stmt_field = $conn->prepare($field_query);
                            $stmt_field->bindParam(':field_id', $field_id, PDO::PARAM_INT);
                            $stmt_field->execute();
                            $field = $stmt_field->fetch(PDO::FETCH_ASSOC);
                            echo $field ? htmlspecialchars($field['name']) : 'Không xác định';
                            ?>
                </td>
                <td>
                    <?php
                            // Lấy giảng viên của khóa học
                            $teacher_id = $course['teacher_id'];
                            $teacher_query = "SELECT full_name FROM users WHERE id = :teacher_id";
                            $stmt_teacher = $conn->prepare($teacher_query);
                            $stmt_teacher->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
                            $stmt_teacher->execute();
                            $teacher = $stmt_teacher->fetch(PDO::FETCH_ASSOC);
                            echo $teacher ? htmlspecialchars($teacher['full_name']) : 'Không xác định';
                            ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Không tìm thấy kết quả với từ khóa: <?php echo htmlspecialchars($search_key); ?></p>
    <?php endif; ?>
</div>