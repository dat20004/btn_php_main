<?php 
include 'header.php'; 
include '../connect.php';  // Kết nối với cơ sở dữ liệu

// Lấy ID ngành từ URL
$major_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Biến để lưu thông báo lỗi
$errors = [];

// Kiểm tra nếu ID ngành hợp lệ
if ($major_id > 0) {
    // Truy vấn để lấy thông tin ngành từ cơ sở dữ liệu
    $query = "SELECT * FROM majors WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $major_id, PDO::PARAM_INT);
    $stmt->execute();
    $major = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra nếu ngành tồn tại
    if (!$major) {
        $errors[] = "Ngành không tồn tại.";
    }
} else {
    $errors[] = "ID ngành không hợp lệ.";
}

// Xử lý khi form được gửi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $image = isset($_POST['image']) ? trim($_POST['image']) : $major['image'];

    // Kiểm tra tên ngành và mô tả không rỗng
    if (empty($name)) {
        $errors[] = "Tên ngành không được để trống.";
    }
    if (empty($description)) {
        $errors[] = "Mô tả ngành không được để trống.";
    }

    // Kiểm tra lỗi và thực hiện cập nhật nếu không có lỗi
    if (empty($errors)) {
        // Nếu có file ảnh mới được tải lên, xử lý upload ảnh
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $imagePath = 'uploads/' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                $image = $imagePath;  // Cập nhật ảnh mới
            } else {
                $errors[] = "Lỗi khi tải ảnh lên.";
            }
        }

        // Cập nhật ngành vào cơ sở dữ liệu
        if (empty($errors)) {
            $query = "UPDATE majors SET name = :name, description = :description, img = :img WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':img', $image);
            $stmt->bindParam(':id', $major_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount()) {
                // Thành công, chuyển hướng về danh sách ngành
                header('Location: major.php');
                exit;
            } else {
                $errors[] = "Không có thay đổi nào hoặc có lỗi khi cập nhật.";
            }
        }
    }
}
?>
<style>
/* Đảm bảo nút căn ngang hàng */
.form-actions {
    display: flex;
    justify-content: center;
    /* Căn 2 nút từ trái */
    gap: 10px;
    /* Khoảng cách giữa các nút */
    margin-top: 20px;
    /* Khoảng cách phía trên */
}

/* Tùy chỉnh kiểu dáng của nút */
.submit-button,
.back-button {
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
    border: none;
    border-radius: 5px;
}

/* Nút tạo */
.submit-button {
    background-color: #4CAF50;
    /* Màu xanh lá */
    color: white;
}

/* Nút quay lại */
.back-button {
    background-color: #f44336;
    /* Màu đỏ */
    color: white;
}

/* Thêm hiệu ứng hover cho nút */
.submit-button:hover {
    background-color: #45a049;
}

.back-button:hover {
    background-color: #e53935;
}
</style>
<div class="content-wrapper">
    <div class="container">
        <div class="edit-major__center">
            <div class="edit-major">
                <h1>Sửa ngành</h1>

                <!-- Hiển thị lỗi nếu có -->
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Form sửa ngành -->
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="major-name">Tên ngành</label>
                        <input type="text" id="major-name" name="name"
                            value="<?php echo htmlspecialchars($major['name']); ?>" placeholder="Nhập tên ngành"
                            class="form-control" />
                    </div>
                    <div class="form-group">
                        <label for="description">Mô tả</label>
                        <textarea id="description" name="description" rows="4" placeholder="Nhập mô tả ngành"
                            class="form-control"><?php echo htmlspecialchars($major['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="upload-image">Hình ảnh</label>
                        <div class="file-input">
                            <button class="upload-button" type="button" id="upload-image">
                                Tải ảnh lên
                            </button>
                            <input type="file" id="file-upload" name="image" accept="image/*"
                                onchange="previewImage()" />
                            <?php if (!empty($major['image'])): ?>
                            <p>Ảnh hiện tại: <img src="<?php echo htmlspecialchars($major['image']); ?>"
                                    alt="Hình ảnh ngành" style="max-width: 100px; max-height: 100px;"></p>
                            <?php endif; ?>
                        </div>
                        <div id="image-preview" style="margin-top: 10px;">
                            <!-- Ảnh xem trước sẽ được hiển thị ở đây -->
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="submit-button">Cập nhật</button>
                        <button type="button" class="back-button" onclick="window.history.back()">Huỷ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage() {
    const file = document.getElementById('file-upload').files[0];
    const preview = document.getElementById('image-preview');
    const reader = new FileReader();

    reader.onload = function(e) {
        const imageUrl = e.target.result;
        // Tạo một thẻ img mới để hiển thị ảnh
        preview.innerHTML =
            `<img src="${imageUrl}" alt="Ảnh xem trước" style="max-width: 100px; max-height: 100px;" />`;
    };

    if (file) {
        reader.readAsDataURL(file);
    }
}
</script>

<?php include 'footer.php'; ?>