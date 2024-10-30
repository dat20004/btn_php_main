<?php 
include '../connect.php';  // Kết nối với cơ sở dữ liệu

// Lấy id ngành từ URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Kiểm tra nếu ID hợp lệ
    if (is_numeric($id)) {
        // Chuẩn bị câu truy vấn DELETE để xóa ngành
        $query = "DELETE FROM majors WHERE id = :id";
        $stmt = $conn->prepare($query);
        
        // Liên kết tham số với giá trị thực tế
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        // Thực thi truy vấn xóa
        if ($stmt->execute()) {
            // Thông báo xóa thành công và chuyển hướng về danh sách ngành
            header('Location: major.php?success=1');
            exit();  // Ngừng thực thi mã PHP sau khi chuyển hướng
        } else {
            // Thông báo lỗi nếu không xóa thành công
            header('Location: major.php?error=1');
            exit();  // Ngừng thực thi mã PHP sau khi chuyển hướng
        }
    } else {
        // Thông báo lỗi nếu ID không hợp lệ
        header('Location: major.php?error=2');
        exit();  // Ngừng thực thi mã PHP sau khi chuyển hướng
    }
} else {
    // Thông báo lỗi nếu không có ID ngành
    header('Location: major.php?error=3');
    exit();  // Ngừng thực thi mã PHP sau khi chuyển hướng
}
?>
