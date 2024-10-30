<?php include 'header.php';
?>

<div class="content-wrapper subject">
    <section class="content-header">
        <h1>
            Quản lý tài khoản
        </h1>

    </section>

    <!-- Main content -->

    <div class="account">
        <ul class="nav nav-tabs__account" id="myTab">
            <li class="nav-item__account">
                <button class="nav-link__account active" onclick="showTab('image')">Danh sách</button>
            </li>
            <li class="nav-item__account">
                <button class="nav-link__account" onclick="showTab('video')">Tìm kiếm</button>
            </li>
        </ul>
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
                    <select name="" id="input" required="required">
                        <option value="">Toàn bộ thời gian</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Lọc</button>
                </form>
            </div>
            <div class="account-search__left">
                <button class="btn btn-primary"><a href="new-product.php" type="submit">+ Đơn hàng mới</a></button>
                <button type="submit" class="btn btn-primary">Xuất dữ liệu</button>
            </div>
        </div>
        <div class="tab-content__account mt-3" id="myTabContent">
            <div id="image" class="tab-pane__account">
                <div class="mt-3">

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>TT</th>
                                <th>Tên khách hàng</th>
                                <th>Khóa học</th>
                                <th>Thanh toán</th>
                                <th>Hình thức</th>
                                <th>Ngày mua </th>
                                <th>Trạng thái</th>
                                <th>Chi tiết</th>

                            </tr>
                        </thead>
                        <tbody>
                            <tr>

                                <td>1</td>
                                <td></td>
                                <td>Nguyễn Văn Đạt</td>
                                <td>dat@gmail.com</td>
                                <td>0</td>
                                <td>24/09/2024</td>
                                <td>1</td>
                                <td>5</td>
                                <td><i class="fa-solid fa-ellipsis-vertical"></i></td>
                            </tr>
                            <tr>

                                <td>1</td>
                                <td></td>
                                <td>Nguyễn Văn Đạt</td>
                                <td>dat@gmail.com</td>
                                <td>0</td>
                                <td>24/09/2024</td>
                                <td>1</td>
                                <td>5</td>
                                <td><i class="fa-solid fa-ellipsis-vertical"></i></td>
                            </tr>
                            <tr>

                                <td>1</td>
                                <td></td>
                                <td>Nguyễn Văn Đạt</td>
                                <td>dat@gmail.com</td>
                                <td>0</td>
                                <td>24/09/2024</td>
                                <td>1</td>
                                <td>5</td>
                                <td><i class="fa-solid fa-ellipsis-vertical"></i></td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>

            <div id="video" class="tab-pane__account hidden">
                <div class="mt-3">
                    <div class="input-group">
                        <input type="text" name="" placeholder="Tìm kiếm" id="">
                        <button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
                        <button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><input type="checkbox" name="" id=""></th>
                                <th>STT</th>
                                <th>TT</th>
                                <th>Tên khách hàng</th>
                                <th>Email</th>
                                <th>Nhóm</th>
                                <th>Ngày tạo tài khoản</th>
                                <th>Khóa học đã mua</th>
                                <th>Giỏ hàng</th>
                                <th>Cài đặt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="checkbox" name="" id=""></td>
                                <td>1</td>
                                <td></td>
                                <td>Nguyễn Văn Đạt</td>
                                <td>dat@gmail.com</td>
                                <td>0</td>
                                <td>24/09/2024</td>
                                <td>1</td>
                                <td>5</td>
                                <td><i class="fa-solid fa-ellipsis-vertical"></i></td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="" id=""></td>
                                <td>1</td>
                                <td></td>
                                <td>Nguyễn Văn Đạt</td>
                                <td>dat@gmail.com</td>
                                <td>0</td>
                                <td>24/09/2024</td>
                                <td>1</td>
                                <td>5</td>
                                <td><i class="fa-solid fa-ellipsis-vertical"></i></td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="" id=""></td>
                                <td>1</td>
                                <td></td>
                                <td>Nguyễn Văn Đạt</td>
                                <td>dat@gmail.com</td>
                                <td>0</td>
                                <td>24/09/2024</td>
                                <td>1</td>
                                <td>5</td>
                                <td><i class="fa-solid fa-ellipsis-vertical"></i></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include 'footer.php' ?>