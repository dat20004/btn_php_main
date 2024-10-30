<?php include 'header.php';
?>

<div class="content-wrapper subject">
    <section class="content-header">
        <h1>
            Trung tâm thông báo
        </h1>

    </section>

    <!-- Main content -->

    <div class="account">
        <ul class="nav nav-tabs__account" id="myTab">
            <li class="nav-item__account">
                <button class="nav-link__account active" onclick="showTab('image')">Danh sách</button>
            </li>
            <li class="nav-item__account">
                <button class="nav-link__account" onclick="showTab('video')">Lịch sử</button>
            </li>
            <button class="btn btn-primary account-btn"><i class="fa-solid fa-gear"></i> Phân quyền thông báo</button>
            <button class="btn btn-primary">+ Tạo thông báo</button>
        </ul>
        <hr>
        <div class="account-option">
            <button class="active">Hệ thống</button>
            <button>Tất cả</button>
            <button>Tài khoản</button>
            <button>Khóa học</button>
            <button>Lớp học</button>
            <button>Tiến trình </button>
            <button>Đơn hàng</button>
        </div>
        <hr>
        <div class="account-search">
            <div class="account-search__right">
                <form action="" method="POST" role="form">
                    <select name="" id="input" required="required">
                        <option value="">Sắp xếp mặc định</option>
                        <option value="">Sắp xếp mặc định</option>
                        <option value="">Sắp xếp mặc định</option>
                        <option value="">Sắp xếp mặc định</option>
                    </select>
                </form>
            </div>
            <div class="account-search__left">
                <form action="" method="POST" role="form">
                    <button type="submit" class="btn btn-primary">Xuất dữ liệu</button>
                </form>
            </div>
        </div>
        <div class="tab-content__account mt-3" id="myTabContent">
            <div id="image" class="tab-pane__account">
                <div class="mt-3">

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Loại</th>
                                <th>Nội dung</th>
                                <th>Ngày tạo</th>
                                <th>Chi tiết</th>

                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><span>Khóa học</span>
                                </td>
                                <td>Đạt đã nhận bài giảng miễn phí</td>
                                <td>24/09/2024 10:14:45</td>
                                <td>
                                    <a class="btn btn-primary">Chi tiết</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>

            <div id="video" class="tab-pane__account hidden">
                <div class="mt-3">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Loại</th>
                                <th>Nội dung</th>
                                <th>Ngày tạo</th>
                                <th>Chi tiết</th>

                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><span>Khóa học</span>
                                </td>
                                <td>Đạt đã nhận bài giảng miễn phí</td>
                                <td>24/09/2024 10:14:45</td>
                                <td>
                                    <a class="btn btn-primary">Chi tiết</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>


<?php include 'footer.php' ?>