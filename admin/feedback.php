<?php
session_start(); 
include 'header.php'; 
include '../connect.php';

$error_message = "";
$feedbacks = []; // Khởi tạo mảng feedbacks

try {
    // Khởi tạo truy vấn cơ bản
    $sql = "SELECT users.full_name, course_feedbacks.feedback, courses.name AS course_name, course_feedbacks.feedback_date, course_feedbacks.id
            FROM course_feedbacks
            JOIN users ON course_feedbacks.student_id = users.id
            JOIN courses ON course_feedbacks.course_id = courses.id";

    // Kiểm tra xem có tìm kiếm không
    if (isset($_GET['search_key']) && !empty($_GET['search_key'])) {
        $search_key = "%" . $_GET['search_key'] . "%"; // Thêm ký tự % để tìm kiếm tương đối
        $sql .= " WHERE users.full_name LIKE :search_key OR courses.name LIKE :search_key";
    }

    // Chuẩn bị truy vấn
    $stmt = $pdo->prepare($sql);
    
    // Ràng buộc tham số tìm kiếm nếu có
    if (isset($search_key)) {
        $stmt->bindParam(':search_key', $search_key, PDO::PARAM_STR);
    }
    
    // Thực thi truy vấn
    $stmt->execute();

    // Lấy kết quả
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Kiểm tra nếu không có phản hồi
    if (empty($feedbacks)) {
        $error_message = "Không có phản hồi nào trong cơ sở dữ liệu.";
    }

} catch (PDOException $e) {
    $error_message = "Lỗi: " . $e->getMessage();
}
?>

<div class="content-wrapper subject">
    <div class="admin-container">
        <div class="admin-main-content">
            <h1>Tất cả đánh giá từ sinh viên</h1>
            <form action="" method="GET">
                <div class="form-group">
                    <input type="text" name="search_key" class="form-control" placeholder="Tìm kiếm" value="<?php echo isset($_GET['search_key']) ? htmlspecialchars($_GET['search_key']) : ''; ?>">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
            </form>

            <!-- Hiển thị thông báo lỗi nếu có -->
            <?php if (!empty($error_message)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <?php else: ?>
                <table class="feedback-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Sinh viên</th>
                            <th>Nội dung đánh giá</th>
                            <th>Khoá học</th>
                            <th>Ngày đánh giá</th>
                            <th>Cài đặt</th>
                        </tr>
                    </thead>
                    <tbody id="feedbackList">
                        <?php 
                            $index = 1;
                            foreach ($feedbacks as $feedback) {
                                echo "<tr>
                                    <td>{$index}</td>
                                    <td>" . htmlspecialchars($feedback['full_name']) . "</td>
                                    <td>" . nl2br(htmlspecialchars($feedback['feedback'])) . "</td>
                                    <td>" . htmlspecialchars($feedback['course_name']) . "</td>
                                    <td>" . htmlspecialchars($feedback['feedback_date']) . "</td>
                                    <td>
                                    <a href='Delete-feedback.php?id={$feedback['id']}' class='btn btn-danger' onclick=\"return confirm('Bạn có chắc chắn muốn xóa đánh giá này?');\">Xóa</a>
                                    </td>
                                </tr>";
                                $index++;
                            }
                        ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
