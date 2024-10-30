CREATE DATABASE IF NOT EXISTS btl;
USE btl;

-- Bảng người dùng
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  	full_name
  name VARCHAR(255) UNIQUE NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) UNIQUE NOT NULL,
  role ENUM ('Student', 'Teacher', 'Admin') DEFAULT 'Student',
  user_info INT,
  state ENUM ('Active', 'Inactive', 'Removed') DEFAULT 'Inactive',
  FOREIGN KEY (user_info) REFERENCES user_infos(id) ON DELETE SET NULL  -- Khóa ngoại liên kết với bảng thông tin người dùng
);


-- Bảng thông tin người dùng
CREATE TABLE user_infos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  fullname VARCHAR(511),
  email VARCHAR(255),
  gender ENUM ('Male', 'Female', 'Others'),
  phone_number VARCHAR(20),
  address VARCHAR(255)
);

-- Bảng ngành học
CREATE TABLE majors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng khóa học
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    fee DECIMAL(10, 2) DEFAULT 0,
    subject_id INT NOT NULL, -- Thêm khóa ngoại nếu cần, ví dụ liên kết với một bảng môn học khác
    major_id INT NOT NULL,
    teacher_id INT NOT NULL,
    start_date DATE,
    end_date DATE,
    FOREIGN KEY (major_id) REFERENCES majors(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng chương học
CREATE TABLE chapters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    course_id INT NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    create_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    update_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng bài học
CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chapter_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    link VARCHAR(511),

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
);

-- Bảng phản hồi khóa học
CREATE TABLE course_feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    student_id INT NOT NULL,
    feedback TEXT,
    rating INT,
    feedback_date DATE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng câu hỏi khóa học
CREATE TABLE course_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    question TEXT NOT NULL,
    state ENUM ('Open', 'Closed', 'Hidden') DEFAULT 'Open',
    create_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    update_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Bảng câu trả lời
CREATE TABLE answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    replier_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    question_id INT NOT NULL,
    create_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    update_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    FOREIGN KEY (replier_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES course_questions(id) ON DELETE CASCADE
);

-- Bảng ghi danh khóa học
CREATE TABLE course_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enroll_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Bảng thông báo
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng bài viết
CREATE TABLE posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng giỏ hàng
CREATE TABLE carts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

INSERT INTO `users` (name, email, password, role, user_info, state) VALUES ('admin', 'admin@gmail.com', '123456', 'Admin', NULL, 'Active');

INSERT INTO users (name, email, password, role, user_info, state) VALUES
('admin', 'admin@gmail.com', '123456', 'Admin', NULL, 'Active'),
('Nguyễn Văn A', 'a@gmail.com', 'password1', 'Student', NULL, 'Active'),
('Trần Thị B', 'b@gmail.com', 'password2', 'Student', NULL, 'Active'),
('Giáo viên A', 'teacher_a@gmail.com', 'password3', 'Teacher', NULL, 'Active'),
('Giáo viên B', 'teacher_b@gmail.com', 'password4', 'Teacher', NULL, 'Active');

-- Bảng thông tin người dùng
INSERT INTO user_infos (fullname, email, gender, phone_number, address) VALUES
('Nguyễn Văn A', 'a@gmail.com', 'Male', '0123456789', 'Hà Nội'),
('Trần Thị B', 'b@gmail.com', 'Female', '0987654321', 'TP.HCM');

-- Bảng ngành học
INSERT INTO majors (name, description) VALUES
('Công nghệ thông tin', 'Ngành học liên quan đến công nghệ thông tin'),
('Kinh doanh', 'Ngành học về quản trị kinh doanh');

-- Bảng khóa học
INSERT INTO courses (name, description, fee, subject_id, major_id, teacher_id, start_date, end_date) VALUES
('Khóa học lập trình PHP', 'Học lập trình PHP từ cơ bản đến nâng cao', 2000000, 1, 1, 1, '2024-01-01', '2024-03-01'),
('Khóa học quản trị doanh nghiệp', 'Khóa học về quản trị và chiến lược doanh nghiệp', 2500000, 2, 2, 2, '2024-02-01', '2024-05-01');

-- Bảng chương học
INSERT INTO chapters (title, description, course_id) VALUES
('Chương 1: Giới thiệu về PHP', 'Tổng quan về ngôn ngữ lập trình PHP', 1),
('Chương 1: Khái niệm cơ bản về doanh nghiệp', 'Tìm hiểu về khái niệm và đặc điểm của doanh nghiệp', 2);

-- Bảng bài học
INSERT INTO lessons (chapter_id, title, content) VALUES
(1, 'Bài học 1: Cài đặt môi trường PHP', 'Hướng dẫn cài đặt môi trường PHP trên máy tính'),
(2, 'Bài học 1: Các loại hình doanh nghiệp', 'Tìm hiểu về các loại hình doanh nghiệp phổ biến');

-- Bảng tài liệu
INSERT INTO materials (lesson_id, name, content, link) VALUES
(1, 'Tài liệu cài đặt PHP', 'Hướng dẫn chi tiết cài đặt PHP', 'http://example.com/install-php'),
(2, 'Tài liệu doanh nghiệp', 'Thông tin về các loại hình doanh nghiệp', 'http://example.com/business');

-- Bảng phản hồi khóa học
INSERT INTO course_feedbacks (course_id, student_id, feedback, rating, feedback_date) VALUES
(1, 1, 'Khóa học rất hữu ích!', 5, '2024-01-15'),
(2, 2, 'Nội dung khóa học cần cải thiện.', 3, '2024-03-20');

-- Bảng câu hỏi khóa học
INSERT INTO course_questions (student_id, course_id, question, state) VALUES
(1, 1, 'Khóa học này có chứng chỉ không?', 'Open'),
(2, 2, 'Có thể học online không?', 'Open');

-- Bảng câu trả lời
INSERT INTO answers (replier_id, name, answer, question_id) VALUES
(1, 'Giáo viên A', 'Có, khóa học sẽ cấp chứng chỉ.', 1),
(2, 'Giáo viên B', 'Có, chúng tôi hỗ trợ học online.', 2);

-- Bảng ghi danh khóa học
INSERT INTO course_enrollments (student_id, course_id) VALUES
(1, 1),
(2, 2);

-- Bảng thông báo
INSERT INTO notifications (user_id, name, message) VALUES
(1, 'Thông báo 1', 'Chúc mừng bạn đã đăng ký thành công khóa học.'),
(2, 'Thông báo 2', 'Bạn đã có phản hồi mới cho khóa học.');

-- Bảng bài viết
INSERT INTO posts (admin_id, title, content) VALUES
(1, 'Thông báo mở lớp mới', 'Chúng tôi sẽ mở lớp học mới vào tháng 5.'),
(1, 'Kết quả khảo sát', 'Kết quả khảo sát ý kiến học viên.');

-- Bảng giỏ hàng
INSERT INTO carts (user_id, course_id, quantity) VALUES
(1, 1, 1),
(2, 2, 1);
