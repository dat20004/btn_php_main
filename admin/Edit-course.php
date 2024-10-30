<?php
include 'header.php'; 
include '../connect.php';  // Kết nối với cơ sở dữ liệu

// Lấy ID khóa học từ URL
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Biến lưu thông báo lỗi và thành công
$errors = [];
$success = "";

// Kiểm tra nếu ID hợp lệ
if ($course_id > 0) {
    // Truy vấn để lấy thông tin khóa học từ cơ sở dữ liệu
    $query = "SELECT * FROM courses WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $course_id, PDO::PARAM_INT);
    $stmt->execute();
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra nếu khóa học tồn tại
    if (!$course) {
        $errors[] = "Khóa học không tồn tại.";
    }
} else {
    $errors[] = "ID khóa học không hợp lệ.";
}

// Xử lý khi form được gửi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy thông tin từ form
    $course_name = trim($_POST['courseName']);
    $course_desc = trim($_POST['courseDesc']);
    $course_field = trim($_POST['courseField']);
    $course_teacher = trim($_POST['courseTeacher']);
    $course_price = trim($_POST['coursePrice']);
    $image = $course['image'];  // Giữ lại ảnh cũ nếu không thay đổi
    if (empty($course_price) || $course_price < 0) {
        $errors[] = "Giá khóa học không hợp lệ.";
    }
    // Kiểm tra các trường không được để trống
    if (empty($course_name)) {
        $errors[] = "Tên khóa học không được để trống.";
    }
    if (empty($course_desc)) {
        $errors[] = "Mô tả khóa học không được để trống.";
    }
    if (empty($course_field)) {
        $errors[] = "Ngành học không được để trống.";
    }

    // Kiểm tra nếu tên khóa học đã tồn tại (trừ khóa học hiện tại)
    $check_query = "SELECT COUNT(*) FROM courses WHERE name = :name AND id != :id";
    $stmt = $pdo->prepare($check_query);
    $stmt->bindParam(':name', $course_name);
    $stmt->bindParam(':id', $course_id, PDO::PARAM_INT);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $errors[] = "Tên khóa học đã tồn tại. Vui lòng chọn tên khác.";
    }

    // Kiểm tra nếu có thay đổi ảnh
    if (isset($_FILES['courseImage']) && $_FILES['courseImage']['error'] == 0) {
        $image_name = $_FILES['courseImage']['name'];
        $image_tmp = $_FILES['courseImage']['tmp_name'];
        $image_size = $_FILES['courseImage']['size'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
        
        // Các loại file ảnh hợp lệ
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($image_ext), $allowed_exts)) {
            $errors[] = "Ảnh phải có định dạng: jpg, jpeg, png, gif.";
        }
        
        if ($image_size > 100 * 1024 * 1024) {  // Giới hạn ảnh dưới 5MB
            $errors[] = "Ảnh không được vượt quá 5MB.";
        }

        // Nếu ảnh hợp lệ, lưu ảnh
        if (empty($errors)) {
            $image_dir = 'uploads/';
            if (!is_dir($image_dir)) {
                mkdir($image_dir, 0777, true);
            }

            $new_image_path = $image_dir . time() . '.' . $image_ext;
            if (move_uploaded_file($image_tmp, $new_image_path)) {
                // Cập nhật ảnh mới
                $image = $new_image_path;
            } else {
                $errors[] = "Không thể tải ảnh lên. Vui lòng thử lại.";
            }
        }
    }

    // Nếu không có lỗi, thực hiện cập nhật khóa học vào cơ sở dữ liệu
    if (empty($errors)) {
        $update_query = "UPDATE courses SET 
            name = :name, 
            description = :description, 
            major_id = :major_id, 
            teacher_id = :teacher_id, 
            fee = :fee, 
            image = :image 
            WHERE id = :id";

        $stmt = $pdo->prepare($update_query);

        // Liên kết các tham số
        $stmt->bindParam(':name', $course_name);
        $stmt->bindParam(':description', $course_desc);
        $stmt->bindParam(':major_id', $course_field);  // Chú ý sửa lại tên tham số
        $stmt->bindParam(':teacher_id', $course_teacher);  // Chú ý sửa lại tên tham số
        $stmt->bindParam(':fee', $course_price);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':id', $course_id, PDO::PARAM_INT);

        // Thực thi câu truy vấn
        if ($stmt->execute()) {
            $_SESSION['success'] = "Khóa học đã được cập nhật thành công.";
            header("Location: course.php");
            exit();
        } else {
            $_SESSION['error'] = "Lỗi khi cập nhật khóa học.";
            header("Location: editCourse.php?id=" . $course_id);
            exit();
        }
    }
}

// Lấy danh sách ngành
try {
    $sql = "SELECT id, name FROM majors";  
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($fields)) {
        echo "Không có dữ liệu ngành.";
    }
} catch (PDOException $e) {
    echo "Lỗi khi lấy danh sách ngành: " . $e->getMessage();
}

// Lấy danh sách giảng viên từ bảng users với role là 'Teacher'
try {
    $sqlTeachers = "SELECT id, full_name FROM users WHERE role = 'Teacher'";
    $stmtTeachers = $pdo->prepare($sqlTeachers);
    $stmtTeachers->execute();
    $teachers = $stmtTeachers->fetchAll(PDO::FETCH_ASSOC);
    if (empty($teachers)) {
        echo "Không có giảng viên nào.";
    }
} catch (PDOException $e) {
    echo "Lỗi khi lấy danh sách giảng viên: " . $e->getMessage();
}
?>

<!-- Hiển thị thông báo lỗi nếu có -->
<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul>
        <?php foreach ($errors as $error): ?>
        <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- Hiển thị thông báo thành công nếu có -->
<?php if (!empty($success)): ?>
<div class="alert alert-success">
    <?php echo htmlspecialchars($success); ?>
</div>
<?php endif; ?>

<!-- Form sửa khóa học -->
<div class="content-wrapper subject">
    <div id="courseForm edit-courseForm" class="courseForm edit-courseForm">
        <h1>Sửa khóa học</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <div>
                <label for="courseName">Tên khóa học:</label><br>
                <input class="form-control" type="text" id="courseName" name="courseName"
                    value="<?php echo htmlspecialchars($course['name']); ?>" placeholder="Nhập tên khóa học">
            </div>

            <div>
                <label for="courseDesc">Mô tả khóa học:</label><br>
                <textarea class="form-control" id="courseDesc" name="courseDesc"
                    placeholder="Nhập mô tả"><?php echo htmlspecialchars($course['description']); ?></textarea>
            </div>

            <div>
                <label for="courseField">Ngành:</label><br>
                <select name="courseField" id="courseField" required>
                    <option value="">Chọn ngành</option>
                    <?php
                    foreach ($fields as $field) {
                        $selected = ($course['major_id'] == $field['id']) ? 'selected' : '';
                        echo "<option value='" . $field['id'] . "' $selected>" . htmlspecialchars($field['name']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div>
                <label for="courseTeacher">Giảng viên:</label><br>
                <select name="courseTeacher" id="courseTeacher" required>
                    <option value="">Chọn giảng viên</option>
                    <?php
                    foreach ($teachers as $teacher) {
                        $selected = ($course['teacher_id'] == $teacher['id']) ? 'selected' : '';
                        echo "<option value='" . $teacher['id'] . "' $selected>" . htmlspecialchars($teacher['full_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div>
                <label for="coursePrice">Giá khóa học:</label><br>
                <input class="form-control" type="text" id="coursePrice" name="coursePrice"
                    value="<?php echo htmlspecialchars($course['fee']); ?>" placeholder="Nhập giá khóa học" required>
            </div>


            <div>
                <label for="courseImage">Ảnh khóa học:</label><br>
                <input type="file" name="courseImage" id="courseImage" accept="image/*">
            </div>
            <div>
                <button type="submit">Cập nhật</button>
                <button type="button" class="btn btn-secondary back-button" onclick="window.history.back()">Huỷ</button>
            </div>


        </form>
    </div>
</div>

<?php
// include 'footer.php'; 
?>