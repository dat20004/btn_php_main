<?php
// Thông tin kết nối cơ sở dữ liệu
$host = 'localhost';
$dbname = 'btl';
$username = 'root';
$password = '';

try {
    // Khởi tạo đối tượng PDO để kết nối cơ sở dữ liệu
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Thiết lập chế độ lỗi cho PDO để ném ra ngoại lệ
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Bắt ngoại lệ nếu kết nối thất bại
    die("Lỗi kết nối: " . $e->getMessage());
}
?>
