<?php 
include 'header.php';
include '../connect.php';  // Kết nối với cơ sở dữ liệu

// Biến để lưu thông báo lỗi
$errors = [];

// Biến để lưu danh sách ngành
$result = [];

// Kiểm tra xem có tìm kiếm không
if (isset($_POST['search_name']) && !empty(trim($_POST['search_name']))) {
    $search_name = trim($_POST['search_name']);
    
    // Truy vấn danh sách ngành theo tên
    $query = "SELECT * FROM majors WHERE name LIKE :name";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['name' => '%' . $search_name . '%']);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); // Lấy tất cả kết quả vào mảng
} else {
    // Truy vấn danh sách ngành nếu không tìm kiếm
    $query = "SELECT * FROM majors";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); // Lấy tất cả kết quả vào mảng
}
?>


<div class="content-wrapper">
    <div class="container">
        <div class="major-admin">
            <div class="major-admin__header d-flex justify-content-between align-items-center">
                <h1>Danh sách Ngành</h1>
                <a href="add-major.php" class="btn btn-primary">Tạo ngành mới</a>
            </div>

            <div class="major-admin__search mb-3">
                <form action="" method="POST" role="form" class="d-flex justify-content-between align-items-center">
                    <div class="form-group mb-0">
                        <input type="text" placeholder="Nhập tên ngành" name="search_name" class="form-control"
                            style="width: 250px;">
                    </div>
                    <button type="submit" class="btn btn-search ml-2"><i
                            class="fa-solid fa-magnifying-glass"></i></button>
                </form>
                <!-- <button id="btnRefresh" class="btn btn-secondary" onclick="resetSearch()">Làm mới</button>
                <button id="btnRefresh" class="btn btn-warning">Import</button> -->
            </div>
            <button id="btnRefresh" class="btn btn-secondary" onclick="resetSearch()">Làm mới</button>
            <button id="btnRefresh" class="btn btn-warning">Import</button>
            <h2>Danh sách ngành</h2>

            <!-- Thông báo nếu xóa thành công -->
            <?php //if (isset($_GET['success'])): ?>
            <!-- <div class="alert alert-success">
                    <strong> Ngành đã được thêm thành công.</strong>
                </div> -->
            <?php //elseif (isset($_GET['error'])): ?>
            <!-- <div class="alert alert-danger">
                    <strong>Thất bại!</strong> Có lỗi khi xóa ngành, vui lòng thử lại.
                </div> -->
            <?php //endif; ?>
            <table class="table table-bordered table-striped table-hover" style="font-size: 23px;">
                <thead class="thead-dark">
                    <tr>
                        <th>STT</th>
                        <th>Hình ảnh</th>
                        <th>Tên ngành</th>
                        <th>Mô tả</th>
                        <th>Cài đặt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($result) > 0): ?>
                    <?php foreach ($result as $index => $row): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <?php if (!empty($row['img'])): ?>
                            <!-- Đảm bảo hình ảnh hiển thị với kích thước hợp lý -->
                            <img src="<?php echo htmlspecialchars($row['img']); ?>" alt="Hình ảnh ngành"
                                class="img-thumbnail" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                            <?php else: ?>
                            <img src="./dist/img/avatar2.png" alt="Ảnh mặc định" class="img-thumbnail"
                                style="max-width: 100px; max-height: 100px; object-fit: cover;">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td>
                            <a href="edit-major.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                            <a href="delete-major.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                                onclick="return confirm('Bạn có chắc chắn muốn xóa?');">Xóa</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Không có ngành nào.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<?php include 'footer.php'; ?>